<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

class ODataResponseHTTP{
    
    /**
     * Send error message
     * @param int $httpError
     * @param string $message
     */
    public static function error($httpError,$message){
        header("HTTP/1.1 $httpError $message");
        die($message);
    }
    
    /**
     * Send error message usign the exception message and code
     * @param Exception $exception
     */
    public static function errorException(Exception $exception){
        ODataResponseHTTP::error($exception->getCode(),$exception->getMessage());
    }
    
    /**
     * Create metadata information
     * @return string
     */
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
    
    /**
     * Send success data as array of objects
     * @param object[] $list
     */
    public static function successArray($list){
        header('Content-Type: application/json; charset=utf-8');
        
        $metadata= ODataResponseHTTP::createMetadata();
        
        exit(json_encode(["odata.metadata"=>"{$metadata}",value=>$list]));
    }
    
    /**
     * Success when a new element is created. Return the created object
     * @param object $element
     */
    public static function successCreatedElement($element){
        header('Content-Type: application/json; charset=utf-8');
        header("HTTP/1.1 201 Created");
        
        $object= json_decode(json_encode($element),true);
        $object["object.metadata"]=ODataResponseHTTP::createMetadata()."/@Element";

        exit(json_encode($object));
    }
    
    /**
     * Success when a new element is mofified
     * @param object $element
     */
    public static function successModifiedElement($element){
        header('Content-Type: application/json; charset=utf-8');
        header("HTTP/1.1 204 No content");
        exit();
    }
    
    /**
     * Success when a element is deleted
     */
    public static function successDeletedElement(){
        header('Content-Type: application/json; charset=utf-8');
        header("HTTP/1.1 204 No content");
        exit();
    }
    
    /**
     * Allows any origin
     */
    public static function allowOrigin(){
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    
    /**
     * Allows all methods
     */
    public static function accessControlAllowMethods(){
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS, DELETE");
    }
    
    /**
     * Allows control headers
     */
    public static function accessControllAllowHeaders(){
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
}