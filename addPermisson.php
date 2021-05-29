
<?php
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
date_default_timezone_set('UTC');
require 'vendor/autoload.php'; 
use sandeepshetty\shopify_api; 
require 'conn-shopify.php';
require 'help.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ADD SCOPE</title>
</head>
<style>
    .opps{
        text-align:center;
    }
    .opps img{
        width: 50%;
        margin: auto;
        text-align: center;
    }
</style>
<body>
<?php 
 
    if (!empty($_GET['shop']) && !empty($_GET['code'])) {  
        $select_settings = $db->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
        $app_settings = $select_settings->fetch_object();
        $shop = $_GET['shop'];  
        //get new access token 
        $access_token = shopify_api\oauth_access_token( $_GET['shop'], $app_settings->api_key, $app_settings->shared_secret, $_GET['code'] ); 
        if(isset($access_token)){
            // update new access_token
            $db->query("update tbl_usersettings set access_token = '$access_token' where store_name = '$shop' and app_id = $appId"); 
            // redirect admin
            header('Location: https://'.$shop.'/admin/apps/quantity-price-breaks-limit-purchase');
        }else{ ?>
            <!-- opps -->
            <div class="opps">
                <img src="https://previews.123rf.com/images/stolenpencil/stolenpencil1603/stolenpencil160300028/55885004-oops-broken-pencil-404-error-page-vector-template.jpg" alt="">
            </div> 
        <?php } 
    
    }  
?>
</body> 
</html>
 
