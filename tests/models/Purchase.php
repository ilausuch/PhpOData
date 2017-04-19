<?php

class Purchase extends ODataSchemeEntity{
    function __construct() {
        parent::__construct(__CLASS__);
        
        $this->addAssociation(new ODataSchemeEntityAssociation(
                "PurchaseProduct",
                "ProductList",
                ODataSchemeEntityAssociation::MULTIPLE,
                [
                    new ODataSchemeEntityAssociationRelationField("id","purchaseFk")
                ]
        ));
    }
    
    public function security_visibleField(ODataSchemeEntityField $field){
        return !in_array($field->getName(),["active"]);
    }
    
    public function security_preprocesInputField(ODataSchemeEntityField $field, $value, $method){
        if ($method=="PUSH" && $field->getName()=="id")
            return md5(rand().microtime (true));
        else
            return $value;
    }
    
}