<?php 
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *'); 
date_default_timezone_set('UTC');
header('Content-Type: application/json');
require 'vendor/autoload.php';
use sandeepshetty\shopify_api;
require 'help.php';
define("MAXOFFER", "-1");  
define("ROOTLINK", "https://apps.omegatheme.com/group-price-attribute/");

if (isset($_GET["action"])) {
    $action = $_GET["action"];  
    $shop = $_GET["shop"];  
    if($shop == "amaterasu-beauty-usa.myshopify.com"){
        date_default_timezone_set('America/Tijuana'); 
    }
	if($action != "getSettings"){ 
		$shopify = shopifyInit($db, $shop, $appId);
	} 
	if($action == "checkExisRule"){
		$result = array(); 
        // check exist rule to disable draforder 
        // if empty rule disable draforder
        $result = db_fetch_array("select * from quantity_collection where shop = '$shop'");
        if(count($result) == 0){
            $result = db_fetch_array("select * from quantity_products where shop = '$shop'");
        }
        if(count($result) == 0){ 
            $result = db_fetch_array("select * from quantity_global where shop = '$shop'");
        }
        if(count($result) == 0){
            $result = db_fetch_array("select * from quantity_variant where shop = '$shop'");
        }  
        echo json_encode(count($result));
    }	
    //check use tagin cart 
    if($action == "checkValid"){
        $settings       = getSettings($shop);  
        $checkValid = checkValid($shopify,$settings,$_GET);
        echo json_decode($checkValid);
    }
    if($action == "checkDiscountCode"){
        if(!isset($_GET['valueDiscountCode'])) return false;
        $title = $_GET['valueDiscountCode'];
        $listDiscount = getPriceRuleFromDB($shop,$title); 
        if(count($listDiscount) > 0){
            echo json_encode(array(
                'value_type' => $listDiscount['value_type'],
                'value' => $listDiscount['value']
            ));
        }
        return true;
    }
    
    // Product Detail 
    if ($action == 'getOfferByVariant') { 
		$variantId =  isset($_GET['variantID'])? $_GET["variantID"] : NULL;  
        $productID =  isset($_GET['idProduct'])? $_GET["idProduct"] : NULL;    
        $price     =  isset($_GET['price'])? round((float)($_GET["price"])/100,2) : NULL;
        // GET OFFER BY VARIANT ID (get rule theo collection , product neu variant ko co rule)
        $offerByvariant = getOfferByVariant($variantId,$productID);  
        
        $html  = ' ';
        $settings       = getSettings($shop,$fields = "*"); 
        if($variantId != NULL){ 
            $checkValid = checkValid($shopify,$settings,$_GET);  
            if ($checkValid == 1) {
                $maxDiscountForCustomerTag = [];
                if(isset($offerByvariant['contentOffer']['ruleForCustomerTag'])){
                    $ruleForCustomerTag = $offerByvariant['contentOffer']['ruleForCustomerTag']; 
                    if($ruleForCustomerTag == "" || $ruleForCustomerTag == NULL){
                        $ruleForCustomerTag = [];
                    }else{
                        $ruleForCustomerTag = json_decode($ruleForCustomerTag,true); 
                    }
                    
                    $customerTags = getCustomerTag($_GET,$shopify); 
                    $maxDiscountForCustomerTag = getMaxDiscountByCustomerTag($customerTags,$ruleForCustomerTag); 
                }
                
                if (!empty($offerByvariant['content_rule'])) {
                    $listOfferOfVariant = $offerByvariant['content_rule'];  
                    // updateStatistic($productID,$offerByvariant);
                    if(isset($settings['layout'])) $layout = $settings['layout'];
                    else $layout = 'table'; 
                    require("layout/$layout.php"); 
                } 
            }
            
        } 
        $response = array(
            "html" => $html, 
        );
        echo json_encode($response);
    }  
	if($action == "checkProductDetailForFactory2Me"){
        // check rieng cho store one-mart-singapore.myshopify.com
        $result = null;
        $variantID = $_GET['variantID'];
        $productID = $_GET['productID'];
        $quantity  = $_GET['quantity'];
        $price  = $_GET['price'];
        $offerByVariant = getOfferByVariant($variantID,$productID);
		 
        $contentOffer   = json_decode($offerByVariant['contentOffer']['content_rule'],true);
        $maxOffer       = $contentOffer[0]['number'];
        $idOfferPrefer  = MAXOFFER;
        foreach($contentOffer as $k=>$offer){
            if($quantity >= $offer['number']){
                if($offer['number'] >= $maxOffer){
                    $maxOffer      = $offer['number'];
                    $idOfferPrefer = $k;
                }
            }
        
        } 
        if($idOfferPrefer != MAXOFFER){ 
            if($contentOffer[$idOfferPrefer]['discountType'] == 'percent'){
                $result  =  round(($quantity*($price - $contentOffer[$idOfferPrefer]['price']*$price/100)), 2);
                 
            }else{ 
                $result  =  round(($quantity*($price - $contentOffer[$idOfferPrefer]['price']*100)), 2);
            }
        } 
        echo json_encode($result);
    }	 
    if($action == "getSettings"){
        $settings       = getSettings($shop); 
        echo json_encode($settings);
    }
    if($action == "getNewPriceInCollection"){ 
        $shop = $_GET['shop']; 
        if(!isset($_GET['IDProduct'])) return false;
        $newPrice = null;
        $IDProduct = $_GET['IDProduct'];
        $isDiscount = false; 
        $product = getProductByProductID($shopify,$IDProduct,$fields = "id,variants");
        $minPrice = getMinPriceVariant($product['variants']);
        if (!defined("APP_PATH"))   define("APP_PATH",dirname(__FILE__)) ; 
        if (!defined("CACHE_PATH"))  define("CACHE_PATH",APP_PATH."/cache/") ; 
        $key_path = CACHE_PATH.$shop."products";
            // neu  co rule cho product
        if(is_file($key_path)){  
            $data_cache = file_get_contents($key_path);
            $products = json_decode($data_cache,true);  
            foreach($products as $product){
                if($product['product_id'] == $IDProduct){
                    $content_rule = json_decode($product['content_rule'],true);
                    if($content_rule[count($content_rule) - 1]['discountType'] == "percent"){
                        $newPrice = $minPrice - $minPrice * $content_rule[count($content_rule) - 1]['price']/100; 
                    }else{  
                        $newPrice = $minPrice -  $content_rule[count($content_rule) - 1]['price'];
                    }
                    $isDiscount = true;
                }
            } 
        }
        // neu khong co rule cho product check rule theo custome collections
        if($newPrice == null){ 
            $collections = getCustomColletionByProductID($shopify,$IDProduct); 
            if(count($collections) > 0 ){ 
                $discountByCustomCollection = checkDiscountByCollectionID($shop,$collections,$minPrice);
                $isDiscount = $discountByCustomCollection['isDiscount'];
                $newPrice = $discountByCustomCollection['newPrice'];
            } 
            // neu khong co rule cho product check rule theo smart collections
            if($isDiscount == false){
                $collections = getSmartColletionByProductID($shopify,$IDProduct);
                $discountBySmartCollection = checkDiscountByCollectionID($shop,$collections,$minPrice);
                $isDiscount = $discountBySmartCollection['isDiscount'];
                $newPrice = $discountBySmartCollection['newPrice'];
            }
        } 
        $response = array(
            "isDiscount" => $isDiscount,
            "newPrice" => sprintf("%.2f",round(($newPrice),2))
        );
        echo json_encode($response);
    }
    if($action == "showLayoutInCollectionPage"){
        if(!isset($_GET['IDProduct'])) return false;
        $productID = $_GET['IDProduct'];
        $offerByvariant = getOfferByVariant("",$productID);  
        $html  = ' ';
        $settings       = getSettings($shop,$fields = "*"); 
        $html .= '<style>'.$settings['customCss'].'</style> '; 
        $checkValid = checkValid($shopify,$settings,$_GET); 
        $product = getProductByProductID($shopify,$productID,$fields = "id,variants");
        $price = getMinPriceVariant($product['variants']);
        if ($checkValid == 1) {
            $maxDiscountForCustomerTag = [];
            if(isset($offerByvariant['contentOffer']['ruleForCustomerTag'])){
                $ruleForCustomerTag = $offerByvariant['contentOffer']['ruleForCustomerTag']; 
                if($ruleForCustomerTag == "" || $ruleForCustomerTag == NULL){
                    $ruleForCustomerTag = [];
                }else{
                    $ruleForCustomerTag = json_decode($ruleForCustomerTag,true); 
                }
                
                $customerTags = getCustomerTag($_GET,$shopify); 
                $maxDiscountForCustomerTag = getMaxDiscountByCustomerTag($customerTags,$ruleForCustomerTag); 
            }
            if (!empty($offerByvariant['content_rule'])) {
                $listOfferOfVariant = $offerByvariant['content_rule'];   
                if(isset($settings['layout'])) $layout = $settings['layout'];
                else $layout = 'table'; 
                require("layout/$layout.php"); 
            } 
        } 
        $response = array(
            "html" => $html, 
        );
        echo json_encode($response);
    }
    if($action == "getDataStore"){
        if (!defined("APP_PATH")) define("APP_PATH",dirname(__FILE__)) ;
        if (!defined("CACHE_PATH")) define("CACHE_PATH",APP_PATH."/cache/") ;
        $listTypeRule = [];  
        $listTypeRule['collection'] = db_fetch_array("select * from quantity_collection where shop = '$shop'");
        $listTypeRule['products'] = db_fetch_array("select * from quantity_products where shop = '$shop'");
        $listTypeRule['variant'] = db_fetch_array("select * from quantity_variant where shop = '$shop'");
        $listTypeRule['global'] = db_fetch_array("select * from quantity_global where shop = '$shop'"); 
        foreach($listTypeRule as $k=>$v){
            $key_path = CACHE_PATH.$shop.$k;
            if(!is_file($key_path) || (time() - filemtime($key_path) > 1800)){   
                $v = json_encode($v);
                file_put_contents($key_path,is_array($v)?json_encode($v):$v); 
            } 
        }
        echo json_encode(true); 
    } 
    if($action == 'getLimitByVariant') { 
        $variantId  = $_GET["variantID"];  
        $customerTags = getCustomerTag($_GET,$shopify);
        $limitByvariant = getRuleLimitByVariantID($variantId,$shop); 
        $settings       = getSettings($shop);
        $html  = '';  
        $checkValid = checkValid($shopify,$settings,$_GET);
        if(!isset($limitByvariant['limitforCustomerTag'])) $limitByvariant['limitforCustomerTag'] = NULL;
        if($limitByvariant['limitforCustomerTag'] != NULL || $limitByvariant['limitforCustomerTag'] != ""){ 
            $limitforCustomerTag = $limitByvariant['limitforCustomerTag'] ;  
            $ruleLimitSatisfyTag = checkItemInArray($limitforCustomerTag,$customerTags,'tag');
            if(isset($ruleLimitSatisfyTag['min']) && isset($ruleLimitSatisfyTag['max'])){
                if(isset($limitByvariant['multiple']) && $limitByvariant['multiple'] != ""){
                    $ruleLimitSatisfyTag['multiple'] = $limitByvariant['multiple'];
                }
                $limitByvariant = $ruleLimitSatisfyTag;
            } 
        }  
        if($checkValid == 1 && isset($limitByvariant['min']) && isset($limitByvariant['max'])) { 
            if($limitByvariant) {
                require('layout/limit_variant.php');
            }
        }
        $textCheckLimit = true;
        $multiple = 0;
        if($settings['limit_on_product'] == 1) {  
            if(isset($_GET["quantity"])) { 
                $quantity  = $_GET["quantity"];
                $checkLimitByVariant = $limitByvariant; 
                    if (!empty($checkLimitByVariant)) {
                        if (!isset($checkLimitByVariant['min'])) $checkLimitByVariant['min'] = 0;
                        if (!isset($checkLimitByVariant['max'])) $checkLimitByVariant['max'] = 0;
                        if (($quantity < $checkLimitByVariant['min']) && $checkLimitByVariant['min'] != 0 ) {
                            $textCheckLimit = "<span style='color:{$settings['limit_text_color']}; font-size:{$settings['limit_text_size']}px;'> {$settings['min_text']} {$checkLimitByVariant['min']} </span>";
                        } elseif ( ($quantity > $checkLimitByVariant['max']) && $checkLimitByVariant['max'] != 0) {
                            $textCheckLimit = "<span style='color:{$settings['limit_text_color']}; font-size:{$settings['limit_text_size']}px;'> {$settings['max_text']} {$checkLimitByVariant['max']} </span>";
                        }
                   }
            }
        }   
        if(isset($limitByvariant['multiple'])){
            $multiple = $limitByvariant['multiple'];
        }
        $response = array( 
           "html" => $html,
           "checkLimit" => $textCheckLimit,
           "multiple" => $multiple,
        );
        echo json_encode($response);
        exit();
     }

    if ($action == "checkExpire") {
        $checkExpire = checkExpired($db, $shop, $appId, $trialTime);
        $money_format = getMoneyFormat($shopify);
        $settings       = getSettings($shop);
        $enableApp      = $settings['enableApp'];
        $response = array(
            "money_format" => strip_tags($money_format['money_format']),
            "money_with_currency_format" => strip_tags($money_format['money_with_currency_format']),
            "checkExpire"  => $checkExpire,
            "enableApp"    => $enableApp ,
            "settings"    => $settings ,
            "version"      => time()
       );
       echo json_encode($response);
    } 
}
 
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $action = $_POST['action'];
    $shop = $_POST['shop'];
    $shopify = shopifyInit($db, $shop, $appId);
    if($action == "updatePriceCart"){
        $result = array();  
        if(isset($_POST['productListInCart'])){
            $settings       = getSettings($shop);
            $variantListInCart = $_POST['productListInCart'];
            if(isset($_POST['customerId'])){
                $customerId = $_POST['customerId'];
            }else{
                $customerId = null;
            }
            $result = updatePriceProduct($variantListInCart,$settings,$customerId,$shopify);  
        }  
        echo json_encode($result);
    }
    if($action == "createDraftOrder"){  
        $quantity = 1; 
        $total_price = 0;

        if(!isset($_POST['products'])) return false;
        $settings       = getSettings($shop);  
        $totalAmountDiscountInCart = checkTotoalAmountDiscountInCart($_POST['products'],$settings['usePriceRule'],$settings['labelCheckout']);
        $amount    = $totalAmountDiscountInCart['amount'];
        $products  = $totalAmountDiscountInCart['products']; 
        $quantity  = $totalAmountDiscountInCart['quantity']; 
        $total_price = $totalAmountDiscountInCart['total_price'];
        // use draft order  
        if($settings['usePriceRule'] == 0){ 
            $respone = postDraftOrder($_POST,$products,$shopify,$amount);
            echo json_encode($respone);
        }else{ 
            // use price rule api 
            if($amount < 1) {echo 0; return false;} 
            $respone = postPriceRule($settings['labelCheckout'],$amount,$quantity,$shopify); 
            echo json_encode($respone);
        } 
            
    }
} 
function updateStatistic($productID, $offerByVariant,$shop){
    if($productID == NULL) return false;
    $checkExist = db_fetch_row("SELECT * FROM quantity_statistic_rule WHERE shop = '$shop'  AND productID = $productID");
    if(empty($checkExist)){
        $countView = 1;
        db_insert("quantity_statistic_rule",[
            "shop" => $shop,
            "productID" => $productID,

        ]);
    }else{
        $countView = $checkExist['countViewProductPage'] + 1;
    }
    
}
function getMaxDiscountByCustomerTag($customerTags,$ruleForCustomerTag){ 
    $maxDiscount = 0;
    $result = [];
    if(!is_array($ruleForCustomerTag)) return [];
    foreach($ruleForCustomerTag as $rule){
        $checkSatisfyRuleCondition = checkItemInArray($rule,$customerTags,'tag');
        if(!empty($checkSatisfyRuleCondition)){
            if($checkSatisfyRuleCondition['price'] >  $maxDiscount){
                $result = $checkSatisfyRuleCondition;
            }
        }
    }
    return $result;
} 
function postPriceRule($labelCheckout,$amount,$quantity,$shopify){
    $code = $labelCheckout."_";
    $date = strtotime("- 1 day");
    $code .= substr(md5(uniqid(rand(1,6))), 0, 16);
    $newDiscountRule = array (
        'price_rule' => array (
            'title' => $code,
            'target_type' => 'line_item',
            'target_selection' => 'all',
            'allocation_method' => 'across', 
            'value_type' => 'fixed_amount',
            'value' => - $amount / 100,
            'customer_selection' => 'all',
            'once_per_customer' => true,
            'starts_at' => date('Y-m-d H:i:s', $date),
            'prerequisite_quantity_range' => array(
                'greater_than_or_equal_to' => $quantity
            ), 
        )
    );  
    $newDiscountRule = postDataPriceRule($shopify,$newDiscountRule);  
    if(!isset($newDiscountRule['id'])) return false; 
    $newDiscountCode = array (
        'discount_code' => array(
            'code' => $code
        )
    ); 
    $newDiscountCode = postDiscountCode($shopify,$newDiscountRule['id'],$newDiscountCode); 
    if($newDiscountCode == true)  return $code;
    return false;  
}
function postDraftOrder($POST,$products,$shopify,$amount){
    $data = array(
        "draft_order" => array(  
            "line_items"        => array()
        )
    );  
    // use add discount code customer 
    $note = (isset($POST['note']))? $POST['note']:"";
    $data['draft_order']['note'] = $note;
    if(isset($POST['titleDiscountCodeOfCustomer']) && isset($POST['valueDiscountCodeOfCustomer']) ){
        $title_description = "APP QUANTITY BY OMEGA";
        $data['draft_order']['applied_discount'] =  array(
            "description"   => $title_description,
            "value_type"    => "fixed_amount",
            "value"         => round($POST['valueDiscountCodeOfCustomer']/100,2) ,
            "amount"        => round($POST['valueDiscountCodeOfCustomer']/100,2),
            "title"         => $POST['titleDiscountCodeOfCustomer']
        ); 
    } 
    if($amount != 0){
        $data['draft_order']['line_items'] = $products;  
        $response = postDataDraftOrder($shopify,$data);  
        if(isset($response['invoice_url'])){
            return $response['invoice_url'];    
        }
    } 
    $redirect = $_SERVER['HTTP_ORIGIN']."/checkout"; 
    return $redirect; 
}
function checkTotoalAmountDiscountInCart($products,$usePriceRule,$labelCheckout){  
    $amount = 0;  
    $quantity = 0;
    $total_price = 0;
    
    foreach ($products as &$v) {  
        if(!isset($v['properties']) || $v['properties'] == null){
            $v['properties'] = [];
        } else{ 
            if(is_array($v['properties'])){
                if(count($v['properties']) > 0){
                    $properties = array();
                    $i = 0;
                    foreach($v['properties'] as $k=>$property){
                        $properties[$i]['name'] = $k;
                        $properties[$i]['value'] = $property;
                        $i++;
                    }
                    $v['properties'] = $properties;
                }
            } 
        }
        $quantity = $quantity  + $v['quantity'];
        if (isset($v['isDiscount'])) {
            if($v['isDiscount'] == 1){ 
                $amount += ($v['line_price'] - $v['price_new']);
                $total_price += $v['price_new']; 
                $discountPerProduct = ($v['line_price'] - $v['price_new'])/($v['quantity'] * 100);
                $title_description = "APP QUANTITY BY OMEGA -";
                $valueDraft = round($discountPerProduct,2);
                if($v['discountType'] == "percent"){
                    $value_type= "percentage";
                }else{
                    $value_type= "fixed_amount";
                }
                $valueDiscount= $v['valueDiscount'] ;
                if(isset($v['IDOffer'])) $title_description .= $v['IDOffer'];
                if(isset($v['typeOffer'])) $title_description .= ",".$v['typeOffer'];
                $v["applied_discount"]  =  array(
                    "description"   => $title_description,
                    "value_type"    => $value_type,
                    "value"         => $valueDiscount,
                    "title"         => $labelCheckout
                );  
            }else{
                $total_price += $v['line_price'];
            } 
        } 
        $v['product_description'] = str_replace('"', '', $v['product_description']);
        $v['product_title']       = str_replace('"', '', $v['product_title']);
        $v['title']               = str_replace('"', '', $v['title']); 
    } 
    $result['amount']   = $amount;
    $result['products'] = $products; 
    
    $result['quantity'] = $quantity;
    $result['total_price'] = $total_price; 
    return $result;
} 
function getMinPriceVariant($variants){
    $indexMinPrice = 0;
    $minPrice = $variants[$indexMinPrice]['price'];
    foreach($variants as $k=>$variant){
        if($variant['price'] < $minPrice){
            $minPrice = $variant['price'];
            $indexMinPrice = $k;
        }
    } 
    return $minPrice; 
 }
 function checkValid($shopify,$settings,$GET){ 
    $isValid = 0; //customer_tag
    // valid = 1 | run app
    if ($GET["customerId"] != "") {
       $customerId = $GET["customerId"];
       $customer = getCustomer($shopify,$customerId);
       if(!isset($customer['tags'])) $customer['tags'] = '';
       $customer['tags'] = str_replace(" ", "", $customer['tags']);
       $customerTags = explode(",", $customer['tags']);
   } else {
       $customer     = "";
       $customerTags = "";
   }
   // $customer == '' customer chua login
   if($settings['use_tag'] == 0) return 1;
   
   if($settings['type_tag_for_customer'] == 1){
       // customer co tag trong setting dc hien thi rule
       if($settings['use_tag'] == 1 && $settings['customer_tag'] == '' && $customer == '') return 0; // tag admin nhap trong, cusomter chua login
       if($settings['use_tag'] == 1 && $settings['customer_tag'] == '' && $customer != '') return 1; // tag admin nhap trong, cusomter da login
       if($settings['use_tag'] == 1 && $settings['customer_tag'] != '' && $customer == '') return 0; 
       if($settings['use_tag'] == 1 && $settings['customer_tag'] != '' && $customer != '' && $customerTags == '') return 0;
       if($settings['use_tag'] == 1 && $settings['customer_tag'] != '' && $customer != '' && $customerTags != '') return checkTag($settings['customer_tag'],$customerTags);
   }else{
       // customer co tag trong setting dc KHONG hien thi rule
       // $customer == '' customer chua login
       if($settings['use_tag'] == 1 && $settings['customer_tag'] == '' && $customer == '') return 1;
       if($settings['use_tag'] == 1 && $settings['customer_tag'] == '' && $customer != ''  && $customerTags == '') return 0;
       if($settings['use_tag'] == 1 && $settings['customer_tag'] == '' && $customer != ''  && $customerTags != '') return 1;

       
       if($settings['use_tag'] == 1 && $settings['customer_tag'] != '' && $customer == '') return 1; 
       if($settings['use_tag'] == 1 && $settings['customer_tag'] != '' && $customer != '' && $customerTags == '') return 1;

       if($settings['use_tag'] == 1 && $settings['customer_tag'] != '' && $customer != '' && $customerTags != '') return (1 - checkTag($settings['customer_tag'],$customerTags));
   }
   
   

   return $isValid ;
}
function checkTag($settings_tag, $customerTags) {
         $settings_tag= str_replace(" ", "", $settings_tag);
         $settings_tag = explode(",", $settings_tag);
         $result = checkExistArray($customerTags, $settings_tag);
         return $result; 
}  
 function checkExpired($db, $shop, $appId, $trialTime) {
    $shop_data = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
    $shop_data = $shop_data->fetch_object();
    $installedDate    = $shop_data->installed_date;
    $confirmation_url = $shop_data->confirmation_url;
    $clientStatus     = $shop_data->status;
    $date1    = new DateTime($installedDate);
    $date2    = new DateTime("now");
    $interval = date_diff($date1, $date2);
    $diff = (int) $interval->format('%R%a');
    if ($diff > $trialTime && $clientStatus != 'active') {
        return true;
    } else {
        return false;
    }
} 

