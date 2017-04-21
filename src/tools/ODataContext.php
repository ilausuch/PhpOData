<?php

/**
 * Context, you can set user information, or other global information
 *
 * @author ilausuch
 */
class ODataContext {
    private $associated;
    
    function __construct() {
        $this->associated=[];
    }
    
    /**
     * Set $value for $name
     * @param string $name
     * @param any $value
     */
    public function set($name,$value){
        $this->associated[$name]=$value;
    }
    
    /**
     * Get of $name
     * @param string $name
     * @return any
     */
    public function get($name){
        return $this->associated[$name];
    }
}
