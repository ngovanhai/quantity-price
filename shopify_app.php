<?php
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');
ini_set('display_errors', TRUE);
error_reporting(E_ALL);

require 'vendor/autoload.php';
use sandeepshetty\shopify_api;
require 'conn-shopify.php';

$select_settings = $db->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
$app_settings = $select_settings->fetch_object();

if(!empty($_GET['shop'])){  
	$shop = $_GET['shop'];  
	$select_store = $db->query("SELECT store_name FROM tbl_usersettings WHERE store_name = '$shop' and app_id = $appId"); //check if the store exists
  
	if($select_store->num_rows > 0){
      
		if(shopify_api\is_valid_request($_GET, $app_settings->shared_secret)){   
			header('Location: '.$rootLink.'/admin.php?shop='.$shop);
		} 
	}else{     
 		$permissions = $app_settings->permissions;
 		$permission_url = shopify_api\permission_url(
			$_GET['shop'], $app_settings->api_key, $permissions
		);
        $permission_url .= '&redirect_uri=' . $app_settings->redirect_url;
 		header('Location: ' . $permission_url);
	}
}

?>