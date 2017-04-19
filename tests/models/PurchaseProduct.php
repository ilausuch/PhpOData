<?php

class PurchaseProduct extends ODataSchemeEntity{
    function __construct() {
        parent::__construct(__CLASS__);
        
        $this->addAssociation(new ODataSchemeEntityAssociation(
                "Product",
                "Product",
                ODataSchemeEntityAssociation::SINGLE,
                [
                    new ODataSchemeEntityAssociationRelationField("productFk","id")
                ]
        ));
    }
    
    public function security_visibleField(ODataSchemeEntityField $field){
        return !in_array($field->getName(),["active"]);
    }
}