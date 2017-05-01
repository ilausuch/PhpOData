<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

class ODataQueryFilterAggregator extends ODataQueryFilterBase{
    private $left;
    private $rigth;
    private $op;
    
    /**
     * And operation
     */
    const OP_AND="and";
    
    /**
     * Or operation
     */
    const OP_OR="or";
    
    
    /**
     * 
     * @param type $list
     * @return ODataQueryFilterAggregator
     */
    public static function CreateAndList($list,$op=ODataQueryFilterAggregator::OP_AND){
        $agregator=new ODataQueryFilterAggregator();
        $agregator->op=$op;
        $agregator->left=$list[0];
        array_shift($list);
        if (count($list)>0){
           $agregator->rigth=ODataQueryFilterAggregator::AndList($list); 
        }
        
        return $agregator;
    }
    
    /**
     * Fuses two QueryFilter in one agregator
     * @param ODataQueryFilterBase $ag1
     * @param ODataQueryFilterBase $ag2
     * @param string $op
     * @return \ODataQueryFilterAggregator
     */
    public static function union(ODataQueryFilterBase $ag1,ODataQueryFilterBase $ag2,$op=ODataQueryFilterAggregator::OP_AND){
        $union=new ODataQueryFilterAggregator();
        $union->setLeft($ag1);
        $union->setRight($ag2);
        $union->setOp(ODataQueryFilterAggregator::OP_AND);
        
        return $union;
    }
    
    /**
     * Set left item
     * @param ODataQueryFilterBase $left
     */
    public function setLeft(ODataQueryFilterBase $left){
        $this->left=$left;
    }
    
    /**
     * Set rigth item
     * @param ODataQueryFilterBase $left
     */
    public function setRight(ODataQueryFilterBase $rigth){
        $this->rigth=$rigth;
    }
    
    /**
     * Set boolean operation
     * @param string $op
     */
    public function setOp($op){
        $this->op=$op;
    }
    
    /**
     * Get left item
     * @return ODataQueryFilterBase
     */
    public function getLeft(){
        return $this->left;
    }
    
    /**
     * Get right item
     * @return ODataQueryFilterBase
     */
    public function getRight(){
        return $this->rigth;
    }
    
    /**
     * Get operation
     * @return string
     */
    public function getOp(){
        return $this->op;
    }
    
    
}

