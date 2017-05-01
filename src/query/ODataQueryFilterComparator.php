<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

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
