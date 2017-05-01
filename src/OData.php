<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once("db/ODataDBAdapter.php");
require_once("db/ODataMysql.php");
require_once("scheme/ODataScheme.php");
require_once("scheme/ODataSchemeEntity.php");
require_once("scheme/ODataSchemeEntityField.php");
require_once("scheme/ODataSchemeEntityAssociation.php");
require_once("scheme/ODataSchemeEntityAssociationRelationField.php");
require_once("scheme/ODataSchemePrimitive.php");
require_once("io/ODataRequest.php");
require_once("io/ODataRequestHTTP.php");
require_once("io/ODataRequestORM.php");
require_once("io/ODataResponse.php");
require_once("io/ODataResponseHTTP.php");
require_once("query/ODataQuery.php");
require_once("query/ODataQueryFilterBase.php");
require_once("query/ODataQueryFilterAggregator.php");
require_once("query/ODataQueryFilterComparator.php");
require_once("query/ODataQueryFilterOperation.php");
require_once("query/ODataQueryExpand.php");
require_once("query/ODataQueryOrderBy.php");
require_once("query/ODataQueryOrderByList.php");
require_once("config/ODataOptions.php");
require_once("tools/ODataCrypt.php");
require_once("tools/ODataSchemeEntityGeneralTools.php");
require_once("tools/ODataFilterParser.php");
require_once("tools/ODataContext.php");


class OData{
    
    /**
     * Unique OData object
     * @var OData OData
     */
    public static $object;
    
    /**
     * The configuration options
     * @var ODataOptions 
     */
    protected $config;
    
    /**
     * the DB adapter
     * @var ODataDBAdapter
     */
    protected $db;
    
    /**
     * The scheme
     * @var ODataScheme
     */
    protected $scheme;
    
    /**
     * The context
     * @var ODataContext
     */
    private $context;


    /**
     * Constructor
     * @param ODataDBAdapter $db Datadase adapter
     * @param ODataOptions $config OData configuration
     * @param ODataScheme $scheme Scheme configuration
     */
    function __construct (ODataDBAdapter $db,ODataOptions $config,ODataScheme $scheme=null){
        $this->db=$db;
        $this->config=$config;
        $this->scheme=$scheme;
        $this->context=new ODataContext();
        OData::$object=$this;
    }
    
    /**
     * Returns the scheme
     * @return ODataScheme 
     */
    public function getScheme(){
        return $this->scheme;
    }
    
    /**
     * Returns the DB adapter
     * @return ODataDBAdapter
     */
    public function getDb(){
        return $this->db;
    }
    
    /**
     * Returns the configuration object
     * @return ODataOptions
     */
    public function getConfig(){
        return $this->config;
    }
    
    /**
     * Returns the context
     * @return ODataContext
     */
    public function getContext(){
        return $this->context;
    }
    
    /**
     * Returns a complete Entity Scheme convining configured scheme and DB scheme
     * @param string $entityName
     * @return ODataSchemeEntity
     */
    public function getEntityScheme($entityName){
        $scheme=$this->scheme->getEntity($entityName);
        
        if (isset($scheme))
            return $scheme;
        else
            throw new Exception("Scheme for {$entityName} isn't defined", ODataResponseHTTP::E_not_implemented);
    }
    
    /**
     * Execute the service
     * @param boolean $debug If is in debug mode or not
     */
    public function run($debug=false){
        $app = new \Slim\App([
            "debug"=>$debug
        ]);
        
        $container=$app->getContainer();
        $container["odata"]=$this;
        
        $this->setSlimRule($app);
        
        $app->run();
    }
    
    /**
     * If you already have an Slim app created, you can created the rule directly
     * @param SlimApp $app
     */
    public function setSlimRule($app){
        $app->any('/odata/{entityQueryStr}', function (Request $requestSlim, Response $responseSlim,$args) {
            return OData::$object->execute($requestSlim, $responseSlim, $args);
        });
    }
    
    /**
     * Execute OData query without HTTP output
     * @param ODataRequest $request
     * @return ODataResponse
     */
    public function executeLocal(ODataRequest $request){
        $response=new ODataResponse();
        $this->serve($request,$response);
        return $response;
    }
    
