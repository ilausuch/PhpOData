<?php

class ODataSchemeEntityField{
    private $name;
    private $pk;
    private $type;
    private $allowNulls;
    private $len;
    private $defaultValue;
    private $extra;
    
    function __construct($name,$type,$allowNulls,$len,$isPk,$defaultValue,$extra){
        $this->name=$name;
        $this->type=$type;
        $this->allowNulls=$allowNulls;
        $this->len=$len;
        $this->pk=$isPk;
        $this->defaultValue=$defaultValue;
        $this->extra=$extra;
    }
    
    public static function parse($config){
        //TODO : Parse field
    }
    
    public function isPk(){
        return $this->pk;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getType(){
        return $this->type;
    }
    
    public function getAllowNulls(){
        return $this->allowNulls;
    }
    
    public function getLen(){
        return $this->len;
    }
    
    public function getDefaultValue(){
        return $this->defaultValue;
    }
    
    public function getExtra(){
        return $this->extra;
    }
    
}