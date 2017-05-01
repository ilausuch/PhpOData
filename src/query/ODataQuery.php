<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

class ODataQuery{
    private $select;
    private $from;
    private $where;
    
    /**
     *
     * @var ODataQueryFilterAggregator
     */
    private $filterAggregator;
    
    /**
     *
     * @var ODataQueryOrderByList
     */
    private $orderByList;
    
    private $top;
    private $skip;
    private $expand;
    
    function __construct($from) {
        $this->select="*";
        $this->from=$from;
        $this->where=[];
    }
    
    public function setSelect($list){
        $this->select= implode(",", $list);
    }
    
    public function addWhere($id,$value){
        $this->where[$id]=$value;
    }
    
    public function setFilter($filter){
        /*
        $tokens=preg_split("/\s+/i", $filter);
            
        $this->filterAggregator=new ODataQueryFilterAggregator();
        $this->filterAggregator->parse($tokens);
         * 
         */
        
        $this->filterAggregator=$filter;
    }
    
    
    public function setFilterAggregator(ODataQueryFilterAggregator $filterAggregator){
        $this->filterAggregator=$filterAggregator;
    }
    
    /**
     * Enable query order by
     * @param ODataQueryOrderByList $orderList
     */
    public function setOrder(ODataQueryOrderByList $orderByList){
        $this->orderByList=$orderByList;
    }
    
    public function setTop($top){
        $this->top=$top;
    }
    
    public function setSkip($skip){
        $this->skip=$skip;
    }
    
    public function setExpand($expand){
        $this->expand=ODataQueryExand::parse($expand);
    }
    
    /*
     * GETS
     */
    
    public function getSelect(){
        return $this->select;
    }
    
    public function getFrom(){
        return $this->from;
    }
    
    public function getWhere(){
        return $this->where;
    }
    
    /**
     * Get filter aggregator
     * @return OdataQueryFilterAggregator
     */
    public function getFilterAggregator(){
        return $this->filterAggregator;
    }
    
    /**
     * Return order by
     * @return ODataQueryOrderBy[]
     */
    public function getOrderByList(){
        return $this->orderByList;
    }
   
    public function getTop(){
        return $this->top;
    }
    
    public function getSkip(){
        return $this->skip;
    }
    
    public function getExpand(){
        return $this->expand;
    }
    
    
}