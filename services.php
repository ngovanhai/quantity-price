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
    if ($action == "addGroupPrice") {
        $groups = $_GET["groups"];
        $variantId = $_GET["variantId"];
        $variantTitlte = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $_GET["variantTitle"]);
        $options = $_GET["options"];
        $productId = $_GET["productId"];
        $groupsId = array();
        $resultGroup = array();
        $customVariants = array();
        $shopify = shopifyInit($db, $shop, $appId);
        $temp = $shopify("GET", "/admin/variants/$variantId.json");
        $sku = $temp["sku"];
        $grams = $temp["grams"];
        $weight = $temp["weight"];
        $weight_unit = $temp["weight_unit"];
        $groups = preGroupsData($groups);
        foreach ($groups as $group) {
            $productGroupId = $group->productGroup;
            $price = $group->price;
            $percent = $group->percent;
            $number = $group->number;
            if ($price == 0 || $number == 0) {
                if (isset($group->id)) {
                    deleteGroup($db, $group->id, $variantId, $productId, $shopify);
                }
            } else {
                if (isset($group->id)) {
                    $id = $group->id;
                    updateGroup($db, $shopify, $id, $price, $percent, $number, $variantTitlte, $productGroupId);
                    $sql = "select * from price_groups where id = $id";
                    $query = $db->query($sql);
                    if ($query) {
                        while ($row = $query->fetch_assoc()) {
                            $resultGroup[] = $row;
                        }
                    }
                } else {
                    $insertGroup = insertGroup($weight_unit, $weight, $grams, $sku, $db, $shopify, $price, $percent, $number, $variantTitlte, $variantId, $productId, $shop, $options, $productGroupId);
                    $group->id = $insertGroup["id"];
                    $groupsId[] = $group->id;
                    $resultGroup[] = $group;
                    $customVariants[] = $insertGroup["variant"];
                }
            }
        }
        //}
        if ($groupsId) {
            updateCustomVariantTable($db, $variantId, $groupsId, $shop);
        }
        $result = array(
            "success" => 1,
            "msg" => "success",
            "groups" => json_encode($resultGroup),
            "variants" => $customVariants
        );
        echo json_encode($result);
    }
    if ($action == "deleteGroup") {
        $shopify = shopifyInit($db, $shop, $appId);
        $groupId = $_GET["id"];
        $variantId = $_GET["variantId"];
        $productId = $_GET["productId"];
        deleteGroup($db, $groupId, $variantId, $productId, $shopify);
    }
    
    // if($action == 'webhookProductCreate'){
    //     $shopify = shopifyInit($db, $shop, $appId); 
    //     $webhook = fopen('php://input', 'rb');
    //     while (!feof($webhook)) {
    //         $webhookContent .= fread($webhook, 4096);
    //     }  
    //     fclose($webhook); 
         
    //     $webhookContent = json_decode($webhookContent,true);  
    //     $product_id = $webhookContent['id'];   
    //     $product_title = $webhookContent['title'];

    //     $collections = $shopify("GET", "/admin/custom_collections.json?product_id=$product_id");
    //     if(count($collections) == 0){
    //         $collections = $shopify("GET", "/admin/smart_collections.json?product_id=$product_id");
    //     }
    //     $tesst_collection = json_encode($collections);
    
    //     foreach($collections as $collection){
    //         $id_collection = $collection['id'];  
    //        // $db->query("insert into test_data(test,name) values('$id_collection','id collection')"); 
    //         $collectionInDB = db_fetch_row("select * from quantity_product where collection_id = '$id_collection' and shop = '$shop'");  
    //         if(count($collectionInDB) != 0){
    //             $data = array(
    //                 'product_id' => $product_id,
    //                 'product_title' => $product_title,
    //                 'collection_id' => $collectionInDB['collection_id'],
    //                 'shop' => $shop,
    //                 'idOffer' => $collectionInDB['idOffer'],
    //                 'collection_title' =>  $collectionInDB['collection_title']
    //             );
    //             db_insert("quantity_product",$data);
    //             $tesst = json_encode($data);
                 
    //         }
    //     } 
    //     return 200;
    // } 
    if ($action == "addProductGroup") {
        $shopify = shopifyInit($db, $shop, $appId);
        $groupTitle = $_GET["groupTitle"];
        $sql = "insert into price_break_group(title, shop) values('$groupTitle','$shop') ON DUPLICATE KEY UPDATE title = '$groupTitle'";
        $query = $db->query($sql);
    }
    if ($action == "getProductGroup") {
        $sql = "select * from price_break_group where shop = '$shop'";
        $query = $db->query($sql);
        $productGroups = array();
        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $productGroups[] = $row;
            }
        }
        echo json_encode($productGroups);
    }
    if ($action == "saveSettings") {
        $settings = $_GET["settings"];
        $settings = json_decode($settings);
        $table_border_size = $settings->table_border_size;
        $table_width = $settings->table_width;
        $table_border_color = $settings->table_border_color;
        $table_text_size = $settings->table_text_size;
        $table_text_color = $settings->table_text_color;
        $limit_text_size = $settings->limit_text_size;
        $limit_text_color = $settings->limit_text_color;
        $limit_border_color = $settings->limit_border_color;
        $limit_border_size = $settings->limit_border_size;
        $limit_background = $settings->limit_background;
        $table_heading_size = $settings->table_heading_size;
        $table_heading_color = $settings->table_heading_color;
        $input_border_size = $settings->input_border_size;
        $input_border_color = $settings->input_border_color;
        $show_heading = $settings->show_heading;
        $groups_heading = $settings->groups_heading;
        $group_table_heading = $settings->group_table_heading;
        $price_table_heading = $settings->price_table_heading;
        $limits_heading = $settings->limits_heading;
        $min_table_heading = $settings->min_table_heading;
        $max_table_heading = $settings->max_table_heading;
        $max_text = $settings->max_text; 
        $min_text = $settings->min_text;
        $use_tag = $settings->use_tag;
        $customer_tag = $settings->customer_tag;
        $table_text = $settings->table_text;
        $table_text1 = $settings->table_text1;
        $show_percent = $settings->show_percent;
        $limit_on_product = $settings->limit_on_product;
        $total_amount_text = $settings->total_amount_text;
        $show_total_amount = $settings->show_total_amount;
        $db->query("update custom_order_settings set table_border_size=$table_border_size, table_width='$table_width',"
                . "table_border_color='$table_border_color', table_text_size=$table_text_size,"
                . "table_text_color='$table_text_color', limit_text_size=$limit_text_size,"
                . "limit_text_color='$limit_text_color', limit_border_color='$limit_border_color',"
                . "limit_border_size=$limit_border_size, limit_background='$limit_background',"
                . "input_border_size=$input_border_size, input_border_color='$input_border_color',"
                . "show_heading=$show_heading, groups_heading='$groups_heading', show_percent=$show_percent, limit_on_product=$limit_on_product, total_amount_text='$total_amount_text', show_total_amount=$show_total_amount,"
                . "max_text='$max_text', min_text='$min_text', table_text1 = '$table_text1', table_text = '$table_text', use_tag = $use_tag, customer_tag = '$customer_tag',"
                . "group_table_heading='$group_table_heading', price_table_heading='$price_table_heading',"
                . "limits_heading='$limits_heading', min_table_heading='$min_table_heading', max_table_heading='$max_table_heading',"
                . "table_heading_size=$table_heading_size, table_heading_color='$table_heading_color' where shop = '$shop'");
    }
    if ($action == "getSettings") {
        $settings = getShopSettings($db, $shop);
        echo json_encode($settings);
    }
    if ($action == "getProductList") {
        $shopify = shopifyInit($db, $shop, $appId);
        $page = $_GET["page"];
        $count = $_GET["count"];
        $filter = json_decode($_GET["filter"]);
        if (isset($filter->title)) {
            $title = $filter->title;
        } else {
            $title = "";
        }
        $productsCount = $shopify("GET", "/admin/products/count.json?page=$page&limit=$count&title=$title");
        $products = $shopify("GET", "/admin/products.json?page=$page&limit=$count&title=$title&fields=id,title,variants");
        $response = array(
            "products" => $products,
            "count" => $productsCount
        );
        echo json_encode($response);
    }
    if ($action == "getCollectionList") {
        $shopify = shopifyInit($db, $shop, $appId);
        $page = $_GET["page"];
        $count = $_GET["count"];
        $filter = json_decode($_GET["filter"]);
        if (isset($filter->title)) {
            $title = $filter->title;
        } else {
            $title = "";
        }
        $collectsCount = $shopify("GET", "/admin/custom_collections/count.json?page=$page&limit=$count&title=$title");
        $collects = $shopify("GET", "/admin/custom_collections.json?page=$page&limit=$count&title=$title");
        $response = array(
            "collects" => $collects,
            "count" => $collectsCount
        );
        echo json_encode($response);
    }
    if ($action == "getGroupsList") {
        $id = $_GET["id"];
        $sql = "select * from custom_variants where shop = '$shop' and variant_id = '$id'";
        $result = array();
        $query = $db->query($sql);
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $result = $row;
            }
        }
        if (isset($result["group_id"])) {
            $groups = $result["group_id"];
            $groups = explode(",", $groups);
            $temp = array();
            foreach ($groups as $group) {
                $sql = "select * from price_groups where id = $group";
                $query = $db->query($sql);
                if ($query) {
                    while ($row = $query->fetch_assoc()) {
                        $temp[] = $row;
                    }
                }
            }
            $result["groups"] = $temp;
        }
        echo json_encode($result);
    }
    if ($action == "getLimitList") {
        $id = $_GET["id"];
        $sql = "select * from variant_limit where shop ='$shop' and variant_id = '$id'";
        $response = array();
        $query = $db->query($sql);
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $response[] = $row;
            }
        }
        echo json_encode($response);
    }
    if ($action == "saveVariantLimit") {
        $variant = $_GET["variant"];
        $fields = "variant_id,shop,";
        $values = "'$variant','$shop',";
        $update = '';
        if (isset($_GET["min"])) {
            $min = $_GET["min"];
            $fields = $fields . "min";
            $values = $values . "$min";
            $update = 'min = ' . $min;
        }
        if (isset($_GET["max"])) {
            $max = $_GET["max"];
            if ($min) {
                $fields = $fields . ",max";
                $values = $values . ",$max";
                $update = $update . ',max = ' . $max;
            } else {
                $fields = $fields . "max";
                $values = $values . "$max";
                $update = 'max = ' . $max;
            }
        }
        $sql = "select * from variant_limit where variant_id = '$variant' and shop = '$shop'";
        $query = $db->query($sql);
        if ($query) {
            if ($query->num_rows > 0) {
                $sql = "update variant_limit set $update where variant_id = '$variant'";
                $db->query($sql);
                $response = array(
                    "error" => 0,
                    "msg" => "success"
                );
            } else {
                $sql = "insert into variant_limit($fields) values($values)";
                $query = $db->query($sql);
                if ($query) {
                    $response = array(
                        "error" => 0,
                        "msg" => "success"
                    );
                } else {
                    $response = array(
                        "error" => 1,
                        "msg" => "insert error"
                    );
                }
            }
        }
        echo json_encode($response);
    }
    if ($action == "saveWholeLimit") {
        $shopify = shopifyInit($db, $shop, $appId);
        $productCount = $shopify("GET", "/admin/products/count.json");
        $loop = (int) ($productCount / 50);
        if ($productCount % 50 > 0) {
            $loop++;
        }
        for ($i = 1; $i <= $loop; $i++) {
            $products = $shopify("GET", "/admin/products.json?page=$i");
            usleep(600000);
            foreach ($products as $product) {
                $variants = $product["variants"];
                foreach ($variants as $variant1) {
                    $variant = $variant1["id"];
                    $fields = "variant_id,shop,";
                    $values = "'$variant','$shop',";
                    $update = '';
                    if (isset($_GET["min"])) {
                        $min = $_GET["min"];
                        $fields = $fields . "min";
                        $values = $values . "$min";
                        $update = 'min = ' . $min;
                    }
                    if (isset($_GET["max"])) {
                        $max = $_GET["max"];
                        if ($min) {
                            $fields = $fields . ",max";
                            $values = $values . ",$max";
                            $update = $update . ',max = ' . $max;
                        } else {
                            $fields = $fields . "max";
                            $values = $values . "$max";
                            $update = 'max = ' . $max;
                        }
                    }
                    $sql = "select * from variant_limit where variant_id = '$variant' and shop = '$shop'";
                    $query = $db->query($sql);
                    if ($query) {
                        if ($query->num_rows > 0) {
                            $sql = "update variant_limit set $update where variant_id = '$variant'";
                            $db->query($sql);
                            $response = array(
                                "error" => 0,
                                "msg" => "success"
                            );
                        } else {
                            $sql = "insert into variant_limit($fields) values($values)";
                            $query = $db->query($sql);
                        }
                    }
                }
            }
        }
        $response = array("success" => true);
        echo json_encode($response);
    }
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if(isset($_POST['action']) && isset($_POST['shop'])){
        $action = $_POST['action'];
        $shop = $_POST['shop'];
        $shopify = shopifyInit($db, $shop, $appId);
        if ($action == "saveWholeProduct") {
            $typeWhole = $_POST['typeWhole'];
            if (isset($_POST['groups'])) $groups = $_POST['groups'];
            $listProductChoosen = $_POST['productChoosen'];
            if ($typeWhole == 0) {
                foreach ($listProductChoosen as $v_product) {
                    foreach ($v_product['variants'] as $v) {
                        $options = $v['options'];
                        $price_variant = $v['price'];
                        $v['title'] = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $v['title']);
                        if (!strpos($v['title'], "and above")) { // check variant có phải variant mình tạo hay ko
                            $groupsId = array();
                            foreach ($groups as $group) {
                                $insertGroup = updateVariantProduct($db, $v_product['id'], $shopify, $v['id'], $price_variant, $group, $options, $shop);
                                array_push($groupsId, $insertGroup);
                            }
                            updateCustomVariantTable($db, $v['id'], $groupsId, $shop);
                        }
                    }
                }
            }
            echo json_encode(true);
        }
    }
     
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

