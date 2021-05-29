<?php
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
header('Content-Type: application/json');
require 'vendor/autoload.php';
include 'admin/version1/lib/Classes/PHPExcel/IOFactory.php';

use sandeepshetty\shopify_api;

require 'conn-shopify.php';
require 'help.php';
const LIMIT_PRODUCT_PER_PAGE = 50;
const MAX_LIMIT_PRODUCT_PER_PAGE = 250;
if (isset($_GET["action"])) {
    $action = $_GET["action"];
    $shop = $_GET["shop"];
    $shopify = shopifyInit($db, $shop, $appId);

    if ($action == "getSettings") {
        // ACTION GET SETTING BY SHOP  
        $settings = getSettings($shop);
        echo json_encode($settings);
        exit();
    }

    if ($action == "reloadData") {
        if (is_dir(CACHE_PATH . "$shop")) {
            remove_dir(CACHE_PATH . "$shop");
            db_delete("quantity_price_rule", "shop = '$shop'");
        }
        echo json_encode(true);
    }

    if ($action == "check") {
        $recu = $shopify("GET", "/admin/api/2020-04/recurring_application_charges.json");
        pr($recu);
    }
    if ($action == "updatePlan") {
        $recu = $shopify("GET", "/admin/api/2020-04/recurring_application_charges.json");
        if (isset($recu) && is_array($recu)) {
            foreach ($recu as $v) {
                if ($v['name'] == $chargeTitle && $v['status'] == "active") {
                    $recurring_application_charge_id = $v['id'];
                    $respone = $shopify("DELETE", "/admin/recurring_application_charges/$recurring_application_charge_id.json");
                }
            }
        }
        $charge = array(
            "recurring_application_charge" => array(
                "name" => $chargeTitle,
                "price" => $price * 0.7,
                "return_url" => "$rootLink/charge.php?shop=$shop",
                "test" => $testMode,
                "trial_days" => $trialTime
            )
        );

        if ($chargeType == "one-time") {
            $recu = $shopify("POST", "/admin/application_charges.json", $charge);
            $confirmation_url = $recu["confirmation_url"];
        } else {
            $recu = $shopify("POST", "/admin/recurring_application_charges.json", $charge);
            $confirmation_url = $recu["confirmation_url"];
        }
        header('Location: ' . $confirmation_url);
    }
    if ($action == "getCountCollection") {
        $type = isset($_GET["type"]) ? $_GET["type"] : "custom";
        $collections = getCountCollection($shopify, $type);
        echo json_encode($collections);
        exit();
    }

    if ($action == "getCollectionPerPage") {
        if (!isset($_GET["since_id"])) return [];
        $since_id = $_GET["since_id"];
        $type = isset($_GET["type"]) ? $_GET["type"] : "custom";
        $collections = getCollectionPerPage($shopify, $since_id, $limit = 250, $fields = "id,title,handle", $type);
        if (empty($collections)) {
            echo json_encode([]);
            exit();
        }
        foreach ($collections as &$collection) {
            $getOfferByCollection  = array();
            $getOfferByCollection = getRuleByCollectionID($collection['id'], $shop);
            $collection['countProduct']  = 0;
            if (!empty($getOfferByCollection)) {
                $collection['hasrule'] = true;
                $collection['content_rule'] = json_decode($getOfferByCollection['content_rule'], true);
                $collection['total_rules'] = count($collection['content_rule']);
            } else {
                $collection['hasrule'] = false;
                $collection['total_rules'] = "";
            }
        }
        echo json_encode($collections);
        exit();
    }
    if ($action == "getPlan") {
        $recu = $shopify("GET", "/admin/api/2020-04/recurring_application_charges.json");
        pr($recu);
    }
    if ($action == "getTotalDiscountCode") {
        $checkExistRule = db_fetch_row("SELECT * FROM quantity_price_rule WHERE shop = '$shop'");
        if (count($checkExistRule) > 0) {
            echo json_encode(false);
        } else {
            $totalDiscountCode = getTotalPriceRule($shopify);
            echo json_encode($totalDiscountCode);
        }

        exit();
    }
    if ($action == "getDiscountCodeSaveToDB") {
        if (!isset($_GET['since_id'])) return [];
        $since_id = $_GET['since_id'];
        $listPriceRulePerPage = getPriceRule($shopify, $since_id, $limit = 250);
        if (!empty($listPriceRulePerPage)) {
            foreach ($listPriceRulePerPage as $v) {
                db_insert("quantity_price_rule", [
                    "title" => $v['title'],
                    "value" => $v['value'],
                    "value_type" => $v['value_type'],
                    "shop" => $shop
                ]);
            }
            echo json_encode($listPriceRulePerPage[count($listPriceRulePerPage) - 1]['id']);
            exit();
        } else {
            echo json_encode(false);
            exit();
        }
    }
    if ($action == "changeVersion") {
        if (!isset($_GET['usePriceRule'])) $usePriceRule = 1;
        $usePriceRule = $_GET['usePriceRule'];
        $newUsePriceRule = ($_GET['usePriceRule'] == 1) ? 0 : 1;
        $showDiscountCode = ($_GET['usePriceRule'] == 1) ? 0 : 1;
        $data = [
            "usePriceRule" => $newUsePriceRule,
            "showDiscountCode" => $showDiscountCode
        ];
        $response = db_update("custom_order_settings", $data, "shop = '$shop'");
        if ($response) {
            echo json_encode(true);
        } else {
            echo json_encode(false);
        }
    }
    if ($action == "getGlobal") {
        // ACTION GET RULE FOR GLOBAL
        $rule = getAllRuleByType($shop, $type = "global");
        if (!empty($rule)) {
            $rule['content_rule'] = json_decode($rule['content_rule'], true);
            if ($rule['ruleForCustomerTag'] != NULL)   $rule['ruleForCustomerTag'] = json_decode($rule['ruleForCustomerTag'], true);
        }

        echo json_encode($rule);
    }

    if ($action == "saveGlobal") {
        // ACTION SAVE RULE FOR GLOBAL
        if (!isset($_GET['status']) || !isset($_GET['group']))  return false;
        $status = $_GET['status'];
        $group  = json_encode($_GET['group']);
        $start_date = (isset($_GET['start_date']) && $_GET['start_date'] != NULL) ?   $_GET['start_date'] : "0000-00-00";
        $end_date = (isset($_GET['end_date']) && $_GET['end_date'] != NULL) ?   $_GET['end_date'] : "0000-00-00";
        $ruleForCustomerTag  = NULL;
        if (isset($_GET['ruleForCustomerTag'])) {
            $ruleForCustomerTag = json_encode($_GET['ruleForCustomerTag']);
        }
        $query  = "INSERT INTO quantity_global (status,content_rule,start_date,end_date,ruleForCustomerTag,shop)  VALUES ";
        $query .= "('" . $status . "','" . $group . "','" . $start_date . "',' " . $end_date . "',' " . $ruleForCustomerTag . "', '" . $shop . "'),";
        $query = rtrim($query, ',');
        $query .= " ON DUPLICATE KEY UPDATE status = '$status',content_rule = '$group',start_date = '" . $start_date . "',end_date = '" . $end_date . "',ruleForCustomerTag = '" . $ruleForCustomerTag . "',shop = '$shop'";
        $query = $query . ";";
        $response = $db->query($query);
        echo json_decode(true);
    }
    if ($action == "installWebhook") {
        $webhook = array(
            "webhook" => array(
                "topic" => "orders/create",
                "address" => $rootLink . '/services_v2.php?shop=' . $shop . '&action=webhookOrderCreate',
                "format" => "json"
            )
        );
        $put = $shopify("POST", "/admin/webhooks.json", $webhook);
        echo json_encode(true);
    }
    if ($action == "webhookOrderCreate") {
        $webhookContent = "";
        $webhook = fopen('php://input', 'rb');
        while (!feof($webhook)) {
            $webhookContent .= fread($webhook, 4096);
        }
        fclose($webhook);
        $webhookContent = json_decode($webhookContent, true);
        $discount_id = null;
        $discount_type = null;
        if (isset($webhookContent['discount_applications'][0])) {
            $discount_applications = $webhookContent['discount_applications'][0];
            if (isset($webhookContent['discount_applications'][0]['description'])) {
                $description_discount_order = $webhookContent['discount_applications'][0]['description'];
            } else {
                $description_discount_order = "";
            }

            if (strpos($description_discount_order, "APP QUANTITY BY OMEGA") !== false) {
                // applied app
                $description_discount_order = explode("-", $description_discount_order);
                $InfoDiscount = explode(",", $description_discount_order[1]);
                $discount_id = $InfoDiscount[0];
                $discount_type = $InfoDiscount[1];
                if ($discount_type == "variant")     $discount_type  = 0;
                if ($discount_type == "product")    $discount_type  = 1;
                if ($discount_type == "collection")  $discount_type  = 2;
                if ($discount_type == "global")      $discount_type  = 3;
            }
        }
        db_insert("quantity_statistic", [
            "id_order"    => $webhookContent['id'],
            "id_rule"     => $discount_id,
            "type_rule"   => $discount_type,
            "create_date" => $webhookContent['created_at'],
            "total_price" => $webhookContent['total_price'],
            "discount_price" => $webhookContent['total_discounts'],
            "shop"       => $shop,
        ]);
        http_response_code(200);
    }
    if ($action == "showAllRuleLimitFollowColleciton") {
        $limits = db_fetch_array("SELECT * FROM variant_limit WHERE shop = '$shop' AND id_collection IS NOT NULL AND title_collection IS NOT NULL GROUP BY id_collection");
        foreach ($limits as &$limit) {
            if ($limit['limitforCustomerTag'] == NULL && $limit['limitforCustomerTag'] == "")         $limit['limitforCustomerTag'] = '[]';
            $limit['limitforCustomerTag'] = json_decode($limit['limitforCustomerTag'], true);
        }
        echo json_encode($limits);
    }
    if ($action == "getDataToShowChart") {
        $result['allOrder'] = [];
        $result['orderAppliedRule'] = [];
        $allOrder = db_fetch_array("SELECT create_date, COUNT(*) AS number_record FROM quantity_statistic WHERE shop = '$shop' GROUP BY create_date HAVING number_record > 0");
        foreach ($allOrder as $k => &$v) {
            $arrayTemp = [$v['create_date'], $v['number_record']];
            $result['allOrder'][$k] = $arrayTemp;
        }
        $orderAppliedRule = db_fetch_array("SELECT create_date, COUNT(*) AS number_record FROM quantity_statistic WHERE shop = '$shop' AND id_rule IS NOT NULL  AND id_rule != 0 GROUP BY create_date HAVING number_record > 0");
        foreach ($orderAppliedRule as $k => &$v) {
            $arrayTemp = [$v['create_date'], $v['number_record']];
            $result['orderAppliedRule'][$k] = $arrayTemp;
        }
        echo json_encode($result);
    }
    if ($action == "getStatistic") {
        $where = "";
        $time = getTime($shopify);
        $today = $time['today'];
        $yesterday = $time['yesterday'];
        $week = $time['week'];
        $month = $time['month'];
        $lastmonth = $time['lastmonth'];
        if (isset($_GET['valueFillterDate']) && $_GET['valueFillterDate'] != 0) {
            $valueFillterDate = $_GET['valueFillterDate'];
            //   1, "Today",  //   2, "Yesterday" , //   3, "Week" , //   4, "Month" , //   5, "Last Month" ,  
            if ($valueFillterDate == 1) $where = "AND create_date = '$today'";
            if ($valueFillterDate == 2) $where = "AND create_date = '$yesterday'";
            if ($valueFillterDate == 3) $where = "AND create_date BETWEEN '$week' AND '$today'";
            if ($valueFillterDate == 4) $where = "AND create_date BETWEEN '$month' AND '$today'";
            if ($valueFillterDate == 5) $where = "AND create_date BETWEEN '$lastmonth' AND '$month'";
        }
        $listStatistic = db_fetch_array("SELECT * FROM quantity_statistic WHERE shop = '$shop' $where");
        echo json_encode($listStatistic);
    }
    if ($action == "getAllRule") {
        $where = "";
        $time = getTime($shopify);
        $today = $time['today'];
        $yesterday = $time['yesterday'];
        $week = $time['week'];
        $month = $time['month'];
        $lastmonth = $time['lastmonth'];
        if (isset($_GET['valueFillterDate']) && $_GET['valueFillterDate'] != 0) {
            $valueFillterDate = $_GET['valueFillterDate'];
            $where = " AND (start_date = '0000-00-00' AND end_date = '0000-00-00')";
            //   1, "Today",   2, "Yesterday" ,   3, "Week" ,  4, "Month" ,  5, "Last Month" 
            if ($valueFillterDate == 1) $where .= "OR (start_date = '$today' AND end_date = '$today')";
            if ($valueFillterDate == 2) $where .= "OR (start_date = '$yesterday' AND end_date = '$yesterday')";
            if ($valueFillterDate == 3) $where .= "OR (start_date >= '$week' AND end_date <= '$today')";
            if ($valueFillterDate == 4) $where .= "OR (start_date >= '$month' AND end_date <= '$today')";
            if ($valueFillterDate == 5) $where .= "OR (start_date <= '$lastmonth' AND end_date <= '$month')";
        }
        $quantity_variants = db_fetch_row("SELECT COUNT(*) as count FROM quantity_variant WHERE shop = '$shop' $where");
        $quantity_products = db_fetch_row("SELECT COUNT(*) as count FROM quantity_products WHERE shop = '$shop' $where");
        $quantity_collection = db_fetch_row("SELECT COUNT(*) as count FROM quantity_collection WHERE shop = '$shop' $where");
        $quantity_global = db_fetch_row("SELECT COUNT(*) as count FROM quantity_global WHERE shop = '$shop' $where");

        $count = $quantity_variants['count'] + $quantity_products['count'] + $quantity_collection['count'] + $quantity_global['count'];
        echo json_encode($count);
    }

    if ($action == "saveRuleForCollection") {
        // ACTION SAVE RULE FOR COLLECTION 
        // DELETE FILE CACHE FOR COLLECTION
        if (!isset($_GET['groups']) || !isset($_GET['collectionChoosen'])) return false;
        $content_rule = json_encode($_GET['groups']);
        $collectionChoosens = $_GET['collectionChoosen'];
        $start_date = (isset($_GET['start_date']) && $_GET['start_date'] != NULL) ? ($_GET['start_date']) : "0000-00-00";
        $end_date = (isset($_GET['end_date']) && $_GET['end_date'] != NULL) ? ($_GET['end_date']) : "0000-00-00";
        $ruleForCustomerTag  = NULL;
        if (isset($_GET['ruleForCustomerTag'])) {
            $ruleForCustomerTag = json_encode($_GET['ruleForCustomerTag']);
        }
        $query = "INSERT INTO quantity_collection (collection_id, content_rule,start_date,end_date,ruleForCustomerTag,shop)  VALUES ";
        foreach ($collectionChoosens as $collectionChoosen) {
            db_delete("quantity_collection", "collection_id = '" . $collectionChoosen . "' and shop = '" . $shop . "'");
            $query .= "('" . $collectionChoosen . "', '" . $content_rule . "', '" . $start_date . "', '" . $end_date . "', '" . $ruleForCustomerTag . "','" . $shop . "'),";
        }
        $query = rtrim($query, ',');
        $query = $query . ";";
        $response = $db->query($query);
        $fileCollection = CACHE_PATH . $shop . "collection";
        if (file_exists($fileCollection))  unlink($fileCollection);
        echo json_encode(true);
    }
    if ($action == "getProductByCollection") {
        // ACTION GET PRODUCT BY COLLECTION CHECK PRODUCT HAS RULE 
        if (!isset($_GET['collectionID'])) return false;
        $collectionID = $_GET['collectionID'];
        $products = getProductByCollectionID($shopify, $collectionID, $limit = 250);
        foreach ($products as &$product) {
            $product['countVariants'] = count($product['variants']);
            $product_id = $product['id'];
            $getOfferByProduct = getRuleByProductID($product_id, $shop);
            if (!empty($getOfferByProduct)) {
                $product['hasrule'] = true;
                $product['content_rule'] = json_decode($getOfferByProduct['content_rule'], true);
                $product['total_rules'] = count($product['content_rule']);
            } else {
                $product['hasrule'] = false;
                $product['total_rules'] = "";
            }
            $product['variants'] = array();
        }
        echo json_encode($products);
    }
    if ($action == "deleteRuleCollection") {
        if (!isset($_GET['idCollection'])) return false;
        $collection_id = $_GET['idCollection'];
        $respone        = db_delete("quantity_collection", "collection_id = '" . $collection_id . "' and shop = '" . $shop . "'");
        echo json_encode(true);
    }
    if ($action == "updateRuleCollection") {
        if (!isset($_GET['idCollection'])) return false;
        $collection_id = $_GET['idCollection'];
        $content_rule  = json_encode($_GET['groups']);
        $start_date = (isset($_GET['start_date']) && $_GET['start_date'] != NULL) ?  ($_GET['start_date']) : "0000-00-00";
        $end_date = (isset($_GET['end_date']) && $_GET['end_date'] != NULL) ?  ($_GET['end_date']) :  "0000-00-00";
        $data = array(
            'content_rule' => $content_rule,
            'start_date' => $start_date,
            'end_date' => $end_date,
        );
        $respon = db_update("quantity_collection", $data, "collection_id = '" . $collection_id . "' and shop = '" . $shop . "'");
        $fileCollection = CACHE_PATH . $shop . "collection";
        if (file_exists($fileCollection)) unlink($fileCollection);
        echo json_encode(true);
    }
    if ($action == "getRuleByCollectionID") {
        if (!isset($_GET['idCollection'])) return false;
        $idCollection = $_GET['idCollection'];
        $result = getRuleByCollectionID($idCollection, $shop);
        if (!empty($result['content_rule']))  $result['content_rule'] = json_decode($result['content_rule'], true);
        if ($result['ruleForCustomerTag'] != NULL) {
            $result['ruleForCustomerTag'] = json_decode($result['ruleForCustomerTag'], true);
        } else {
            $result['ruleForCustomerTag'] = [];
        }
        echo json_encode($result);
    }
    if ($action == "getRuleCollectionBeforeAdd") {
        if (!isset($_GET['idCollection'])) return false;
        $idCollection = $_GET['idCollection'];
        $content_rule = array();
        $ruleForCollection = getRuleByCollectionID($idCollection, $shop);
        $countRule         = count($ruleForCollection);
        if ($countRule == 0) {
            $ruleForGlobal = getRuleGlobal($shop);
            if (count($ruleForGlobal) != 0) {
                $content_rule = json_decode($ruleForGlobal['content_rule'], true);
            }
        } else {
            $content_rule = json_decode($ruleForCollection['content_rule'], true);
        }
        echo json_encode($content_rule);
    }
    //PRODUCT

    if ($action == "deleteRuleProduct") {
        if (!isset($_GET['idProduct'])) return false;
        $product_id = $_GET['idProduct'];
        $respon     = db_delete("quantity_products", "product_id = '" . $product_id . "' and shop = '" . $shop . "'");
        echo json_encode(true);
    }
    if ($action == "updateRuleProduct") {
        if (!isset($_GET['idProduct'])) return false;
        $product_id   = $_GET['idProduct'];
        $content_rule = json_encode($_GET['groups']);
        $start_date = (isset($_GET['start_date']) && $_GET['start_date'] != NULL) ?  ($_GET['start_date']) : "0000-00-00";
        $end_date = (isset($_GET['end_date']) && $_GET['end_date'] != NULL) ?  ($_GET['end_date']) : "0000-00-00";
        $ruleForCustomerTag  = NULL;
        if (isset($_GET['ruleForCustomerTag'])) {
            $ruleForCustomerTag = json_encode($_GET['ruleForCustomerTag']);
        }
        $data = array(
            'content_rule' => $content_rule,
            'start_date' => $start_date,
            'ruleForCustomerTag' => $ruleForCustomerTag,
            'end_date' => $end_date
        );
        $respon = db_update("quantity_products", $data, "product_id = '" . $product_id . "' and shop = '" . $shop . "'");
        $fileProduct = CACHE_PATH . $shop . "products";
        if (file_exists($fileProduct))   unlink($fileProduct);
        echo json_encode(true);
    }
    if ($action == "getRuleByProductID") {
        if (!isset($_GET['idProduct'])) return false;
        $idProduct = $_GET['idProduct'];
        $result = getRuleByProductID($idProduct, $shop);
        if (!empty($result['content_rule']))   $result['content_rule'] = json_decode($result['content_rule'], true);
        if ($result['ruleForCustomerTag'] != NULL) {
            $result['ruleForCustomerTag'] = json_decode($result['ruleForCustomerTag'], true);
        } else {
            $result['ruleForCustomerTag'] = [];
        }

        echo json_encode($result);
    }
    //VARIANT RULE
    if ($action == "getVariantByProductID") {
        if (!isset($_GET['idProduct'])) return false;
        $idProduct = $_GET['idProduct'];
        $product = getProductByProductID($shopify, $idProduct, $fields = "id,variants");
        $variants = [];
        if (isset($product['variants'])) $variants = $product['variants'];
        foreach ($variants as &$variant) {
            $variant_id = $variant['id'];
            $result = getRuleByVariantID($variant_id, $shop);
            if (count($result) != 0) {
                $variant['hasRule']      = true;
                $variant['content_rule'] = json_decode($result['content_rule'], true);
                $variant['total_rules'] = count($variant['content_rule']);
            } else {
                $variant['hasRule'] = false;
                $variant['total_rules'] = "";
            }
        }
        $product['variants'] = $variants;
        echo json_encode($product);
    }
    if ($action == "deleteRuleVariant") {
        if (!isset($_GET['idVariant'])) return false;
        $variant_id = $_GET['idVariant'];
        $respon     = db_delete("quantity_variant", "variant_id = '" . $variant_id . "' and shop = '" . $shop . "'");
        echo json_encode(true);
    }
    if ($action == "deleteAllRuleForVariant") {
        $respon     = db_delete("quantity_variant", "shop = '" . $shop . "'");
        echo json_encode(true);
    }
    if ($action == "updateRuleVariant") {
        if (!isset($_GET['idVariant'])) return false;
        $variant_id   = $_GET['idVariant'];
        $content_rule = json_encode($_GET['groups']);
        $data = array(
            'content_rule' => $content_rule
        );
        $respon = db_update("quantity_variant", $data, "variant_id = '" . $variant_id . "' and shop = '" . $shop . "'");
        echo json_encode(true);
    }
    if ($action == "getRuleByVariantID") {
        if (!isset($_GET['idVariant'])) return false;
        $idVariant = $_GET['idVariant'];
        $result    = getRuleByVariantID($idVariant, $shop);
        if (!empty($result['content_rule']))   $result['content_rule'] = json_decode($result['content_rule'], true);
        if ($result['ruleForCustomerTag'] != NULL) {
            $result['ruleForCustomerTag'] = json_decode($result['ruleForCustomerTag'], true);
        } else {
            $result['ruleForCustomerTag'] = [];
        }
        echo json_encode($result);
    }
    if ($action == "getProductPerPage") {
        if (!isset($_GET["since_id"])) return [];
        $since_id = $_GET["since_id"];
        $products = getProductInPage($shopify, $since_id, 150, $fields = "id,title,variants,handle");
        if (count($products) == 0) {
            if (is_dir(CACHE_PATH . "$shop")) {
                remove_dir(CACHE_PATH . "$shop");
            }
            $products = getProductInPage($shopify, $since_id, 150, $fields = "id,title,variants,handle");
        }
        foreach ($products as &$product) {
            $product['countVariants'] = count($product['variants']);
            $getOfferByProduct = getRuleByProductID($product['id'], $shop);
            if (!empty($getOfferByProduct)) {
                $product['hasrule'] = true;
                $product['content_rule'] = json_decode($getOfferByProduct['content_rule'], true);
                $product['total_rules'] = count($product['content_rule']);
            } else {
                $product['hasrule'] = false;
                $product['total_rules'] = "";
            }
            foreach ($product['variants'] as &$v) {
                $sku = $v['sku'];
                $v = [];
                $v['sku'] = $sku;
                $product['sku'] = $sku;
            }
        }
        echo json_encode($products);
        exit();
    }
    if ($action == "getAllCollection") {
        $collections = getAllCollection($shopify, $limit = 250, $fields = "id,title,handle");
        foreach ($collections as &$collection) {
            $getOfferByCollection  = array();
            $getOfferByCollection = getRuleByCollectionID($collection['id'], $shop);
            $collection['countProduct']  = getCountProductByCollection($collection['id'], $shopify);
            if (!empty($getOfferByCollection)) {
                $collection['hasrule'] = true;
                $collection['content_rule'] = json_decode($getOfferByCollection['content_rule'], true);
                $collection['total_rules'] = count($collection['content_rule']);
            } else {
                $collection['hasrule'] = false;
                $collection['total_rules'] = "";
            }
        }
        echo json_encode($collections);
    }

    if ($action == "deleteAllRule") {
        $res = db_delete("quantity_global", "shop = '" . $shop . "'");
        $res = db_delete("quantity_collection", "shop = '" . $shop . "'");
        $res = db_delete("quantity_products", "shop = '" . $shop . "'");
        echo json_encode(true);
    }
    // LIMIT VARIANT
    if ($action == "getLimitVariantBeforeAdd") {
        if (!isset($_GET['idVariant'])) return false;
        $variantID = $_GET['idVariant'];
        $result = getRuleLimitByVariantID($variantID, $shop, 1);
        echo json_encode($result);
    }
    if ($action == "deleteGroupCollection") {
        if (!isset($_GET['idCollection'])) return false;
        $idCollection = $_GET['idCollection'];
        db_delete('variant_limit', "id_collection = $idCollection AND shop = '$shop'");
        echo json_encode(TRUE);
    }
    if ($action == "getVariantLimitByProductID") {
        if (!isset($_GET['idProduct'])) return false;
        $idProduct = $_GET['idProduct'];
        $products = getProductByProductID($shopify, $idProduct, $fields = "id,variants");
        $variants = $products['variants'];
        foreach ($variants as &$variant) {
            $variant_id = $variant['id'];
            $result = getRuleLimitByVariantID($variant_id, $shop, 1);
            if (count($result) != 0) {
                $variant['hasLimit'] = true;
                $variant['max'] = $result['max'];
                $variant['min'] = $result['min'];
                $variant['multiple'] = $result['multiple'];
            } else {
                $variant['hasLimit'] = false;
                $variant['max'] = "";
                $variant['min'] = "";
                $variant['multiple'] = "";
            }
        }
        $products['variants'] = $variants;
        echo json_encode($products);
    }
    if ($action == "deleteLimitVariant") {
        if (!isset($_GET['idVariant'])) return false;
        $idVariant = $_GET['idVariant'];
        if ($idVariant == NULL) {
            $id = db_delete("variant_limit", "shop = '" . $shop . "'");
        } else {
            $id = db_delete("variant_limit", "variant_id = '" . $idVariant . "' and shop = '" . $shop . "'");
        }
        echo json_encode(true);
    }
    if ($action == "deleteLimit") {
        if (!isset($_GET['IDLimit'])) return false;
        $IDLimit = $_GET['IDLimit'];
        $res = db_delete("variant_limit", "id = '" . $IDLimit . "' and shop = '" . $shop . "'");
        echo json_encode(true);
    }
    if ($action == "getLimitByVariantID") {
        if (!isset($_GET['idVariant'])) return false;
        $variantIDChange   = $_GET['idVariant'];
        $limitList = getRuleLimitByVariantID($variantIDChange, $shop, 1);
        echo json_encode($limitList);
    }
    if ($action == "updateLimitVariant") {
        if (!isset($_GET['idVariant'])) return false;
        $idVariant = $_GET['idVariant'];
        $min = $_GET['min'];
        $max = $_GET['max'];
        $data = [
            "min" => $min,
            "max" => $max,
        ];
        $id = db_update("variant_limit", $data, "shop = '$shop' AND variant_id = '$idVariant'");
        echo json_encode(true);
    }
    // STATISTIC 
    if ($action == "showListOrder") {
        if (!isset($_GET['id'])) return false;
        $idRule = $_GET['id'];
        $listOrder = db_fetch_array("SELECT * FROM quantity_statistic WHERE id_rule = $idRule AND shop = '$shop'");
        echo json_encode($listOrder);
    }
    if ($action == "getAllRuleProduct") {
        $result = getAllRuleProduct();
        echo json_encode($result);
    }
    if ($action == "getAllRuleCollection") {
        $result = getAllRuleCollection();
        echo json_encode($result);
    }
    if ($action == "getAllRuleGlobal") {
        $result = getAllRuleGlobal();
        echo json_encode($result);
    }
    if ($action == "getCountProduct") {
        $counProduct  = getCountAllProduct($shopify);
        echo $counProduct;
    }
    if ($action == "getCountOrderByRule") {
        $where = "";
        $time = getTime($shopify);
        $today = $time['today'];
        $yesterday = $time['yesterday'];
        $week = $time['week'];
        $month = $time['month'];
        $lastmonth = $time['lastmonth'];
        if (isset($_GET['valueFillterDate']) && $_GET['valueFillterDate'] != 0) {
            $valueFillterDate = $_GET['valueFillterDate'];
            //   1, "Today", //   2, "Yesterday" ,  //   3, "Week" , //   4, "Month" , //   5, "Last Month" ,
            if ($valueFillterDate == 1) $where = "AND create_date = '$today'";
            if ($valueFillterDate == 2) $where = "AND create_date = '$yesterday'";
            if ($valueFillterDate == 3) $where = "AND create_date BETWEEN '$week' AND '$today'";
            if ($valueFillterDate == 4) $where = "AND create_date BETWEEN '$month' AND '$today'";
            if ($valueFillterDate == 5) $where = "AND create_date BETWEEN '$lastmonth' AND '$month'";
        }
        //echo "SELECT id_rule,COUNT(*) AS number_record,SUM(discount_price) AS total_discount FROM quantity_statistic WHERE id_rule is not null $where AND shop = '$shop'  GROUP BY id_rule HAVING number_record > 0 "; 
        $listOrderByRule = db_fetch_array("SELECT id_rule FROM quantity_statistic WHERE id_rule is not null $where AND shop = '$shop'  GROUP BY id_rule");

        foreach ($listOrderByRule as &$v) {
            $content_rule = getInfoOrderByRuleID($v['id_rule'], $shop);
            $totalDiscount = 0;
            foreach ($content_rule as $value) {
                $totalDiscount += $value['discount_price'];
                $v['type_rule'] = $value['type_rule'];
            }

            $v['number_record'] = count($content_rule);
            $v['total_discount'] = $totalDiscount;

            $v['content_rule'] = getRuleByOrder($v['id_rule'], $v['type_rule'], $shop);
        }
        echo json_encode($listOrderByRule);
    }
}
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["action"])) {
    $action  = $_POST['action'];
    $shop    = $_POST['shop'];
    $shopify = shopifyInit($db, $shop, $appId);
    if ($action == "importExcel") {
        $inputFileName = $_FILES['importFile']['tmp_name'];
        $inputFileType = $_FILES['importFile']['type'];
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        // Lấy tổng số cột của file, trong trường hợp này là 4 dòng
        $highestColumn = $sheet->getHighestColumn();
        if ($highestRow > 2000) {
            echo json_encode([
                'success' => 0,
                'error' => 0,
                'validate' => "Limit 2000 rows"
            ]);
            die;
        }
        // Khai báo mảng $rowData chứa dữ liệu

        //  Thực hiện việc lặp qua từng dòng của file, để lấy thông tin
        for ($row = 1; $row <= $highestRow; $row++) {
            // Lấy dữ liệu từng dòng và đưa vào mảng $rowData
            $rowData[] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        }
        $countSuccess = 0;
        $countError = 0;
        //In dữ liệu của mảng
        for ($i = 1; $i < count($rowData); $i++) {
            $handle = $rowData[$i][0][0];
            $ruleCope = $rowData[$i][0][1];
            $variantOption1 = $rowData[$i][0][2];
            $variantOption2 = $rowData[$i][0][3];
            $variantOption3 = $rowData[$i][0][4];
            $variantTitle = $variantOption1;
            if (strlen($variantOption2) > 0) {
                $variantTitle .= " / " . $variantOption2;
            }
            if (strlen($variantOption3) > 0) {
                $variantTitle .= " / " . $variantOption3;
            }

            $startDate = $rowData[$i][0][5];
            $endDate = $rowData[$i][0][6];

            $miniumQty = $rowData[$i][0][7];
            $discountPerItem = $rowData[$i][0][8];
            $typeDiscount = $rowData[$i][0][9];
            //customer tag
            $customerTag = $rowData[$i][0][10];
            $typeDiscountCustomerTag = $rowData[$i][0][11];
            $discountPerItemCustomerTag = $rowData[$i][0][12];
            /*
            check rule scope xem là variant,collection, product hay global
        */
            //validate
            // $handle = trim($handleSpace);
            if (
                $ruleCope == '' && $handle == '' && $variantOption1 == '' && $variantOption2 == '' &&
                $variantOption3 == '' &&
                $endDate == '' &&
                $startDate == '' &&
                $miniumQty == '' &&
                $discountPerItem == '' &&
                $typeDiscount == '' &&
                $customerTag == '' &&
                $typeDiscountCustomerTag == '' &&
                $discountPerItemCustomerTag == ''
            ) {
                echo json_encode([
                    "success" => $countSuccess,
                    "error" => $countError,
                    "validate" => null
                ]);
                exit;
            }
            // if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$startDate)) {
            //     echo json_encode([
            //         'success'=>$countSuccess,
            //         'error' => $countError,
            //         'validate' => "Row $i: 'startDate' format is incorrect, it must be text type - not date and true with formatting (yyyy-mm-dd) "
            //     ]);
            //     exit;
            // } 
            // if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$endDate)) {
            //     echo json_encode([
            //         'success'=>$countSuccess,
            //         'error' => $countError,
            //         'validate' => "Row $i: 'endDate' format is incorrect, it must be text type - not date and true with formatting (yyyy-mm-dd) "
            //     ]);
            //     exit;
            // }
            if ($ruleCope !== 'variant' && $ruleCope !== 'collection' && $ruleCope !== 'product' && $ruleCope !== 'global') {
                echo json_encode([
                    'success' => $countSuccess,
                    'error' => $countError,
                    'validate' => "Row $i: 'ruleCope' must be one of the following options: global, collection, product, vanriant"
                ]);
                exit;
            }
            // if($typeDiscountCustomerTag!=='price'&&$typeDiscountCustomerTag!=='percent'){
            //     echo json_encode([
            //         'success'=>$countSuccess,
            //         'error' => $countError,
            //         'validate' => "Row $i: 'type Discount Customer Tag' must be one of the following options: price, percent"
            //     ]);
            //     exit;
            // }

            if ($typeDiscount !== 'price' && $typeDiscount !== 'percent') {
                echo json_encode([
                    'success' => $countSuccess,
                    'error' => $countError,
                    'validate' => "Row $i: 'type Discount' must be one of the following options: price, percent"
                ]);
                exit;
            }

            if ($typeDiscount == 'percent' && (float)$discountPerItem > 100) {
                echo json_encode([
                    'success' => $countSuccess,
                    'error' => $countError,
                    'validate' => "Row $i: 'Discount per item' not exceeding 100%"
                ]);
                exit;
            }
            if ($typeDiscountCustomerTag == 'percent' && (float)$discountPerItemCustomerTag > 100) {
                echo json_encode([
                    'success' => $countSuccess,
                    'error' => $countError,
                    'validate' => "Row $i: 'Discount Per Item Customer Tag' not exceeding 100%"
                ]);
                exit;
            }
            if (!is_numeric((float)$miniumQty) || floor((float)$miniumQty) !== (float)$miniumQty || (float)$miniumQty <= 0) {
                echo json_encode([
                    'success' => $countSuccess,
                    'error' => $countError,
                    'validate' => "Row $i: 'miniumQty' must be a positive integer"
                ]);
                exit;
            }
            if (!is_numeric((float)$discountPerItem) || (float)$discountPerItem < 0) {
                echo json_encode([
                    'success' => $countSuccess,
                    'error' => $countError,
                    'validate' => "Row $i: 'discount Per Item' must be a integer"
                ]);
                exit;
            }
            if (!is_numeric((float)$discountPerItemCustomerTag) || (float)$discountPerItemCustomerTag < 0) {
                echo json_encode([
                    'success' => $countSuccess,
                    'error' => $countError,
                    'validate' => "Row $i: 'discount Per Item Customer Tag' must be a integer"
                ]);
                exit;
            }
            if ($ruleCope == 'variant') {
                if ($shop == 'hi-tech-fasteners.myshopify.com') {
                    $variantsInProduct = $shopify("GET", APIVERSION . "products/$handle.json" . "?fields=variants");
                } else {
                    $variantsInProduct = $shopify("GET", APIVERSION . "products.json?handle=" . $handle . "&fields=variants");
                }
                if ($shop == 'hi-tech-fasteners.myshopify.com') {
                    $variant = $variantsInProduct['variants'];
                } else {
                    $variant = $variantsInProduct[0]['variants'];
                }
                if (is_array($variant) && count($variant) > 0) {
                    $checkExitVariant = false;
                    for ($j = 0; $j < count($variant); $j++) {
                        $title = $variant[$j]['title'];
                        if ($title == $variantTitle) {
                            $checkExitVariant = true;
                            $idVariant = $variant[$j]['id'];
                            $data = [
                                "price" => "$discountPerItem",
                                "number" => "$miniumQty",
                                "discountType" => $typeDiscount,
                            ];
                            $result = db_fetch_array("SELECT content_rule,ruleForCustomerTag FROM quantity_variant WHERE shop = '$shop' AND variant_id = $idVariant");
                            if (count($result) > 0) {
                                $content_rule = json_decode($result[0]['content_rule'], true);
                            } else {
                                $content_rule = null;
                            }

                            if ($content_rule == null || count($content_rule) == 0) {
                                $dataInsert = json_encode(array($data));
                                $dbSuccess = $db->query("INSERT INTO quantity_variant (content_rule, variant_id, shop, start_date,end_date)
                                    VALUES ('$dataInsert', $idVariant, '$shop', '$startDate','$endDate')");
                                if ($dbSuccess == 1) {
                                    $countSuccess++;
                                } else {
                                    $countError++;
                                }
                            } else {
                                $checkExitRule = false;
                                for ($k = 0; $k < count($content_rule); $k++) {  //kiểm tra xem rule vs number có chưa, có thì update chưa thì add
                                    if ($content_rule[$k]['number'] == $miniumQty) {
                                        $checkExitRule = true;
                                        $content_rule[$k]['price'] = "$discountPerItem";
                                        $content_rule[$k]['discountType'] = $typeDiscount;
                                        break;
                                    }
                                }
                                if ($checkExitRule == false) {
                                    $content_rule = array_merge($content_rule, array($data));
                                }
                                $dataUpdate = json_encode($content_rule);
                                $dbSuccessUpdate = $db->query("UPDATE quantity_variant SET content_rule='$dataUpdate', start_date = '$startDate', end_date = '$endDate'  WHERE shop='$shop' AND variant_id='$idVariant'");

                                if ($dbSuccessUpdate == 1) {
                                    $countSuccess++;
                                } else {
                                    $countError++;
                                }
                            }
                            //customer tags
                            if (strlen($discountPerItemCustomerTag) > 0 && strlen($customerTag) > 0 && strlen($typeDiscountCustomerTag) > 0) {
                                $dataTag = [
                                    "price" => "$discountPerItemCustomerTag",
                                    "tag" => $customerTag,
                                    "discountType" => $typeDiscountCustomerTag
                                ];
                                if (count($result) > 0) {
                                    $ruleForCustomerTag = json_decode($result[0]['ruleForCustomerTag'], true);
                                } else {
                                    $ruleForCustomerTag = null;
                                }
                                if ($ruleForCustomerTag == null) {
                                    $ruleForCustomerTag = array($dataTag);
                                } else {
                                    $checkExistRuleTag = false;
                                    for ($g = 0; $g < count($ruleForCustomerTag); $g++) {  //kiểm tra xem rule vs number có chưa, có thì update chưa thì add
                                        if ($ruleForCustomerTag[$g]['tag'] == $customerTag) {
                                            $checkExistRuleTag = true;
                                            $ruleForCustomerTag[$g]['price'] = "$discountPerItemCustomerTag";
                                            $ruleForCustomerTag[$g]['discountType'] = $typeDiscountCustomerTag;
                                            break;
                                        }
                                    }
                                    if ($checkExistRuleTag == false) {
                                        $ruleForCustomerTag = array_merge($ruleForCustomerTag, array($dataTag));
                                    }
                                }

                                $dataUpdateTag = [
                                    "ruleForCustomerTag" => json_encode($ruleForCustomerTag),
                                ];
                                db_update("quantity_variant", $dataUpdateTag, "shop = '$shop' and variant_id ='$idVariant'");
                            };
                        }
                    }
                    if ($checkExitVariant == false) {
                        echo json_encode([
                            'success' => $countSuccess,
                            'error' => $countError,
                            'validate' => "Row $i: 'Cannot find varians of $handle products on the store, please check each option"
                        ]);
                        exit;
                    }
                };
            }
            if ($ruleCope == 'product') {
                if ($shop == 'hi-tech-fasteners.myshopify.com') {
                    $productArray = $shopify("GET", APIVERSION . "products/$handle.json" . "?fields=id");
                } else {
                    $productArray = $shopify("GET", APIVERSION . "products.json?handle=" . $handle . "&fields=id");
                };
                if (is_array($productArray) && count($productArray) > 0) {
                    if ($shop == 'hi-tech-fasteners.myshopify.com') {
                        $idProduct =  $productArray['id'];
                    } else {
                        $idProduct = $productArray[0]['id'];
                    }
                    $data = [
                        "price" => "$discountPerItem",
                        "number" => "$miniumQty",
                        "discountType" => $typeDiscount,
                    ];
                    $result = db_fetch_array("SELECT content_rule,ruleForCustomerTag FROM quantity_products WHERE shop = '$shop' AND product_id = $idProduct");
                    if (count($result) > 0) {
                        $content_rule = json_decode($result[0]['content_rule'], true);
                    } else {
                        $content_rule = null;
                    }
                    if ($content_rule == null || count($content_rule) == 0) {
                        $dataInsert = json_encode(array($data));
                        $dbSuccess = $db->query("INSERT INTO quantity_products (content_rule, product_id, shop, start_date,end_date)
                            VALUES ('$dataInsert', $idProduct, '$shop', '$startDate','$endDate')");
                        if ($dbSuccess == 1) {
                            $countSuccess++;
                        } else {
                            $countError++;
                        }
                    } else {
                        $checkExitRule = false;
                        for ($k = 0; $k < count($content_rule); $k++) {  //kiểm tra xem rule vs number có chưa, có thì update chưa thì add
                            if ($content_rule[$k]['number'] == $miniumQty) {
                                $checkExitRule = true;
                                $content_rule[$k]['price'] = "$discountPerItem";
                                $content_rule[$k]['discountType'] = $typeDiscount;
                                break;
                            }
                        }
                        if ($checkExitRule == false) {
                            $content_rule = array_merge($content_rule, array($data));
                        }
                        $dataUpdate = json_encode($content_rule);
                        $dbSuccessUpdate = $db->query("UPDATE quantity_products SET content_rule='$dataUpdate', start_date = '$startDate', end_date = '$endDate'  WHERE shop='$shop' AND product_id='$idProduct'");
                        if ($dbSuccessUpdate == 1) {
                            $countSuccess++;
                        } else {
                            $countError++;
                        }
                    }
                    //customer tags
                    if (strlen($discountPerItemCustomerTag) > 0 && strlen($customerTag) > 0 && strlen($typeDiscountCustomerTag) > 0) {
                        $dataTag = [
                            "price" => "$discountPerItemCustomerTag",
                            "tag" => $customerTag,
                            "discountType" => $typeDiscountCustomerTag
                        ];
                        if (count($result) > 0) {
                            $ruleForCustomerTag = json_decode($result[0]['ruleForCustomerTag'], true);
                        } else {
                            $ruleForCustomerTag = null;
                        }
                        if ($ruleForCustomerTag == null) {
                            $ruleForCustomerTag = array($dataTag);
                        } else {
                            $checkExistRuleTag = false;
                            for ($g = 0; $g < count($ruleForCustomerTag); $g++) {  //kiểm tra xem rule vs number có chưa, có thì update chưa thì add
                                if ($ruleForCustomerTag[$g]['tag'] == $customerTag) {
                                    $checkExistRuleTag = true;
                                    $ruleForCustomerTag[$g]['price'] = "$discountPerItemCustomerTag";
                                    $ruleForCustomerTag[$g]['discountType'] = $typeDiscountCustomerTag;
                                    break;
                                }
                            }
                            if ($checkExistRuleTag == false) {
                                $ruleForCustomerTag = array_merge($ruleForCustomerTag, array($dataTag));
                            }
                        }
                        $dataUpdateTag = [
                            "ruleForCustomerTag" => json_encode($ruleForCustomerTag),
                        ];
                        db_update("quantity_products", $dataUpdateTag, "shop = '$shop' and product_id ='$idProduct'");
                    };
                } else {
                    echo json_encode([
                        'success' => $countSuccess,
                        'error' => $countError,
                        'validate' => "Row $i: 'Cannot find $handle products on the store, please check the handle!"
                    ]);
                    exit;
                }
            }

            if ($ruleCope == 'collection') {
                $collection = $shopify("GET", APIVERSION . "collections/$handle.json");
                if (isset($collection['id'])) {
                    $handle = $collection['id'];

                    $data = [
                        "price" => "$discountPerItem",
                        "number" => "$miniumQty",
                        "discountType" => $typeDiscount,
                    ];
                    $result = db_fetch_array("SELECT content_rule,ruleForCustomerTag FROM quantity_collection WHERE shop = '$shop' AND collection_id = $handle");
                    if (count($result) > 0) {
                        $content_rule = json_decode($result[0]['content_rule'], true);
                    } else {
                        $content_rule = null;
                    }
                    if ($content_rule == null || count($content_rule) == 0) {
                        $dataInsert = json_encode(array($data));
                        //     "content_rule" => json_encode(array($data)),
                        //     "collection_id" => $handle,
                        //     "shop" => $shop,
                        //     "start_date" => $startDate,
                        //     "end_date" => $endDate,
                        // ];
                        // $dbSuccess = db_insert("quantity_collection", $dataInsert);
                        $dbSuccess = $db->query("INSERT INTO quantity_collection (content_rule, collection_id, shop, start_date,end_date)
                            VALUES ('$dataInsert', $handle, '$shop', '$startDate','$endDate')");
                        if ($dbSuccess == 1) {
                            $countSuccess++;
                        } else {
                            $countError++;
                        }
                    } else {
                        $checkExitRule = false;
                        for ($k = 0; $k < count($content_rule); $k++) {  //kiểm tra xem rule vs number có chưa, có thì update chưa thì add
                            if ($content_rule[$k]['number'] == $miniumQty) {
                                $checkExitRule = true;
                                $content_rule[$k]['price'] = "$discountPerItem";
                                $content_rule[$k]['discountType'] = $typeDiscount;
                                break;
                            }
                        }
                        if ($checkExitRule == false) {
                            $content_rule = array_merge($content_rule, array($data));
                        }
                        $dataUpdate = json_encode($content_rule);
                        //     "content_rule" => json_encode($content_rule),
                        //     "start_date" => $startDate,
                        //     "end_date" => $endDate,
                        // ];
                        $dbSuccessUpdate = $db->query("UPDATE quantity_collection SET content_rule='$dataUpdate', start_date = '$startDate', end_date = '$endDate'  WHERE shop='$shop' AND collection_id='$handle'");

                        // $dbSuccess = db_update("quantity_collection", $dataUpdate, "shop = '$shop' and collection_id ='$handle'");
                        if ($dbSuccessUpdate == 1) {
                            $countSuccess++;
                        } else {
                            $countError++;
                        }
                    }
                    //customer tags
                    if (strlen($discountPerItemCustomerTag) > 0 && strlen($customerTag) > 0 && strlen($typeDiscountCustomerTag) > 0) {
                        $dataTag = [
                            "price" => "$discountPerItemCustomerTag",
                            "tag" => $customerTag,
                            "discountType" => $typeDiscountCustomerTag
                        ];
                        if (count($result) > 0) {
                            $ruleForCustomerTag = json_decode($result[0]['ruleForCustomerTag'], true);
                        } else {
                            $ruleForCustomerTag = null;
                        }
                        if ($ruleForCustomerTag == null) {
                            $ruleForCustomerTag = array($dataTag);
                        } else {
                            $checkExistRuleTag = false;
                            for ($g = 0; $g < count($ruleForCustomerTag); $g++) {  //kiểm tra xem rule vs number có chưa, có thì update chưa thì add
                                if ($ruleForCustomerTag[$g]['tag'] == $customerTag) {
                                    $checkExistRuleTag = true;
                                    $ruleForCustomerTag[$g]['price'] = "$discountPerItemCustomerTag";
                                    $ruleForCustomerTag[$g]['discountType'] = $typeDiscountCustomerTag;
                                    break;
                                }
                            }
                            if ($checkExistRuleTag == false) {
                                $ruleForCustomerTag = array_merge($ruleForCustomerTag, array($dataTag));
                            }
                        }
                        $dataUpdateTag = [
                            "ruleForCustomerTag" => json_encode($ruleForCustomerTag),
                        ];
                        db_update("quantity_collection", $dataUpdateTag, "shop = '$shop' and collection_id ='$handle'");
                    };
                } else {
                    echo json_encode([
                        'success' => $countSuccess,
                        'error' => $countError,
                        'validate' => "Row $i: 'Cannot find collections on store, please check the handle!"
                    ]);
                    exit;
                }
            }
            if ($ruleCope == 'global') {
                $data = [
                    "price" => "$discountPerItem",
                    "number" => "$miniumQty",
                    "discountType" => $typeDiscount,
                ];
                $result = db_fetch_array("SELECT content_rule,ruleForCustomerTag FROM quantity_global WHERE shop = '$shop'");
                if (count($result) > 0) {
                    $content_rule = json_decode($result[0]['content_rule'], true);
                } else {
                    $content_rule = null;
                }
                if ($content_rule == null || count($content_rule) == 0) {
                    $dataInsert = json_encode(array($data));
                    //     "content_rule" => json_encode(array($data)),
                    //     "status" => 'active',
                    //     "shop" => $shop,
                    //     "start_date" => $startDate,
                    //     "end_date" => $endDate,
                    // ];
                    $dbSuccess = $db->query("INSERT INTO quantity_global (content_rule, status, shop, start_date,end_date)
                        VALUES ('$dataInsert','active', '$shop', '$startDate','$endDate')");
                    // $dbSuccess = db_insert("quantity_global", $dataInsert);
                    if ($dbSuccess == 1) {
                        $countSuccess++;
                    } else {
                        $countError++;
                    }
                } else {
                    $checkExitRule = false;
                    for ($k = 0; $k < count($content_rule); $k++) {  //kiểm tra xem rule vs number có chưa, có thì update chưa thì add
                        if ($content_rule[$k]['number'] == $miniumQty) {
                            $checkExitRule = true;
                            $content_rule[$k]['price'] = "$discountPerItem";
                            $content_rule[$k]['discountType'] = $typeDiscount;
                            break;
                        }
                    }
                    if ($checkExitRule == false) {
                        $content_rule = array_merge($content_rule, array($data));
                    }
                    $dataUpdate = json_encode($content_rule);
                    //     "content_rule" => json_encode($content_rule),
                    //     "start_date" => $startDate,
                    //     "end_date" => $endDate,
                    // ];
                    $dbSuccessUpdate = $db->query("UPDATE quantity_global SET content_rule='$dataUpdate', start_date = '$startDate', end_date = '$endDate'  WHERE shop='$shop'");

                    // $dbSuccess = db_update("quantity_global", $dataUpdate, "shop = '$shop'");
                    if ($dbSuccessUpdate == 1) {
                        $countSuccess++;
                    } else {
                        $countError++;
                    }
                }
                //customer tags
                if (strlen($discountPerItemCustomerTag) > 0 && strlen($customerTag) > 0 && strlen($typeDiscountCustomerTag) > 0) {
                    $dataTag = [
                        "price" => "$discountPerItemCustomerTag",
                        "tag" => $customerTag,
                        "discountType" => $typeDiscountCustomerTag
                    ];
                    if (count($result) > 0) {
                        $ruleForCustomerTag = json_decode($result[0]['ruleForCustomerTag'], true);
                    } else {
                        $ruleForCustomerTag = null;
                    }
                    if ($ruleForCustomerTag == null) {
                        $ruleForCustomerTag = array($dataTag);
                    } else {
                        $checkExistRuleTag = false;
                        for ($g = 0; $g < count($ruleForCustomerTag); $g++) {  //kiểm tra xem rule vs number có chưa, có thì update chưa thì add
                            if ($ruleForCustomerTag[$g]['tag'] == $customerTag) {
                                $checkExistRuleTag = true;
                                $ruleForCustomerTag[$g]['price'] = "$discountPerItemCustomerTag";
                                $ruleForCustomerTag[$g]['discountType'] = $typeDiscountCustomerTag;
                                break;
                            }
                        }
                        if ($checkExistRuleTag == false) {
                            $ruleForCustomerTag = array_merge($ruleForCustomerTag, array($dataTag));
                        }
                    }
                    $dataUpdateTag = [
                        "ruleForCustomerTag" => json_encode($ruleForCustomerTag),
                    ];
                    db_update("quantity_global", $dataUpdateTag, "shop = '$shop'");
                };
            }
        }
        echo json_encode([
            "success" => $countSuccess,
            "error" => $countError,
            "validate" => null
        ]);
        exit;
    }
    if ($action == "saveRuleForProduct") {
        $content_rule = json_encode($_POST['groups']);
        $allProductChoosen = $_POST['allProductChoosen'];
        $start_date = (isset($_POST['start_date']) && $_POST['start_date'] != NULL) ? $_POST['start_date'] : "0000-00-00";
        $end_date =   (isset($_POST['end_date']) && $_POST['end_date'] != NULL) ? $_POST['end_date'] : "0000-00-00";
        $ruleForCustomerTag  = NULL;
        if (isset($_POST['ruleForCustomerTag'])) {
            $ruleForCustomerTag = json_encode($_POST['ruleForCustomerTag']);
        }
        $query = "INSERT INTO quantity_products (product_id, content_rule,start_date,end_date,ruleForCustomerTag,shop)  VALUES ";
        foreach ($allProductChoosen as $productChoosen) {
            db_delete("quantity_products", "product_id = '" . $productChoosen . "' and shop = '" . $shop . "'");
            $query .= "('" . $productChoosen . "', '" . $content_rule . "', '" . $start_date . "', '" . $end_date . "', '" . $ruleForCustomerTag . "','" . $shop . "'),";
        }
        $query = rtrim($query, ',');
        $query = $query . ";";
        $response = $db->query($query);
        $fileProduct = CACHE_PATH . $shop . "products";
        if (file_exists($fileProduct)) {
            unlink($fileProduct);
        }
        echo json_encode(true);
    }
    if ($action == "saveRuleForVariant") {
        $content_rule = json_encode($_POST['groups']);
        $allVariantChoosen = $_POST['allVariantChoosen'];
        $start_date = (isset($_POST['start_date']) && $_POST['start_date'] != NULL) ? ($_POST['start_date']) : "0000-00-00";
        $end_date = (isset($_POST['end_date']) && $_POST['end_date'] != NULL) ? ($_POST['end_date']) : "0000-00-00";
        $ruleForCustomerTag  = NULL;
        if (isset($_POST['ruleForCustomerTag'])) {
            $ruleForCustomerTag = json_encode($_POST['ruleForCustomerTag']);
        }
        $query = "INSERT INTO quantity_variant (variant_id, content_rule,start_date,end_date,ruleForCustomerTag,shop)  VALUES ";
        foreach ($allVariantChoosen as $variantChoosen) {
            db_delete("quantity_variant", "variant_id = '" . $variantChoosen . "' and shop = '" . $shop . "'");
            $query .= "('" . $variantChoosen . "', '" . $content_rule . "', '" . $start_date . "', '" . $end_date . "', '" . $ruleForCustomerTag . "','" . $shop . "'),";
        }
        $query = rtrim($query, ',');
        $query = $query . ";";
        $response = $db->query($query);
        $fileVariant = CACHE_PATH . $shop . "variant";
        if (file_exists($fileVariant)) {
            unlink($fileVariant);
        }
        echo json_encode(true);
    }

    if ($action == "saveLimitForVariant") {
        if (!isset($_POST['allVariantChoosen'])) return false;
        if (!isset($_POST['min'])) $_POST['min'] = 0;
        if (!isset($_POST['max'])) $_POST['max'] = 0;
        $variantID = $_POST['allVariantChoosen'];
        $min       = $_POST['min'];
        $max       = $_POST['max'];
        $multiple  = $_POST['multiple'];
        $start_date = NULL;
        $end_date = NULL;
        if (isset($_POST['date'])) {
            $date      = $_POST['date'];
            $date = explode(" - ", $date);
            if (isset($date[0]) && isset($date[1])) {
                $start_date = $date[0];
                $end_date = $date[1];
            }
        }

        $listRuleLimitCustomerTag = [];
        if (isset($_POST['listRuleLimitCustomerTag'])) {
            $listRuleLimitCustomerTag = $_POST['listRuleLimitCustomerTag'];
        }

        foreach ($variantID as $value) {
            $check_variant = getRuleLimitByVariantID($value, $shop, 1);
            if (count($check_variant) == 0) {
                $data = [
                    "variant_id" => $value,
                    "min" => $min,
                    "max" => $max,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "multiple" => $multiple,
                    "limitforCustomerTag" => json_encode($listRuleLimitCustomerTag),
                    "shop" => $shop,
                ];
                $id = db_insert("variant_limit", $data);
            } else {
                $data = [
                    "min" => $min,
                    "multiple" => $multiple,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "max" => $max,
                    "limitforCustomerTag" => json_encode($listRuleLimitCustomerTag),
                ];
                $id = db_update("variant_limit", $data, "shop='$shop' AND variant_id = '$value'");
            }
        }
        echo json_encode(true);
    }
    if ($action == "saveLimitForProducts") {
        if (!isset($_POST['selected'])) return false;
        $min = $_POST['min'];
        $max =  $_POST['max'];
        $multiple  = $_POST['multiple'];
        $start_date = NULL;
        $end_date = NULL;
        $listRuleLimitCustomerTag = [];
        if (isset($_POST['listRuleLimitCustomerTag'])) {
            $listRuleLimitCustomerTag = $_POST['listRuleLimitCustomerTag'];
        }
        $listRuleLimitCustomerTag = str_replace('"', "'", json_encode($listRuleLimitCustomerTag));
        if (isset($_POST['date'])) {
            $date      = $_POST['date'];
            $date = explode(" - ", $date);
            if (isset($date[0]) && isset($date[1])) {
                $start_date = $date[0];
                $end_date = $date[1];
            }
        }
        $selected = $_POST['selected'];
        if ($selected == 0) { // Products
            $productsChoosen = $_POST['productsChoosen'];
            $id = insertLimitForProductVariant($productsChoosen, $min, $max, $multiple, $start_date, $end_date, $listRuleLimitCustomerTag);
        } else if ($selected == 1) {
            $collectionsChoosen = $_POST['collectionsChoosen'];
            $id = insertLimitForCollectionVariant($collectionsChoosen, $min, $max, $multiple, $start_date, $end_date, $listRuleLimitCustomerTag);
        } else if ($selected == 2) {
            $id = insertLimitForAllVariant($min, $max, $multiple, $start_date, $end_date, $listRuleLimitCustomerTag);
        }
        echo json_encode(true);
    }
    if ($action == "saveSettings") {
        $settings = $_POST['settings'];
        $settingsCurrent = db_fetch_row("SELECT enableApp FROM custom_order_settings WHERE shop = '$shop'");
        $data = array(
            'table_border_size'    => $settings['table_border_size'],
            'table_width'          => $settings['table_width'],
            'table_border_color'   => $settings['table_border_color'],
            'table_text_size'      => $settings['table_text_size'],
            'table_text_color'     => $settings['table_text_color'],
            'limit_text_size'      => $settings['limit_text_size'],
            'limit_text_color'     => $settings['limit_text_color'],
            'limit_border_color'   => $settings['limit_border_color'],
            'limit_border_size'    => $settings['limit_border_size'],
            'limit_background'     => $settings['limit_background'],
            'table_heading_size'   => $settings['table_heading_size'],
            'table_heading_color'  => $settings['table_heading_color'],
            'input_border_size'    => $settings['input_border_size'],
            'input_border_color'   => $settings['input_border_color'],
            'show_heading'         => $settings['show_heading'],
            'groups_heading'       => $settings['groups_heading'],
            'group_table_heading'  => $db->real_escape_string($settings['group_table_heading']),
            'price_table_heading'  => $db->real_escape_string($settings['price_table_heading']),
            'limits_heading'       => $db->real_escape_string($settings['limits_heading']),
            'min_table_heading'    => $db->real_escape_string($settings['min_table_heading']),
            'max_table_heading'    => $db->real_escape_string($settings['max_table_heading']),
            'max_text'             => $db->real_escape_string($settings['max_text']),
            'min_text'             => $db->real_escape_string($settings['min_text']),
            'use_tag'              => $settings['use_tag'],
            'customer_tag'         => $settings['customer_tag'],
            'table_text'           => $db->real_escape_string($settings['table_text']),
            'table_text1'          => $db->real_escape_string($settings['table_text1']),
            'show_percent'         => $settings['show_percent'],
            'limit_on_product'     => $settings['limit_on_product'],
            'total_amount_text'    => $db->real_escape_string($settings['total_amount_text']),
            'show_total_amount'    => $settings['show_total_amount'],
            'layout'               => $settings['layout'],
            'numberItem'           => $settings['numberItem'],
            'autoplay'             => $settings['autoplay'],
            'colorPrice'           => $settings['colorPrice'],
            'speedSlide'           => $settings['speedSlide'],
            'textAfter'            => $settings['textAfter'],
            'enableApp'            => $settings['enableApp'],
            'showDiscountCode'     => $settings['showDiscountCode'],
            'customCss'            => $settings['customCss'],
            'type_tag_for_customer' => $settings['type_tag_for_customer'],
            'labelCheckout'        => $db->real_escape_string($settings['labelCheckout']),
            'notificationInCollection'  => $db->real_escape_string($settings['notificationInCollection']),
            'showColumnLayoutTable'     => $settings['showColumnLayoutTable'],
            'residual_text'             => $db->real_escape_string($settings['residual_text']),
            'notificationInCart'        => $db->real_escape_string($settings['notificationInCart']),
            'notificationMultiple'        => $db->real_escape_string($settings['notificationMultiple']),
        );
        $response = db_update("custom_order_settings", $data, "shop = '$shop'");
        if ($settingsCurrent['enableApp'] == 1 && $data['enableApp'] == 0) {
            $db->query("
                INSERT INTO quantity_email_jobs 
                SET shop = '$shop', email_type = 4, start_date = NOW() + INTERVAL 5 MINUTE , start_date_report = NOW() , end_date_report = NOW() + INTERVAL 30 DAY
            ");
            $db->query("
                INSERT INTO quantity_email_jobs 
                SET shop = '$shop', email_type = 5, start_date = NOW() + INTERVAL 3 DAY 
            ");
        } else {
            db_delete("quantity_email_jobs", "(email_type = 4 OR email_type = 5 ) AND shop = '" . $shop . "'");
        }
        if ($response) {
            echo json_encode(true);
        } else {
            echo json_encode(false);
        }
    }
}

function getInfoOrderByRuleID($id_rule, $shop)
{
    $result = db_fetch_array("SELECT * FROM quantity_statistic WHERE shop = '$shop' AND id_rule = $id_rule");
    return $result;
}
function getRuleByOrder($idRule, $typeRule, $shop)
{
    $content_rule = [];
    if ($typeRule == 0)  $typeRule  = "variant";
    if ($typeRule == 1)  $typeRule  = "products";
    if ($typeRule == 2)  $typeRule  = "collection";
    if ($typeRule == 3)  $typeRule  = "global";
    $table = "quantity_" . $typeRule;
    $rule = db_fetch_row("SELECT * FROM $table WHERE shop = '$shop' AND id = $idRule");
    if (!isset($rule['content_rule'])) return [];
    $content_rule = json_decode($rule['content_rule'], true);
    return $content_rule;
}
function getAllRuleProduct()
{
    global $shop;
    global $shopify;
    $result = getAllRuleByType($shop, $type = "products");
    if (count($result) > 0) {
        foreach ($result as &$v) {
            $v['typeRule'] = 'Product';
            $v['content_rule'] = json_decode($v['content_rule'], true);
            $product_id    = $v['product_id'];
            $infoProductByID = getProductByProductID($shopify, $product_id, $fields = "title,handle");
            if (is_array($infoProductByID) && $infoProductByID != null) {
                $v['title'] = $infoProductByID['title'];
                $v['handle'] = $infoProductByID['handle'];
            }
        }
    }
    return $result;
}
function getAllRuleCollection()
{
    global $shop;
    global $shopify;
    $result = getAllRuleByType($shop, $type = "collection");
    if (count($result) > 0) {
        foreach ($result as &$v) {
            $v['typeRule'] = 'Collection';
            $v['content_rule'] = json_decode($v['content_rule'], true);
            $collection_id = $v['collection_id'];
            $infoCollectionByID = getCollectionByID($shopify, $collection_id, $limit = 250, $fields = "id,title,handle");
            if (count($infoCollectionByID) > 0) {
                $v['title'] = $infoCollectionByID['title'];
                $v['handle'] = $infoCollectionByID['handle'];
            }
        }
    }
    return $result;
}
function getAllRuleGlobal()
{
    global $shop;
    $result = getRuleGlobal($shop, $condition = "");
    if (count($result) > 0) {
        $result[0]['content_rule'] = json_decode($result[0]['content_rule'], true);
        $result[0]['typeRule'] = 'Global';
        $result[0]['title'] = 'Entire store';
    }
    return $result;
}
function insertLimitForCollectionVariant($collectionChoosen, $min, $max, $multiple, $start_date, $end_date, $listRuleLimitCustomerTag)
{
    $query = "INSERT INTO variant_limit ( variant_id,min,max,multiple,start_date,end_date,limitforCustomerTag,shop,id_product,id_collection,title_collection)  VALUES ";
    global $shopify;
    global $shop;
    global $db;
    foreach ($collectionChoosen as $v) {
        $collection_id  = $v['id'];
        $title  = $v['title'];
        $products  = getProductByCollectionID($shopify, $collection_id, $limit = 250);
        foreach ($products as $product) {
            $product_id = $product['id'];
            foreach ($product['variants'] as $variant) {
                $query .= '("' . $variant['id'] . '", "' . $min . '", "' . $max . '","' . $multiple . '", "' . $start_date . '","' . $end_date . '","' . $listRuleLimitCustomerTag . '","' . $shop . '","' . $product_id . '","' . $collection_id . '","' . $title . '"),';
            }
        }
    }
    $query = rtrim($query, ',');
    $query .= "
     ON DUPLICATE KEY UPDATE 
        min = '$min', 
        max = '$max',
        multiple = '$multiple',
        start_date = '$start_date',
        end_date = '$end_date', 
        limitforCustomerTag = '" . str_replace("'", '"', $listRuleLimitCustomerTag) . "',  
        id_product = '$product_id', 
        id_collection = '$collection_id', 
        title_collection = '$title'
    ";
    $query = $query . ";";
    $response = $db->query($query);
    return $response;
}
function insertLimitForProductVariant($productsChoosen, $min, $max, $multiple, $start_date, $end_date, $listRuleLimitCustomerTag)
{
    /*
        || LIMIT FOR PRODUCT BULK ACTION VERSION 1
    */
    global $shopify;
    global $shop;
    global $db;
    $query = "INSERT INTO variant_limit (variant_id,min,max,multiple,start_date,end_date,limitforCustomerTag,shop)  VALUES ";
    foreach ($productsChoosen as $value) {
        $product_id = $value['id'];
        $variant = getVariantByProductID($shopify, $product_id);
        foreach ($variant as  $val) {
            $query .=  '("' . $val['id'] . '", "' . $min . '", "' . $max . '","' . $multiple . '","' . $start_date . '","' . $end_date . '","' . $listRuleLimitCustomerTag . '", "' . $shop . '"),';
        }
    }
    $query = rtrim($query, ',');
    $query .= " ON DUPLICATE KEY UPDATE min = '$min', max = '$max', multiple = '$multiple', start_date = '$start_date', end_date = '$end_date',limitforCustomerTag = '" . str_replace("'", '"', $listRuleLimitCustomerTag) . "'";
    $query = $query . ";";
    $response = $db->query($query);
    return $response;
}
function insertLimitForAllVariant($min, $max, $multiple, $start_date, $end_date, $listRuleLimitCustomerTag)
{
    global $shopify;
    global $shop;
    global $db;
    $query = "INSERT INTO variant_limit ( variant_id,min,max,multiple,start_date,end_date,limitforCustomerTag, shop)  VALUES ";
    $response = true;
    $count   = getCountAllProduct($shopify);
    if ($count > 0) {
        $pages = ceil($count / 10);
        for ($i = 0; $i < $pages; $i++) {
            $listProductInPages = getProductInPage($shopify, ($i + 1), $limit = 10, $fields = "id,variants");
            foreach ($listProductInPages as $listProductInPage) {
                foreach ($listProductInPage['variants'] as $variantProduct) {
                    $query .= '("' . $variantProduct['id'] . '", "' . $min . '", "' . $max . '","' . $multiple . '","' . $start_date . '","' . $end_date . '","' . $listRuleLimitCustomerTag . '", "' . $shop . '"),';
                }
            }
            $query = rtrim($query, ',');
            $query .= " ON DUPLICATE KEY UPDATE min = '$min', max = '$max', multiple = '$multiple', start_date = '$start_date', end_date = '$end_date',limitforCustomerTag = '" . str_replace("'", '"', $listRuleLimitCustomerTag) . "'";
            $query = $query . ";";
            $response = $db->query($query);
            $query = "INSERT INTO variant_limit ( variant_id,min,max,multiple,start_date,end_date,limitforCustomerTag, shop)  VALUES ";
        }
    }
    return $response;
}

function saveDataToFileJSon($path, $shop)
{
    $result = [];
    $result = getALLDataByShop($shop);
    // check file ton tai. neu ton tai thi xoa file de tao file moi
    if (file_exists($path)) unlink($path);
    $respone = file_put_contents($path, json_encode($result));
    return $respone;
}

function remove_dir($dir = null)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") remove_dir($dir . "/" . $object);
                else unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
