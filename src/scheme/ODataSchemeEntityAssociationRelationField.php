<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

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
