<?php

class ODataHTTP{
    const E_not_implemented=501;
    const E_bad_request=400;
    const E_unauthorized=401;
    const E_forbidden=403;
    const E_method_not_allowed=405;
    const E_internal_error=500;
    
    public static function error($httpError,$message){
        header("HTTP/1.1 $httpError $message");
        die($message);
    }
    
    public static function errorException(Exception $exception){
        header("HTTP/1.1 {$exception->getCode()} {$exception->getMessage()}");
        die($message);
    }
    
    
    
    private static function createMetadata(){
        $split=split("\?",$_SERVER["REQUEST_URI"]);
        $split=split("/",$split[0]);
        
        $entityName=$split[count($split)-1];
        preg_match("/(\w+)/", $entityName, $output_array);
        $entityName=$output_array[0];
        
        unset($split[count($split)-1]);
        $odataPath=join("/", $split);
        
        return $_SERVER["HTTP_HOST"].$odataPath."/\$metadata#{$entityName}";
    }
    
    public static function successArray($list){
        header('Content-Type: application/json; charset=utf-8');
        
        $metadata= ODataHTTP::createMetadata();
        
        exit(json_encode(["odata.metadata"=>"{$metadata}",value=>$list]));
    }
    
    public static function successCreatedElement($element){
        header('Content-Type: application/json; charset=utf-8');
        header("HTTP/1.1 201 Created");
        
        $object= json_decode(json_encode($element),true);
        $object["object.metadata"]=ODataHTTP::createMetadata()."/@Element";

        exit(json_encode($object));
    }
    
    public static function successModifiedElement($element){
        header('Content-Type: application/json; charset=utf-8');
        header("HTTP/1.1 204 No content");
        exit();
    }
    
    public static function successDeletedElement(){
        header('Content-Type: application/json; charset=utf-8');
        header("HTTP/1.1 204 No content");
        exit();
    }
    
    public static function allowOrigin(){
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    
    public static function accessControlAllowMethods(){
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS, DELETE");
    }
    
    public static function accessControllAllowHeaders(){
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
}