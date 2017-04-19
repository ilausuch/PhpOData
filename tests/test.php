<?php

function execute($url){
    echo $url."\n\n";
    
    $ch=curl_init(htmlentities($url));
    curl_exec($ch);
    echo "\n\n";

    echo "-------------------------------\n";
}

echo "Running tests\n";
echo "-------------------------------\n";

$serverUrl="http://localhost:8000/tests/server.php/odata/";
execute($serverUrl."User?\$orderby=id%20asc");
execute($serverUrl."User?\$orderby=active,id%20desc");
execute($serverUrl."User(1)");
execute($serverUrl."User?\$filter=name%20eq%20'Ivan'");
execute($serverUrl."User?\$filter=name%20eq%20'Ivan'%20or%20Id%20eq%203");
execute($serverUrl."User?\$expand=PurchaseList/ProductList");