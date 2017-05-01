<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

/**
 * ODataRequestORM allows to query directly to OData with out a backend call
 *
 * @author ilausuch
 */
class ODataRequestORM extends ODataRequest{
    function __construct($entity,$method) {
        $this->entity=$entity;
        $this->method=$method;
    }
    
    /**
     * Set the primary key
     * @param string $value
     * @return ODataRequestORM
     */
    public function Pk($value){
        if (is_array($value))
            $this->pk=$value;
        else
            $this->pk=[$value];
        
        return $this;
    }
    
    /**
     * Set the body data for create and modify
     * @param string $value
     * @return ODataRequestORM
     */
    public function Body($value){
        $this->body=$value;
        return $this;
    }
    
    /**
     * Set filter of query
     * @param string $value
     * @return ODataRequestORM
     */
    public function Filter($value){
        $this->filter=$value;
        return $this;
    }
    
    /**
     * Set expand for query
     * @param string $value
     * @return ODataRequestORM
     */
    public function Expand($value){
        $this->expand=$value;//ODataQueryExand::parse($value);
        return $this;
    }
    
    /**
     * Set select for query
     * @param string $value
     * @return ODataRequestORM
     */
    public function Select($value){
        throw new Exception(ODataResponse::E_not_implemented);
        
        $this->select=$value;
        return $this;
    }
    
    /**
     * Set ordery by for query
     * @param string $value
     * @return ODataRequestORM
     */
    public function OrderBy($value){
        $this->orderby=$value;
        return $this;
    }
    
    /**
     * Set the maximun number of elements to return
     * @param int $value
     * @return ODataRequestORM
     */
    public function Top($value){
        $this->top=$value;
        return $this;
    }
    
    /**
     * Set the maximun number of elements to return
     * @param int $value
     * @return ODataRequestORM
     */
    public function Limit($value){
        $this->top=$value;
        return $this;
    }
    
    /**
     * Set the number of elements to ignore
     * @param int $value
     * @return ODataRequestORM
     */
    public function Skip($value){
        $this->skip=$value;
        return $this;
    }
    
    /**
     * Set the page to return of a query
     * @param int $page
     * @param int $countPerPage
     * @return ODataRequestORM
     */
    public function page($page,$countPerPage){
        $this->top=$countPerPage;
        $this->skip=$countPerPage*$page;
        return $this;
    }
    
    /**
     * 
     * @param type $value
     * @return ODataRequestORM
     */
    public function Count($value){
        throw new Exception(ODataResponse::E_not_implemented);
        $this->count=$value;
        return $this;
    }
    
    /**
     * 
     * @param type $value
     * @return ODataRequestORM
     */
    public function Search($value){
        throw new Exception(ODataResponse::E_not_implemented);
        $this->search=$value;
        return $this;
    }
    
}
