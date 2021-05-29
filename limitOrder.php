<?php 
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *'); 
date_default_timezone_set('UTC');
header('Content-Type: application/json');
require 'vendor/autoload.php';
use sandeepshetty\shopify_api;
require 'conn-shopify.php';
require 'help.php'; 
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $action = $_POST['action'];
    $shop = $_POST['shop'];
    $shopify = shopifyInit($db, $shop, $appId);
    if($action == "checkLimitVariantInCart"){  
        if(isset($_POST['variant_id'])){
            $variant_id = $_POST['variant_id']; 
            $result = getRuleLimitByVariantID($variant_id,$shop);
            if(isset($result['limitforCustomerTag'])){ 
                if($result['limitforCustomerTag'] != NULL || $result['limitforCustomerTag'] != ""){
                    $limitforCustomerTag = $result['limitforCustomerTag'];
                    $customerTags = getCustomerTag($_POST,$shopify);
                    $ruleLimitSatisfyTag = checkItemInArray($limitforCustomerTag,$customerTags,'tag');
                    if(isset($ruleLimitSatisfyTag['min']) && isset($ruleLimitSatisfyTag['max'])){
                        if(isset($result['multiple']) && $result['multiple'] != ""){
                            $ruleLimitSatisfyTag['multiple'] = $result['multiple'];
                        }
                        $result = $ruleLimitSatisfyTag;
                    } 
                }   
            }
            $settings       = getSetting($shop);
            $response = array( 
                "result" => $result,
                "settings"  => $settings
             );
        }else{
            $response = array(
                "result" => null,
                "settings"  => null
             );
        }
        
        echo json_encode($response);
    } 
} 
function getSetting($shop){
    $result = db_fetch_row("select max_text,min_text,notificationMultiple from custom_order_settings where shop = '$shop'");
    return  $result;
}
 ?>