<?php 
// https://dev.omegatheme.com/group-price-attribute/cron/update_version.php?shop=sajaro-invitations.myshopify.com
ini_set('display_errors', TRUE); 
error_reporting(E_ALL); 
ini_set("max_execution_time",0);
require '../vendor/autoload.php'; 
use sandeepshetty\shopify_api; 
require '../conn-shopify.php'; 
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); 
$action = $_GET['action']; 
$shop = $_GET['shop']; 
$shopify = shopifyInit($db, $shop, $appId); 
// update bang phu
if($action == "getProduct"){  
    $listcountVariant = db_fetch_array("select * from custom_variants where shop = '$shop'"); 
    $countVariant     = count($listcountVariant);
    $pages            = ceil($countVariant / 100);
    echo json_encode($pages);
} 
// convert db

if($action == "updateNewDB"){   
    $pages            = $_GET['pages']; 
    $start            = 0; 
    $start = ($pages - 1) * 100;
    $listVariant = db_fetch_array("select * from custom_variants  where shop = '$shop' limit $start,100"); 
    foreach ($listVariant as $k => $v_variant) {     
        $variantID = $v_variant['variant_id'];
        $group_id  = $v_variant['group_id'];
        $group_id  = explode(",",$group_id);  
        $listPriceGroup = array();
        foreach($group_id as $idGroupPrice){
            if($idGroupPrice != ''){ 
                array_push($listPriceGroup,getPriceGroupByID($idGroupPrice,$shop)); 
            }
        }
        $data_insert = array(
            'shop'          => $shop,
            'content_rule'  => json_encode($listPriceGroup),
            'variant_id'    => $variantID
        );
         
        db_insert('quantity_variant',$data_insert);
    }   
    echo json_encode(true);
} 
function db_duplicate($table,$data,$content_duplicate){
    global $db;
    $fields = "(" . implode(", ", array_keys($data)) . ")";
    $values = "(";
    foreach ($data as $field => $value) {
        if ($value === NULL)
            $values .= "NULL, ";
        elseif ($value === TRUE || $value === FALSE)
            $values .= "$value, ";
        else
            $values .= "'" . addslashes($value) . "',";
    }  
    $values = rtrim($values,',');
    $values .= ")";
    $query = "INSERT INTO $table  $fields  VALUES $values ON DUPLICATE KEY UPDATE $content_duplicate";  
    db_query($query);
    return  mysqli_insert_id($db);  
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
function db_update($table, $data, $where) {
    global $db;
    $sql = "";
    foreach ($data as $field => $value) {
        if ($value === NULL) {
            $sql .= "$field=NULL, ";
        } elseif (is_numeric($value)) {
            $sql .= "$field=" . addslashes($value) . ", ";
        } elseif ($value == 'true' || $value == 'false') {
            $sql .= "$field=" . addslashes($value) . ", ";
        } else
            $sql .= "$field='" . addslashes($value) . "', ";
    }
    $sql = substr($sql, 0, -2);
    db_query("
            UPDATE `$table`
            SET $sql
            WHERE $where
    "); 
    return mysqli_affected_rows($db); 
}  
function getProductByVariant($id,$shop){ 
    $result = db_fetch_row("select * from quantity_convert_db where shop ='$shop' and variant_id = '$id'"); 
     return $result; 
}
function getPriceGroupByID($id,$shop){
    $offer = array();
    $result = db_fetch_row("select percent,number from price_groups  where shop = '$shop' AND id = '$id'");  
    $offer['number'] =  $result['number'];
    $offer['price'] =  $result['percent'];
    $offer['discountType'] = 'percent';
    return $offer;
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
    $shopify = shopify_api\client($shop, $shop_data->access_token, $app_settings->api_key, $app_settings->shared_secret );
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