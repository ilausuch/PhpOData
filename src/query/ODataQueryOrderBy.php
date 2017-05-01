<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

/**
 * Description of ODataQueryOrderBy
 *
 * @author ilausuch
 */
class ODataQueryOrderBy {
    //put your code here
    private $field;
    private $order;
    
    const ASC="asc";
    const DESC="desc";
    
    function __construct($field,$order=ODataQueryOrderBy::ASC) {
        $this->field=$field;
        $this->order=$order;
    }
    
    public function getField(){
        return $this->field;
    }
    
    public function getOrder(){
        return $this->order;
    }
    
    /**
     * Parse a single orderby element
     * @param string $str
     * @return \ODataQueryOrderBy
     */
    public static function parse($str){
        
        if (preg_match("/^\s*(?P<var>[a-zA-Z_]+)(\s+(?P<type>(asc|desc)))?\s*$/iA", $str, $tokens)){
            $mode= ODataQueryOrderBy::ASC;
            
            if (isset($tokens["type"]) && strtolower($tokens["type"])=="desc")
                $mode= ODataQueryOrderBy::DESC;
            
            return new ODataQueryOrderBy($tokens["var"],$mode);
        }
        
    }
    
    /**
     * Compare two elements usign this orderby
     * @param object $e1
     * @param object $e2
     * @return int
     */
    public function compare($e1,$e2){
        if ($this->getOrder()==ODataQueryOrderBy::ASC)
            $mod=1;
        else
            $mod=-1;
        
        $field1=$e1["___scheme___"]->getField($this->field);
        $field2=$e2["___scheme___"]->getField($this->field);
        
        $v1=$e1[$this->field];
        $v2=$e2[$this->field];
                
        if ($field1==null || $field1==null || $field1!=$field2)
            throw new Exception("Invalid orderby field "+$this->field, ODataResponse::E_bad_request);
        
        switch($field1->getType()){
            case "int":
            case "float":
            case "decimal":
                if ($v1==$v2) 
                    return 0;
                
                if ($v1<$v2) 
                    return 1*$mod;
                else
                    return -1*$mod;
            
            //TODO : Sort datetime and others
                
            default:
                return strcmp($v1,$v2)*$mod;
                
        }
        
    }
}
