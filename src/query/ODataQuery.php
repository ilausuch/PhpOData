<?php

class ODataQuery{
    private $select;
    private $from;
    private $where;
    private $filterAggregator;
    private $top;
    private $skip;
    
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
        $tokens=preg_split("/\s+/i", $filter);
            
        $this->filterAggregator=new ODataQueryFilterAggregator();
        $this->filterAggregator->parse($tokens);
    }
    
    public function setTop($top){
        $this->top=$top;
    }
    
    public function setSkip($skip){
        $this->skip=$skip;
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
    
    public function getFilterAggregator(){
        return $this->filterAggregator;
    }
   
    public function getTop(){
        return $this->top;
    }
    
    public function getSkip(){
        return $this->skip;
    }
    
    
}