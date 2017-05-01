<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

class ODataRequest{
    
    const METHOD_GET="GET";
    const METHOD_POST="POST";
    const METHOD_PUT="PUT";
    const METHOD_PATCH="PATCH";
    const METHOD_DELETE="DELETE";
    
    protected $entity;
    protected $pk;
    protected $isGuid;
    protected $method;
    protected $body;
    
    protected $filter;
    protected $expand;
    protected $select;
    protected $top;
    protected $skip;
    protected $count;
    protected $search;
    protected $format;
    
    
    public function getEntity() {return $this->entity;}
    public function getPk() {return $this->pk;}
    public function getMethod() {return $this->method;}
    public function getBody(){return $this->body;}
    
    public function getFilter() {return $this->filter;}
    public function getExpand() {return $this->expand;}
    public function getSelect() {return $this->select;}
    public function getOrderBy() {return $this->orderby;}
    public function getTop() {return $this->top;}
    public function getSkip() {return $this->skip;}
    public function getCount() {return $this->count;}
    public function getSearch() {return $this->search;}
    public function getFormat() {return $this->format;}
    
}