    /**
     * Internal execution function
     * @param Request $requestSlim
     * @param Response $responseSlim
     * @param type $args
     */
    protected function execute(Request $requestSlim, Response $responseSlim,$args){
        
        //Check cli
        $this->checkCli();

        // Allow from any origin
        $this->allowAnyOrigin();

        // Access-Control headers are received during OPTIONS requests
        $this->enableOptionsRequest();

        //Check clients
        $this->checkClients();

        //Query options modifiers
        $this->queryMethodModifiers();

        //Prepare request
        $request=new ODataRequestHTTP($requestSlim);
        //Run server
        try{
            $response=$this->executeLocal($request);
            
            switch($request->getMethod()){
                case ODataRequest::METHOD_GET:
                    ODataResponseHTTP::successArray($response->getData());
                    break;
                case ODataRequest::METHOD_POST:
                    ODataResponseHTTP::successCreatedElement($response->getData());
                    break;
                case ODataRequest::METHOD_PATCH:
                    ODataResponseHTTP::successModifiedElement($response->getData());
                    break;
                case ODataRequest::METHOD_DELETE:
                    ODataResponseHTTP::successDeletedElement();
                    break;
            }
        }catch(Exception $e){
            ODataResponseHTTP::errorException($e);
        }
        
    }
    
   
    
    /**
     * Serves a request
     * @param ODataRequest $request
     */
    protected function serve(ODataRequest $request, ODataResponse $response){
        
        //Check auth
        //TODO $this->checkAuth();
        
        switch($request->getMethod()){
            case ODataRequest::METHOD_GET:
                $this->serveGet($request, $response);
            break;
            case ODataRequest::METHOD_POST:
                $this->servePost($request, $response);
            break;
            case ODataRequest::METHOD_PATCH:
                $this->servePatch($request, $response);
            break;
            case ODataRequest::METHOD_DELETE:
                $this->serveDelete($request, $response);
            break;
            default:
                $response->error(ODataResponse::E_not_implemented, $request->getMethod(). "method is not implememn");
        }
        
    }
    
    /**
     * Get service (Query)
     * @param ODataRequest $request
     * @param ODataResponse $response
     */
    protected function serveGet(ODataRequest $request, ODataResponse $response){
        //Get scheme
        list($table,$scheme)=$this->prepareOperation($request);
        
        //Init query
	$query=new ODataQuery($scheme->getName());
        
        //Add where elements for primary key
        $schemePk=$scheme->getPk();
        
        if (count($schemePk)==0){
            throw Exception("Table ".$scheme->getName()." has not Primary key",ODataResponse::E_internal_error);
        }
        else{
            if (count($schemePk)==1){
                if (count($request->getPk())==0){
                    //DO nothing
                }
                else
                    if (count($request->getPk())==1)
                        $query->addWhere(
                            $schemePk[0]->getName(),
                            $scheme->security_preprocesInputField(
                                $schemePk[0],
                                $request->getPk()[0],
                                $request->getMethod()
                            )
                        );
                    else
                        //TODO: Implement multiple
                        throw Exception("Multiple primary keys are not implemented yet.",ODataResponse::E_not_implemented);
            }
            else{
                //TODO : Prepare WHERE with multiples Ids
                throw Exception("Multiple primary keys are not implemented yet.",ODataResponse::E_not_implemented);
            }
        }
        
        //Setup filter
        if ($request->getFilter()!=null)
            $query->setFilter(ODataFilterParser::parse($request->getFilter()));
        
        //Extract extra conditions from Entity scheme
        $externalConditions=$scheme->query_db_conditions();
        
        if ($externalConditions!=null){
            //If exists an aggregator
            if ($query->getFilterAggregator()==null){
                //If is filter comparator, envolve in filter aggregator
                if ($externalConditions instanceof ODataQueryFilterComparator)
                    $externalConditions= ODataQueryFilterAggregator::CreateAndList ([$externalConditions]);
                
                //Set filter aggreagtor to query
                $query->setFilterAggregator($externalConditions);
            }else{
                //Append aggregator to current
                $query->setFilterAggregator(ODataQueryFilterAggregator::union($externalConditions,$query->getFilterAggregator()));
            }
        }
        
        
        //Set internal Order
        $orderby=$scheme->query_db_orderby();
        if ($orderby!=null)
            $query->setOrder($orderby);
        
        
        //Top or Limits
        $top=$request->getTop();
        $top2=$scheme->query_db_limit();
        
        if ($top!=null && $top2!=null)
            $query->setTop(min([$top,$top2]));
        else{
            if ($top!=null)
                $query->setTop($top);
            
            if ($top2!=null)
                $query->setTop($top2);
        }
        
        //Skip
        if ($request->getSkip()!=null)
            $query->setSkip($request->getSkip());
        
        //Expand
        if ($request->getExpand()!=null)
            $query->setExpand($request->getExpand());
        
        
        $result=$this->db->query($query);
        $result=$scheme->createEntityListFromSource($result);

        //Check if exists a expand in query
        $expand=$query->getExpand();
        if ($expand!=null){
            //For each entity of result
            foreach ($result as &$entityData){
                //For each expand entity
                foreach ($expand as $expandEntity)
                    $this->expand($entityData,$scheme,$expandEntity);
            }
        }

        //If exist an order sort this
        if ($request->getOrderBy()!=null){
            $order=ODataQueryOrderByList::parse($request->getOrderBy());
            $result=$order->sort($result);
        }
        
        $result=$scheme->prepareEntityListForOutput($result);
        
        $response->success($result);
        
    }
    
