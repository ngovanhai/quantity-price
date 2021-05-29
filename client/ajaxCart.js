// v3
var totalPriceInAjaxCart;
var productsInCart;
var discountPrice;
var elementFormAjaxCart ;
var otElementButton = 'form[action^="/checkout"] :submit[name^="checkout"],.cart__checkout,form[action^="/checkout"] .checkout-button,#checkout,.checkout_btn,form[action^="/checkout"]:last .action_button,.OTCheckout,[name^="checkout"],form[action^="/cart"] :submit[name^="checkout"],form[action^="/cart"] .checkout-button';
var elemenAjaxCartTotal = $(".cart-subtotal--price small,.cart-item-total-price,.ot_ajax_cart__subtotal_new,.cart__subtotal,.cart_subtotal");
var otelementTotalPricePerProductInAjaxCart = $(".ajaxcart__row");
var otDiscountCodeOfCustomer = [];
var elemntAppendDiscountbox = ".OTElemntAppendDiscountbox";

// ---- ajax cart ---- 
let issetLimit = false ;
elementFormAjaxCart = $(".ajaxcart,form.Cart.Drawer__Content,.ajaxifyCart--content,.cart-drawer-right,.mini-cart-info,form#cart,.cart-drawer,.mini-cart__inner,#CartContainer,.cart,.cart_content.animated.fadeIn:last"); 

setInterval(function () { 
    if(elementFormAjaxCart.length > 0 && window.OTSettings.useAjaxCart == 1){
        otQuantityAjaxCartInit(); 
    } else{
        if(Shopify.shop != "thirstyrun-llc.myshopify.com" && Shopify.shop != "foliebutikken.myshopify.com" && Shopify.shop != "phenyx-pro.myshopify.com"){
            startLimitPurchase();
        } 
    }
}, 1000)
 

// discount code box  
if(window.OTSettings.showDiscountCode == 1){ 
    appendHtmlDiscountCodeBox();
    listenEventDiscountCodeInCart();
} 
// listen event checkout redirect to draftoder
if(Shopify.shop != "plastic-solutions-ireland.myshopify.com"){
    listEventClickCheckout();
}




// FUNCTION 

function otQuantityAjaxCartInit() {
    $.ajax({
        type    : 'GET',
        url     : '/cart.json',
        dataType: 'json'
    }).done(cart => { 
        if(typeof window.allItemInCart == 'undefined') window.allItemInCart = {}; 
        if(JSON.stringify(window.allItemInCart) !== JSON.stringify(cart) || (JSON.stringify(window.allItemInCart) == JSON.stringify(cart) && $(".checkExistAppend").length == 0)){
            //if ajax cart change 
            var itemCart = JSON.parse(JSON.stringify(cart.items));
            if(itemCart.length > 0){
                for(let i =0; i < itemCart.length ; i++){
                    itemCart[i]['product_description'] = '';
                }
                otQuantityUpdatePriceAjaxCart(itemCart); 
                totalPriceInAjaxCart = cart.total_price   
            }  
            window.allItemInCart = cart; 
            window.note = cart.note; 
            // limit puurchase 
            startLimitPurchase();
            window.totalPriceInCart =  cart.total_price;
        }
        
    })
}
function startLimitPurchase(){
    if ($('form[action^="/cart"],form[action^="/checkout"]').length >= 1 && $(".ot-error").length == 0) {
        issetLimit = true; 
    }
    if(issetLimit == true){
        limitInit();
        issetLimit = false;
    }
}
function otQuantityUpdatePriceAjaxCart(productListInCart) {
    if(typeof customerId == "undefined"){
        var customerId;
        if (typeof meta.page.customerId === "undefined" || !meta.page.customerId) {
            customerId = '';
        } else {
            customerId = meta.page.customerId;
        }
    }
    $.ajax({
        url     : `${rootlinkQuantity}/quantity_break_v2.php`,
        type    : 'POST',
        dataType: 'json',
        data: { action: 'updatePriceCart', shop: shop, productListInCart: productListInCart , customerId:customerId}
    }).done((response) => {
        if (typeof response == "string") {
            response = JSON.parse(response);
        }
        var result = $.map(response, function (value, index) {
            return [value];
        });
        if (JSON.parse(JSON.stringify(result)).length == 0) {
            productsInCart = productListInCart
        } else {
            productsInCart = JSON.parse(JSON.stringify(result))
        }
        appendPriceNewInAjaxCart(result);
    }).fail((error) => {
        console.log(error)
    });
}
  
