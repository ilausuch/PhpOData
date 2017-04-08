<?php

class ODataSchemeEntity{
    private $name;
    private $pk;
    private $fields;
    private $associations;
    
    //TODO: Cambiar idFieldName por multiple fields
    function __construct($name){
        $this->name=$name;
        $this->fields=[];
        $this->associations=[];
        $this->pk=[];
    }
    
    /*
     * Extract pk from fields
     */
    private function extractPrimaryKey(){
        $this->pk=[];
        foreach ($this->fields as $field)
            if ($field->isPk())
                $this->pk[]=$field;
    }
    
    public function setFields($fields){
        $this->fields=$fields;
        $this->extractPrimaryKey();
    }
    
    public function addField(ODataSchemeEntityField $field){
        $this->fields[]=$field;
        $this->extractPrimaryKey();
    }
    
  
    
    public function setAssociations($associations){
        $this->associations=$associations;
    }
    
    public function addAssociation(ODataSchemeEntityAssociation $association){
        if (!isset($this->associations))
            $this->associations=[];
        
        $this->associations[$association->getField()]=$association;
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
    
    public function getAssociations(){
        return $this->associations;
    }
    
    /**
     * Retruns an association by name
     * @param string $name
     * @return OdataSchemeEntityAssociation
     */
    public function getAssociation($name){
        if (!isset($this->associations[$name]))
            ODataHTTP::error (ODataHTTP::E_not_implemented, "Invalid associated {$name} for {$this->name}");
            
        return $this->associations[$name];
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