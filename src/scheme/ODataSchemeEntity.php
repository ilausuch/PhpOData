<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

class ODataSchemeEntity{
    private $name;
    private $pk;
    private $fields;
    private $associations;
    
    /**
     * Constructor of an Entity
     * @param string $name
     */
    function __construct($name){
        $this->name=$name;
        $this->fields=[];
        $this->associations=[];
        $this->pk=[];
    }
    
    /**
     * Extract the primary key from fields
     */
    private function extractPrimaryKey(){
        $this->pk=[];
        foreach ($this->fields as $field)
            if ($field->isPk())
                $this->pk[]=$field;
    }
    
    /**
     * Set the list of fields
     * @param ODataSchemeEntityField[] $fields
     */
    public function setFields($fields){
        $this->fields=$fields;
        $this->extractPrimaryKey();
    }
    
    /**
     * Add a field
     * @param ODataSchemeEntityField $field
     */
    public function addField(ODataSchemeEntityField $field){
        $this->fields[]=$field;
        $this->extractPrimaryKey();
    }
    
    /**
     * Get the field by name
     * @param string $name
     * @return ODataSchemeEntityField
     */
    public function getField($name){
        foreach($this->fields as $field)
            if ($field->getName()==$name)
                return $field;
            
        return null;
    }
  
    /**
     * Set the list of associations
     * @param ODataSchemeEntityAssociation[] $associations
     */
    public function setAssociations($associations){
        $this->associations=$associations;
    }
    
    /**
     * Add a new association
     * @param ODataSchemeEntityAssociation $association
     */
    public function addAssociation(ODataSchemeEntityAssociation $association){
        if (!isset($this->associations))
            $this->associations=[];
        
        $this->associations[$association->getField()]=$association;
    }
    
    /**
     * Get name of the entity
     * @return string
     */
    public function getName(){
        return $this->name;
    }
    
    /**
     * Get primary key of entity
     * @return ODataSchemeEntityField[]
     */
    public function getPk(){
        $this->getFields();
        return $this->pk;
    }
    
    /**
     * Get list of all fields.
     * If they aren't declared, will be discovered
     * @return ODataSchemeEntityField[]
     */
    public function getFields(){
        if (count($this->fields)==0){
            $dbScheme=OData::$object->getDb()->discoverTableScheme($this->name);
            if (isset($dbScheme))
                $this->setFields($dbScheme->getFields());
            else
                throw new Exception("Entity {$entityName} isn't defined in DB", ODataResponse::E_not_implemented);
        }
        
        return $this->fields;
    }
    
    /**
     * Get list of associations
     * @return ODataSchemeEntityAssociation[]
     */
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
            throw new Exception("Invalid associated {$name} for {$this->name}", ODataResponse::E_not_implemented);
            
        return $this->associations[$name];
    }
    
    /**
     * Creates a new entity object from data source usign only fields and applying restrictions
     * @param object $data
     * @return object
     */
    public function createEntityFromSource($data){
        $element=[];
        
        foreach($this->getFields() as $field){
            $element[$field->getName()]=$data[$field->getName()];
        }
        $element["___scheme___"]=$this;
        
        return $element;
    }
    
    /**
     * Creates a list of new entity objects from data source usign only fields applying restrictions
     * @param object[] $list
     * @return object[]
     */
    public function createEntityListFromSource($list){
        $result=[];
        
        foreach($list as $item){
            $result[]=$this->createEntityFromSource($item);
        }
        
        return $result;
    }
    
    /**
     * Transform an entity
     * @param object $element
     * @return object
     */
    public function prepareEntityForOuput($element){
        $newElement=[];
        
        //For each field...
        foreach($this->getFields() as $field){
            
            //Check if it is visible
            if ($this->security_visibleField($field,$data,$data[$field->getName()])){
                
                //Postprocess field
                $newElement[$field->getName()]=
                    $this->query_postprocessField(
                        $field,
                        $element,
                        $element[$field->getName()]
                    );
            }
        }
        
        //For each association
        foreach ($this->getAssociations() as $association){
            //Add if it isn't null
            if ($element[$association->getField()]!=null)
                $newElement[$association->getField()]=$element[$association->getField()];
        }
        
        //Perform the postprocess
        $newElement=$this->query_postprocessEntity($newElement);
        
        
        return $newElement;
    }
    
    /**
     * Transform a list of entities
     * @param object[] $list
     * @return object[]
     */
    public function prepareEntityListForOutput($list){
        $result=[];
        
        foreach($list as $item){
            if ($this->sequrity_visibleEntity($item))
                $result[]=$this->prepareEntityForOuput($item);
        }
        
        $result=$this->query_postprocessList($result);
        
        return $result;
    }
    
    /**
     * Returns true if method is allowed, otherways false
     * By default uses defaultAllowedMethods from configuration options
     * @param string $method
     * @return boolean
     */
    public function security_allowedMethod($method){
        return in_array($method,OData::$object->getConfig()->defaultAllowedMethods);
    }
    
    /**
     * Check if field is visible in result
     * @param ODataSchemeEntityField $field
     * @return boolean
     */
    public function security_visibleField(ODataSchemeEntityField $field,$entity,$value){
        return true;
    }
    
    /**
     * Check if an entity will returned
     * @return type
     */
    public function sequrity_visibleEntity($entity){
        return true;
    }
    
    /**
     * Check if it is allowed to extends
     * @param ODataSchemeEntityAssociation $association
     * @return boolean
     */
    public function security_allowExtends(ODataSchemeEntityAssociation $association){
        return true;
    }
    
    /**
     * Transform a value of a field
     * @param ODataSchemeEntityField $field
     * @param string $value
     * @return string
     */
    public function security_preprocesInputField(ODataSchemeEntityField $field, $value, $method){
        return $value;
    }
    
    /**
     * Returns an aggregator for query conditions
     * @return ODataQueryFilterAggregator
     */
    public function query_db_conditions(){
        return null;
    }
    
    /**
     * Returns a list of orderby elements to sort the result
     * @return ODataQueryOrderByList
     */
    public function query_db_orderby(){
        return null;
    }
    
    /**
     * Returns the limit to return
     * @return integer
     */
    public function query_db_limit(){
        return null;
    } 
    
    public function query_postprocessField(ODataSchemeEntityField $field, $entity, $value){
        return $value;
    }
    
    public function query_postprocessEntity($entity){
        return $entity;
    }
    
    public function query_postprocessList($list){
        return $list;
    }
    
    
}