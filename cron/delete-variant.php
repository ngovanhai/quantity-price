<?php
// xoa cac variant minh da tao tu version cu
// xoa variant tren 1 product
//https://dev.omegatheme.com/group-price-attribute/cron/delete-variant.php?action=deleteOnlyProduct&product_id=1111&shop=
// xóa trên bảng phụ  khi mình covert
//https://dev.omegatheme.com/group-price-attribute/cron/delete-variant.php?action=deleteFromDB&shop=
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
require '../vendor/autoload.php';

use sandeepshetty\shopify_api;

require '../conn-shopify.php'; 
$shop = $_GET['shop'];
$action = $_GET['action'];
$shopify = shopifyInit($db, $shop, $appId); 

if($action == "deleteFromDB"){
    $variantList = db_fetch_array("select * from price_groups  where shop = '$shop'");
    foreach($variantList as $variant){
        $id_variant = $variant['custom_variant'];
        $getProductByVariant = db_fetch_row("select * from quantity_convert_db where shop = '$shop' and variant_id = '$id_variant'");
        $product_id = $getProductByVariant['product_id'];
        $respon = $shopify("DELETE", "/admin/products/{$product_id}/variants/{$id_variant}.json");
        if($respon){
            echo $getProductByVariant['product_title'];
        }
        sleep(1);
    }
} 
if($action == "deleteOnlyProduct"){
     $product_id = $_GET['product_id'];
     $product = $shopify("GET", "/admin/products/{$product_id}.json?fields=variants"); 
     foreach($product['variants'] as $variants){ 
        $id_variant = $variants['id'];
        $pos = strpos($variants['title'], " and above"); 
         if ($pos != false) {
            $respon = $shopify("DELETE", "/admin/products/{$product_id}/variants/{$id_variant}.json");
            // xoa variant trong bang price_groups
            $check = db_delete("price_groups", "custom_variant = '$id_variant' and shop = '$shop'");
            if($check){
               echo "DONE";
            }
        }
     }   
} 

function db_insert($table, $data) {
    global $db;
    $fields = "(" . implode(", ", array_keys($data)) . ")";
    $values = "";
    foreach ($data as $field => $value) {
        if ($value === NULL)
            $values .= "NULL, ";
        else
            $values .= "'" . addslashes($value) . "', ";
    }
    $values = substr($values, 0, -2);
    db_query("
            INSERT INTO $table $fields
            VALUES($values)
        ");
    return mysqli_insert_id($db);
}
function db_fetch_row($query_string) {
    global $conn;
    $result = array();
    $mysqli_result = db_query($query_string);
    $result = mysqli_fetch_assoc($mysqli_result);
    mysqli_free_result($mysqli_result);
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

function db_delete($table, $where) {
    global $db;
    $query_string = "DELETE FROM " . $table . " WHERE $where";
    db_query($query_string);
    return mysqli_affected_rows($db);
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