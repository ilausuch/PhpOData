<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

class ODataQueryExand{
    private $name;
    private $child;
    
    function __construct($levels) {
        $this->name=$levels[0];
        
        array_shift($levels);
        if (count($levels)>0)
            $this->child=new ODataQueryExand($levels);
    }
    
    public static function parse($str){
        $parts=split(",", $str);
        
        $list=[];
        
        foreach($parts as $part){
            $levels=split("/", $part);
            $list[]=new ODataQueryExand($levels);
        }
        
        return $list;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getChild(){
        return $this->child;
    }
}

