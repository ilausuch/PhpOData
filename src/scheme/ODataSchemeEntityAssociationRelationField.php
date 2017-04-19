<?php

/**
 * Description of ODataSchemeEntityAssociationRelationField
 *
 * @author ilausuch
 */
class ODataSchemeEntityAssociationRelationField {
    private $local;
    private $foreign;
    
    function __construct($local,$foreign) {
        $this->local=$local;
        $this->foreign=$foreign;
    }
    
    public function getLocal(){
        return $this->local;
    }
    
    public function getForeign(){
        return $this->foreign;
    }
}
