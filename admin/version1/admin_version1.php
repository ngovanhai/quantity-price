 <?php
    header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');
    ?>
 <!DOCTYPE html>

 <head>
     <title>QUANTITY PRICE BREAKS & LIMIT PURCHASE | APP INSTALLATION</title>
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
     <link rel="stylesheet" type="text/css" href="admin/version1/styles/styles.css?v=<?php echo time(); ?>">
     <link rel="stylesheet" href="admin/version1/lib/vue-multiselect.min.css">
     <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
     <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.css" />
     <link rel="stylesheet" type="text/css" href="admin/version1/lib/daterangepicker.css" />
     <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
     <script src="http://code.jquery.com/jquery.min.js"></script>
     <script id="helpdesk" src="https://apps.omegatheme.com/helpdesk/plugin.js?appId=34&v=<?php echo time(); ?>"></script>

     <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
     <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
     <script type="text/javascript">
         ShopifyApp.init({
             apiKey: '<?php echo $apiKey; ?>',
             shopOrigin: 'https://<?php echo $shop; ?>',
         });
     </script>
     <script type="text/javascript">
         shopifyBarButton();

         function shopifyBarButton() {
             ShopifyApp.ready(function() {
                 ShopifyApp.Bar.initialize({
                     buttons: {
                         secondary: [{
                             label: "Instructions",
                             href: "https://quantity-break-demo.myshopify.com/pages/document",
                             target: "_blank"
                         }],
                     },
                 });

             });
         }
     </script>
 </head>


 <body>

     <span style="display: none" class="shopName"><?php echo $shop; ?></span>
     <?php require 'review/star.php'; ?>
     <div id="quantity_price">
         <!-- <import-rule ref="importExcel"></import-rule> -->

         <b-tabs v-model="tab">
             <b-tab title="">
                 <template slot="title" @click="clickTab()"> <i class="fa fa-tachometer" aria-hidden="true"></i> Dashboard </template>
                 <statistic-rule></statistic-rule>
             </b-tab>
             <b-tab title="">
                 <template slot="title" @click="clickTab()"> <i class="fa fa-tags" aria-hidden="true"></i> Product </template>
                 <product-rule ref="productRule" @change-tab="changeTab" @get-products="getProducts" :fillter-collection="fillterCollection" :fillter-product="fillterProduct"></product-rule>
             </b-tab>
             <b-tab title="" @click="clickTab()">
                 <template slot="title"> <i class="fa fa-tasks" aria-hidden="true"></i> Collection </template>
                 <collection-rule ref="collectionRule"></collection-rule>
             </b-tab>
             <!-- <b-tab title=""  @click="clickTab()"> 
                <template slot="title">
                <i class="fa fa-list" aria-hidden="true"></i>
                    Manage rule
                </template>
                <list-rule ref="listRule"></list-rule>
            </b-tab> -->
             <b-tab title="Limit purchase">
                 <template slot="title"> <i class="fa fa-cart-arrow-down" aria-hidden="true"></i>Limit purchase </template>
                 <limit :all-products="allProducts" :variants="variants"></limit>
             </b-tab>
             <b-tab title="Settings">
                 <template slot="title"> <i class="fa fa-cog" aria-hidden="true"></i>Settings </template>
                 <settings></settings>
             </b-tab>

         </b-tabs>
     </div>
      <!-- Global site tag (gtag.js) - Google Analytics -->
     <script async src="https://www.googletagmanager.com/gtag/js?id=UA-126587266-1"></script>
     <script>
         window.dataLayer = window.dataLayer || [];

         function gtag() {
             dataLayer.push(arguments);
         }
         gtag('js', new Date());

         gtag('config', 'UA-126587266-1');
     </script>
     <?php include 'google_remarketing_tag.txt'; ?>
     <?php
        if (!isset($version)) $version  = time();
        ?>
     <script>
         window.shop = "<?php echo $shop; ?>";
         window.rootlink = "<?php echo $rootLink; ?>";
         window.money_format = "<?php echo $shopInfo['currency'] ?>";
         window.process = {
             env: {
                 NODE_ENV: 'production'
             }
         };
         window.version = "<?php echo time(); ?>";
     </script>
     <div class="app-footer">
         <div class="footer-header">Some other sweet <strong>Omega</strong> apps you might like! <a target="_blank" href="https://apps.shopify.com/partners/developer-30c7ea676d888492">(View all app)</a></div>
         <div class="omg-more-app">
             <div>
                 <p><a href="https://apps.shopify.com/order-tagger-by-omega?utm_source=quantity_break_admin" target="_blank"><img alt="Order Tagger by Omega" src="https://s3.amazonaws.com/shopify-app-store/shopify_applications/small_banners/17108/splash.png?1510565540"></a></p>

             </div>
             <div>
                 <p><a href="https://apps.shopify.com/facebook-reviews-1?utm_source=quantity_break_admin" target="_blank"><img alt="Facebook Reviews by Omega" src="https://s3.amazonaws.com/shopify-app-store/shopify_applications/small_banners/13331/splash.png?1499916138"></a></p>
             </div>
             <div>
                 <p><a href="https://apps.shopify.com/delivery-date-omega?utm_source=quantity_break_admin" target="_blank"><img alt="Delivery date" src="https://s3.amazonaws.com/shopify-app-store/shopify_applications/small_banners/20857/splash.png?1523954773"></a></p>
             </div>
         </div>
         <div class="footer-copyright">©2018 <a href="https://www.omegatheme.com/" target="_blank">Omegatheme</a> All Rights Reserved.</div>
     </div>
     <script src="admin/version1/lib/vue.js"></script>
     <script type="text/javascript" src="admin/version1/lib/httpVueLoader.js"></script>
     <script type="text/javascript" src="admin/version1/lib/vue-multiselect.min.js"></script>
     <script src="https://unpkg.com/babel-polyfill@latest/dist/polyfill.min.js"></script>
     <script src="admin/version1/lib/bootstrap-vue.js"></script>
     <script src="admin/version1/lib/vue-resource.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
     <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
     <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
     <script type="text/javascript" src="admin/version1/lib/daterangepicker.min.js"></script>

     <script src="https://cdn.jsdelivr.net/npm/vue-apexcharts"></script>
     <script src="admin/version1/scripts/main.js?v=<?php echo time(); ?>"></script>
     <?php
        $customer = $shopify('GET', '/admin/shop.json');
        $email = $customer['email'];

        ?>
     <div style="display:none;"><?php echo $email;  ?></div>
  
 </body>

 <!-- <script>
     module.exports = {
         data() {
             return {
                 centerDialogVisible: false,
             }
         },
         methods: {
             onClose() {
                 this.centerDialogVisible = false;
             },
             showModal(e) {
                 this.themeDelete = e;
                 this.centerDialogVisible = true
             },
         },
     };
 </script> -->