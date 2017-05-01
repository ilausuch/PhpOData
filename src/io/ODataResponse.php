<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

/**
 * Description of ODataResponse
 *
 * @author ilausuch
 */
class ODataResponse {
    private $data;
    private $errorCode;
    private $errorMessage;
    
    const E_UNDEFINED_ERROR=-1;
    const E_not_implemented=501;
    const E_bad_request=400;
    const E_unauthorized=401;
    const E_forbidden=403;
    const E_method_not_allowed=405;
    const E_internal_error=500;
    
    /**
     * Active sucess mode
     * @param Object $data
     */
    public function success($data){
        $this->data=$data;
    }
    
    /**
     * Active error mode
     * @param int $errorCode
     * @param string $errorMessage
     * @throws Exception
     */
    public function error($errorCode,$errorMessage){
        $this->errorCode=$errorCode;
        $this->errorMessage=$errorMessage;
        
        throw Exception($errorMessage,$errorCode);
    }
    
    /**
     * Active error mode (usign internal error exception)
     * @param Exception $exception
     */
    public function errorException(Exception $exception){
        $this->error(ODataResponse::E_internal_error,$exception->getMessage());
    }
    
    /**
     * Get success data
     * @return object
     */
    public function getData(){
        return $this->data;
    }
    
    /**
     * Get error message
     * @return string
     */
    public function getErrorMessage(){
        return $this->errorMessage;
    }
    
    /**
     * Get error code
     * @return int
     */
    public function getErrorCode(){
        return $this->errorCode;
    }
}
