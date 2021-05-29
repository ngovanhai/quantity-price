<?php
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
require 'vendor/autoload.php';

use sandeepshetty\shopify_api;

require 'conn-shopify.php';
require 'help.php';
session_start();
if (isset($_GET['shop'])) {
    $shop = $_GET['shop'];
} else {
    ?>
    <head>
        <script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    <div class="container">
        <h1 class="page-heading">Input your shop name to continue: </h1>
        <form class="form-inline">
            <input type="text" style="width: 500px" class="inputShop form-control">
            <button class="btn btn-primary submitShop" type="submit">Continue</button>
        </form>
    </div>
    <script>
        $('.submitShop').click(function (e) {
            e.preventDefault();
            window.location = 'https://' + $('.inputShop').val() + '/admin/api/auth?api_key=<?php echo $apiKey; ?>';
        });
    </script>
    <?php
    die();
}
$shop_data = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
$shop_data = $shop_data->fetch_object();
$installedDate = $shop_data->installed_date;
$confirmation_url = $shop_data->confirmation_url;
$clientStatus = $shop_data->status;

if ($clientStatus != 'active') {
    header('Location: ' . $rootLink . '/chargeRequire.php?shop=' . $shop);
} else {
    $select_settings = $db->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
    $app_settings = $select_settings->fetch_object();
    $shop_data = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
    $shop_data = $shop_data->fetch_object();
    $shopify = shopify_api\client(
            $shop, $shop_data->access_token, $app_settings->api_key, $app_settings->shared_secret
    );
    $shopInfo = $shopify("GET", "/admin/shop.json");
    ?> 
       
    <?php
        $custom_order_settings = db_fetch_row("select * from custom_order_settings where shop = '" . $shop . "'");
        if($custom_order_settings['version'] == 1){
            require "admin/version" . $custom_order_settings['version'] . "/admin_version" . $custom_order_settings['version'] . ".php";
        }else{
    ?>
    <head>
    <title>Quantity Price Breaks & Limit Purchase admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="admin/version0/styles/toaster.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://material.angularjs.org/1.1.6/angular-material.css">
    <link rel="stylesheet" type="text/css" href="admin/version0/styles/styles.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>  
    <script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>	
	
</head>

<body ng-app="customOrderApp">      
    <?php
    if ($clientStatus != 'active') {
        $dayLeft = $trialTime - $diff;
        echo '<a href="' . $confirmation_url . '" style="position: absolute; right: 0;border: 1px solid red; padding: 5px;color: #ddd" target="_blank" class="refreshCharge">' . $dayLeft . ' days left<div>Buy now</div></a>';
    }
    ?> 	
    <span style="display: none" class="shopName"><?php echo $shop; ?></span>
    <span class="hide currency"><?php echo $shopInfo["currency"]; ?></span>
    <h1 class="page-heading">Quantity Price Breaks & Limit Purchase <a href='./guide.html' style="text-decoration: none;color: #48cfae" target="_blank">| App Installation</a></h1>
    <div layout-padding style="width: 100%">
        <md-tabs md-center-tabs md-dynamic-height md-border-bottom>
            <md-tab label="whole price">
                <div ng-include="src = 'admin/version0/templates/wholeProduct.html'" ng-controller="wholeProductController as whole"></div>
            </md-tab>
            <md-tab label="QUANTITY PRICE BREAKS">
                <md-content class="md-padding">
                    <div ng-include="src = 'admin/version0/templates/quantitiesBreak.html'" ng-controller="quantitiesBreakController as main"></div>
                </md-content>
            </md-tab>
            <md-tab label="Limit Product Order">
                <md-content class="md-padding">
                    <div ng-include="src = 'admin/version0/templates/limitOrder.html'" ng-controller="limitOrderController as limit"></div>
                </md-content>
            </md-tab>
            <md-tab label="Style options">
                <md-content class="md-padding">
                    <div ng-include="src = 'admin/version0/templates/styleOptions.html'" ng-controller="styleOptionController as st"></div>
                </md-content>
            </md-tab>
        </md-tabs>
        <hr>
        <div class="updateVersion">
        
            <div class="content">
                We have updated the new version. Please update
                <a target="_blank" href="https://dev.omegatheme.com/group-price-attribute/cron/update_version.php?shop=<?php echo $shop; ?>">here</a>
            </div>
            <a class="closeUpdateVersion">x</a>
        </div>
        <div class="app-footer">
            <div class="footer-header">Some other sweet <strong>Omega</strong> apps you might like! <a target="_blank" href="https://apps.shopify.com/partners/developer-30c7ea676d888492">(View all app)</a></div>
            <div class="omg-more-app">
                <div>
                    <p><a href="https://apps.shopify.com/facebook-reviews-1" target="_blank"><img alt="Facebook Reviews by Omega" src="https://s3.amazonaws.com/shopify-app-store/shopify_applications/small_banners/13331/splash.png?1499916138"></a></p>
                </div>
                <div>
                    <p><a href="https://apps.shopify.com/google-reviews-by-omega" target="_blank"><img alt="Google Reviews by Omega" src="https://s3.amazonaws.com/shopify-app-store/shopify_applications/small_banners/15234/splash.png?1506055600"></a></p>
                </div>
                <div>
                    <p><a href="https://apps.shopify.com/ot-accordion-slider" target="_blank"><img alt="OT Accordion Slider" src="https://s3.amazonaws.com/shopify-app-store/shopify_applications/small_banners/3529/splash.png?1435917995"></a></p>
                </div>
            </div>
            <div class="footer-copyright">©2018 <a href="https://www.omegatheme.com/" target="_blank">Omegatheme</a> All Rights Reserved.</div>
        </div>
    </div>
    <?php include 'facebook-chat.html'; ?>
    <script>
        $('.closeUpdateVersion').click(function(){
            $(".updateVersion").hide();
        })
        $(".closeNote").click(function(e){
        e.preventDefault();
        $(".noteWrap, .review-message").hide();
        });
        $(".refreshCharge").click(function(e){
        e.preventDefault();
        $.get("recharge.php?shop=<?php echo $shop; ?>", function(result){
        location.href = result;
        });
        });
        
    </script>
    <!-- Angular Material Dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.6/angular.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.6/angular-animate.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.6/angular-aria.min.js"></script>
    <script src="admin/version0/scripts/ng-table.min.js"></script>
    <script src="admin/version0/scripts/toaster.min.js" type="text/javascript"></script>
    <script src="admin/version0/scripts/angular-material.js"></script>
    <script type="text/javascript" src="admin/version0/scripts/angular-route.min.js"></script>
    <script type="text/javascript" src="admin/version0/scripts/app.js"></script>
    <script type="text/javascript" src="admin/version0/scripts/quantities-break.js"></script>
    <script type="text/javascript" src="admin/version0/scripts/limit-order.js"></script>
    <script type="text/javascript" src="admin/version0/scripts/style-coptions.js"></script> 
    <script type="text/javascript" src="admin/version0/scripts/wholeProduct.js"></script>
</body>

    <?php
        }
    ?>
        
 <?php } ?>