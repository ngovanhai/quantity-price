<?php

require 'conn-shopify.php'; 
require 'help.php'; 
$webhookContent = "";  
$webhook = fopen('php://input', 'rb');
while (!feof($webhook)) {  $webhookContent .= fread($webhook, 4096); } 
fclose($webhook); 
 
$webhookContent = json_decode($webhookContent,true);
$shopInfo = $webhookContent; 
if (isset($webhookContent['myshopify_domain'])) {
    $shop = $webhookContent['myshopify_domain'];  
} else if (isset($webhookContent['domain'])) {
    $shop = $webhookContent['domain']; 	
} 


if(isset($shop)){
    // gui email marketing 
    $db->query('delete from custom_order_settings where shop ="'.$shop.'"');
    $db->query('delete from tbl_usersettings where store_name = "' . $shop . '" and app_id = ' . $appId);
    $db->query('delete from quantity_email_jobs where shop = "' . $shop . '"' ); 
    require 'email/uninstall_email.php'; 
}


