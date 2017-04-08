<?php
require '../vendor/autoload.php';
require '../src/OData.php';

$db=ODataMysql::create("sensors","root","supersecret","127.0.0.1");
$options=new ODataOptions();

$json= file_get_contents("scheme.json");
$scheme= ODataScheme::parse($json);

$arrest=new OData($db,$options,$scheme);

$arrest->execute();