function updateVariantProduct($db, $productId, $shopify, $variantId, $price_variant, $group, $options, $shop) {
    $percent = $group['percent'];
    $number = $group['number'];
    $price_new = round(($price_variant - $price_variant * $percent / 100), 2);
    $data = array(
        "variant" => array(
            "price" => $price_new,
            "metafields" => array(
                array(
                    "key" => "customVariant",
                    "value" => 1,
                    "value_type" => "string",
                    "namespace" => "omega"
                ),
                array(
                    "key" => "variantId",
                    "value" => $variantId,
                    "value_type" => "integer",
                    "namespace" => "omega"
                ),
                array(
                    "key" => "number",
                    "value" => $number,
                    "value_type" => "integer",
                    "namespace" => "omega"
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
    $variant = $shopify("POST", "/admin/products/$productId/variants.json", $data);
    if (isset($variant['id'])) {
        $customVariant = $variant['id'];
        if ($customVariant) {

            $sql = "insert into price_groups(price,percent,number,custom_variant,variant_id, product_group, shop)"
                    . "values($price_new,$percent, $number, '$customVariant', '$variantId', 0, '$shop')";

            $query = $db->query($sql);
            return $db->insert_id;
        }
    }
}

function getCustomVariantByVariantID($db, $shop, $main_variantId, $shopify) {
    $sql = "select * from price_groups where variant_id = '$main_variantId'";
    $query = $db->query($sql);
    $listCustomVariant = array();
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $listCustomVariant[] = $row;
        }
    }
    $main_variantProduct = $shopify("GET", "/admin/variants/{$main_variantId}.json");
    foreach ($listCustomVariant as $v) {
        $price_new = round(($main_variantProduct['price'] - $main_variantProduct['price'] * $v['percent'] / 100), 2);
        $id = $v['id'];
        $id_variant = $v['custom_variant'];
        $db->query("update price_groups set price = $price_new where id = '$id'");
        $data = array(
            "variant" => array(
                "id" => $v['custom_variant'],
                "price" => $price_new
            )
        );
        $changeVariant = $shopify("PUT", "/admin/variants/$id_variant.json", $data);
    }
}

function getCustomerList($shopify) {
    $customerList = $customers = $shopify("GET", "/admin/customers.json");
    return $customerList;
}

function checkExistCustomer($db, $customerId) {
    $result = array();
    $sql = "select * from customer_level where customer_id = '$customerId'";
    $query = $db->query($sql);
    if ($query) {
        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $result = array(
                    "exist" => true,
                    "customerLevel" => $row
                );
                return $result;
            }
        } else {
            $result = array(
                "exist" => false
            );
            return $result;
        }
    }
}

