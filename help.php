<?php
use sandeepshetty\shopify_api;
require 'conn-shopify.php';

// DATABASE

function db_query($query_string) {
    global $db;
    $result = mysqli_query($db, $query_string);
    if (!$result) {
         echo ('Query Error' . $query_string); die();
    }
    return $result;
} 
function db_insert($table, $data) {
    global $db;
    $fields = "(" . implode(", ", array_keys($data)) . ")";
    $values = "";

    foreach ($data as $field => $value) {
        if ($value === NULL) {
            $values .= "NULL, ";
        } elseif (is_numeric($value)) {
            $values .= $value . ", "; 
        } elseif ($value == 'true' || $value == 'false') {
            $values .= $value . ", ";
        } else {
            $values .= "'" . addslashes($value) . "', ";
        }
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
function db_delete($table, $where) {
    global $db;
    $query_string = "DELETE FROM " . $table . " WHERE $where";
     db_query($query_string);
    return mysqli_affected_rows($db);
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
function db_fetch_array($query_string) {
    global $db;
    $result = array();
    $mysqli_result = db_query($query_string);
    while ($row = mysqli_fetch_assoc($mysqli_result)) {
        $result[] = $row;
    }
    mysqli_free_result($mysqli_result);
    if(!is_array($result)){
        $result = array();
    }
    return $result;
} 
function db_fetch_row($query_string) {
    global $conn;
    $result = array();
    $mysqli_result = db_query($query_string);
    $result = mysqli_fetch_assoc($mysqli_result);
    mysqli_free_result($mysqli_result);
    if(!is_array($result)){
        $result = array();
    }
    return $result;
}
 
// SHOPIFY
function getCollectionPerPage($shopify,$since_id = 0,$limit = 250,$fields = "id,title,handle",$type="custom"){
    $collections = []; 
    if($type == "custom"){
        $collections = $shopify("GET",APIVERSION."custom_collections.json?published_status=published&limit=250&fields=id,title,handle&since_id=$since_id");
        if(is_array($collections)) return $collections;
    }
    $collections  = $shopify("GET",APIVERSION."smart_collections.json?published_status=published&limit=250&fields=id,title,handle&since_id=$since_id");
    if(is_array($collections)) return $collections;  
    return $collections;
}
function getCountCollection($shopify,$type="custom"){
    $count = 0; 
    if($type == "custom"){
        $count = $shopify("GET",APIVERSION."custom_collections/count.json");   
        return $count;
    }
    $count  = $shopify("GET",APIVERSION."smart_collections/count.json");
    return $count;
}
function deleteWebhook($shopify,$id){
    $result = $shopify("DELETE", APIVERSION."webhooks/".$id.".json");
    return $result;
}
function createWebhook($shopify,$link){
    $webhook = array(
        "webhook" => array(
            "topic" => "products/create",
            "address" => $link,
            "format" => "json"
        )
    ); 
    $result = $shopify("POST", APIVERSION."webhooks.json",$webhook);
    return $result;
}
function editWebhook($shopify,$link,$id){
    $webhook = array(
        "webhook" => array(
            "id"    => $id,
            "topic" => "products/create",
            "address" => $link,
            "format" => "json"
        )
    ); 
    $result = $shopify("PUT", APIVERSION."webhooks.json",$webhook);
    return $result;
}
function getListWebhook($shopify){
    $result = $shopify("GET", APIVERSION."webhooks.json");
    return $result;
}
function shopifyInit($db, $shop, $appId) {
    $select_settings = $db->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
    $app_settings = $select_settings->fetch_object(); 
    $shop_data1 = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
    $shop_data = $shop_data1->fetch_object();
    if(!isset($shop_data->access_token)){
        die("Please check the store: ".$shop." seems to be incorrect access_token.");
    }
    $shopify = shopify_api\client( 
        $shop, $shop_data->access_token, $app_settings->api_key, $app_settings->shared_secret
    );
    return $shopify;
}
function getProductInPage($shopify,$since_id = 0,$limit = 50,$fields = "id,title,handle"){ 
    if(!is_numeric($since_id)) $since_id = 0;
    $products =[];
    $products = $shopify("GET", APIVERSION."products.json?since_id=$since_id&limit=$limit&fields=$fields"); 
    if(!isset($products) || !is_array($products)) return [];
    return $products;
}
function getProductByCollectionID($shopify,$collectionID,$limit=250,$fields = "id,title,handle,variants"){
    if(!isset($collectionID)) return [];
    $products = $shopify("GET", APIVERSION."products.json?collection_id=$collectionID&limit=$limit&fields=$fields");
    if(!isset($products) || !is_array($products)) return [];
    return $products;
}
function getProductByProductID($shopify,$idProduct,$fields = "id,variants"){
    if(!isset($idProduct)) return [];
    $product = $shopify("GET", APIVERSION."products/".$idProduct.".json?fields=$fields"); 
    return $product;
}
function getAllCollection($shopify,$limit=250,$fields="id,title,handle"){
    $collections = [];
    $collections_smart =  [];
    $collections_custom =  []; 
    $smart  = $shopify("GET",APIVERSION."smart_collections.json?published_status=published&limit=$limit&fields=$fields");
    $custom = $shopify("GET",APIVERSION."custom_collections.json?published_status=published&limit=$limit&fields=$fields");
    if(is_array($smart)) $collections_smart = $smart;
    if(is_array($custom)) $collections_custom = $custom; 
    $collections = array_merge($collections_smart,$collections_custom);
    return $collections;
}
function getVariantByProductID($shopify,$product_id){
    if(!isset($idProduct)) return [];
    $variants = $shopify("GET",APIVERSION."products/".$product_id."/variants.json");
    if(!is_array($variants)) return [];
    return $variants;
}
function getCollectionByID($shopify,$collection_id,$limit = 250,$fields = "id,title,handle"){
    if(!isset($collection_id)) return [];
    $infoCollectionByID = [];
    $infoCollectionByID = $shopify("GET",APIVERSION."custom_collections/".$collection_id.".json?fields=$fields&limit=$limit");
    if(is_array($infoCollectionByID) && count($infoCollectionByID) > 0){
        return $infoCollectionByID; 
    }else{
        $infoCollectionByID = $shopify("GET",APIVERSION."smart_collections/".$collection_id.".json?fields=$fields&limit=$limit");
        if(is_array($infoCollectionByID) && count($infoCollectionByID) > 0){
            return $infoCollectionByID; 
        } 
    }
    return $infoCollectionByID;
} 
function getCountProductByCollection($collection_id,$shopify){
    if(!isset($collection_id)) return 0;
    $countProduct  = $shopify("GET", APIVERSION."products/count.json?collection_id=".$collection_id."&fields=id");
    return $countProduct;
}
function getCountAllProduct($shopify){
    $counProduct  = $shopify("GET",APIVERSION."products/count.json"); 
    return $counProduct;
}
function getPriceRule($shopify,$since_id,$limit = 250){
    $result = [];
    $result = $shopify('GET',APIVERSION."price_rules.json?since_id=$since_id&limit=$limit");
    if(!is_array($result)) return [];
    return $result;
}
function getPriceRuleFromDB($shop,$title){
    $result = db_fetch_row("SELECT * FROM quantity_price_rule WHERE shop = '$shop' AND title = '$title' ");
    return $result;
}
function getTotalPriceRule($shopify){
    $result = 0;
    $result = $shopify('GET',APIVERSION.'price_rules/count.json');
    if(is_array($result)) return 0;
    return $result;
}
function getCustomColletionByProductID($shopify,$IDProduct){
    if(!isset($IDProduct)) return [];
    $collections = $shopify("GET", APIVERSION."custom_collections.json?product_id=$IDProduct"); 
    if(!is_array($collections)) return [];
    return $collections;
}
function getSmartColletionByProductID($shopify,$IDProduct){
    if(!isset($IDProduct)) return [];
    $collections = $shopify("GET", APIVERSION."smart_collections.json?product_id=$IDProduct"); 
    if(!is_array($collections)) return [];
    return $collections;
}
function postDataPriceRule($shopify,$data){
    if(!isset($data) || (!isset($shopify))) return [];
    $newDiscountRule = $shopify("POST", APIVERSION."price_rules.json", $data); 
    return $newDiscountRule;
}
function postDiscountCode($shopify,$discountRuleID,$data){
    if(!isset($data) || (!isset($discountRuleID))) return [];
    $newDiscountCode = $shopify("POST", APIVERSION."price_rules/". $discountRuleID ."/discount_codes.json", $data);
    return $newDiscountCode;
}
function postDataDraftOrder($shopify,$data){
    if(!isset($data) || (!isset($shopify))) return [];
    $response = $shopify("POST", APIVERSION."draft_orders.json", $data);  
    return $response;
}
function getMoneyFormat($shopify){  
    $shopInfo = $shopify("GET", APIVERSION."shop.json");  
    $result = array();
    if(isset($shopInfo['money_format'])){
        $result['money_format'] = $shopInfo['money_format'];
        $result['money_with_currency_format'] = $shopInfo['money_with_currency_format'];
    }else{
        $result['money_format'] = NULL;
        $result['money_with_currency_format'] = NULL;
    } 
    return $result;
}
function getCustomer($shopify,$customerId) { 
    if(!isset($customerId)) return [];
    $result = $shopify('GET', APIVERSION."customers/{$customerId}.json");
    return $result;
}
// RULE FUNCTION
function getRuleByProductID($id,$shop,$date = ""){
    if(!isset($shop) || !isset($id)) return [];
    $rule = db_fetch_row("select * from quantity_products where shop = '$shop' and product_id = '$id' $date"); 
    return $rule;
}
function getRuleByVariantID($id,$shop,$date = ""){
    if(!isset($shop) || !isset($id)) return [];
    $rule = db_fetch_row("select * from quantity_variant where variant_id = '$id' and shop = '$shop' $date");
    return $rule;
}
function getRuleByCollectionID($idCollection,$shop,$date = ""){
    if(!isset($shop) || !isset($idCollection)) return []; 
    $rule = db_fetch_row("select * from quantity_collection where collection_id = '$idCollection' and shop = '$shop' $date"); 
    return $rule;
}
function getRuleGlobal($shop,$condition = "and status = 'active'",$date = ""){
    if(!isset($shop)) return [];  
    $rule = db_fetch_row("select * from quantity_global where shop = '$shop' $condition $date"); 
    return $rule;
}
function getSettings($shop,$fields = "*"){
    if(!isset($shop)) return []; 
    $settings = db_fetch_row("select $fields from custom_order_settings where shop ='".$shop."'");  
    return $settings;
}
 
function getAllRuleByType($shop,$type="global"){
    /*
        typerule :  collection,global,products,variant
    */
    if(!isset($shop)) return [];  
    $rules = db_fetch_row("select * from quantity_$type where shop = '$shop'");
    return $rules;
}
function getRuleLimitByVariantID($variantID,$shop,$all = 0){
    if(!isset($shop) || !isset($variantID)) return [];  
    if($all == 0){
        $time = date("m/d/Y h:m"); 
        $where  = "AND (( end_date >= '$time' AND start_date <= '$time') OR (end_date = '' AND start_date <= '$time') OR (end_date = '' AND start_date = '') OR (end_date IS NULL AND `start_date` IS NULL))";
    }else{
        $where ="";
    }
    $result = db_fetch_row("select * from variant_limit where shop='$shop' AND variant_id = '$variantID' $where"); 
    if(!empty($result)){
        if($result['limitforCustomerTag'] != null && $result['limitforCustomerTag'] != []){
            $result['limitforCustomerTag'] = json_decode($result['limitforCustomerTag'],true);
        }
    }
    return $result;
}
function getAllRuleLimit($shop){
    $result = db_fetch_array("SELECT * FROM variant_limit where shop = '$shop' AND variant_id IS NULL");
    echo json_encode($result);
}
function checkItemInArray($mainArray,$array,$feild){  
    foreach($mainArray as $mainItem){
        foreach($array as $item){
            if(isset($mainItem[$feild])){
                if($mainItem[$feild] == $item){
                    return $mainItem;
                }
            }else{
                if($mainItem == $item){
                    return $mainArray;
                }
            }
        }
    }
    return [];
}
function getCustomerTag($data,$shopify){
    if ($data["customerId"] != "") {
        $customerId = $data["customerId"];
        $customer = getCustomer($shopify,$customerId);
        if(!isset($customer['tags'])) $customer['tags'] = '';
        $customer['tags'] = str_replace(" ", "", $customer['tags']);
        $customerTags = explode(",", $customer['tags']);
    } else {
        $customer     = "";
        $customerTags = [];
    }   
    return $customerTags;
}
function checkDiscountByCollectionID($shop,$collections,$minPrice){
    $key_path = CACHE_PATH.$shop."collection"; 
    $result = [
        'newPrice' => NULL,
        'isDiscount' => FALSE
    ];
    if(!is_file($key_path)) return $result; 
    $data_cache = file_get_contents($key_path);
    $dataCollections = json_decode($data_cache,true);  
    foreach($collections as $collection){
        $collectionID = $collection['id']; 
        foreach($dataCollections as $dataCollection){
            if($dataCollection['collection_id'] == $collectionID){ 
                $content_rule = json_decode($dataCollection['content_rule'],true); 
                if($content_rule[count($content_rule) - 1]['discountType'] == "percent"){
                    $result['newPrice'] = $minPrice - $minPrice * ($content_rule[count($content_rule) - 1]['price']/100);
                }else{
                    $result['newPrice'] = $minPrice -  $content_rule[count($content_rule) - 1]['price'];
                }
                $result['isDiscount'] = true;
            }
        } 
    }  
    return $result; 
}
// orther function
function checkExistArray($array1, $array2) {
    if (is_array($array1) && is_array($array2)) {
        $check = array();
        foreach ($array1 as $v1) {
            array_push($check, $v1);
        }
        foreach ($array2 as $v2) {
            if (in_array($v2, $check)) {
                return $result = 1;
                break;
            } else {
                $result = 0;
            }
        }
    } else {
        return 0;
    }
    return $result;
} 
function getmicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
} 
function cvf_convert_object_to_array($data) {
    if (is_object($data)) {
        $data = get_object_vars($data);
    }
    if (is_array($data)) {
        return array_map(__FUNCTION__, $data);
    } else {
        return $data;
    }
} 
function creatSlug($string, $plusString) {
    $search = array(
        '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
        '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
        '#(ì|í|ị|ỉ|ĩ)#',
        '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
        '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
        '#(ỳ|ý|ỵ|ỷ|ỹ)#',
        '#(đ)#',
        '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
        '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
        '#(Ì|Í|Ị|Ỉ|Ĩ)#',
        '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
        '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
        '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
        '#(Đ)#',
        "/[^a-zA-Z0-9\-\_]/",
    );
    $replace = array(
        'a',
        'e',
        'i',
        'o',
        'u',
        'y',
        'd',
        'A',
        'E',
        'I',
        'O',
        'U',
        'Y',
        'D',
        '-',
    );
    $string = preg_replace($search, $replace, $string);
    $string = preg_replace('/(-)+/', '-', $string);
    $string = strtolower($string);
    return $string . $plusString;
} 
function pr($data) {
    if (is_array($data)) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }else{
        var_dump($data);
    }
} 
function redirect($data)  {
    header("Location: $data");
} 
function getCurl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1');
    $response = curl_exec($ch);
    if ($response === false) {
        $api_response = curl_error($ch);
    } else {
        $api_response = $response;
    }
    curl_close($ch);
    return $api_response;
} 
function valaditon_get($data) {
    $data = "";
    if($data)  return $data;
    return $data;
} 
function result_fetch_object($data) {
    $result = $data->fetch_object();
    return $result;
} 
function getTime($shopify){
    $shopInfo  = $shopify("GET",APIVERSION."shop.json");
    if(!isset($shopInfo['iana_timezone'])) $shopInfo['iana_timezone'] = 'UTC'; 
    date_default_timezone_set($shopInfo['iana_timezone']);
    $today = date("Y-m-d"); 
    $yesterday = date("Y-m-d", time() - 60*60*24); 
    $week = date("Y-m-d", time() - 60*60*24*7); 
    $month = date("Y-m-d", time() - 60*60*24*30); 
    $lastmonth = date("Y-m-d", time() - 60*60*24*30*2);  
    return [
        'today'     => $today,
        'yesterday' => $yesterday,
        'week'      => $week,
        'month'     => $month,
        'lastmonth' => $lastmonth,
    ];
}