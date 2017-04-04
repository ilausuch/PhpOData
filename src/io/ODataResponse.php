<?php

use \Psr\Http\Message\ResponseInterface as Response;

class ODataResponse{
    public $responseSlim;
    
    function __construct(Response $responseSlim) {
        $this->responseSlim=$responseSlim;
    }
    
    function sendData($data){
        $this->responseSlim->getBody()->write(json_encode($data));
    }
    //$response->getBody()->write("Hello, $entity ".$request->getMethod()." ". json_encode());
            
}