function checkApplyLevel($db, $level, $shopify, $shop) {
    $sql = "select * from levels where id = $level";
    $query = $db->query($sql);
    $settings = getSettings($db, $shop);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $level = $row;
        }
    }
    if ($level["applied"]) {
        $sql = "select * from customer_level where shop = '$shop'";
        $query = $db->query($sql);
        $savedCustomers = array();
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $savedCustomers[] = $row;
            }
        }
        $customers = getCustomerList($shopify);
        foreach ($customers as $customer) {
            if ($settings["auto_type"] == 0) {
                checkApply($db, $shop, $customer, $level, $savedCustomers, "total_spent");
            } else {
                checkApply($db, $shop, $customer, $level, $savedCustomers, "orders_count");
            }
        }
    }
}

function getProductByCollection($collection_id, $shopify) {
    $result = $shopify("GET", "/admin/products.json?collection_id=$collection_id");
    return $result[0];
}

function getCustomLevel($db, $levelId) {
    $sql = "select * from levels where id = $levelId";
    $query = $db->query($sql);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $level = $row;
        }
    }
    return $level;
}

function checkApply($db, $shop, $customer, $level, $savedCustomers, $filter) {
    if ($level[$filter] <= $customer[$filter]) {
        $check = true;
        foreach ($savedCustomers as $savedCustomer) {
            if ($savedCustomer["customer_id"] == $customer["id"]) {
                $check = false;
                $temp = $level["id"];
                $temp1 = $savedCustomer["customer_id"];
                $customLevel = getCustomLevel($db, $savedCustomer["level_id"]);
                if ($customLevel[$filter] <= $level[$filter]) {
                    $db->query("update customer_level set level_id = $temp where customer_id = '$temp1'");
                }
            }
        }
        if ($check) {
            $temp = $customer["id"];
            $temp1 = $level["id"];
            $db->query("insert into customer_level(shop, customer_id, level_id) values('$shop', '$temp', $temp1)");
        }
    } else {
        foreach ($savedCustomers as $savedCustomer) {
            if ($savedCustomer["customer_id"] == $customer["id"] && $savedCustomer["level_id"] == $level["id"]) {
                $tempCustomerId = $savedCustomer["customer_id"];
                $sql = "select * from levels where applied = 1";
                $appliedLevels = array();
                $query = $db->query($sql);
                if ($query) {
                    while ($row = $query->fetch_assoc()) {
                        $appliedLevels[] = $row;
                    }
                }
                $temp = array();
                foreach ($appliedLevels as $appliedLevel) {
                    if ($appliedLevel[$filter] <= $customer[$filter]) {
                        if (!$temp) {
                            $temp = $appliedLevel;
                        } else {
                            if ($temp[$filter] < $appliedLevel[$filter]) {
                                $temp = $appliedLevel;
                            }
                        }
                    }
                }
                if ($temp) {
                    $tempLevelId = $temp["id"];
                    $db->query("update customer_level set level_id = $tempLevelId where customer_id = '$tempCustomerId'");
                } else {
                    $db->query("delete from customer_level where customer_id = '$tempCustomerId'");
                }
            }
        }
    }
}

