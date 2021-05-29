<?php
header('Access-Control-Allow-Origin: *');  
header('Content-Type: application/json');

require 'vendor/autoload.php';

use sandeepshetty\shopify_api;

require 'conn-shopify.php';
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $shop = $_POST["shop"];
    if ($action == "updateCart") {
        $setting = getShopSettings($db, $shop);
        if($setting["use_tag"] == 1){
            if(isset($_POST["customer_tag"])){
                $customerTags = $_POST["customer_tag"]; 
                if(in_array($setting["customer_tag"], $customerTags)){
                    $items = $_POST["items"];
                    $shopify = shopifyInit($db, $shop, $appId);
                    $result = updateCart($items, $db, $shopify);
                    $response = array(
                        "result" => $result,
                        "expired" => checkExpired($db, $shop, $appId, $trialTime)
                    );
                    echo json_encode($response);
                } else {
                    $response = array(
                        "result" => false,
                        "expired" => checkExpired($db, $shop, $appId, $trialTime)
                    );
                    echo json_encode($response);
                }
            } else {
                $response = array(
                    "result" => false,
                    "expired" => checkExpired($db, $shop, $appId, $trialTime)
                );
                echo json_encode($response);
            }
        } else {
            $items = $_POST["items"];
            $shopify = shopifyInit($db, $shop, $appId);
            $result = updateCart($items, $db, $shopify);
            $response = array(
                "result" => $result,
                "expired" => checkExpired($db, $shop, $appId, $trialTime)
            );
            echo json_encode($response);
        }
    }
    if ($action == "checkLimitOrder") {
        $variants = [];
        if(isset($_POST["variants"]) && is_array($_POST["variants"])){
            $variants = $_POST["variants"];
        }  
        $limitVariants = array();
        $changeVriantlink = array();
        foreach ($variants as $variant) {
            $variantId = $variant["variantId"];
            $sql = "select * from price_groups where custom_variant = '$variantId'";
            $query = $db->query($sql);
            $custom = false;
            if ($query) {
                if ($query->num_rows > 0) {
                    while ($row = $query->fetch_assoc()) {
                        $temp = $variantId;
                        $variantId = $row["variant_id"];
                        $custom = true;
                        $temp = array(
                            "customVariant" => $row["custom_variant"],
                            "variantId" => $row["variant_id"]
                        );
                        $changeVriantlink[] = $temp;
                    }
                }
            }
            $quantity = $variant["quantity"];
            $sql = "select * from variant_limit where variant_id = '$variantId'";
            $query = $db->query($sql);
            if ($query) {
                while ($row = $query->fetch_assoc()) {
                    if ($custom) {
                        $row["custom_variant"] = $temp;
                    }
                    $limitVariants[] = $row;
                }
            }
        }
        $response = array(
            "limitVariant" => $limitVariants,
            "changeVriantlink" => $changeVriantlink
        );
        echo json_encode($response);
    }
    if ($action == "getListPriceGroups") {
        $variantId = $_POST["variantId"];
        $sql = "select * from custom_variants where variant_id = '$variantId'";
        $groupsList = array();
        $limits = array();
        $query = $db->query($sql);
        if ($query) {
            if($query->num_rows > 0){
                while ($row = $query->fetch_assoc()) {
                    $groupIds = $row["group_id"];
                    if($groupIds){
                        $groupIds = explode(",", $groupIds);
                        foreach ($groupIds as $groupId) {
                            $sql = "select * from price_groups where id = $groupId";
                            $query = $db->query($sql);
                            if ($query) {
                                while ($row = $query->fetch_assoc()) {
                                    $groupsList[] = $row;
                                }
                            }
                        }
                    }
                }
            }
        }
        $sql = "select * from variant_limit where variant_id = '$variantId'";
        $query = $db->query($sql);
        if ($query) {
            if($query->num_rows > 0){
                while ($row = $query->fetch_assoc()) {
                    $limits[] = $row;
                }
            }
        }
        $response = array(
            "limits" => $limits,
            "groupsList" => $groupsList
        );
        echo json_encode($response);
    }
    if($action == "getSettings"){
        $settings = getShopSettings($db, $shop);
        $response = array(
            "settings" => $settings,
            "expired" => checkExpired($db, $shop, $appId, $trialTime)
        );
        echo json_encode($response);
    }
}

