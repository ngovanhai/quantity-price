<?php 
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
require '../vendor/autoload.php'; 
use sandeepshetty\shopify_api; 
require '../conn-shopify.php';
$shop = $_GET['shop'];


$listPriceGroup = getPrice_groups($db, $shop); 
if (count($listPriceGroup) > 0) {
    $shopify = shopifyInit($db, $shop, $appId);
    $view = "<table>";
    $view .= "<thead>";
    $view .= "<td style='padding:15px;'>Vanriant ID";
    $view .= "</td>";
    $view .= "<td>Name Variant Product";
    $view .= "</td>";
    $view .= "<td>Product";
    $view .= "</td>";
    $view .= "<td>Price Updated";
    $view .= "</td>";
    $view .= "</thead>";
    foreach ($listPriceGroup as $v) {
        $view .= "<tr>";
        $id = $v['custom_variant'];
        $variant_id = $v['variant_id'];
        $listVariantProduct = $shopify("GET", "/admin/variants/{$variant_id}.json");
		if(is_array($listVariantProduct)){
			$price_new = round(($listVariantProduct['price'] - $listVariantProduct['price'] * $v['percent'] / 100), 2);
			$data = array(
				"variant" => array(
					"id" => $id,
					"price" => $price_new
				)
			);
			$db->query("update price_groups set price = $price_new where custom_variant = '$id'");
			$changeVariant = $shopify("PUT", "/admin/variants/$id.json", $data);
			$productChangePrice = getProductByID($changeVariant['product_id'], $shopify);
			if ($changeVariant) {
				$view .= "<td style='padding:10px;'>" . $changeVariant['id'] . "</td>" . "<td>" . $changeVariant['title'] . "</td>" . "<td>" . $productChangePrice['title'] . "</td>" . "<td>" . $changeVariant['price'] . "</td>";
			}
		}
		$view .= "</tr>";
    }
    $view .= "<table>";
    echo $view;
}else{
    echo "No one variant product need update";
}



function getPrice_groups($db, $shop) {
    $result = db_fetch_array("Select * from price_groups WHERE shop= '$shop' ");
     return $result;
}

function getProductByID($id, $shopify) {
    $result = $shopify("GET", "/admin/products/$id.json?fields=id,title");
    return $result;
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

function db_fetch_array($query_string) {
    global $db;
    $result = array();
    $mysqli_result = db_query($query_string);
    while ($row = mysqli_fetch_assoc($mysqli_result)) {
        $result[] = $row;
    }
    mysqli_free_result($mysqli_result);
    return $result;
}

function db_query($query_string) {
    global $db;
    $result = mysqli_query($db, $query_string);
    if (!$result) {
        // db_sql_error('Query Error', $query_string);
        echo ('Query Error' . $query_string);
    }
    return $result;
}

function show_array($data) {
    if (is_array($data)) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}

?>