function getCustomersOfLevel($level, $db, $shopify, $shop) {
    $levelId = $level["id"];
    $sql = "select * from customer_level where level_id = $levelId";
    $temp = array();
    $query = $db->query($sql);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $temp[] = $row;
        }
    }
    return $temp;
}

function suspendLevels($customers, $db, $shopify, $shop) {
    foreach ($customers as $customer) {
        suspendLevel($customer, $db, $shopify, $shop);
    }
}

function suspendLevel($customer, $db, $shopify, $shop) {
    $customer = getCustomerById($customer['customer_id'], $shopify);
    changeNewLevel($customer, $db, "orders_count", $shop);
}

function getCustomerById($customerId, $shopify) {
    $customer = $shopify("GET", "/admin/customers/$customerId.json");
    return $customer;
}

function changeNewLevel($customer, $db, $filter, $shop) {
    $filter1 = $customer[$filter];
    $sql = "select * from levels where orders_count <= $filter1 and shop = '$shop' and applied = 1 order by $filter desc limit 0,1";
    $query = $db->query($sql);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $newLevel = $row;
        }
    }
    if (isset($newLevel)) {
        $levelId = $newLevel["id"];
        $customerId = $customer["id"];
        $sql = "update customer_level set level_id = $levelId where customer_id = $customerId";
        $db->query($sql);
    }
}

