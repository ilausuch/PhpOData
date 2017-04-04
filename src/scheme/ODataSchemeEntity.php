<?php

class ODataSchemeEntity{
    private $name;
    private $pk;
    private $fields;
    
    //TODO: Cambiar idFieldName por multiple fields
    function __construct($name,$fields){
        $this->name=$name;
        $this->fields=$fields;
        $this->pk=[];
        
        //Extract pk from fields
        foreach ($fields as $field)
            if ($field->isPk())
                $this->pk[]=$field;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getPk(){
        return $this->pk;
    }
    
    public function getFields(){
        return $this->fields;
    }
    
    
    public function checkNewElement($element){
        //TODO checkNewElement
        return true;
    }
    
    public function checkUpdateElement($element){
        //TODO checkUpdateElement
        return true;
    }
    
    public function checkReplaceElement($element){
        //TODO checkReplaceElement
        return true;
    }
    
    public function createElementFromSource($data){
        $element=[];
        
        foreach($this->getFields() as $field){
            $element[$field->getName()]=$data[$field->getName()];
        }
        
        return $element;
    }
}