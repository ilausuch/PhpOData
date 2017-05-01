<?php
require '../vendor/autoload.php';
require '../src/OData.php';

//Load models
require 'models/User.php';
require 'models/Purchase.php';
require 'models/PurchaseProduct.php';


//Declare the DB connector
$db=ODataMysql::create("sample","root","supersecret","127.0.0.1");

//Declare options
$options=new ODataOptions();
$options->defaultAllowedMethods=["GET","POST","PATCH","DELETE"];

//Declare the scheme
$scheme=new ODataScheme([
    new User(),
    new Purchase(),
    new PurchaseProduct(),
    new ODataSchemeEntity("Product")
]);

//Start odata server
$odata=new OData($db,$options,$scheme);

$request=new ODataRequestORM("User", ODataRequest::METHOD_GET);
//$request->Pk(1);
$request->Expand("PurchaseList/ProductList");
//$request->Top(1);
//$request->OrderBy("name desc");
//$request->Filter("name eq 'Ivan'");
//$request->Skip(1);


//$request->prepare("GET","User?\$expand=PurchaseList",[]);
//$request->prepare("GET","User",[]);


try{
    echo "<pre>";
    print_r($odata->executeLocal($request)->getData());
}catch(Exception $e){
    echo($e->getMessage());
}