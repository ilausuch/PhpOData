<?php

class ODataQueryFilterComparator extends ODataQueryFilterBase{
    public $not;
    public $op;
    public $left;
    public $right;
    
    function __construct($left=null,$op=null,$rigth=null,$not=false) {
        $this->left=$left;
        $this->right=$rigth;
        $this->op=strtolower($op);
        $this->not=$not;
    }
    
    public function getNot(){
        return $this->not;
    }
    
    public function getLeft(){
        return $this->left;
    }
    
    public function getRight(){
        return $this->right;
    }
    
    public function getOp(){
        return $this->op;
    }
}
