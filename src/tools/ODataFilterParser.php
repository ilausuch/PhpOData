<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ODataToken
 *
 * @author ilausuch
 */

class ODataFilterToken{
    public $text;
    
    const LOGICAL_AGRE="LOGICAL_AGRE";
    const LOGICAL_COMP="LOGICAL_COMP";
    const LOGICAL_NEG="LOGICAL_NEG";
    const VARIABLE="VARIABLE";
    const VALUE_NUMBER="VALUE_NUMBER";
    const FNC="FNC";
    const ENDFNC="ENDFNC";
    const VALUE_CAD="VALUE_CAD";
    
    function __construct($text) {
        $this->text=$text;
    }
    
    public function isLogicalAggregation(){
        return $this->checkStringList(["and","or"]);
    }
    
    public function isLogicalComparator(){
        return $this->checkStringList(["=","!=",">",">=","<","<=","<>","eq","ne","gt","lt","ge","le"]);
    }
    
    public function isLogicalNegation(){
        return $this->checkStringList(["not","!"]);
    }
    
    public function isVariable(){
        return preg_match("/[_a-zA-Z]\w*/", $this->text, $tokens);
    }
    
    public function isNumber(){
        return preg_match("/\d+(\.\d+)?|\.\d+/", $this->text, $tokens);
    }
    
    public function isCad(){
        return preg_match("/'(.*)'/", $this->text, $tokens);
    }
    
    public function isFunctionBegin(){
        return preg_match("/[_a-zA-Z]\w*'('/", $this->text, $tokens);
    }
    
    
    
    private function checkStringList($list){
        foreach($list as $str){
            if ($this->checkString($str))
                return true;
        }
        
        return false;
    }
    
    private function checkString($str){
        return strtolower($this->text)==$str;
    }
}

class ODataFilterParser {
    
    
    public static function parse($str){
        if (!preg_match_all("/[_a-zA-Z]\w*|\d+(\.\d+)?|\.\d+|\(|\)|\'(.*)'|==|>=|<=|!=|<>|[=+-\/\\\*><!]/", $str, $tokens))
           ODataHTTP::error (ODataHTTP::E_bad_request, "Invalid filter string: "+$str);
        
        $tokens=$tokens[0];
        $list=[];
        
        foreach($tokens as $token){
            $list[]=new ODataFilterToken($token);
        }
        
        list($result,$list2)=ODataFilterParser::process_aggregator($list);
        return $result;
    }
    
    public static function process_aggregator($list){
        $token=$list[0];
        if ($token->text=="("){
            array_shift($list);
            
            list($result,$list)=ODataFilterParser::process_aggregator($list);
            
            if ($result==null)
                return null;
            
            $token = array_shift($list);
            if ($token->text!=")")
                return null;
            
            return [$result,$list];
        }else{
            list($result,$list)= ODataFilterParser::process_expression($list);
            
            $aggregator=new ODataQueryFilterAggregator();
            $aggregator->setLeft($result);
            
            if (count($list)==0 || $list[0]->text==")"){
                return [$aggregator,$list];
            }else{
                $token = array_shift($list);
                if (!$token->isLogicalAggregation())
                    return null;
                
                $aggregator->setOp($token->text);
                
                list($result,$list)=ODataFilterParser::process_aggregator($list);
                if ($result==null)
                    return null;
                
                $aggregator->setRight($result);
                
                return [$aggregator,$list];
            }
        }
    }
    
    public static function process_expression($list){
        list($result,$list)=ODataFilterParser::process_comparation($list);
        if ($result!=null){
            return [$result,$list];
        }
        else{
            $result=ODataFilterParser::process_function($list);
            if ($result!=null)
                return [$result,$list];
        }
        
        return null;
    }
    
    public static function process_comparation($list){
        $neg=false;
        
        $token=array_shift($list);
        if ($token->isLogicalNegation()){
            $neg=true;
            $token=array_shift($list);
        }
        
        if (!$token->isVariable())
           return null;
        
        $left=$token->text;
        $token=array_shift($list);
        
        if (!$token->isLogicalComparator())
           return null;
        
        $op=$token->text;
        
        
        list($value,$list)=ODataFilterParser::process_value($list);
        if ($value==null)
           return null;
        
        return [new ODataQueryFilterComparator($left,$op,$value,$neg),$list];
    }
    
    public static function process_value($list){
        $token=array_shift($list);
        
        if ($token->isCad() || $token->isNumber())
            return [$token->text,$list];
        else
            return null;
    }
    
    public static function process_function($list){
        ODataHTTP::error(ODataHTTP::E_not_implemented, "Filter functions are not implemented yet");
    }
    
    
   
    
}