function shopifyInit($db, $shop, $appId) {
    $select_settings = $db->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
    $app_settings = $select_settings->fetch_object();
    $shop_data = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
    $shop_data = $shop_data->fetch_object();
    $shopify = shopify_api\client(
            $shop, $shop_data->access_token, $app_settings->api_key, $app_settings->shared_secret
    );
    return $shopify;
}

function updateCart($items, $db, $shopify) {
    $result = array();
    $saveData = array();
    if(!is_array($items)) $items = [];
    foreach ($items as &$item) {
        $variantId = $item["variant_id"];
        $sql = "select * from price_groups where (custom_variant = '$variantId' "
                . "and product_group > 0) or "
                . "(variant_id = '$variantId' and product_group > 0)";
        $query = $db->query($sql);
        if($query->num_rows > 0){
            while ($row = $query->fetch_assoc()){
                $item["product_group"] = $row["product_group"];
            }
        } else {
                $item["product_group"] = 0;
        }
    }
    
    foreach ($items as $key=>&$value) {
        $value["newquantity"] = (int)$value["quantity"];
        foreach ($items as $key1=>$value1) {
            if($key != $key1 && $value["product_group"] == $value1["product_group"] && $value["product_group"] > 0){
                $value["newquantity"] += (int)$value1["quantity"];
            }
        }
    }  
    foreach ($items as $item) {
        $productId = $item["product_id"];
        $variantId = $item["variant_id"];
        $quantity = $item["quantity"];
        if(isset($item["newquantity"])){
            $quantityToCal = $item["newquantity"];
        } else {
            $quantityToCal = $item["quantity"];
        }
        $isCustomVariant = isCustomVariant($db, $variantId);
        $exist = $isCustomVariant['exist'];
        $customVariant = getCustomVariant($variantId, $db, $quantity, $exist);
        $data = array(
            "custom_variant" => $variantId,
            "quantity" => $item["quantity"],
            "variant_id" => $customVariant["groups"][0]["variant_id"]
        );
        $saveData[] = $data;
        if ($exist) {
            $existCustomVariant = $isCustomVariant['existCustomVariant'];
            $variantId1 = $isCustomVariant['variantId1'];
            $variant = variantIsCustom($db, $existCustomVariant, $customVariant, $quantity, $quantityToCal, $productId, $variantId1, $variantId);
            if ($variant) {
                $result[] = $variant;
            }
        } else {
            $variant = variantIsNotCustom($customVariant, $variantId, $quantity, $quantityToCal, $productId);
            if ($variant) {
                $result[] = $variant;
            }
        }
    }
   
    if ($result) {
        foreach ($result as &$item) {
            $count = (int) $item["item_quantity"];
            foreach ($saveData as $data) {
                if ($item["variant_id"] != $data["custom_variant"] && $data["variant_id"] == $item["variant_id"]) {
                    $count = $count + (int) $data["quantity"];
                    $productId = $item["product_id"];
                    $variantId = $item["variant_id"];
                    $temp = array(
                        "id" => 0,
                        "variant_id" => $data["custom_variant"],
                        "item_quantity" => 0
                    );
                    $result[] = $temp;
                    $item = finalResult($count, $productId, $variantId, $shopify);
                }
            }
        }
    } else {
        foreach ($saveData as $item) {
            $count = (int) $item["quantity"];
            foreach ($items as $item1) {
                if ($item["variant_id"] == $item1["variant_id"] && $item1["variant_id"] != $item["custom_variant"]) {
                    $count = $count + (int) $item1["quantity"];
                    $deleteId = $item1["variant_id"];
                    $productId = $item1["product_id"];
                    $variantId = $item["variant_id"];
                    $updateItem = finalResult($count, $productId, $variantId, $shopify);
                    $result[] = $updateItem;
                    if ($updateItem["quantity"] > $item["quantity"]) {
                        $temp = array(
                            "id" => 0,
                            "variant_id" => $item["custom_variant"],
                            "item_quantity" => $count
                        );
                        $result[] = $temp;
                    }
                    $temp = array(
                        "id" => 0,
                        "variant_id" => $deleteId,
                        "item_quantity" => $count
                    );
                    $result[] = $temp;
                    unset($item1);
                }
            }
        }
    }
//    var_dump($result);die;
    return $result;
}

