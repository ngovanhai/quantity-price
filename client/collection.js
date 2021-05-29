 //   <div class="ot-infoproduct ot-product-{{product.id}}" data-id ="{{product.id}}">  </div> in collection-template
 //   <div class="ot-layout-collectionpage ot-layout-collectionpage-{{product.id}}" data-id ="{{product.id}}">  </div>  hien layout o bat cu cho nao
 let otQuantityListProductInCollection = $(".ot-infoproduct"); 

 //show new price in collection page
 
 if (otQuantityListProductInCollection.length > 0) { 
     $("head").append(`<style> 
         .ot-infoproduct{ 
             position: absolute;
             bottom: 35px;
             right: 0px; 
         } 
         .priceInCollection{
             color:#cb594c;
         }
     </style>`); 
     $(".ot-infoproduct").parent().css("position","relative")
     otCollectionInit(otQuantityListProductInCollection); 
 }
 
 //show layout in collection page
 let otQuantityLayoutInCollectionPage = $(".ot-layout-collectionpage");
 if(otQuantityLayoutInCollectionPage.length > 0){
     showLayoutInCollectionPage(otQuantityLayoutInCollectionPage);
 }
 
 async function otCollectionInit(otQuantityListProductInCollection){ 
     $.each(otQuantityListProductInCollection, function(index,element){
         let productID = $(element).attr("data-id") 
         $.ajax({
             type: 'GET', 
             data: {action: 'getNewPriceInCollection', shop: shop, IDProduct:productID},
             url: `${rootlinkQuantity}/quantity_break_v2.php`,
             dataType: 'json'
         }).done(result => {
             if (typeof result == "string") {
                 result = JSON.parse(result);
             } 
             if(result.isDiscount == true){
                 result.newPrice = Shopify.formatMoney(result.newPrice)
                 $(".ot-product-"+productID).append(`<div class="ot-priceIncollection"> ${settings.notificationInCollection} <span class="money_with_currency priceInCollection">${result.newPrice}</span></div>`)
             }   
         })
     });  
 }
 
 function showLayoutInCollectionPage(){
     var customerId;
     if (typeof meta.page.customerId === "undefined" || !meta.page.customerId) {
         customerId = ''; 
     } else {
         customerId = meta.page.customerId;
     }
     $.each(otQuantityLayoutInCollectionPage, function(index,element){
         let productID = $(element).attr("data-id");
         $.ajax({
             type: 'GET', 
             data: {action: 'showLayoutInCollectionPage', shop: shop, IDProduct:productID,customerId:customerId},
             url: `${rootlinkQuantity}/quantity_break_v2.php`,
             dataType: 'json'
         }).done(result => {
             if (typeof result == "string") {
                 result = JSON.parse(result);
             }  
             
             if( $(`.ot-layout-collectionpage-${productID} .priceGroupList`).length == 0 ){
                $(".ot-layout-collectionpage-"+productID).append(`${result.html}`)  
             }
           
         })
     });
 }
 