function appendPriceNewInAjaxCart(listProductUpdatedPrice) {
    var total_new = 0; // new subtotal 
    if ($(".ot-ajax-cart").length > 0) { elementFormAjaxCart = $(".ot-ajax-cart:last"); } 
    for (let item of listProductUpdatedPrice) {
        if (item.price_new) {
            total_new += Number(item.price_new);
        }else{
            total_new += Number(item.line_price);
        }
        if (parseInt(item.isDiscount) == 1) { 
            item.price = Shopify.formatMoney(item.price,Shopify.money_format);
            item.line_price = Shopify.formatMoney(item.line_price,Shopify.money_format);
            item.priceNewPerProduct = Shopify.formatMoney(item.price_new / item.quantity,Shopify.money_format);
            item.price_new = Shopify.formatMoney(item.price_new,Shopify.money_format);
            item.total_price = Shopify.formatMoney(item.total_price,Shopify.money_format); 

            // FIND ELEMENT
            let elementTotalPricePerProductInAjaxCart = $(`.ot-ajax-quantity-cart-total[data-id='${item.variant_id}'],.variant-id[data-variant-id='${item.variant_id}'],.ot-ajax-quantity-cart-total[data-id='${item.key}']`); // add code
            let elementPricePerProductInAjaxCart = $(`.ot_ajax_quantity_line[data-id='${item.variant_id}']`); // add code
            let elementHandleProductInAjaxCart = elementFormAjaxCart.find(`[href^='/products/${item.handle}?variant=${item.variant_id}']:last`).parent();
            if(elementTotalPricePerProductInAjaxCart.length == 0){
                // auto find element
                parentElement = elementHandleProductInAjaxCart;
                let countParent = 7;
                for (let i = 0; i < countParent; i++) { 
                    if ($(parentElement).is(`:contains(${item.price})`) || $(parentElement).is(`:contains(${item.line_price})`)) {
                        break;
                    } else {
                        parentElement = $(parentElement).parent(); 
                    } 
                }
                elementTotalPricePerProductInAjaxCart =  $(parentElement).find(`:contains(${item.line_price})`).last();
                if(elementPricePerProductInAjaxCart.length == 0){
                    elementPricePerProductInAjaxCart = $(parentElement).find(`span:contains(${item.price})`).last();
                }
                 
            }

            // Append price
            removeElementWhenDoubleAppend(elementTotalPricePerProductInAjaxCart,".ot_ajax_price_per_product_new");
            removeElementWhenDoubleAppend(elementTotalPricePerProductInAjaxCart,".ot_ajax_price_new");
            removeElementWhenDoubleAppend(elementHandleProductInAjaxCart,".ot_ajax_notification_product"); 
            elementTotalPricePerProductInAjaxCart.children().css("text-decoration", "line-through");
            elementTotalPricePerProductInAjaxCart.children().after(`<span class="ot_ajax_price_new money" style="display: block;color: red;">${item.price_new}</span>`);  
            elementPricePerProductInAjaxCart.children().after(`<span class="ot_ajax_price_per_product_new money" style="display: block;color: red;">${item.priceNewPerProduct}</span>`);
            if(Shopify.shop != "84degreeseast.myshopify.com"){
                elementHandleProductInAjaxCart.append(`<span class="ot_ajax_notification_product" style="display: block;color: red;">${item.notification}</span>`);  
            }
            
          }
      }
      if(Shopify.shop != "84degreeseast.myshopify.com"){
      formatMoneyByClass("ot_formatPriceInNoticeCart");
      }
      // Append  total cart new 
      if (totalPriceInAjaxCart != total_new && total_new != 0) { 
        window.totalPriceInCart =  total_new; 
        let subTotalPriceOldInAjaxCart = Shopify.formatMoney(totalPriceInAjaxCart,Shopify.money_format);
        let elementCartTotalCurrency = elementFormAjaxCart.find(`:contains(${subTotalPriceOldInAjaxCart})`).last();  
        totalPriceInCartCurrent = Shopify.formatMoney(total_new, Shopify.money_with_currency_format); 
        if(elemenAjaxCartTotal.length == 0){ // auto find by class
            if(elementCartTotalCurrency == 0){ // find element total price cart by | $29
                elemenAjaxCartTotal = elementFormAjaxCart.find(`:contains(${totalPriceInCartCurrent})`).last();
            }else{ // find element total price cart by | $29 USD
                elemenAjaxCartTotal = elementCartTotalCurrency;
            }
        }  
        // debugger;
        
        elemenAjaxCartTotal.css("text-decoration", "line-through");  
        if(elementFormAjaxCart.find(".checkExistAppend").length > 0){
            elementFormAjaxCart.find(".checkExistAppend").remove();
        } 
        window.leltimberShop = `<span class="ot_ajax_cart__subtotal_new checkExistAppend money"  style="color:red;display:block;">${Shopify.formatMoney(total_new)}</span>`;
        if(Shopify.shop == "el-michelero-dips.myshopify.com"){
            $(".ajaxcart__footer.ajaxcart__footer--fixed .ot_ajax_cart__subtotal_new").css("text-decoration", "line-through"); 
            $(".ajaxcart__footer.ajaxcart__footer--fixed .ot_ajax_cart__subtotal_new").after(`<span class="ot_ajax_cart__subtotal_new checkExistAppend money"  style="color:red;display:block;">${Shopify.formatMoney(total_new)}</span>`); 
        }else if(Shopify.shop == "gebnalak.myshopify.com"){
            $(".ot_ajax_cart__subtotal_new").css("text-decoration", "line-through"); 
            $(".ot_ajax_cart__subtotal_new").after(`<span class="ot_ajax_cart__subtotal_new checkExistAppend money"  style="color:red;display:block;">${Shopify.formatMoney(total_new)}</span>`); 
        }else{
            elementFormAjaxCart.first().find(elemenAjaxCartTotal).last().after(`<span class="ot_ajax_cart__subtotal_new checkExistAppend money"  style="color:red;display:block;">${Shopify.formatMoney(total_new)}</span>`); 
        } 
        if(Shopify.shop == "plastic-solutions-ireland.myshopify.com"){
            listEventClickCheckout();
        }
      }   
  } 
