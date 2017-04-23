<?php

function get($url){
    echo "GET ".$url."\n\n";
    
    $ch=curl_init(htmlentities($url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    
    if(curl_error($ch)){
        die('error:' . curl_error($ch));
    }
    else{
        echo "success: ";
        $result=json_decode($result,true);
        print_r($result);
    }
    
    echo "\n\n";
    echo "-------------------------------\n";
    
    return $result;
}

function post($url,$data){
    echo "POST ".$url."\n\n";
    
    $dataString=json_encode($data);
    
    $ch=curl_init(htmlentities($url));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($dataString))
    );
    
    $result = curl_exec($ch);
    
    
    if(curl_error($ch)){
        die('error:' . curl_error($ch));
    }
    else{
        echo "success: ";
        $result=json_decode($result,true);
        print_r($result);
    }
    
    
    echo "\n\n";
    echo "-------------------------------\n";
    return $result;
}

function patch($url,$data){
    echo "PATCH ".$url."\n\n";
    
    $dataString=json_encode($data);
    
    $ch=curl_init(htmlentities($url));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);                                                                  
    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($dataString))                                                                       
    );
    
    curl_exec($ch);
    
    if(curl_error($ch)){
        die('error:' . curl_error($ch));
    }
    else{
        echo "success: ";
    }
    
    
    echo "\n\n";
    echo "-------------------------------\n";
}

function delete($url){
    echo "DELETE ".$url."\n\n";
    
    
    $ch=curl_init(htmlentities($url));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");                                                                   
    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($dataString))                                                                       
    );
    
    curl_exec($ch);
    
    if(curl_error($ch)){
        die('error:' . curl_error($ch));
    }
    else{
        echo "success: ";
    }
    
    
    echo "\n\n";
    echo "-------------------------------\n";
}

echo "Running tests\n";
echo "-------------------------------\n";

$serverUrl="http://localhost:8000/tests/server.php/odata/";
/*
get($serverUrl."User?\$orderby=id%20asc");
get($serverUrl."User?\$orderby=active,id%20desc");
get($serverUrl."User(1)");
get($serverUrl."User?\$filter=name%20eq%20'Ivan'");
get($serverUrl."User?\$filter=name%20eq%20'Ivan'%20or%20Id%20eq%203");
get($serverUrl."User?\$expand=PurchaseList/ProductList");
*/

//Complete test of insert, modify, get and delete
$element=post($serverUrl."Product",[name=>"test",price=>3.5]);
patch($serverUrl."Product({$element["id"]})",[price=>5.4]);
$element2=get($serverUrl."Product({$element["id"]})");
delete($serverUrl."Product({$element["id"]})");