function updateGroup($db, $shopify, $id, $price, $percent, $number, $variantTitlte, $productGroupId) {
    $group = getGroupById($db, $id);
    $customVariantId = $group["custom_variant"];
    updateCustomVariant($shopify, $customVariantId, $price, $number, $variantTitlte);
    //update in DB
    $sql = "update price_groups set price = $price, percent = $percent, number = $number, product_group = $productGroupId where id = $id";
    $db->query($sql);
}

function getGroupById($db, $id) {
    $sql = "select * from price_groups where id = $id";
    $query = $db->query($sql);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $group = $row;
        }
    }
    return $group;
}

function updateCustomVariant($shopify, $customVariantId, $price, $number, $variantTitlte) {
    $data = array(
        "variant" => array(
            "id" => $customVariantId,
            "option1" => $variantTitlte . " (" . $number . " and above)",
            "price" => $price
        )
    );
    $changeVariant = $shopify("PUT", "/admin/variants/$customVariantId.json", $data);
    $metafields = getMetafieldsOfVariant($shopify, $customVariantId);
    foreach ($metafields as $metafield) {
        if ($metafield["key"] == "number") {
            $metafieldId = $metafield["id"];
        }
    }
    $data = array(
        "metafield" => array(
            "id" => $metafieldId,
            "option1" => $variantTitlte . " (" . $number . " and above)",
            "value" => $number
        )
    );
    $changeMetafield = $shopify("PUT", "/admin/variants/$customVariantId/metafields/$metafieldId.json", $data);
}