function listEventClickCheckout(){
    $.ajax({
        url: `${rootlinkQuantity}/quantity_break_v2.php`,
        type: 'GET',
        dataType: 'json',
        data: { action: 'checkExisRule', shop: shop }
    }).done((response) => {
        if (response > 0) { 
            //   ------CREATE DRAFORDER -------  
            $('body').on('click', otElementButton, function (e) {   
                e.preventDefault(); 
                if(Shopify.shop == "myrecess.myshopify.com"){
                    debugger;
                }
                let data = {
                    action: 'createDraftOrder',
                    shop: Shopify.shop,
                    products: productsInCart,
                    titleDiscountCodeOfCustomer: otDiscountCodeOfCustomer.titleDiscount,
                    valueDiscountCodeOfCustomer :otDiscountCodeOfCustomer.value,
                    note:window.note
                } 
                $.ajax({
                    url: `${rootlinkQuantity}/quantity_break_v2.php`,
                    type: 'POST',
                    data: data,
                    dataType: 'json'
                }).done(draftOrderUrl => {  
                    if(window.usePriceRule == "1"){ 
                        if(draftOrderUrl != false){ 
                            window.location.href = document.location.origin + "/checkout?discount=" + draftOrderUrl;
                        }else{
                            window.location.href = document.location.origin  + "/checkout";
                        } 
                    }else{ 
                        window.location.href = draftOrderUrl
                    }   
                }).fail(error => {
                    console.log("error",error);
                    window.location.href = document.location.origin  + "/checkout";
               });  
                return false;
            }) 
        }
    }).fail((error) => {
        console.log(error)
    }); 
}
function listenEventDiscountCodeInCart(){
    if(window.OTSettings.version == 1 && window.OTSettings.usePriceRule == 0){ 
        $('body').on('click', '.btnDiscountCode', function (e) {
            e.preventDefault();
            $(".btnDiscountCode").attr("disable",true);
            $(".btnDiscountCode").html("Checking..."); 
            let valueDiscountCode = $(".otInputDiscountCode").val(); 
            $(".wpResultDiscount").remove();
            $(".noticeDiscountCode").hide();
            if(valueDiscountCode != ""){
                $(".validDiscountCode").hide();
                $.ajax({
                    url: `${rootlinkQuantity}/quantity_break_v2.php`,
                    type: 'GET',
                    data: {action:'checkDiscountCode',shop:shop,valueDiscountCode:valueDiscountCode},
                    dataType: 'json'
                }).done(result => { 
                    if(typeof result.value != "undefined"){  
                        if(cart > -1)  { window.totalPriceInCart = totalPriceInCart; console.log("aaa",totalPriceInCart); }
                        let newTotalPriceIncart;
                        let noticeDiscount;
                        let valuePriceDsicountOrder; 
                        if(result.value_type == "percentage"){ 
                            newTotalPriceIncart = window.totalPriceInCart + window.totalPriceInCart * parseFloat( result.value / 100);  
                            noticeDiscount = result.value+"%";
                            valuePriceDsicountOrder = window.totalPriceInCart * parseFloat( result.value / 100)*(-1); // shopify respone value discount < 0
                        }else{ 
                            newTotalPriceIncart = window.totalPriceInCart + parseFloat(result.value*100); 
                            noticeDiscount = Shopify.formatMoney(result.value*100,Shopify.money_format);
                            valuePriceDsicountOrder = parseFloat(result.value*100,Shopify.money_format); // shopify respone value discount < 0 
                        }    
                        if(cart > -1)  { 
                            appendNewTotalAfterApplyDiscountCodeInCartPage(newTotalPriceIncart,valueDiscountCode,noticeDiscount);
                        }else{ 
                            appendNewTotalAfterApplyDiscountCodeInAjaxCartPage(newTotalPriceIncart,valueDiscountCode,noticeDiscount);
                        }

                        otDiscountCodeOfCustomer['titleDiscount'] = valueDiscountCode;
                        otDiscountCodeOfCustomer['value'] = valuePriceDsicountOrder; 
                    } 
                    $(".btnDiscountCode").attr("disable",false);
                    $(".btnDiscountCode").html("Apply discount");
                }).fail(error => { 
                    $(".validDiscountCode").hide();
                    $(".btnDiscountCode").attr("disable",false);
                    $(".btnDiscountCode").html("Apply discount");
                })
                
            }else{
                $(".validDiscountCode").show();
                $(".btnDiscountCode").attr("disable",false);
                $(".btnDiscountCode").html("Apply discount");
            }
        })
    }
}
function appendNewTotalAfterApplyDiscountCodeInAjaxCartPage(newTotalPriceIncart,valueDiscountCode,noticeDiscount){
    if(elemenAjaxCartTotal.length > 0 && newTotalPriceIncart > 0){  
        let elemenTotalCartDiscountCode ;
        if($(".ot_ajax_cart__subtotal_new").length > 0){
            elemenTotalCartDiscountCode = ".ot_ajax_cart__subtotal_new";
        }else{
            elemenTotalCartDiscountCode = elemenAjaxCartTotal;
        }
        $(elemenTotalCartDiscountCode).css("text-decoration","line-through");
        $(elemenTotalCartDiscountCode).after(`
           <div class="wpResultDiscount">
                <div> 
                    <span class="money">${Shopify.formatMoney(newTotalPriceIncart,Shopify.money_format)}</span> 
                </div>
                <div>
                    <span class="otDiscountTag">
                        <img src="https://windy.omegatheme.com/group-price-attribute/images/125-1257131_price-tag-icon-tag-icon-png-transparent-clipart.png" width="10px"/> 
                        ${valueDiscountCode}
                    </span> 
                    (${noticeDiscount})
                </div>
           </div>
        `);
    }else{
        $(".noticeDiscountCode").show();
    }
}
function appendNewTotalAfterApplyDiscountCodeInCartPage(newTotalPriceIncart,valueDiscountCode,noticeDiscount){
    if(elementCartTotal.length > 0 && newTotalPriceIncart > 0){  
        let elemenTotalCartDiscountCode ;
        if($(".ot_cart__subtotal_new").length > 0){
            elemenTotalCartDiscountCode = ".ot_cart__subtotal_new";
        }else{
            elemenTotalCartDiscountCode = elementCartTotal;
        }
        $(elemenTotalCartDiscountCode).css("text-decoration","line-through");
        $(elemenTotalCartDiscountCode).after(`
           <div class="wpResultDiscount">
                <div> 
                    <span class="money">${Shopify.formatMoney(newTotalPriceIncart,Shopify.money_format)}</span> 
                </div>
                <div>
                    <span class="otDiscountTag">
                        <img src="https://www.pinclipart.com/picdir/middle/125-1257131_price-tag-icon-tag-icon-png-transparent-clipart.png" width="10px"/> 
                        ${valueDiscountCode}
                    </span> 
                    (${noticeDiscount})
                </div>
           </div>
        `);
    }else{
        $(".noticeDiscountCode").show();
    }
}
function appendHtmlDiscountCodeBox(){
    let html = `
        <div class="ot-wrapper-discount-code">
            <input type="text" name="otDiscountCode"  class="otInputDiscountCode" placeholder="Enter discount code" value=""/>
            <div class="validDiscountCode">Please enter the correct discount code</div> 
            <div class="noticeDiscountCode">Cannot be used with this discount code</div> 
            <a class="btnDiscountCode btn">Apply discount</a> 
        </div>
        <style> .validDiscountCode{ display:none; color:red; } .noticeDiscountCode{ display:none; color:red; } </style>
    `; 
    if($(elemntAppendDiscountbox).length  != 0) {
        $(elemntAppendDiscountbox).last().after(html);
    }else{
        $(elementFormAjaxCart).last().after(html);
    }
}
function removeElementWhenDoubleAppend(parentElement,ItemRemove){
    if(parentElement.find(`${ItemRemove}`).length > 0){
        parentElement.find(`${ItemRemove}`).remove();
    }
}
