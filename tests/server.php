<?php
require '../vendor/autoload.php';
require '../src/OData.php';

//Load models
require 'models/User.php';
require 'models/Purchase.php';
require 'models/PurchaseProduct.php';


/*
session_start();
ODataCrypt::setupSession();
*/

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

/*
$scheme=new ODataScheme([
    new ODataSchemeEntity("User"),
    new ODataSchemeEntity("Purchase"),
    new ODataSchemeEntity("PurchaseProduct"),
    new ODataSchemeEntity("Product")
]);
*/

/*
$json=file_get_contents("scheme.json");
$scheme=ODataScheme::parse($json);
*/

//Start odata server
$odata=new OData($db,$options,$scheme);
$odata->run(true);

/*
 * Examples
 * tests/test1.php/odata/User?$expand=PurchaseList/ProductList/Product
 */