function getCustomVariant($variantId, $db, $quantity, $exist) {
    if (!$exist) {
        $sql = "select * from custom_variants where variant_id = '$variantId'";
        $query = $db->query($sql);
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $groupIds = $row["group_id"];
            }
        }
        $groupIds = explode(",", $groupIds);
        $groups = array();
        foreach ($groupIds as $groupId) {
            $sql = "select * from price_groups where id = $groupId";
            $query = $db->query($sql);
            if ($query) {
                while ($row = $query->fetch_assoc()) {
                    $groups[] = $row;
                }
            }
        }
        return $customVariant = array(
            "variant_id" => $variantId,
            "groups" => $groups,
            "quantity" => $quantity
        );
    } else {
        $sql = "select * from price_groups where custom_variant = '$variantId'";
        $query = $db->query($sql);
        $groups = array();
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $groups[] = $row;
            }
        }
        return $customVariant = array(
            "variant_id" => $variantId,
            "groups" => $groups,
            "quantity" => $quantity
        );
    }
}

function isCustomVariant($db, $variantId) {
    $sql = "select * from price_groups where custom_variant = '$variantId'";
    $query = $db->query($sql);
    if ($query) {
        if ($query->num_rows > 0) {
            $exist = true;
            while ($row = $query->fetch_assoc()) {
                $existCustomVariant = $row;
                $variantId1 = $row["variant_id"];
            }
        } else {
            $exist = false;
            $response = array(
                'exist' => $exist
            );
            return $response;
        }
    }
    $response = array(
        'exist' => $exist,
        'existCustomVariant' => $existCustomVariant,
        'variantId1' => $variantId1
    );
    return $response;
}

function variantIsCustom($db, $existCustomVariant, $customVariant, $quantity, $quantityToCal, $productId, $variantId1, $variantId) {
    $temp = array();
    $groups = array();
    $sql = "select * from price_groups where variant_id = '$variantId1'";
    $query = $db->query($sql);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $groups[] = $row;
        }
    }
    $customVariant = array(
        "variant_id" => $variantId,
        "groups" => $groups
    );
    if ($quantityToCal < $existCustomVariant["number"]) {
        foreach ($customVariant["groups"] as $group) {
            if ($group["number"] <= $quantityToCal) {
                if (isset($temp["quantity"])) {
                    if ($temp["quantity"] < $group["number"]) {
                        $temp = array(
                            "id" => $group["custom_variant"],
                            "quantity" => $group["number"],
                            "variant_id" => $variantId,
                            "item_quantity" => $quantity,
                            "product_id" => $productId,
                            "action" => "add"
                        );
                    }
                } else {
                    $temp = array(
                        "id" => $group["custom_variant"],
                        "quantity" => $group["number"],
                        "variant_id" => $variantId,
                        "item_quantity" => $quantity,
                        "product_id" => $productId,
                        "action" => "add"
                    );
                }
            }
        }
        if (!$temp) {
            $temp = array(
                "id" => $variantId1,
                "quantity" => $group["number"],
                "variant_id" => $variantId,
                "item_quantity" => $quantity,
                "product_id" => $productId,
                "action" => "add"
            );
        }
    } else if ($quantityToCal >= $existCustomVariant["number"]) {
        foreach ($customVariant["groups"] as $group) {
            if ($group["number"] <= $quantityToCal) {
                if (isset($temp["quantity"])) {
                    if ($temp["quantity"] < $group["number"]) {
                        $temp = array(
                            "id" => $group["custom_variant"],
                            "quantity" => $group["number"],
                            "variant_id" => $variantId,
                            "item_quantity" => $quantity,
                            "product_id" => $productId,
                            "action" => "add"
                        );
                    }
                } else {
                    $temp = array(
                        "id" => $group["custom_variant"],
                        "quantity" => $group["number"],
                        "variant_id" => $variantId,
                        "item_quantity" => $quantity,
                        "product_id" => $productId,
                        "action" => "add"
                    );
                }
            }
        }
        if ($variantId == $temp["id"]) {
            $temp = array();
        }
    }
    if ($temp) {
        return $temp;
    }
}