    /**
     * Post service (Creation)
     * @param ODataRequest $request
     */
    protected function servePost(ODataRequest $request, ODataResponse $response){
        list($table,$scheme)=$this->prepareOperation($request);
        
        //Check body and extract element
        $element=$request->getBody();
        $element= json_decode($element,true);
        
        //TODO: Check element
        //$scheme->checkNewElement($element);
        
        //Send to DB
        $result=$this->db->insert($element,$table);
        $result=$scheme->prepareEntityForOuput($result);
    
        $response->success($result);
    }
    
    /**
     * Patch service (modifications)
     * @param ODataRequest $request
     */
    protected function servePatch(ODataRequest $request, ODataResponse $response){
        list($table,$scheme)=$this->prepareOperation($request);
        //Check body and extract element
        $element=$request->getBody();
        $element= json_decode($element,true);
        
        $pkValues=$this->extractPK($request,$scheme);
        
        foreach ($pkValues as $k=>$v)
            $element[$k]=$v;
        
        //TODO: Check element
        //$tableScheme->checkNewElement($element);
        
        
        $result=$this->db->update($element,$table);
        $result=$scheme->prepareEntityListForOutput($result);
        
        $response->success($result);
    }
    
    /**
     * Delete service
     * @param ODataRequest $request
     */
    public function serveDelete(ODataRequest $request, ODataResponse $response){
        list($table,$scheme)=$this->prepareOperation($request);
        
        $pkValues=$this->extractPK($request,$scheme);
        
        $this->db->delete($pkValues,$table);
        $response->success();
    }
    
    protected function extractPK(ODataRequest $request, ODataSchemeEntity $scheme){
        //Get primary key scheme
        $schemePk=$scheme->getPk();
        
        $result=[];
        
        if (count($schemePk)==0){
            throw new Exception(ODataResponse::E_internal_error,"Table ".$scheme->getName()." has not Primary key");
        }
        else{
            if (count($schemePk)==1){
                if (count($request->getPk())==0){
                    //DO nothing
                }
                else
                    if (count($request->getPk())==1){
                        $result[$schemePk[0]->getName()]=
                                $scheme->security_preprocesInputField(
                                    $schemePk[0],
                                    $request->getPk()[0],
                                    $request->getMethod()
                                );
                    }
                    else
                        //TODO: Implement multiple
                        throw new Exception("Multiple primary keys are not implemented yet.",ODataResponse::E_not_implemented);
            }
            else{
                //TODO : Prepare WHERE with multiples Ids
                throw new Exception("Multiple primary keys are not implemented yet.",ODataResponse::E_not_implemented);
            }
        }
        
        return $result;
    }
    
