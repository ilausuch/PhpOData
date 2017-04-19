<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ODataSchemeEntityAssociation
 *
 * @author ilausuch
 */
class ODataSchemeEntityAssociation {
    private $associated;
    private $field;
    private $relationType;
    private $relationFields;
    
    const MULTIPLE="multiple";
    const SINGLE="single";
    
    /**
     * Create a new association
     * @param string $associated
     * @param string $field
     * @param string $relationType
     * @param ODataSchemeEntityAssociationRelationField[] $relationFields
     */
    function __construct($associated,$field,$relationType,$relationFields) {
        $this->associated=$associated;
        $this->field=$field;
        $this->relationType=strtolower($relationType);
        $this->relationFields=$relationFields;
        
        //TODO : Check validation
    }
    
    public static function parse($field,$config){
        //TODO: Check config
        return new ODataSchemeEntityAssociation($config["associated"],$field,$config["relationType"],$config["relationFields"]);
    }
    
    public function getAssociated(){
        return $this->associated;
    }
    
    public function getField(){
        return $this->field;
    }
    
    public function getRelationType(){
        return $this->relationType;
    } 
    
    public function getRelationFields(){
        return $this->relationFields;
    }
    
    public function isMultiple(){
        return $this->relationType=== ODataSchemeEntityAssociation::MULTIPLE;
    }
    
    public function isSingle(){
        return $this->relationType===ODataSchemeEntityAssociation::SINGLE;
    }
}
