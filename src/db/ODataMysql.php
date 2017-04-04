<?php
class ODataMysql extends ODataDBAdapter{
    private $tableSchemes;
    
    public static function create($dbName,$user,$password,$host="localhost",$port=3306){
        return new ODataMysql(
                "mysql:host=$host;port=$port;dbname=$dbName;",
                $user,
                $password,
                array(
                    \PDO::ATTR_AUTOCOMMIT => true,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_general_ci", time_zone = "+00:00";',
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                )
        );
    }
    
    function __construct($dns,$user,$password,$options) {
        parent::__construct($dns,$user,$password,$options);
        $this->connected=false;
        $this->tableSchemes=[];
    }
    
    private function processFilterComparator(ODataQueryFilterComparator $comparator){
        $query="";
        
        if ($comparator->getNot())
            $query.="NOT ";
        
        $query.=$comparator->getLeft();
        
        switch ($comparator->getOp()){
            case eq:
                $query.="=";
                break;
            case ne:
                $query.="<>";
                break;
            case gt:
                $query.=">";
                break;
            case ge:
                $query.=">=";
                break;
            case lt:
                $query.="<";
                break;
            case le:
                $query.="<=";
                break;
            //TODO : Other operations
            default:
                throw new Exception("Operation ".$comparator->getOp()." not implemnted in \$filter",ODataHTTP::E_not_implemented);
        }
        
        $query.=$comparator->getRight();
        
        return $query;
    }
    
    private function processFilterAggregator(ODataQueryFilterAggregator $aggregator){
        $query="(";
        $query.=$this->processFilterComparator($aggregator->getLeft());
        
        if ($aggregator->getRight()){
            $query.=" ".$aggregator->getOp()." ".$this->processFilterAggregator($aggregator->getRight());
        }
        
        $query.=")";
        
        return $query;
    }
    
    public function query(ODataQuery $query){
        //Connect to DB
        $this->connect();
        
        //Init strings
        $queryString="SELECT ";
        $whereString.="";
        
        //Selector and from
        $queryString.="{$query->getSelect()} FROM {$query->getFrom()}";
    
        //Where for Primary key
        $where=$query->getWhere();

        if ($where!=null && count($where)>0){
            foreach ($where as $k=>$v){
                $whereString.="{$k}='{$v}'";
            }

            $whereString.=" ";
        }
        
        //Where for filters
        $filter=$query->getFilterAggregator();
        if (isset($filter)){
            if ($whereString!="")
                $whereString.=" and ";
                $whereString.=$this->processFilterAggregator($filter);
        }
        
        //Add where
        if ($whereString!="")
            $queryString.=" WHERE ".$whereString;
        
        //Set top
        if ($query->getTop())
            $queryString.=" LIMIT ".$query->getTop();
        
        //Set offset
        if ($query->getSkip())
            $queryString.=" OFFSET ".$query->getSkip();
         
        //Send query
        $stmt=$this->db->prepare($queryString) ;
        $stmt -> execute();
        
        $scheme=$this->discoverTableScheme($query->getFrom());
        
        $result=[];
        foreach ($stmt->fetchAll() as $item){
            $result[]=$scheme->createElementFromSource($item);
        }
        
        return $result;
    }
    
    public function insert($element, $table){
        $scheme=$this->discoverTableScheme($table);
        $pk=$scheme->getPk();

        //Extract elements for insert
        $keys=[];
        $values=[];
        $fakeValues=[];
        foreach ($element as $k=>$v){
            $keys[]="`".$k."`";
            $fakeValues[]="?";
            $values[]=$v;
        }
        
        //Perform insert
        $queryString="INSERT INTO $table (".join(",", $keys).") VALUES (".join(",",$fakeValues).")";
        $stmt=$this->db->prepare($queryString) ;
        $result=$stmt->execute($values);
        
        if ($result==true){
            $scheme=$this->discoverTableScheme($table);
            $pk=$scheme->getPk();
            
            //Check if PK is definned (only for one key)
            //TODO : For multiple pk
            if (isset($element[$pk[0]->getName()])){
                $pkValue=$element[$pk[0]->getName()];
            }else{
                $pkValue = $this->db->lastInsertId();
            }
            
            $queryString="SELECT * FROM $table WHERE `{$pk[0]->getName()}`=\"$pkValue\"";
            $stmt=$this->db->prepare($queryString);
            $result=$stmt->execute($values);
            //TODO : Check result and throw exception if fails
            
            //Create element and return it
            $element=$scheme->createElementFromSource($stmt->fetch());
            return $element;
        }
        else{
            throw new Exception("Cannot insert into $table. ".json_encode($this->db->errorInfo()),500);
        }
    }
    
    public function update($element, $table){
        $scheme=$this->discoverTableScheme($table);
        $pk=$scheme->getPk();

        //Extract elements for insert
        $keys=[];
        $values=[];
        
        $pkKeys=[];
        $pkValues=[];
        
        foreach ($element as $k=>$v){
            if ($k==$pk[0]->getName()){
                $pkKeys[]="`".$k."`=?";
                $pkValues[]=$v;
            }
            else{
                $keys[]="`".$k."`=?";
                $values[]=$v;
            }
        }
        
        if (count($pkKeys)==0)
            throw new Exception("It requires IDs fields and values",ODataHTTP::E_bad_request);
        
        $queryString="UPDATE $table SET ".join(",", $keys)." WHERE ".join(" and ",$pkKeys);
        
        $values=array_merge($values,$pkValues);
        
        $stmt=$this->db->prepare($queryString) ;
        $result=$stmt->execute($values);
    }
    /*
     * Extract table scheme from DB
     * @return ODataSchemeEntity The scheme
     */
    public function discoverTableScheme($table){
        if (isset($this->tableSchemes[$table]))
            return $this->tableSchemes[$table];
        
        $this->connect();
        $stmt=$this->db->prepare("describe {$table};") ;
        $stmt->execute();
        
        $fields=[];
        foreach ($stmt->fetchAll() as $field){
            $fields[]=new ODataSchemeEntityField(
                $field["Field"],
                $field["Type"], //TODO: Get only type
                $field["Null"]=="YES",
                0, //TODO: Extract from type
                $field["Key"]=="PRI",
                $field["Default"],
                $field["Extra"]
            );
        };
        
        $this->tableSchemes[$table]=new ODataSchemeEntity($table,$fields);
        return $this->tableSchemes[$table];
    }
}