<?php

class ODataQueryFilterComparator{
    public $not;
    public $op;
    public $left;
    public $right;
    
    public function init($left,$op,$rigth,$not=false){
        $this->left=$left;
        $this->right=strtolower($rigth);
        $this->op=$op;
        $this->not=$not;
        
        return $this;
    }
    
    public function parse($tokens){
        $str=join(" ",$tokens);
        if (preg_match("/\s*((?P<not>not)\s+)?(?P<left>\S+)\s+(?P<op>\S+)\s+(?P<right>\S+)\s*/i", $str,$matches)){
            $this->not= strtolower($matches["not"])=="not";
            $this->op= strtolower($matches["op"]);
            $this->left=$matches["left"];
            $this->right=$matches["right"];
        }
        else{
            //TODO: Other filter options
            die();
        }
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
