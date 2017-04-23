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
        
        $query.="`".$comparator->getLeft()."`";
        
        switch ($comparator->getOp()){
            case eq:
            case "=":
                $query.="=";
                break;
            case ne:
            case "<>":
                $query.="<>";
                break;
            case gt:
            case ">":
                $query.=">";
                break;
            case ge:
            case ">=":
                $query.=">=";
                break;
            case lt:
            case "<":
                $query.="<";
                break;
            case le:
                case "<=":
                $query.="<=";
                break;
            //TODO : Other operations
            default:
                throw new Exception("Operation ".$comparator->getOp()." not implemnted in \$filter",ODataHTTP::E_not_implemented);
        }
        
        if ($comparator->getRight()[0]!="'")
            $query.="'".$comparator->getRight()."'";
        else
            $query.=$comparator->getRight();
        
        return $query;
    }
    
    private function processFilterBase(ODataQueryFilterBase $filter){
        if ($filter instanceof ODataQueryFilterAggregator)
            return $this->processFilterAggregator($filter);
        else
            return $this->processFilterComparator($filter);
    }
    
    private function processFilterAggregator(ODataQueryFilterAggregator $aggregator){
        $query="(".$this->processFilterBase($aggregator->getLeft());
        
        if ($aggregator->getRight()){
            //$query.=" ".$aggregator->getOp()." '".$this->processFilterAggregator($aggregator->getRight())."'";
            $query.=" ".$aggregator->getOp()." ".$this->processFilterBase($aggregator->getRight());
        }
        
        $query.=")";
        
        return $query;
    }
    
    /**
     * Prepare orderby
     * @param ODataQueryOrderBy[] $list
     * @return string
     */
    private function prepareOrderBy($orderByList){
        //TODO
        $items=[];
        foreach($orderByList as $orderBy){
            if ($orderBy->getOrder()== ODataQueryOrderBy::ASC)
                $direction="ASC";
            else
                $direction="DESC";
            
            $items[]=$orderBy->getField()." ".$direction;
        }
        
        
        return join(",", $items);
    }
    
    public function internalQuery(){
        //TODO
    }
    
    public function query(ODataQuery $query){
        //Connect to DB
        $this->connect();
        
        //Init strings
        $queryString="SELECT ";
        $whereString.="";
        
        //Selector and from
        $queryString.="{$query->getSelect()} FROM `{$query->getFrom()}`";
    
        //Where for Primary key
        $where=$query->getWhere();

        if ($where!=null && count($where)>0){
            foreach ($where as $k=>$v){
                $whereString.="`{$k}`='{$v}'";
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
        
        //Add order
        $orderByList=$query->getOrderByList();
        if ($orderByList!=null)
            $queryString.=" ORDER BY ".$this->prepareOrderBy($orderByList->getList());
        
        
        //Set top
        if ($query->getTop())
            $queryString.=" LIMIT ".$query->getTop();
        
        //Set offset
        if ($query->getSkip())
            $queryString.=" OFFSET ".$query->getSkip();
         
        //var_dump($queryString);
        
        //Send query
        $stmt=$this->db->prepare($queryString) ;
        $stmt -> execute();
        
        return $stmt->fetchAll();
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
            /*
            $element=$scheme->createElementFromSource($stmt->fetch());
            return $element;
            */
            return $stmt->fetch();
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
        
        //TODO : check if item exists before update, because if nothing is changed, rowCount()==0 and it seems it's an error, but it isn't one
        
        $queryString="UPDATE $table SET ".join(",", $keys)." WHERE ".join(" and ",$pkKeys);
        
        $values=array_merge($values,$pkValues);
        
        $stmt=$this->db->prepare($queryString) ;
        $result=$stmt->execute($values);
        
        return true;
    }
    
    public function delete($element, $table){
        $scheme=$this->discoverTableScheme($table);
        $pk=$scheme->getPk();
        
        //Extract field values for insert
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
        
        $queryString="DELETE FROM $table WHERE ".join(" and ",$pkKeys);
        
        $values=array_merge($values,$pkValues);
        
        $stmt=$this->db->prepare($queryString) ;
        $result=$stmt->execute($values);
        
        $rowsAffected=$stmt->rowCount();
        
        if ($rowsAffected==0)
            ODataHTTP::error (ODataHTTP::E_bad_request,"Element doesn't exist");
        
        return true;
    }
    
    /**
     * Convert type to OData type
     * https://dev.mysql.com/doc/refman/5.7/en/data-types.html
     * https://dev.mysql.com/doc/refman/5.7/en/storage-requirements.html     
     * @param string $type
     * @return string
     */
    private function convertType($type){
        preg_match("/(?P<name>\w+)(\((?P<len>\d+)(,(?P<len2>\d+))?\))?/", $Type, $output_array);
            $mysqlType=$output_array["name"];
            $mysqlLen=$output_array["len"];
            
            switch($mysqlType){
                case BIT:       return ODataSchemePrimitive::Byte;
                    
                case TINYINT:   return ODataSchemePrimitive::Byte;
                case SMALLINT:  return ODataSchemePrimitive::Int16;
                case MEDIUMINT: return ODataSchemePrimitive::Int32; //Must be 24, but doesn't exist
                case INTEGER:   return ODataSchemePrimitive::Int32;
                case INT:       return ODataSchemePrimitive::Int32;
                case BIGINT:    return ODataSchemePrimitive::Int64;
                  
                case DECIMAL:   return ODataSchemePrimitive::Decimal;
                case NUMERIC:   return ODataSchemePrimitive::Decimal;
                
                case FLOAT:     return ODataSchemePrimitive::Double;
                case DOUBLE:    return ODataSchemePrimitive::Double;
                    
                case CHAR:      return ODataSchemePrimitive::String;
                case VARCHAR:   return ODataSchemePrimitive::String;
                case TEXT:      return ODataSchemePrimitive::String;
                
                case BINARY:    return ODataSchemePrimitive::Stream;
                case VARBINARY: return ODataSchemePrimitive::Stream;
                case BLOG:      return ODataSchemePrimitive::Stream;
                
                case ENUM:      return ODataSchemePrimitive::String;
                case SET:       return ODataSchemePrimitive::String;
                    
                case DATE:      return ODataSchemePrimitive::Date;
                case DATETIME:  return ODataSchemePrimitive::DateTimeOffset;
                case TIMESTAMP: return ODataSchemePrimitive::DateTimeOffset;
                    
                case TIME:    return ODataSchemePrimitive::TimeOfDay;
                case YEAR:    return ODataSchemePrimitive::Byte;
            }
    }
    
    /*
     * Extract table scheme from DB
     * @return ODataSchemeEntity The scheme
     */
    public function discoverTableScheme($table){
        if (isset($this->tableSchemes[$table]))
            return $this->tableSchemes[$table];
        
        $this->connect();
        $stmt=$this->db->prepare("describe `{$table}`;") ;
        $stmt->execute();
        
        $fields=[];
        foreach ($stmt->fetchAll() as $field){
            $fields[]=new ODataSchemeEntityField(
                $field["Field"],
                new ODataSchemePrimitive($this->convertType($field["Type"])),
                $field["Null"]=="YES",
                0, //TODO: Extract from type
                $field["Key"]=="PRI",
                $field["Default"],
                $field["Extra"]
            );
        };
        
        $this->tableSchemes[$table]=new ODataSchemeEntity($table);
        $this->tableSchemes[$table]->setFields($fields);
        return $this->tableSchemes[$table];
    }
}