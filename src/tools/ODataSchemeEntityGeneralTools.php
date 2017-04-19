<?php

class ODataSchemeEntityGeneralTools{
    public static function security_notAllowDelete($method){
        return $method!="DELETE";
    }
    
    public static function security_readOnly($method){
        return in_array($method,["GET","EXTENDS"]);
    }
    
}