<?php

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
            $this->db = new \PDO($this->dsn,$this->user,$this->password,$this->options);
            $this->connected=true;
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