function getMetafieldsOfVariant($shopify, $variantId) {
    $metafields = $shopify("GET", "/admin/variants/$variantId/metafields.json");
    return $metafields;
}

function insertGroup($weight_unit, $weight, $grams, $sku, $db, $shopify, $price, $percent, $number, $variantTitlte, $variantId, $productId, $shop, $options, $productGroupId) {
    $data = array(
        "variant" => array(
            "sku" => $sku,
            "grams" => $grams,
            "weight" => $weight,
            "weight_unit" => $weight_unit,
            "price" => $price,
            "metafields" => array(
                array(
                    "key" => "customVariant",
                    "value" => 1,
                    "value_type" => "string",
                    "namespace" => "omega"
                ),
                array(
                    "key" => "variantId",
                    "value" => $variantId,
                    "value_type" => "integer",
                    "namespace" => "omega"
                ),
                array(
                    "key" => "number",
                    "value" => $number,
                    "value_type" => "integer",
                    "namespace" => "omega"
                )
            )
        )
    );
    $options = json_decode($options);
    if ($options->option1) {
        if ($options->option2) {
            $option1Title = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options->option1);
        } else {
            $option1Title = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options->option1 . " ($number and above)");
        }
        $data["variant"]["option1"] = $option1Title;
    }
    if ($options->option2) {
        if ($options->option3) {
            $option2Title = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options->option2);
        } else {
            $option2Title = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options->option2 . " ($number and above)");
        }
        $data["variant"]["option2"] = $option2Title;
    }
    if ($options->option3) {
        $data["variant"]["option3"] = preg_replace('/[^A-Za-z0-9\-\(\)\. ]/', '', $options->option3 . " ($number and above)");
    }
    $variant = $shopify("POST", "/admin/products/$productId/variants.json", $data);
    $customVariant = $variant["id"];
    $sql = "insert into price_groups(price,percent,number,custom_variant,variant_id, product_group, shop) values($price,$percent, $number, '$customVariant', '$variantId', $productGroupId, '$shop')";
    $query = $db->query($sql);
    $insertId = $db->insert_id;
    $result = array(
        "id" => $insertId,
        "variant" => $variant
    );
    return $result;
}

