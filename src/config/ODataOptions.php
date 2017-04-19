<?php

class ODataOptions{
    public $allowAnyOrigin;
    public $enableOptionsRequest;
    public $clients;
    public $allowQueryMethodModifiers;
    public $defaultAllowedMethods;
    
    function __construct() {
        $this->allowAnyOrigin=false;
        $this->enableOptionsRequest=true;
        $this->clients=[];
        $this->allowQueryMethodModifiers=false;
        $this->defaultAllowedMethods=["GET"];
    }
    
    public function checkAuth(ODataRequest $request){
        return true;
    }
    
    public function entityAlias($alias){
        return $alias;
    }
    
    public function allow(ODataRequest $request){
        return true;
    }
}