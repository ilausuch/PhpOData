<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Description of ODataRequestHTTP
 *
 * @author ilausuch
 */
class ODataRequestHTTP extends ODataRequest{
    
    private function prepare($method,$entityQueryStr,$body){
        $this->method=$method;
        $this->body=$body;
        
        //Check for ID
        $rex="/^(?P<entity>\w+)(\(((?P<id>\w+)|guid'(?P<guid>[\w-]+)'|'(?P<ids>[\w-=\+\\/]+)')\))?$/i";
        //$rex="/^(?P<entity>\w+)\((?P<id>\w+)|guid'(?P<guid>[\w-]+)'|'(?P<ids>[\w-=\+\\/]+)'\)$/i";
        
        if (!preg_match($rex, $entityQueryStr, $matches)){
            throw new Exception("Invalid URL OData format: ".$entityQueryStr, ODataResponse::E_bad_request);
        }
        
        $this->entity=$matches["entity"];
        
        //TODO : Implement multiple PK and PK nammed
        $this->pk=[];
                
        if (isset($matches["guid"]) && $matches["guid"]!=""){
            $this->pk[]=$matches["guid"];
            $this->isGuid=true;
        }
        else
            if (isset($matches["ids"]) && $matches["ids"]!=""){
                $this->pk[]=$matches["ids"];
                $this->isGuid=false;
            }
            else
                if (isset($matches["id"]) && $matches["id"]!=""){
                    $this->pk[]=$matches["id"];
                    $this->isGuid=false;
                }
        
    }
    
    /**
     * Construct a HTTP request from slim request object
     * @param Request $requestSlim
     */
    function __construct(Request $requestSlim) {
        $this->prepare(
            $requestSlim->getMethod(),
            $requestSlim->getAttribute('entityQueryStr'),
            $requestSlim->getBody()->read(1000000)
        );
        
        $this->filter=$_GET["\$filter"];
        $this->expand=$_GET["\$expand"];
        $this->select=$_GET["\$select"];
        $this->orderby=$_GET["\$orderby"];
        $this->top=$_GET["\$top"];
        $this->skip=$_GET["\$skip"];
        $this->count=$_GET["\$count"];
        $this->search=$_GET["\$search"];
        $this->format=$_GET["\$format"];
    }
}