function updateCustomVariantTable($db, $variantId, $groupsId, $shop) {
    $sql = "select * from custom_variants where variant_id = $variantId and shop = '$shop'";
    $query = $db->query($sql);
    if ($query) {
        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $customVariant = $row;
            }
            $customGroupIds = $customVariant["group_id"];
            $customGroupIds = explode(",", $customGroupIds);
            foreach ($groupsId as $groupId) {
                array_push($customGroupIds, intval($groupId));
            }
            $customGroupIds = implode(",", $customGroupIds);
            $sql = "update custom_variants set group_id = '$customGroupIds' where variant_id = '$variantId'";
            $db->query($sql);
        } else {
            $groupsId = implode(",", $groupsId);
            $sql = "insert into custom_variants(variant_id, group_id, shop) value('$variantId','$groupsId', '$shop')";
            $db->query($sql);
        }
    }
}

function preGroupsData($groups) {
    foreach ($groups as &$group) {
        $group = json_decode($group);
    }
    foreach ($groups as $key1 => $value1) {
        foreach ($groups as $key2 => $value2) {
            if ($key1 != $key2 && ((($value1->number == $value2->number) && ($value1->number != 0) ) || ($value1->price == $value2->price && $value1->price != 0))) {
                error("Duplicate values in price groups");
                die;
            }
        }
    }
    return $groups;
}

function getPriceGroupByVariantId($db, $variantId) {
    $sql = "select * from price_groups where variant_id = '$variantId'";
    $query = $db->query($sql);
    $groups = array();
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $groups[] = $row;
        }
    }
    return $groups;
}

function error($msg) {
    $response = array(
        "success" => 0,
        "msg" => $msg
    );
    echo json_encode($response);
}

function deleteGroup($db, $groupId, $variantId, $productId, $shopify) {
    $sql = "select * from price_groups where id = $groupId";
    $query = $db->query($sql);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $customVariantId = $row["custom_variant"];
        }
    }
    $sql = "delete from price_groups where id = $groupId";
    $query = $db->query($sql);
    $sql = "select * from custom_variants where variant_id = '$variantId'";
    $query = $db->query($sql);
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $groupIds = $row["group_id"];
        }
    }
    $groupIds = explode(",", $groupIds);
    foreach ($groupIds as $key => $value) {
        if ($value == $groupId) {
            unset($groupIds[$key]);
        }
    }
    $groupIds = implode(",", $groupIds);
    $sql = "update custom_variants set group_id = '$groupIds' where variant_id = '$variantId'";
    $query = $db->query($sql);
    deleteVariantById($shopify, $productId, $customVariantId);
}

function deleteVariantById($shopify, $productId, $customVariantId) {
    $deleteVariant = $shopify("DELETE", "/admin/products/$productId/variants/$customVariantId.json");
}

function getShopSettings($db, $shop) {
    $sql = "select * from custom_order_settings where shop = '$shop'";
    $query = $db->query($sql);
    $settings = array();
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $settings = $row;
        }
    }
    return $settings;
}
