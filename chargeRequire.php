<?php
require 'conn-shopify.php';

require 'vendor/autoload.php';

use sandeepshetty\shopify_api;

if (isset($_GET["shop"])) {
    $shop = $_GET["shop"];
    $shopify = shopifyInit($db, $shop, $appId);

    //charge fee
    $charge = array(
        "recurring_application_charge" => array(
            "name" => $chargeTitle,
            "price" => $price,
            "return_url" => "$rootLink/charge.php?shop=$shop",
            "test" => $testMode,
            "trial_days" => $trialTime
        )
    );
    if ($chargeType == "one-time") {
        $recu = $shopify("POST", APIVERSION."application_charges.json", $charge);
        $confirmation_url = $recu["confirmation_url"];
    } else {
        $recu = $shopify("POST", APIVERSION."recurring_application_charges.json", $charge);
        $confirmation_url = $recu["confirmation_url"];
    }
    ?>

    <head>
        <script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    <div class="container" style="padding-bottom: 50px;">
        <div class="header">
            <h1 class="logoWrapper">
                <span class="shopNameRight">SHOP: <?php echo $shop; ?></span>
            </h1>
        </div>
        <h1 class="page-heading">To proceed with the installation, click below to activate the app and approve the charge.</h1>
        <a href="<?php echo $confirmation_url; ?>">Activate App</a>
    </div>
	<?php include 'facebook-chat.html'; ?>
    <?php
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
