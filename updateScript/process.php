<?php
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
define("SWITCH_PATH", dirname(__FILE__) . "/");
header('Content-Type: application/json');
require SWITCH_PATH . '../vendor/autoload.php'; 
use sandeepshetty\shopify_api;

require SWITCH_PATH . '../conn-shopify.php';
require SWITCH_PATH . '../help.php';

if (isset($_GET["action"])) {
    $action = $_GET["action"];
    if($action == "execQuery"){
        if(!isset($_GET['queryDB']) || !isset($_GET['typeQuery'])) return [];
        $typeQuery = $_GET['typeQuery'];
        $queryDB = $_GET['queryDB'];
        $result = execQuery($typeQuery,$queryDB);
        echo json_encode($result);
        exit();
    }
    if($action == "saveNoteStore"){ 
        if(!isset($_GET['note']) || !isset($_GET['shop'])  )  return false;
        $note = $_GET['note'];
        $shop = $_GET['shop'];
        db_update("shop_installed", [
            'note' => $note,
            
        ], "shop = '$shop' AND app_id = $appId");

        echo json_encode(true);
        exit;
    }
    if ($action == "getListShop") { 
        $sql = "
            SELECT q_settings.shop,q_settings.version,q_settings.usePriceRule,q_settings.enableApp,shop_installed.* 
            FROM shop_installed
            LEFT JOIN custom_order_settings as q_settings  
            ON shop_installed.shop = q_settings.shop 
            WHERE shop_installed.app_id = $appId
            ORDER BY shop_installed.date_installed DESC
        "; 
        $list_shop = db_fetch_array($sql);
        foreach($list_shop as $k=>&$v){
            $shop = $v['shop'];
            $infoShop = db_fetch_row("SELECT * FROM tbl_usersettings WHERE store_name= '$shop' AND app_id = $appId");
            if(empty($infoShop)){
                $v['status'] = "uninstall";
            }else{
                $v['status'] = $infoShop['status'];
                $v['access_token'] = $infoShop['access_token']; 
            } 
        }
        echo json_encode($list_shop);
    }
    if($action == "getDataChar") {
        // get all store install , uninstall within 7days
        $result = [];
        $data = [
            "type" => "bar",
            "data" => [
                "labels" => [],
                "datasets" => [
                    [
                        "label" => "Install",
                        "data" => [],
                        "fill" => false,
                        "backgroundColor" => "turquoise"
                    ],
                    [
                        "label" => "Uninstall",
                        "data" => [],
                        "fill" => false,
                        "backgroundColor" => "red"
                    ],
                ]
            ],
            
        ];
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime("+7 day")); 
        while (strtotime($startDate) <= strtotime($endDate)) {
            $date = $startDate;
            $start = $date." 00:00:00";
            $end = $date." 23:59:59";
            $result[date("d/m",strtotime( $date ))]["totalUninstall"] = db_fetch_row("select count(*) as total from shop_installed where app_id = $appId AND date_uninstalled BETWEEN '$start' AND '$end'")['total'];
            $result[date("d/m",strtotime( $date ))]["totalInstall"] = db_fetch_row("select count(*) as total from shop_installed where app_id = $appId AND date_installed BETWEEN '$start' AND '$end'")['total'];
            $startDate = date("Y-m-d", strtotime("+" . 1 . " day", strtotime($startDate)));
        }

        foreach ($result as $key => $value) {
            array_push($data['data']['labels'],$key);
            array_push($data['data']['datasets'][0]['data'],$value['totalInstall']);
            array_push($data['data']['datasets'][1]['data'],$value['totalUninstall']);
        }
        echo json_encode($data);
        exit;
    }
    if($action == "getListJobEmail"){
        $listJob = db_fetch_array("SELECT * FROM quantity_email_jobs");
        echo json_encode($listJob);
    }
    if($action == "getDemoRuleLimitPurchase"){
        if(!isset($_GET['shop'])) return [];
        $shop = $_GET['shop'];
        $rule = db_fetch_array("SELECT * FROM variant_limit WHERE shop = '$shop' LIMIT 5");
        echo json_encode($rule);
    }
    if($action == "deleteJob"){
        if(!isset($_GET['shop'])) return [];
        $shop = $_GET['shop'];
        $id = $_GET['id'];
        db_delete("quantity_email_jobs","id = $id AND shop = '".$shop."'");
        echo json_encode(true);
    }
    if($action == "getListTemplateEmail"){
        $listEmailTemplate = getAllListTemplate();
        echo json_encode($listEmailTemplate);
        exit();
    }
    if($action == "getSettings"){
        if(!isset($_GET['shop'])) return [];
        $shop = $_GET['shop'];
        $settings = getSettings($shop,$fields = "*");
        echo json_encode($settings);
        exit();
    }
    if($action == "changeEnableApp"){
        if(!isset($_GET['enableApp']) || !isset($_GET['shop'])) return false;
        $shop = $_GET['shop'];
        $enableApp = $_GET['enableApp'];
        $data = array( 
            'enableApp' => $settings['enableApp'] 
         );
        $response = db_update("custom_order_settings",$data,"shop = '$shop'");
        echo json_encode(true);
    } 
    if($action == "getFileLiquidCartPage"){
        $shop = $_GET['shop'];
        $shopify = shopifyInit($db, $shop, $appId);
        $IDMainTheme = getMainTheme($shopify);
        $assets = $shopify("GET", APIVERSION."themes/$IDMainTheme/assets.json?asset[key]=snippets/cartpage-design1.liquid");
        pr($assets); 
        die();
    }
    if($action == "putFileLiquidCartPage"){
        $shop = $_GET['shop'];
        $shopify = shopifyInit($db, $shop, $appId);
        $IDMainTheme = getMainTheme($shopify);
        $data = [
            "asset" => [
                "key" => "snippets/cartpage-design1.liquid",
                "src" =>  "https://windy.omegatheme.com/group-price-attribute/cart.liquid"
            ]
        ];
        pr($data);
        $assets = $shopify("PUT", APIVERSION."themes/$IDMainTheme/assets.json",$data);
        pr($assets);
        die();
    }
}
if (isset($_POST["action"])) {
    $action = $_POST["action"]; 
    if($action == "saveContentEmailTemplate"){
        if(!isset($_POST['emailEdit'])) return [];
        $emailEdit = $_POST['emailEdit'];
        $id = $emailEdit['id'];
        $title = $emailEdit['title'];
        $content = $emailEdit['content'];
        db_update("quantity_template_email",[
            "title" => $title,
            "content" => $content,
        ],"id = $id");
        $listEmailTemplate = getAllListTemplate();
        echo json_encode($listEmailTemplate);
        exit();
    }
    if($action == "saveSettings"){
        if(!isset($_POST['shop'])) return false;
        $shop = $_POST['shop'];
        $settings = $_POST['settings'];
        $data = array( 
            'enableApp'            => $settings['enableApp'], 
            'customCss'            => $settings['customCss'], 
            'useAjaxCart'            => $settings['useAjaxCart']
         );
        $response = db_update("custom_order_settings",$data,"shop = '$shop'");
        echo json_encode(true);
    }
    
}
function execQuery($typeQuery,$queryDB){
    if($typeQuery == "get"){ 
        $result = db_fetch_array($queryDB);
        return $result;
    } else{
        return db_query($queryDB);
    } 
}
function getAllListTemplate(){
    $listEmailTemplate = db_fetch_array("SELECT * FROM quantity_template_email");
    return $listEmailTemplate;
}
function updateVariantProductExist($db, $productId, $shopify, $variantId, $price_variant, $group, $options, $shop) {
    $percent = $group['percent'];
    $number = $group['number'];
    $price_new = round(($price_variant - $price_variant * $percent / 100), 2);
    $data = array(
        "variant" => array(
            "id" => $variantId,
            "price" => $price_new,
            "metafields" => array(
                array(
                    "key" => "customVariant",
                    "value" => 1,
                    "value_type" => "string",
                    "namespace" => "global"
                ),
                array(
                    "key" => "variantId",
                    "value" => $variantId,
                    "value_type" => "integer",
                    "namespace" => "global"
                ),
                array(
                    "key" => "number",
                    "value" => $number,
                    "value_type" => "integer",
                    "namespace" => "global"
                )
            )
        )
    );

    if ($options['option1']) {
        if ($options['option2']) {
            $option1Title = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options['option1']);
        } else {
            $option1Title = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options['option1'] . " ($number and above)");
        }
        $data["variant"]["option1"] = $option1Title;
    }
    if ($options['option2']) {
        if ($options['option3']) {
            $option2Title = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options['option2']);
        } else {
            $option2Title = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options['option2'] . " ($number and above)");
        }
        $data["variant"]["option2"] = $option2Title;
    }
    if ($options['option3']) {
        $data["variant"]["option3"] = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options['option3'] . " ($number and above)");
    }
    $variant = $shopify("PUT", "/admin/variants/$variantId.json", $data);
    if (isset($variant['id'])) {
        $customVariant = $variant['id'];
        if ($customVariant) {

            $sql = "update price_groups(price,percent,number,custom_variant,variant_id, product_group, shop)"
                . "values($price_new,$percent, $number, '$customVariant', '$variantId', 0, '$shop')";

            $query = $db->query($sql);
            return $db->insert_id;
        }
    }
}
function getMainTheme($shopify){
    $themes = $shopify("GET", APIVERSION."themes.json");
    if(!is_array($themes)) return null;
    foreach($themes as $theme){
        if($theme['role'] == "main"){
            return $theme['id'];
        }
    } 
}