function updatePriceProduct($variantListInCart,$settings,$customerId,$shopify){  
    $result = array();   
    // mang chua nhung variant ko du quantity de set rule
    $listVariantSameOffer = [
        'variant' => [],
        'global' => [],
        'product' => [],
        'collection' => [],
    ];  
    // get rule by variant >> check quantity
    foreach($variantListInCart as $k=>$variantIncart){  
        $variantIncart['title'] = str_replace('"',"'",$variantIncart['title']);
        $variantIncart['featured_image'] = [];
        $variantIDInCart  = $variantIncart['id'];
        if(!isset($variantIncart['product_id'])) $variantIncart['product_id']  = null;
        $product_id       = $variantIncart['product_id'];  
        if(!isset($variantIncart['variant_id']))  $variantIncart['variant_id'] = null;
        $offerByVariant   = getOfferByVariant($variantIncart['variant_id'],$product_id); 
        if (isset($offerByVariant['contentOffer']) && count($offerByVariant['contentOffer']) > 0) { // variant has rule
            $contentOffer     = $offerByVariant['contentOffer']; 
            $maxDiscountForCustomerTag = [];
            if(isset($offerByVariant['contentOffer']['ruleForCustomerTag'])){
                $ruleForCustomerTag = $offerByVariant['contentOffer']['ruleForCustomerTag']; 
                if($ruleForCustomerTag == "" || $ruleForCustomerTag == NULL){ 
                    $ruleForCustomerTag = []; 
                }else{
                    $ruleForCustomerTag = json_decode($ruleForCustomerTag,true);
                } 
                $customerTags = getCustomerTag(["customerId"=>$customerId],$shopify);  
                $maxDiscountForCustomerTag = getMaxDiscountByCustomerTag($customerTags,$ruleForCustomerTag); 
            }
            
            $offers           = json_decode($contentOffer['content_rule'],true);
            $typeOffer        =  $offerByVariant['typeOffer'];
            $variantIncart['maxDiscountForCustomerTag'] = $maxDiscountForCustomerTag;
            if($settings['shop'] == "thecloseoutchannel.myshopify.com"){
                $offerByVariant['typeOffer'] = "variant";
            }
            if($offerByVariant['typeOffer'] == "variant"){
                // variant has rule variant  
                $offerPreferForVariant = getOfferPreferForVariant($offerByVariant,$variantIncart,$settings);  
                if ($offerPreferForVariant['isDiscount'] == 1) {   
                    array_push($result, $offerPreferForVariant);  
                } else {
                    array_push($result, $variantIncart);
                }  
            }else if ($offerByVariant['typeOffer'] != "variant") {  
                if(!empty($maxDiscountForCustomerTag) && isset($maxDiscountForCustomerTag['discountType'])){
                     
                } 
                $variantIncart['offers'] = $offers; 
                $variantIncart['typeOffer'] = $typeOffer; 
                $variantIncart['IDOffer'] = $offerByVariant['contentOffer']['id']; 
                array_push($listVariantSameOffer[$typeOffer], $variantIncart);  
            }
            
        }else{
            array_push($result, $variantIncart); 
        } 
        
    } 
    // truyen nhung san pham ma khong du quantity de check giao thoa voi nhau 
    // Cac variant cung thuoc 1 global
    $idOfferPreferGlobal = getOfferListvariantGlobal($listVariantSameOffer); 
  
    if(!empty($idOfferPreferGlobal['contentOfferGobal'])){
        foreach($listVariantSameOffer['global'] as $v){ 
            array_push($result, updatePriceVariantByOffer($idOfferPreferGlobal['contentOfferGobal'],$v,$settings)); 
        }
    } 
    // Cac varinat cung thuoc 1 product

    $idOfferPreferProduct = getOfferListvariantProduct($listVariantSameOffer, $settings); 
    if(!empty($idOfferPreferProduct)){
        foreach($idOfferPreferProduct as $v){ 
            array_push($result, $v); 
        }
    }  
    // Cac varinat cung thuoc 1 collection
    $idOfferPreferCollection = getOfferListvariantCollection($listVariantSameOffer,$settings);  
      if(!empty($idOfferPreferCollection)){
        foreach($idOfferPreferCollection as $v){
            array_push($result, $v); 
        }
    }     
    
    return $result;
} 
 
