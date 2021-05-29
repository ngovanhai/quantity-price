<?php
ini_set('display_errors', TRUE);
error_reporting(E_ALL); 
date_default_timezone_set('UTC');

require 'vendor/autoload.php'; 
use sandeepshetty\shopify_api; 
require 'conn-shopify.php';
$select_settings = $db->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
$app_settings = $select_settings->fetch_object();
if (!empty($_GET['shop']) && !empty($_GET['code'])) { 
    $shop = $_GET['shop']; //shop name
    //get permanent access token
    $access_token = shopify_api\oauth_access_token(
            $_GET['shop'], $app_settings->api_key, $app_settings->shared_secret, $_GET['code']
    ); 
    $installed = checkInstalled($db, $shop, $appId);
    if($installed["installed"]){
        $date_installed = $installed["installed_date"];
        $db->query("
         INSERT INTO tbl_usersettings 
         SET access_token = '$access_token',
         store_name = '$shop', app_id = $appId, installed_date = '$date_installed', confirmation_url = ''
     ");
       $date1 = new DateTime($installed["installed_date"]);
       $date2 = new DateTime("now");
       $interval = date_diff($date1, $date2);
       $diff = (int)$interval->format('%R%a');
       $trialTime = $trialTime - $diff;
       if($trialTime < 0){
           $trialTime = 0;
       }
    } else {
        $db->query("
         INSERT INTO tbl_usersettings 
         SET access_token = '$access_token',
         store_name = '$shop', app_id = $appId, installed_date = NOW(), confirmation_url = ''
     ");
        $db->query("
         INSERT INTO shop_installed 
         SET shop = '$shop', app_id = $appId, date_installed = NOW()
     ");
    } 
    //insert shop setting for custom-order app
    $settings = getShopSettings($db, $shop);
    if(count($settings) < 1){
        $db->query("insert into custom_order_settings(shop,version,usePriceRule) values('$shop',1,1)");
    }	    
    $shop_data = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
    $shop_data = $shop_data->fetch_object();
    $shopify = shopify_api\client(
            $shop, $shop_data->access_token, $app_settings->api_key, $app_settings->shared_secret
    ); 
    //charge fee
    $charge = array(
        "recurring_application_charge" => array(
            "name" => $chargeTitle,
            "price" => $price,
            "return_url" => "$rootLink/charge.php?shop=$shop",
            "test" => $testMode,
            "trial_days" => $trialTime
        )
    );
	
    if($chargeType == "one-time"){
        $recu = $shopify("POST", "/admin/application_charges.json", $charge);
        $confirmation_url = $recu["confirmation_url"];
    } else {
        $recu = $shopify("POST", "/admin/recurring_application_charges.json", $charge);
        $confirmation_url = $recu["confirmation_url"];
    }
    $db->query("update tbl_usersettings set confirmation_url = '$confirmation_url' where store_name = '$shop' and app_id = $appId"); 
    // Gui email cho customer khi cai dat
    $db->query("
         INSERT INTO quantity_email_jobs 
         SET shop = '$shop', email_type = 2, start_date = NOW() + INTERVAL 3 DAY 
    ");
    // $db->query("
    //      INSERT INTO quantity_email_jobs 
    //      SET shop = '$shop', email_type = 3, start_date = NOW() + INTERVAL 15 DAY , start_date_report = NOW() , end_date_report = NOW() + INTERVAL 30 DAY
    // ");
    $db->query("
         INSERT INTO quantity_email_jobs 
         SET shop = '$shop', email_type = 3, start_date = NOW() + INTERVAL 30 DAY 
    ");
	 require 'email/install_email.php';   
  
    
    //add js to shop
    $check = true;
    $putjs1 = $shopify('GET', '/admin/script_tags.json');
    if($putjs1){
        foreach ($putjs1 as $value) {
            if($value["src"] == $rootLink.'/quantity_break_v2.js'){
                $check = false;
            }
        } 
    }
    if($check){
        $putjs = $shopify('POST', '/admin/script_tags.json', array('script_tag' => array('event' => 'onload', 'src' => $rootLink.'/quantity_break_v2.js')));
    }
    $webhook = array(
        "webhook" => array(
            "topic" => "orders/create",
            "address" => $rootLink . '/services_v2.php?shop='.$shop.'&action=webhookOrderCreate',
            "format" => "json"
        )
    ); 
    $put = $shopify("POST", "/admin/webhooks.json",$webhook);

    $webhookOrderFulfill = $shopify('POST', APIVERSION.'webhooks.json', array('webhook' => array(
        'topic' => 'orders/fulfilled', 
        'address' => 'https://apps.omegatheme.com/add-paypal-tracking/order-tracking.php?shop='.$shop.'&app_id=34', 
        'format' => 'json'
    )));
    $webhookOrderUpdate = $shopify('POST', APIVERSION.'webhooks.json', array('webhook' => array(
        'topic' => 'orders/updated', 
        'address' => 'https://apps.omegatheme.com/add-paypal-tracking/order-updated-tracking.php?shop='.$shop.'&app_id=34', 
        'format' => 'json'
    )));
    $webhook = $shopify('POST', '/admin/webhooks.json', array('webhook' => array('topic' => 'app/uninstalled', 'address' => $rootLink.'/uninstall.php', 'format' => 'json')));
    
    header('Location: '.$confirmation_url);
} 
function checkInstalled($db, $shop, $appId) {
    $sql = "select * from shop_installed where shop = '$shop' and app_id = $appId";
    $query = $db->query($sql);
    if($query->num_rows > 0){
        while ($row = $query->fetch_assoc()) {
            $date_instaled = $row["date_installed"];
            $result = array(
                "installed_date" => $date_instaled,
                "installed" => true
            );
            return $result;
        }
    } else {
        $result = array(
            "installed" => false
        );
        return $result;
    }
}
function getShopSettings($db, $shop) {
    $sql = "SELECT * FROM custom_order_settings WHERE shop = '$shop'";
    $query = $db->query($sql);
    $settings = array();
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $settings = $row;
        }
    }
    return $settings;
} 
?>