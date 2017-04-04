<?php

class ODataScheme{
    private $entities;
    
    public function addEntity(ODataSchemeEntity $entity){
        $this->entities[$entity->getName()]=$entity;
    }
    
    public function getEntity($name){
        return $this->entities[$name];
    }
}