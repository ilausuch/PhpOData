# PhpOData

This is an automatic OData exposition of DB entities with security and tranformation addons.

# Quick start

Basic example:

```php
<?php
//Add requires
require 'vendor/autoload.php';
require 'phpOData/OData.php';

//Declare the DB connector
$db=ODataMysql::create("sample","root","supersecret","127.0.0.1");

//Declare options. 
//In this case, use default options
$options=new ODataOptions();

//Declare the scheme. 
//In this case a list of tables will be exposed
$scheme=new ODataScheme(["User","Product"]);

//Start odata server
$odata=new OData($db,$options,$scheme);
$odata->execute();
```

There are four steps:

 1. Create DB connector
 2. Create Options configuration
 3. Create Scheme
 4. Create and launch server

# Pre-requisites

PhpOData server requires and it is tested on:

 - PHP 5.5.38
 - SLIM framework 3.8.1

ApiGet library is not needed. It is only for documentation purpose.

# Configuration
## DB connector

You must use a connector. 

```php
$db=<CONNECTOR>::create(<data base>,<user>,<password>,<host>);
```

For instance create a MYSQL connector

```php
$db=ODataMysql::create("sample","root","supersecret","127.0.0.1");
```

## Options

When a ODataOptions is created It gets default option values. All attributes of this object are public, so you can modify them

```php
$options=new ODataOptions();
```

 allowAnyOrigin (false)
 : Allow any origin connection

enableOptionsRequres (true)
: TODO, define this

clients ([])
: TODO, define this

allowQueryMethodModifiers (false)
: TODO, define this

defaultAllowedMethods
: TODO, define this
  
## Scheme

An scheme is the list of exposed tables/views from DB. 

```php
scheme=new ODataScheme([<list of scheme entities>]);
```

To simplify you can define a list of strings. All these entities are exposed directly without filters, transformations or any restriction. In the example, User and Purchased are exposed.

```php
scheme=new ODataScheme(["User","Purchase"]);
```

If OData is expossed to public, for instance with javascript access. This way will be very dangerous because, you can add, modify, delete, list all users, or view the email or password.

Is recomended to create a class extended from ODataSchemeEntity and controls the information. And also include associations
 
```php
scheme=new ODataScheme([
    new User(),
    new Purchase(),
]);
```

### Defining an entity scheme

The minimal declaration is:

```php
class User extends ODataSchemeEntity{
    function __construct() {
        parent::__construct(__CLASS__);

		//TODO: Associations
    }

	//TODO: Override security, modifiers and transformation methods
}
```

Fields are automatically extracted from DB so, you don't have to worry about that. However, you can define it manually.

You can overwride the methods you need:

 - Security
	 - security_allowedMethod
	 - security_visibleField
	 - sequrity_visibleEntity
	 - security_allowExtends
	 - security_preprocesInputField
 - DB query modifiers
	 - query_db_conditions
	 - query_db_orderby
	 - query_db_limit
 - Output transformations
	 - query_postprocessField
	 - query_postprocessEntity
	 - query_postprocessList


#### security_allowedMethod(\$method) : bool

This is used to ensure that can execute a method over this scheme. Returns true if method is allowed, other ways false. By default uses defaultAllowedMethods from configuration options.

Current implemented methods are:

 - GET
 - POST
 - PATCH

In this example only when the user is admin all methods are allowed, other ways only get is allowed.

```php
public function security_allowedMethod($method){
	$context=OData::$object->getContext();
	if ($context->get("User")->isAdmin())
		return true;
	else
		return in_array($method,["GET"]);
}
``` 

#### security_visibleField(ODataSchemeEntityField \$field, \$entity, \$value) : bool

Check if field is visible in result.

For instance, we can hide password from User list

```php
public function security_visibleField(ODataSchemeEntityField $field,$entity,$value){
	return $field->getName()!="Password";        
}
```
 
#### sequrity_visibleEntity(\$entity) : bool

Check if this entity will returned in result.

In this case, only completed purchases will be returned

```php
public function sequrity_visibleEntity($entity){
	return $entity["Completed"]==1;
}
```

#### security_allowExtends(ODataSchemeEntityAssociation \$association) : bool 

Check if it is allowed to extends an association

For instance, only admin can extends the purchases from a product

```php
public function security_allowExtends(ODataSchemeEntityAssociation $association){
	$context=OData::$object->getContext();
	
	if ($association->getName()=="PurchaseProduct")
		return $context->get("User")->isAdmin();
	else
		return true; //Other associations are allowed
}
```

#### security_preprocesInputField(ODataSchemeEntityField \$field, \$value,  \$method) : string

Transform a value input before a creation or update.

For instance, password will be the md5 of given password in POST and PATCH

```php

public function security_preprocesInputField(ODataSchemeEntityField $field, $value, $method){
    if ($field->getName("Password") && in_array($method,["POST","PATCH"]){
		return md5($value);
	}else
		return $value;
}
```

#### query_db_conditions() : ODataQueryFilterAggregator
Returns an aggregator for query forced conditions in DB query. Alternativelly you can filter the result using postProcess methods

For instance, normal use only can list active products, admin, all of them

```php
public function query_db_conditions(){
    $context=OData::$object->getContext();
	if ($context->get("User")->isAdmin())
		return null;
		
	return ODataFilterParser::parse("active=1");
}
```

Note: see ODataQueryFilterAggregator and ODataFilterParser for more information


#### query_db_orderby() : ODataQueryOrderByList

Returns a list of orderby elements to sort the result in DB query. Alternativelly you can filter the result using postProcess methods

For instance, You can order the Product list by price from expensive to cheeper from DB

public function query_db_orderby() {
        return new ODataQueryOrderByList(new ODataQueryOrderBy("Price",ODataQueryOrderBy::DESC));
}

#### query_db_limit(): int
Returns the max elements to return in DB query. Alternativelly you can filter the result using postProcess methods. It override $top OData filter.

For instance, to get a product list max number of elements will be 10. In odata you can use $skip for perform a pagination

```php
public function query_db_limit(){
	return 10;
}
```

#### query_postprocessField(ODataSchemeEntityField \$field, \$value) : string

Allows to manipulate the value of each entity to return.

For instance,  if user is premium apply a 20% discount to the price of products

```php
public function query_postprocessField(ODataSchemeEntityField $field, $entity, $value){
	$context=OData::$object->getContext();
	
	if ($field->getName("Price") && $context->get("User")->isPremium())
		return floatval($value)*0,8;
	else
		return $value;
}
```

#### query_postprocessEntity(\$entity) : object

It is possible to modify an entity before be returned.

For instance, It is possible to calc the final price of product usign the discount field and applyDiscount is enabled

```php
public function query_postprocessEntity($entity){
	if ($entity["applyDiscount"]==1)
		$entity["price"]=floatval($entity["price"])*(1-floatval($entity["discount"])/100);
	
	return $entity;
}
```

#### query_postprocessList(\$list) : array

It is possible to manipulate the result list before be returned.

For instance,  in this case admin will be returned from user list

```php
public function query_postprocessList($list){
	$newList=[];
	
	foreach ($list as $entity){
		if ($entity["user"]!="admin")
			$newList[]=$entity;
	}

	return $newList;
}
```

### Defining associations
TODO

### Defining filters
TODO

### Defining orderby
TODO


# Tools

## ODataSchemeEntityGeneralTools
TODO

## ODataCrypt
TODO

## ODataContext
TODO

## Define your own DB connector
TODO

# License

MIT License 
@2017 Ivan Lausuch <ilausuch@gmail.com>



