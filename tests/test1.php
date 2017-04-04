<?php
require '../vendor/autoload.php';

require '../src/OData.php';

$db=ODataMysql::create("sensors","root","supersecret","127.0.0.1");
$options=new ODataOptions();

//$scheme=new ODataScheme();
//$scheme->addEntity(new ODataSchemeEntity("User","Id"));

$arrest=new OData($db,$options);

$arrest->execute();

