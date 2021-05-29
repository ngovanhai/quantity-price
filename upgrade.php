<?php
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
header('Content-Type: application/json');
require 'vendor/autoload.php';
use sandeepshetty\shopify_api;
require 'conn-shopify.php';
require 'help.php';
if (isset($_GET["action"])) {
    $action = $_GET["action"];
    $shop = $_GET["shop"];
    $shopify = shopifyInit($db, $shop, $appId);
    if($action == "getListPackage"){
        $listPackage = db_fetch_array("select * from packages_app where id_app = $appId");
        foreach($listPackage as &$v){
            $v['content_limit'] = json_decode($v['content_limit'],TRUE);
        }
        echo json_encode($listPackage);
    }
    if($action == "updateTypePackage"){
       $typePackage = $_GET['typePackage'];
       $data = array(
           'plan_name' => $typePackage
       );
       db_update("tbl_usersettings",$data,"store_name = '$shop' and app_id = $appId");
        $charge = array(
            "recurring_application_charge" => array(
                "name" => $chargeTitle,
                "price" => $price,
                "return_url" => "$rootLink/charge.php?shop=$shop",
                "test" => "true",
                "trial_days" => $trialTime
            )
        );
        $recu = $shopify("POST", "/admin/application_charges.json", $charge);
        var_dump($recu);
 
    } 
    if($action == "getPackageCurrent"){
        $packageCurrent = db_fetch_row("select plan_name  from tbl_usersettings where store_name = '$shop' and app_id = $appId");
        echo json_encode($packageCurrent);
    }
}

?>