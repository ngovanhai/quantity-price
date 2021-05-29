<?php

require 'conn-shopify.php';
session_start();

unset($_SESSION['shop']);

if (!function_exists('getallheaders')) {

    function getallheaders() {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

}

$headers = array();
$shop = '';
foreach (getallheaders() as $name => $value) {
    if ($name == "X-Shopify-Shop-Domain") {
        $shop = $value;
    }
}

$webhookContent = "";

$webhook = fopen('php://input', 'rb');
while (!feof($webhook)) {
    $webhookContent .= fread($webhook, 4096);
}

fclose($webhook);
$webhookContent = json_decode($webhookContent);
$totalSpent = $webhookContent->customer->total_spent;
$orders_count = $webhookContent->customer->orders_count;
$customerId = $webhookContent->customer->id;
$sql = "select * from custom_order_user_settings where shop = '$shop'";
$query = $db->query($sql);
if ($query) {
    while ($row = $query->fetch_assoc()) {
        $settings = $row;
    }
}
if ($settings["auto_type"] == 0) {
    $sql = "select * from levels where total_spent <= $totalSpent and shop = '$shop'";
} else {
    $sql = "select * from levels where orders_count <= $orders_count and shop = '$shop'";
}
$query = $db->query($sql);
if ($query) {
    if ($query->num_rows > 0) {
        $level = array();
        while ($row = $query->fetch_assoc()) {
            if ($level) {
                if ($settings["auto_type"] == 0) {
                    if ($row["total_spent"] > $level["total_spent"]) {
                        $level = $row;
                    }
                } else {
                    if ($row["orders_count"] > $level["orders_count"]) {
                        $level = $row;
                    }
                }
            } else {
                $level = $row;
            }
        }
    }
}
$levelId = $level["id"];
$sql = "select * from customer_level where customer_id = '$customerId' and shop = '$shop'";
$query = $db->query($sql);
if ($query) {
    if ($query->num_rows > 0) {
        $sql = "update customer_level set level_id = $levelId where customer_id = '$customerId'";
        $db->query($sql);
    } else {
        $sql = "insert into customer_level(level_id, customer_id, shop) values($levelId, '$customerId', '$shop')";
        $db->query($sql);
    }
}
$db->query($sql);
