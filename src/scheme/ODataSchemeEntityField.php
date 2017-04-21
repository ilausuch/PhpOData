<?php

class ODataSchemeEntityField{
    /**
     * Name of field
     * @var string
     */
    private $name;
    
    /**
     * If it is primary key
     * @var boolean
     */
    private $pk;
    
    /**
     * Type of field
     * @var ODataSchemePrimitive
     */
    private $type;
    
    /**
     * True if allows nulls
     * @var boolean 
     */
    private $allowNulls;
    
    /**
     * Length of field
     * @var int
     */
    private $len;
    
    /**
     * Default value
     * @var any
     */
    private $defaultValue;
    
    /**
     * Extra information
     * @var any
     */
    private $extra;
    
    function __construct($name,ODataSchemePrimitive $type,$allowNulls,$len,$isPk,$defaultValue,$extra){
        $this->name=$name;
        $this->type=$type;
        $this->allowNulls=$allowNulls;
        $this->len=$len;
        $this->pk=$isPk;
        $this->defaultValue=$defaultValue;
        $this->extra=$extra;
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