function variantIsNotCustom($customVariant, $variantId, $quantity, $quantityToCal, $productId) {
    $temp = array();
    foreach ($customVariant["groups"] as $group) {
        if ($group["number"] <= $quantityToCal) {
            if (isset($temp["quantity"])) {
                if ($temp["quantityToCal"] < $group["number"]) {
                    $temp = array(
                        "id" => $group["custom_variant"],
                        "quantity" => $group["number"],
                        "variant_id" => $variantId,
                        "item_quantity" => $quantity,
                        "quantityToCal" => $quantityToCal,
                        "product_id" => $productId,
                        "action" => "add"
                    );
                }
            } else {
                $temp = array(
                    "id" => $group["custom_variant"],
                    "quantity" => $group["number"],
                    "variant_id" => $variantId,
                    "item_quantity" => $quantity,
                    "quantityToCal" => $quantityToCal,
                    "product_id" => $productId,
                    "action" => "add"
                );
            }
        }
    }
    if ($temp) {
        return $temp;
    }
}

function finalResult($count, $productId, $variantId, $shopify) {
    $variants = $shopify("GET", "/admin/products/$productId/variants.json");
    $temp = array();
	if(!is_array($variants)) $variants = [];
    foreach ($variants as $variant) {
		if(isset($variant["id"]))  { 
			$variantId1 = $variant["id"];
			$metafields = $shopify("GET", "/admin/variants/$variantId1/metafields.json");
			if ($metafields) {
				foreach ($metafields as $metafield) {
					if ($metafield["key"] == "variantId" && $metafield["value"] == $variantId) {
						foreach ($metafields as $metafield) {
							if ($metafield["key"] == "number" && $metafield["value"] <= $count) {
								if (isset($temp["quantity"])) {
									if ($temp["quantity"] < $metafield["value"]) {
										$temp = array(
											"metafields" => $metafields,
											"id" => $variantId1,
											"quantity" => $metafield["value"],
											"variant_id" => $variantId,
											"item_quantity" => $count,
											"product_id" => $productId,
											"action" => "add"
										);
									}
								} else {
									$temp = array(
										"metafields" => $metafields,
										"id" => $variantId1,
										"quantity" => $metafield["value"],
										"variant_id" => $variantId,
										"item_quantity" => $count,
										"product_id" => $productId,
										"action" => "add"
									);
								}
							}
						}
					}
				}
			}
		}
    }
    return $temp;
}

function getShopSettings ($db, $shop){
    $sql = "select * from custom_order_settings where shop = '$shop'";
    $query = $db->query($sql);
    $settings = array();
    if($query){
        while ($row = $query->fetch_assoc()){
            $settings = $row;
        }
    }
    return $settings;
}

function checkExpired($db, $shop, $appId, $trialTime) {
    $shop_data = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
    if($shop_data->num_rows < 1){
        return true;
    }
    $shop_data = $shop_data->fetch_object();
    $installedDate = $shop_data->installed_date;
    $confirmation_url = $shop_data->confirmation_url;
    $clientStatus = $shop_data->status;

    $date1 = new DateTime($installedDate);
    $date2 = new DateTime("now");
    $interval = date_diff($date1, $date2);
    $diff = (int)$interval->format('%R%a');
    if($diff > $trialTime && $clientStatus != 'active'){
        return true;
    } else {
        return false;
    }
}