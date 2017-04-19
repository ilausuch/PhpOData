# PhpOData
Authomatic OData exposition of DB entities &amp; security addons 

# Configuration

## Configure DB connection.

You must use a connector. 

```php
$db=<CONNECTOR>::create(<data base>,<user>,<password>,<host>);
```

For instance create a MYSQL connector

```php
$db=ODataMysql::create("sample","root","supersecret","127.0.0.1");
```

## Configure OData server options

```php
$options=new ODataOptions();
```

### Allow any origin
$options->allowAnyOrigin=false;

### Enable option request
$options->enableOptionsRequest=true;

### Add client list
$options->clients=[];

### Allow query method modifiers
$options->allowQueryMethodModifiers=false;


## Define the scheme

scheme=new ODataScheme([
    new User(),
    new Purchase(),
    new PurchaseProduct(),
    new ODataSchemeEntity("Product")
]);

# Execute OData server
$odata=new OData($db,$options,$scheme);
$odata->execute();


## Define an entity scheme

### Define query options

```php
public function sequrity_visibleEntity($entity){
    return $entity["active"]==1;
}

public function query_db_conditions(){
    //$comparator=new ODataQueryFilterComparator();
    //$comparator->init("active","=","1");
    //return ODataQueryFilterAggregator::CreateAndList([$comparator]);

    //return ODataQueryFilterAggregator::CreateAndList([ODataQueryFilterComparator::parse("active=1")]);

    return ODataQueryFilterComparator::parse("active=1");
}
```

### Order by
public function query_db_orderby() {
        $list= new ODataQueryOrderByList();
        $list->add(new ODataQueryOrderBy("name",ODataQueryOrderBy::DESC));
        return $list;
    }


public function query_db_limit(){
        return 1;
    } 


/*
    public function query_postprocessList($list){
        foreach ($list as &$item){
            $item["ext"]=rand();
        }
        return $list;
    }
     */
    
    /*
    public function query_postprocessEntity($entity){
        $entity["ext"]=rand();
        return $entity;
    }
    */

public function query_postprocessField(ODataSchemeEntityField $field, $value){
        if ($field->getName()=="name")
            return strtoupper ($value);
        else
            return $value;
    }