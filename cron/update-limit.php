<?php

ini_set('display_errors', TRUE);
error_reporting(E_ALL);
require '../vendor/autoload.php';

use sandeepshetty\shopify_api;

require '../conn-shopify.php';
$shop = $_GET['shop'];
$collection_id = $_GET['collection_id'];
$max = $_GET['max'];
$min = $_GET['min'];
$shopify = shopifyInit($db, $shop, $appId);
$listProduct = getProductByCollection($collection_id, $shopify);
foreach($listProduct as $v){
    foreach($v['variants'] as $v_variant){
        $variant_id = $v_variant['id'];
        $db->query("update variant_limit set max = $max,min = $min  where variant_id = '$variant_id' and shop = '$shop'");
    }
}
function getProductByCollection($collection_id, $shopify) {
    $result = $shopify("GET", "/admin/products.json?collection_id=$collection_id");
    return $result;
}
function shopifyInit($db, $shop, $appId) {
    $select_settings = $db->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
    $app_settings = $select_settings->fetch_object();
    $shop_data = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
    $shop_data = $shop_data->fetch_object();
    $shopify = shopify_api\client(
            $shop, $shop_data->access_token, $app_settings->api_key, $app_settings->shared_secret
    );
    return $shopify;
}

function db_fetch_array($query_string) {
    global $db;
    $result = array();
    $mysqli_result = db_query($query_string);
    while ($row = mysqli_fetch_assoc($mysqli_result)) {
        $result[] = $row;
    }
    mysqli_free_result($mysqli_result);
    return $result;
}

function db_query($query_string) {
    global $db;
    $result = mysqli_query($db, $query_string);
    if (!$result) {
        // db_sql_error('Query Error', $query_string);
        echo ('Query Error' . $query_string);
    }
    return $result;
}

function show_array($data) {
    if (is_array($data)) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}

?>