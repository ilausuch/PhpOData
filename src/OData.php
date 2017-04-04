<?php

/**
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
**/

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("db/ODataDBAdapter.php");
require_once("db/ODataMysql.php");
require_once("scheme/ODataScheme.php");
require_once("scheme/ODataSchemeEntity.php");
require_once("scheme/ODataSchemeEntityField.php");
require_once("io/ODataHTTP.php");
require_once("io/ODataRequest.php");
require_once("io/ODataResponse.php");
require_once("query/ODataQuery.php");
require_once("query/ODataQueryFilterAggregator.php");
require_once("query/ODataQueryFilterComparator.php");
require_once("query/ODataQueryFilterOperation.php");
require_once("config/ODataOptions.php");

class OData{

    private $config;
    private $db;
    private $scheme;
    
    function __construct (ODataDBAdapter $db,ODataOptions $config,ODataScheme $scheme=null){
        $this->db=$db;
        $this->config=$config;
        $this->scheme=$scheme;
    }
    
    private function allowAnyOrigin(){
        if (isset($this->config->allowAnyOrigin) && $this->config->allowAnyOrigin && isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
    }
    
    private function enableOptionsRequest(){
        if (isset($this->config->enableOptionsRequest) && $this->config->enableOptionsRequest && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }

            exit(0);
        }
    }
    
    private function checkCli(){
        if (strcmp(PHP_SAPI, 'cli') === 0){
            exit(' should not be run from CLI.' . PHP_EOL);
        }
    }
    
    private function checkClients(){
        if ((empty($this->config->clients) !== true) 
                && (in_array($_SERVER['REMOTE_ADDR'], (array) $this->config->clients) !== true)){
            ODataHTTP::error(ODataHTTP::E_forbidden,"Cannot perform this operation from this url");
        }
    }
    
    private function queryMethodModifiers(){
        if ($this->config->allowQueryMethodModifiers){
            if (array_key_exists('HTTP_METHOD', $_GET) === true)
            {
                    $_SERVER['REQUEST_METHOD'] = strtoupper(trim($_GET['HTTP_METHOD']));
            }
            else if (array_key_exists('HTTP_X_HTTP_METHOD_OVERRIDE', $_SERVER) === true)
            {
                    $_SERVER['REQUEST_METHOD'] = strtoupper(trim($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']));
            }
        }
    }
    
    private function checkAuth($entity){
        if ($this->config->checkAuth($_SERVER['REQUEST_METHOD'],$entity))
            ODataHTTP::error(ODataHTTP::E_unauthorized,"You must autenticate first");
    }
    
    private function serve(ODataRequest $request, ODataResponse $response){
        
        //Check auth
        //TODO $this->checkAuth();
        
        switch($request->getMethod()){
            case "GET":
                $this->serveGet($request,$response);
            break;
            case "POST":
                $this->servePost($request,$response);
            break;
            case "PATCH":
                $this->servePatch($request,$response);
            break;
            default:
                ODataHTTP::error(ODataHTTP::E_not_implemented, $request->getMethod(). "method is not implememn");
        }
        
    }
    
    private function serveGet(ODataRequest $request, ODataResponse $response){
        
        //Get table from entity
        $table=$this->config->entityAlias($request->getEntity());
            
        //Get scheme
        /* @var $tableScheme ODataSchemeEntity */
        $tableScheme=$this->db->discoverTableScheme($table);
        
        //TODO: Ofuscade Id
        
        //Check if operation is allowed
        if (!$this->config->allow($request))
            ODataHTTP::error(ODataHTTP::E_forbidden,"Cannot do this operation");
	
        //Init query
	$query=new ODataQuery($table);
        
        //Add where elements for primary key
        $schemePk=$tableScheme->getPk();
        
        if (count($schemePk)==0){
            ODataHTTP::error(ODataHTTP::E_internal_error,"Table ".$table." has not Primary key");
        }
        else{
            if (count($schemePk)==1){
                if (count($request->getPk())>0)
                    $query->addWhere($schemePk[0]->getName(),$request->getPk()[0]);
            }
            else{
                //TODO : Prepare WHERE with multiples Ids
                ODataHTTP::error(ODataHTTP::E_not_implemented,"Multiple primary keys are not implemented yet.");
            }
        }
        
        //Setup filter
        if ($request->getFilter()!=null)
            $query->setFilter($request->getFilter());
        
        if ($request->getTop()!=null)
            $query->setTop($request->getTop());
        
        if ($request->getSkip()!=null)
            $query->setSkip($request->getSkip());

        try{
            $result=$this->db->query($query);
            ODataHTTP::successArray($result);
        }catch(Exception $e){
            echo "<pre>";
            print_r($e->getMessage());
            die();
        }
    }
    
    private function servePost(ODataRequest $request, ODataResponse $response){
        // Allow from any origin
        $this->allowAnyOrigin();
        
        //Get table from entity
        $table=$this->config->entityAlias($request->getEntity());
            
        //Get scheme
        /* @var $tableScheme ODataSchemeEntity */
        $tableScheme=$this->db->discoverTableScheme($table);
        
        //Check if operation is allowed
        if (!$this->config->allow($request))
            ODataHTTP::error(ODataHTTP::E_unauthorized,"Cannot perform this operation");
        
        //Check body and extract element
        $element=$request->getBody();
        $element= json_decode($element,true);
        
        //TODO: Check element
        //$tableScheme->checkNewElement($element);
        
        //Send to DB
        try{
            $result=$this->db->insert($element,$table);
            ODataHTTP::successModifiedElement($result);
        }catch(Exception $ex){
            ODataHTTP::errorException($ex);
        }
    }
    
    private function servePatch(ODataRequest $request, ODataResponse $response){
        // Allow from any origin
        $this->allowAnyOrigin();
        
        //Get table from entity
        $table=$this->config->entityAlias($request->getEntity());
            
        //Get scheme
        /* @var $tableScheme ODataSchemeEntity */
        $tableScheme=$this->db->discoverTableScheme($table);
        
        //Check if operation is allowed
        if (!$this->config->allow($request))
            ODataHTTP::error(ODataHTTP::E_unauthorized,"Cannot perform this operation");
            
        //Check body and extract element
        $element=$request->getBody();
        $element= json_decode($element,true);
        
        
        //TODO: Check element
        //$tableScheme->checkNewElement($element);
        
        //Send to DB
        try{
            $result=$this->db->update($element,$table);
            ODataHTTP::successModifiedElement($result);
        }catch(Exception $ex){
            ODataHTTP::errorException($ex);
        }
    }
    
    public function execute(){
        // Allow from any origin
        $this->allowAnyOrigin();
        
        $app = new \Slim\App;
        
        $container=$app->getContainer();
        $container["odata"]=$this;
        
        $app->any('/odata/{entityStr}', function (Request $requestSlim, Response $responseSlim,$args) {
            $request = new ODataRequest($requestSlim);
            $response= new ODataResponse($responseSlim);
            
            
            // Access-Control headers are received during OPTIONS requests
            //$this->enableOptionsRequest();

            //Check cli
            //$this->checkCli();

            //Check clients
            //$this->checkClients();

            //Query options modifiers
            //$this->queryMethodModifiers();
            
            //Serve
            $this->odata->serve($request,$response);
            
            return $response->responseSlim;
        });
        
        $app->run();
    }
}

