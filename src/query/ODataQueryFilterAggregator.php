<?php

class ODataQueryFilterAggregator{
    private $left;
    private $rigth;
    private $op;
    
    public function parse($tokens){
        $found=false;
        $left=[];
        $right=[];
        
        foreach ($tokens as $token){
            if (!$found){
                switch($token){
                    case "or":
                    case "and":
                        $this->op=$token;
                        $found=true;
                    break;
                    default:
                     $left[]=$token;  
                }
            }
            else{
                $right[]=$token;
            }
        }
        
        $this->left=new ODataQueryFilterComparator();
        $this->left->parse($left);
        
        if (count($right)>0){
            $this->rigth=new ODataQueryFilterAggregator();
            $this->rigth->parse($right);
        }
    }
    
    /**
     * 
     * @param type $list
     * @return ODataQueryFilterAggregator
     */
    public static function CreateAndList($list){
        $agregator=new ODataQueryFilterAggregator();
        $agregator->left=$list[0];
        array_shift($list);
        if (count($list)>0){
           $agregator->rigth=ODataQueryFilterAggregator::AndList($list); 
        }
        
        return $agregator;
    }
    
    public function getLeft(){
        return $this->left;
    }
    
    public function getRight(){
        return $this->rigth;
    }
    
    public function getOp(){
        return $this->op;
    }
}

