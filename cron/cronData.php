<?php 
// chay file html truoc
ini_set('display_errors', TRUE); 
error_reporting(E_ALL); 
ini_set("max_execution_time",0);
require '../vendor/autoload.php'; 
use sandeepshetty\shopify_api; 
require '../conn-shopify.php'; 
 
header('Cache-Control: no-cache'); 
 
if(isset($_GET['shop'])){ 
    //SHOP
    $shop = $_GET['shop'];
    $shopify = shopifyInit($db, $shop, $appId); 
    $respon = cronDbByShop($shop);
    if($respon == true){
        echo "Updated for store:".$shop." -- done";
    }else{
        echo "Updated for store:".$shop." -- fail";
    }
}else{

    // ALLSHOP
    $listShop = db_fetch_array("SELECT a.store_name,b.shop FROM tbl_usersettings as a join quantity_group_offer as b where a.store_name = b.shop GROUP BY b.shop,a.store_name");
     
    echo "All shop:".count($listShop);
    echo "<br/>";
    $i = 0;
    foreach($listShop as $list){ 
        $shop = $list['store_name'];
        $respon = cronDbByShop($shop);
        if($respon == true){
            $i++;
            echo "updated for: ".$shop." -- <b>successfully!</b>";
            echo "<br/>";
        }else{
            echo "updated for: ".$shop." -- <b>fail</b>";
            echo "<br/>";
        }
    }
    echo "Updated:".$i." shop -- done !";
    echo "<br/>";
}
 
function cronDbByShop($shop){
    $result = false;
    $collectionList = db_fetch_array("select * from quantity_product as a join quantity_group_offer as b where a.idOffer = b.id and a.shop = '$shop' and b.shop = '$shop' and b.useCollection = 1 ");
    $productList = db_fetch_array("select * from quantity_product as a join quantity_group_offer as b where a.idOffer = b.id and a.shop = '$shop' and b.shop = '$shop' and a.product_id IS NOT NULL and a.product_id != ''  and b.useCollection = 0");
    $variantList = db_fetch_array("select * from quantity_product as a join quantity_group_offer as b where a.idOffer = b.id and a.shop = '$shop' and b.shop = '$shop' and a.product_title is NULL");
      foreach($collectionList as $collection){
        $collection_id = $collection['collection_id'];
        $content_offer = cv_content_rule($collection['content_offer']); 
        $data_collection = array(
            'collection_id' => $collection_id,
            'content_rule' => $content_offer,
            'shop' => $shop,
        );
        $content_duplicate = "collection_id = $collection_id,content_rule = '$content_offer',shop = '$shop'";
        $respone = db_duplicate('quantity_collection',$data_collection,$content_duplicate);
        if($respone){
            $result = true;
        }
    }
    foreach($productList as $product){
        $product_id = $product['product_id'];
        $content_offer = cv_content_rule($product['content_offer']);
        $data_product = array(
            'product_id' => $product_id,
            'content_rule' => $content_offer,
            'shop' => $shop,
        );
        $content_duplicate = "product_id = $product_id,content_rule = '$content_offer',shop = '$shop'";
        $respone = db_duplicate('quantity_products',$data_product,$content_duplicate); 
        if($respone){
            $result = true;
        }
    }
    foreach($variantList as $variant){
        $variant_id = $variant['variant_id'];   
        $content_offer = cv_content_rule($variant['content_offer']);
        $data_variant_id = array(
            'variant_id' => $variant_id,
            'content_rule' => $content_offer,
            'shop' => $shop,
        );
        $content_duplicate = "variant_id = $variant_id,content_rule = '$content_offer',shop = '$shop'";
        $respone = db_duplicate('quantity_variant',$data_variant_id,$content_duplicate);
        if($respone){
            $result = true;
        }
    }
    return $result;
}
function cv_content_rule($content_rule){
    $content_rule = json_decode($content_rule,true);
    $result = array();
    foreach($content_rule as $k=>$v){
         $result[$k]['discountType'] = $v['percent'];
         $result[$k]['price'] = $v['price'];
         $result[$k]['number'] = $v['number'];
    }
     return json_encode($result);
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