function getOfferListvariantCollection($listVariantSameOffer,$settings){
    global $shopify;
    $listCollection = array();
    $result = array(); 
    $result = checkRuleSmartCollectionOrCustomCollection($listVariantSameOffer,"custom_collections",$settings);
    if(empty($result)){
        $result = checkRuleSmartCollectionOrCustomCollection($listVariantSameOffer,"smart_collections",$settings);
    }
    
    return $result;
}
function checkRuleSmartCollectionOrCustomCollection($listVariantSameOffer,$typeCollection,$settings){
    global $shopify;
    $listCollection = array();
    $result = array();
    $collections = [];
    $result_collections = [];
    foreach($listVariantSameOffer['collection'] as &$value){  
        $product_id = $value['product_id'];  
        if($typeCollection == "custom_collections"){
            $result_collections = getCustomColletionByProductID($shopify,$product_id);
            if(is_array($result_collections)) $collections =  $result_collections ;
            foreach($collections as $collection){
                $getOfferByCollection = getOfferByCollection($collection['id']);
                if(!empty($getOfferByCollection)){
                    $idCollection = $collection['id'];
                    break;
                }
            }
        }else{
            $result_collections = getSmartColletionByProductID($shopify,$product_id);
            if(is_array($result_collections)) $collections =  $result_collections ;
            foreach($collections as $collection){
                $getOfferByCollection = getOfferByCollection($collection['id']);
                if(!empty($getOfferByCollection)){
                    $idCollection = $collection['id'];
                    break;
                }
            }
        } 
        if (isset($idCollection)) { 
            $value['collection_id'] = $idCollection;
            if (isset($listCollection[$idCollection])) { 
                $listCollection[$idCollection]['quantity'] += $value['quantity'];
            } else {
                $array['quantity'] = $value['quantity'];
                $array['id']       = $idCollection;
                $array['offers']   = $value['offers'];
                $listCollection[$idCollection] = array();
                $listCollection[$idCollection] = $array;
               
            }
        } 
    }    
    foreach($listCollection as &$value){ 
        $idOfferPrefer = checkQuantityInNumberOffer($value['quantity'],$value['offers']);
        if($idOfferPrefer > -1){  
            foreach($listVariantSameOffer['collection'] as &$v){
                if(isset($v['collection_id']) && $v['collection_id'] == $value['id']){
                    array_push($result,updatePriceVariantByOffer($value['offers'][$idOfferPrefer],$v,$settings));
                } 
            } 
        }else{
            foreach($listVariantSameOffer['collection'] as &$v){
                if( $v['collection_id'] == $value['id']){
                    array_push($result,$v);
                } 
            } 
        }
    
    }
    
    return $result;
}
function getOfferListvariantProduct($listVariantSameOffer,$settings){ 
    $listProduct = array();
    $result = array();
    foreach($listVariantSameOffer['product'] as $value){ 
        if (isset($listProduct[$value['product_id']])) {
            $listProduct[$value['product_id']]['quantity'] += $value['quantity'];
        }else{
            $product['quantity'] = $value['quantity'];
            $product['id'] = $value['product_id'];
            $product['offers'] = $value['offers'];
            $listProduct[$value['product_id']] = array();
            $listProduct[$value['product_id']] = $product;
        } 
    }
    foreach($listProduct as $value){
        $idOfferPrefer = checkQuantityInNumberOffer($value['quantity'],$value['offers']);
        if($idOfferPrefer > -1){
            foreach($listVariantSameOffer['product'] as $v){ 
                if( $v['product_id'] == $value['id']){
                    array_push($result,updatePriceVariantByOffer($value['offers'][$idOfferPrefer],$v,$settings));
                }
            }
        }else{
            foreach($listVariantSameOffer['product'] as $v){ 
                if( $v['product_id'] == $value['id']){
                    array_push($result,$v);
                }
            }
        }
        
    } 
    return $result;
}
function getOfferListvariantGlobal($listVariantSameOffer) { 
    $idOfferPrefer = null;
    $result = array();
    $contentOfferGobal = array();
    $result['contentOfferGobal'] = array();
    if(!empty($listVariantSameOffer['global'])){
        $quantityGlobal = 0;
        foreach($listVariantSameOffer['global'] as $product){ 
           $contentOfferGobal = $product['offers'];
           $quantityGlobal += $product['quantity'];
        }
        $idOfferPrefer = checkQuantityInNumberOffer($quantityGlobal,$contentOfferGobal);
        if($idOfferPrefer > -1){
            $result['contentOfferGobal'] = $contentOfferGobal[$idOfferPrefer];
        }
        
    }
    
    return $result;
}
function checkQuantityInNumberOffer($quantity,$contentOffer){
    $maxOffer = $contentOffer[0]['number'];
    $idOfferPrefer = MAXOFFER;
    foreach($contentOffer as $k=>$offer){
        if($quantity >= $offer['number']){
             if($offer['number'] >= $maxOffer){
                 $maxOffer      = $offer['number'];
                 $idOfferPrefer = $k;
             }
        }
       
    } 
    return $idOfferPrefer;
}
function updatePriceVariantByOffer($contentOffer,$variantIncart,$settings){    
    if(isset($variantIncart['maxDiscountForCustomerTag'])){
        $maxDiscountForCustomerTag         = $variantIncart['maxDiscountForCustomerTag'];
    }else{
        $maxDiscountForCustomerTag = [];
    }
    
    if(!isset($variantIncart['properties'])) $variantIncart['properties'] = [];
    $result['variant_id']           = $variantIncart['variant_id'];
    $result['key']           = $variantIncart['key'];
    $result['quantity']             = $variantIncart['quantity'];
    $result['price']                = $variantIncart['price'];
    $result['title']                = $variantIncart['product_title'];
    $result['line_price']           = $variantIncart['line_price'];
    $result['product_description']  = $variantIncart['product_description'];
    $result['product_title']        = $variantIncart['product_title'];
    $result['handle']               = $variantIncart['handle'];
    $result['properties']           = $variantIncart['properties'];
    $result['isDiscount']           = 1; 
    $price = $result['price'];
    $priceDiscountByTag = 0;
    $percentDiscountByTag = 0; 
    if($contentOffer['discountType'] == 'percent'){ 
        // $percentDiscountByTag pham tram giam gia them
        $contentOffer['price'] = (float)$contentOffer['price']  + (float)$percentDiscountByTag;  
        if(!empty($maxDiscountForCustomerTag) && isset($maxDiscountForCustomerTag['discountType'])){
            if($maxDiscountForCustomerTag['discountType'] == "percent"){ 
                // Percent - Percent
                $contentOffer['price'] =  $contentOffer['price']  +  (($price- $price*($contentOffer['price']/100))*$maxDiscountForCustomerTag['price'])/$price;  
            }else{
                // Percent - Price
                $percentDiscountByTag = ($maxDiscountForCustomerTag['price']*100/$price)*100;
                $contentOffer['price'] = $contentOffer['price']  + $percentDiscountByTag;  
                
            }
        }
        if($settings['shop'] == "thecloseoutchannel.myshopify.com" || $settings['shop'] == "braneloshop.myshopify.com"){ 
            $giagiam = (int)($contentOffer['price']*$variantIncart['price']*$variantIncart['quantity']/100); 
            $result['price_new2']    =  $variantIncart['price']*$variantIncart['quantity'] - $giagiam ;   
        }
     
        $result['price_new']    =  round(($variantIncart['quantity']*($variantIncart['price']- (float)$contentOffer['price']*$variantIncart['price']/100)), 2);   
        
        if($settings['shop'] == "fest4all-dk.myshopify.com" || $settings['shop'] == "wunder-kissen-de.myshopify.com" || $settings['shop'] == "softero-cushion.myshopify.com"){
            $result['notification'] = sprintf($settings['notificationInCart'], round($contentOffer['price'],2)."%");
        }else{
            $result['notification'] = sprintf($settings['notificationInCart'],(float)$contentOffer['number'],round($contentOffer['price'],2)."%");
        }
    }else{  
        if(!empty($maxDiscountForCustomerTag) && isset($maxDiscountForCustomerTag['discountType'])){
            if($maxDiscountForCustomerTag['discountType'] == "percent"){   
                // Price - Percent
                $percentDiscountByTag = ($price*$maxDiscountForCustomerTag['price'])/100; 
                $contentOffer['price'] = $contentOffer['price']  + sprintf("%.2f", $percentDiscountByTag/100);  
      
            }else{
                // Price - Price
                $priceDiscountByTag = $maxDiscountForCustomerTag['price'];
                $contentOffer['price'] = $contentOffer['price']  + $priceDiscountByTag;  
            }
        }     
        $result['price_new']    =  round(($variantIncart['quantity']*($variantIncart['price']- (float)$contentOffer['price']*100)), 2);
        if($settings['shop'] == "fest4all-dk.myshopify.com" || $settings['shop'] == "wunder-kissen-de.myshopify.com" || $settings['shop'] == "softero-cushion.myshopify.com"){
            $result['notification'] = sprintf($settings['notificationInCart'], "<span class='ot_formatPriceInNoticeCart money'>".round(((float)$contentOffer['price']*100),2)."</span>");
        }else{
            $result['notification'] = sprintf($settings['notificationInCart'],(float)$contentOffer['number'],"<span class='ot_formatPriceInNoticeCart money'>".round(((float)$contentOffer['price']*100),2)."</span>");
        } 
    } 
    $result['discountType']    =  $contentOffer['discountType'];
    $result['valueDiscount']    =  $contentOffer['price'] ;
    if(isset($variantIncart['typeOffer'])) $result['typeOffer'] = $variantIncart['typeOffer'];
    if(isset($variantIncart['IDOffer'])) $result['IDOffer'] = $variantIncart['IDOffer'];
    if(isset($contentOffer['IDOffer']))  $result['IDOffer'] = $contentOffer['IDOffer']; 
    if(isset($contentOffer['typeOffer']))  $result['typeOffer'] = $contentOffer['typeOffer'];  
    return $result;
} 
function getOfferPreferForVariant($offerByVariant,$variantIncart,$settings){ 
    $result = array();
    $contentOffer = json_decode($offerByVariant['contentOffer']['content_rule'],true);
    $maxOffer = $contentOffer[0]['number'];
    $idOfferPrefer = MAXOFFER;
    // MAXOFFER = -1
    // Duyet mang rule. Neu rule nao thoa man quantity lon nhat thi lay va luu quantity lon nhat  $maxOffer va id rule  $idOfferPrefer
    foreach($contentOffer as $k=>$offer){
        if($variantIncart['quantity'] >= $offer['number']){
             if($offer['number'] >= $maxOffer){
                 $maxOffer      = $offer['number'];
                 $idOfferPrefer = $k;
             }
        } 
    } 
    if($idOfferPrefer != MAXOFFER){ 
        if(isset($offerByVariant['id'])){
            $contentOffer[$idOfferPrefer]['IDOffer'] = $offerByVariant['id']; 
        }else{
            $contentOffer[$idOfferPrefer]['IDOffer'] = $offerByVariant['contentOffer']['id'];              
        }
         $contentOffer[$idOfferPrefer]['typeOffer'] = $offerByVariant['typeOffer'];
        $result = updatePriceVariantByOffer($contentOffer[$idOfferPrefer],$variantIncart,$settings); 
        if(!isset($variantIncart['properties'])) $variantIncart['properties'] = []; 
     }else{ 
        $result = $variantIncart;
        $result['isDiscount'] = 0;
     }
     return $result; 
} 
function getOfferByVariant($variantId,$productID=null){ 
    global $shopify; 
    global $shop; 
    $result = array();
    $result['contentOffer'] = []; 
    $result['content_rule'] = []; 
    /*
        contentOffer: array return cho cart
        content_rule: array return product detail
    */
    $time = date("Y-m-d"); 
    $offerByVariant = getRuleByVariantID($variantId,$shop,$date = "AND (( end_date >= '$time' AND start_date <= '$time') OR (end_date = '0000-00-00' AND start_date <= '$time') OR (end_date = '0000-00-00' AND start_date = '0000-00-00'))"); 
    if(count($offerByVariant) != 0){
        // co rule cho variant 
        $result['typeOffer']     = 'variant'; 
        $result['contentOffer'] = $offerByVariant; 
        $result['content_rule'] = json_decode($offerByVariant['content_rule'], true); 
    }else{
        // khong co rule variant , check rule cho product
        $offerByProduct = getOfferByProduct($productID);  
        if(count($offerByProduct) != 0){
            $result['typeOffer']     = 'product'; 
            $result['contentOffer'] = $offerByProduct; 
            $result['content_rule'] = json_decode($offerByProduct['content_rule'], true); 
        }else{
            // khong co rule product , check rule cho custom collection
            $collections = getCustomColletionByProductID($shopify,$productID); 
            if(is_array($collections) && count($collections) != 0 ){ 
                $offerByCustomCollection = getRuleInListCollection($collections); 
                if(isset($offerByCustomCollection['offerByVariant']) && count($offerByCustomCollection['offerByVariant']) != 0){
                    $result['typeOffer']    = 'collection'; 
                    $result['contentOffer'] = $offerByCustomCollection['offerByVariant']; 
                    $result['content_rule'] = json_decode($offerByCustomCollection['offerByVariant']['content_rule'], true); 
                }
            }  
            // khong co custom collection , check rule cho smart collection
            if(empty($result['content_rule'])){
                $collections = getSmartColletionByProductID($shopify,$productID);  
                if(is_array($collections) && count($collections) != 0 ){ 
                    $offerBySmartCollection = getRuleInListCollection($collections); 
                    if(isset($offerBySmartCollection['offerByVariant']) && count($offerBySmartCollection['offerByVariant']) != 0){
                        $result['typeOffer']    = 'collection'; 
                        $result['contentOffer'] = $offerBySmartCollection['offerByVariant']; 
                        $result['content_rule'] = json_decode($offerBySmartCollection['offerByVariant']['content_rule'], true); 
                    }
                }   
            }
            if(empty($result['content_rule'])){ 
                // khong co smart collection , check rule cho global rule
                $offerByGlobal = getGlobalOffer($shop);   
                if(!empty($offerByGlobal)){
                    $result['typeOffer']    = 'global'; 
                    $result['contentOffer'] = $offerByGlobal; 
                    $result['content_rule'] = json_decode($offerByGlobal['content_rule'], true); 
                } 
            }

        }
    }  
    return $result; 
} 
function getRuleInListCollection($collections){
    $offerByVariant = [];
    $result['typeOffer'] = "";
    foreach($collections as $collection){
        $collectionID = $collection['id'];
        $offerByVariant = getOfferByCollection($collectionID);
        if(count($offerByVariant) != 0){
            $result['typeOffer']    = 'collection';
            break;
        } 
    }
    $result['offerByVariant']    = $offerByVariant;     
    return $result;
}
function getOfferByProduct($productID){
    global $shop; 
    $time = date("Y-m-d"); 
    $result = getRuleByProductID($productID,$shop,$date = "AND (( end_date >= '$time' AND start_date <= '$time') OR (end_date = '0000-00-00' AND start_date <= '$time') OR (end_date = '0000-00-00' AND start_date = '0000-00-00'))");
    return $result;
}
function getOfferByCollection($collectionID){
    global $shop;
    $time = date("Y-m-d"); 
    $result = getRuleByCollectionID($collectionID,$shop,$date = "AND (( end_date >= '$time' AND start_date <= '$time') OR (end_date = '0000-00-00' AND start_date <= '$time') OR (end_date = '0000-00-00' AND start_date = '0000-00-00'))");
    return $result;
}
function getGlobalOffer($shop){ 
    $time = date("Y-m-d");  
    $result = getRuleGlobal($shop,$condition = "and status = 'active'",$date = "AND (( end_date >= '$time' AND start_date <= '$time') OR (end_date = '0000-00-00' AND start_date <= '$time') OR (end_date = '0000-00-00' AND start_date = '0000-00-00'))");
    return $result;
}  


 