    /**
     * Expand a entity
     * @param type $entityData
     * @param ODataSchemeEntity $scheme
     * @param ODataQueryExand $expand
     * @return object Entity modified
     */
    protected function expand(&$entityData, ODataSchemeEntity $scheme, ODataQueryExand $expand){
        
        //Get association info
        $association=$scheme->getAssociation($expand->getName());
        
        //Get associated table name
        $associatedTable=$this->config->entityAlias($association->getAssociated());
        
        //GEt associated entity scheme
        $associatedScheme=$this->getEntityScheme($associatedTable);
        
        if (!isset($entityData[$association->getField()]) &&
            //Check if this association is allowed to extends
            $scheme->security_allowExtends($association) 
        ){
            //Prepare where options
            $list=[];
            foreach ($association->getRelationFields() as $relationField){
                $list[]=new ODataQueryFilterComparator($relationField->getForeign(),"=",$entityData[$relationField->getLocal()]);
            }
            
            //Create and list
            $aggregator= ODataQueryFilterAggregator::CreateAndList($list);
            
            //Add external conditions
            $externalConditions=$scheme->query_db_conditions();
            if ($externalConditions!=null){
                $aggregator=ODataQueryFilterAggregator::union($externalConditions,$aggregator);
            }

            //Prepare query
            $query=new ODataQuery($associatedTable);
            $query->setFilterAggregator($aggregator);
            
            //Add order by to query
            if ($associatedScheme->query_db_orderby()!=null)
                $query->setOrder($associatedScheme->query_db_orderby());
            
            //Get associated entities
            $associatedEntities=$this->db->query($query);
            $associatedEntities=$associatedScheme->prepareEntityListForOutput($associatedEntities);
            
            //Add result entities to the parent
            if ($association->isMultiple())
                $entityData[$association->getField()]=$associatedEntities;
            else
                if (count($associatedEntities)>0)
                    $entityData[$association->getField()]=$associatedEntities[0];
        }
        
        //Check if there are child more expands
        if ($expand->getChild()!=null){
            //For each entity of result
            foreach ($entityData[$association->getField()] as &$entityData2){
                $this->expand($entityData2,$associatedScheme,$expand->getChild());
            }
        }
        
        return $entityData;
    }
    
    /**
     * If allow from any origin is defined, send headers
     */
    protected function allowAnyOrigin(){
        if (isset($this->config->allowAnyOrigin) && $this->config->allowAnyOrigin && isset($_SERVER['HTTP_ORIGIN'])) {
            ODataResponseHTTP::allowOrigin();
        }
    }
    
    /**
     * If options method is allowed send headers
     */
    protected function enableOptionsRequest(){
        if (isset($this->config->enableOptionsRequest) && $this->config->enableOptionsRequest && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                ODataResponseHTTP::accessControlAllowMethods();
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                ODataResponseHTTP::accessControllAllowHeaders();
            }

            exit(0);
        }
    }
    
    /**
     * If clients is defined check it and send error if it is necessary
     */
    protected function checkClients(){
        if ((empty($this->config->clients) !== true) 
                && (in_array($_SERVER['REMOTE_ADDR'], (array) $this->config->clients) !== true)){
            throw new Exception("Cannot perform this operation from this url",ODataResponse::E_forbidden);
        }
    }
    
    /**
     * Enable query medhod motifiers
     */
    protected function queryMethodModifiers(){
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
    
    /**
     * Check if it is authorized, other ways send error
     */
    protected function checkAuth($entity){
        if ($this->config->checkAuth($_SERVER['REQUEST_METHOD'],$entity))
            throw new Exception("You must autenticate first",ODataResponse::E_unauthorized);
    }
    
    /**
     * If this operation is called from command line, send error
     */
    protected function checkCli(){
        if (strcmp(PHP_SAPI, 'cli') === 0){
            exit('OData cannot be runned from commandline.' . PHP_EOL);
        }
    }
    
    /**
     * 
     * @param ODataRequest $request
     * @return ODataSchemeEntity
     */
    protected function prepareOperation(ODataRequest $request){
        //Get table from entity
        $entity=$this->config->entityAlias($request->getEntity());
        
        //Get scheme
        /* @var $tableScheme ODataSchemeEntity */
        $entityScheme=$this->getEntityScheme($entity);
        
        //TODO: Ofuscade Id
        
        //Check if operation is allowed
        if (!$this->config->allow($request))
            throw new Exception("Cannot do this operation",ODataResponse::E_forbidden);
	
        //Check if operation is allowed for this entity
        if (!$entityScheme->security_allowedMethod($request->getMethod()))
            throw new Exception("Cannot do this operation for ".$entityScheme->getName(),ODataResponse::E_forbidden);
        
        return [$entity,$entityScheme];
    } 
  
}

