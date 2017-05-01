<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

class ODataDBAdapter{
    protected $dsn;
    protected $user;
    protected $password;
    
    protected $db;
    protected $options;
    
    protected $connected;
    
    
    function __construct($dsn,$user,$password,$options) {
        $this->user=$user;
        $this->password=$password;
        $this->dsn=$dsn;
        $this->options=$options;
        echo $this->dns;
    }
    
    public function connect(){
        if (!$this->connected){
            try {
                $this->db = new \PDO($this->dsn,$this->user,$this->password,$this->options);
                $this->connected=true;
            }catch(PDOException $e) {
                throw new Exception("Cannot connect to DB",ODataResponse::E_internal_error);
            }
        }
    }
    
    public function query(ODataQuery $query){}
    
    public function insert($element, $table){}
    
    /*
     * Extract table scheme from DB
     * @return ODataSchemeEntity The scheme
     */
    public function discoverTableScheme($table){}
    
}