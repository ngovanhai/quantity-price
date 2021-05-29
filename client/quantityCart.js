// 16/4/2019
//debugger;
var totalPriceInCart;
var productsInCart;
var discountPrice;
var customerId;
var otElementFormCart;
var elementCartTotal = $(".ot-subtotal,.hulkapps-cart-original-total,.cart__subtotal,.cart-subtotal--price small,.wh-original-price,.cart-subtotal-value").last();

// -------  check customer login ----------
if (typeof meta.page.customerId === "undefined" || !meta.page.customerId) {
    customerId = '';
} else {
    customerId = meta.page.customerId;
}
window.customerId = customerId;
// -------  end check customer login ----------


// -------  Init quantity in cart  ----------
if (typeof window.otCheckExistFileQuantityCart === 'undefined') { 
    quantityCartInit(); 
    window.otCheckExistFileQuantityCart = false; // check exist file js in theme
}
 
 
function quantityCartInit() { 
    $.ajax({ 
        type: 'GET',
        url: '/cart.json',
        dataType: 'json'
    }).done(cart => {
        //debugger;
        let itemCart = cart.items;
        for(let i =0; i < itemCart.length ; i++){
            itemCart[i]['product_description'] = '';
        }
        checkValidCustomerTagInCart(window.customerId,window.OTSettings,cart);
        let styles = `<style>
                    ${window.OTSettings.customCss}
                </style>`;  
        $("body").append(styles); 
        totalPriceInCart = cart.total_price; 
    })
}


// -------  check customer accept in cart   ----------

function checkValidCustomerTagInCart(customerId,settings,cart){ 
    if(settings.use_tag == 1){ 
        $.ajax({ 
            type: 'GET',
            url: `${rootlinkQuantity}/quantity_break_v2.php`,
            dataType: 'json',
            data: {action:'checkValid',shop:shop,customerId:customerId}
        }).done(respone => {
            if(respone == 1){
                updatePriceCart(cart.items);
            }else{
                productsInCart = cart.items;
                $("[name=checkout]").attr("disabled", false); 
            }
        })
    }else{
        updatePriceCart(cart.items);
    }
     
}

// ---------- get new price of item in cart ----------

function updatePriceCart(productListInCart) {
    if(typeof customerId == "undefined"){
        var customerId;
        if (typeof meta.page.customerId === "undefined" || !meta.page.customerId) {
            customerId = '';
        } else {
            customerId = meta.page.customerId;
        }
    }
    $.ajax({
        url: `${rootlinkQuantity}/quantity_break_v2.php`,
        type: 'POST',
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
            productsInCart = productListInCart;
        } else {
            productsInCart = JSON.parse(JSON.stringify(result));
        }  
        addAppendPriceInCart(result); 
    }).fail((error) => {
        console.log(error);
    });
}

// ---------- append new price of item in cart ----------

function addAppendPriceInCart(listProductUpdatedPrice) { 
    otElementFormCart = $(window.OTSettings.elementFormCart);
    var total_new = 0;
    let i = 0;
    for (let item of listProductUpdatedPrice) { 
        if(typeof item.price_new2 != "undefined"){
            item.price_new     = item.price_new2;
        } 
        if (item.price_new) {
            total_new += Number(item.price_new);
        } else {
            total_new += Number(item.line_price);
        } 
        if (item.isDiscount == 1) {
            
            item.pricePerProduct = Shopify.formatMoney(item.price_new / item.quantity,Shopify.money_format);
            item.price           = Shopify.formatMoney(item.price,Shopify.money_format);
            item.priceCurrency   = Shopify.formatMoney(item.line_price, Shopify.money_with_currency_format);
            item.priceCurrencyReplace = item.priceCurrency.toString().replace(".", ",");
            item.line_price      = Shopify.formatMoney(item.line_price,Shopify.money_format);
            item.price_new       = Shopify.formatMoney(item.price_new,Shopify.money_format);
            item.total_price     = Shopify.formatMoney(item.total_price,Shopify.money_format); 
            //  APPEND PRICE IN CART 
  
            // FIND ELEMENT

            /// add code
            // debugger;
            
            var elementTotalPricePerProductInCart = $(`.ot-quantity-cart-total[data-id='${item.variant_id}'],.ot-quantity-cart-total[data-key='${item.key}'],.saso-cart-item-line-price[data-key='${item.key}']`);
            var elementPricePerProductInCart = $(`.ot_quantity_line[data-id='${item.variant_id}'],.ot_quantity_line[data-key='${item.key}']`);
            var elementHanleProductInCart;
            if($(`.ot_quantity_handle[data-id='${item.key}']`).length > 0){
                elementHanleProductInCart = $(`.ot_quantity_handle[data-id='${item.key}']`);
            }else{
                elementHanleProductInCart = otElementFormCart.find(`[href^='/products/${item.handle}?variant=${item.variant_id}']`).last();
            }  
            i = i+1;
            if(elementTotalPricePerProductInCart.length == 0){
                // auto append
                elementTotalPricePerProductInCart = getElementTotalPricePerProductInCart(elementHanleProductInCart.parent(),item)
            }  
            // Append price 
            
            if(item.line_price != item.price_new){
                elementTotalPricePerProductInCart.find(`.money:first`).css('text-decoration', 'line-through');
                elementTotalPricePerProductInCart.find(`:contains(${item.line_price})`).css('text-decoration', 'line-through');
                elementTotalPricePerProductInCart.children().last().after(`<span class="ot_price_new money" style="display: block;color: red;">${item.price_new}</span>`);
                elementPricePerProductInCart.css('text-decoration', 'line-through');
            } 
            
            elementPricePerProductInCart.after(`<span class="ot_price_per_product_new  money" style="display: block;color: red;">${item.pricePerProduct}</span>`);
            elementHanleProductInCart.after(` <span class="ot_notification_product" style="display: block;color: red;">  ${item.notification} </span>  `);  
        } 
    }
	
    //------FORMAT PRICE CHO TUNG PRODUCT------- 

    formatMoneyByClass("ot_formatPriceInNoticeCart");
   
    //----- APPEND TOTAL PRICE IN CART-------
    
    if (totalPriceInCart != total_new && total_new != 0) {   
        totalPriceInCartCurrent = Shopify.formatMoney(totalPriceInCart, Shopify.money_with_currency_format);
        let totalPriceInCartReplace = totalPriceInCartCurrent.toString().replace(".", ",");
        let subTotalPriceOldInCart = Shopify.formatMoney(totalPriceInCart,Shopify.money_format);  
        if (elementCartTotal.length == 0) { 
            if($(`:contains(${subTotalPriceOldInCart})`).last().length > 0){ // find by price | $29
                elementCartTotal = $(`:contains(${subTotalPriceOldInCart})`).last();
            }else if($(`:contains(${totalPriceInCartCurrent})`).last().length > 0){ // find by price | $29 USD
                elementCartTotal = $(`:contains(${totalPriceInCartCurrent})`).last();
            }else{ // find by price | $29.00 USD
                elementCartTotal = $(`:contains(${totalPriceInCartReplace})`).last(); 
            } 
        }  
        // append subtotal 
        elementCartTotal.css("text-decoration", "line-through"); 
        if(Shopify.shop == "wunder-kissen-de.myshopify.com"){
            elementCartTotal.after(` <span class="ot_cart__subtotal_new money"  style="color:red;display:block;">Gesamtpreis ${Shopify.formatMoney(total_new,Shopify.money_format)}</span>`);
        }else if(Shopify.shop == "cp-us.myshopify.com" || Shopify.shop == "cp-global.myshopify.com"){
            $(".cart-subtotal,.cart-title-total").find(".money").css("text-decoration", "line-through"); 
            $(".cart-subtotal,.cart-title-total").after(` <span class="ot_cart__subtotal_new money"  style="color:red;display:block;">  ${Shopify.formatMoney(total_new,Shopify.money_format)}</span>`);
        }else{
            elementCartTotal.after(` <span class="ot_cart__subtotal_new money"  style="color:red;display:block;"> ${Shopify.formatMoney(total_new,Shopify.money_format)}</span>`);    
        } 
        
        window.totalPriceInCart = total_new;
        if(Shopify.shop == "plastic-solutions-ireland.myshopify.com"){
            listEventClickCheckout();
        }
    } 
}

function getElementTotalPricePerProductInCart(elementHanleProductInCart,item){
    let parentElement = elementHanleProductInCart; 
    let countParent = 4;   
    for (let i = 0; i < countParent; i++) { 
        if ($(parentElement).is(`:contains(${item.line_price})`) || $(parentElement).is(`:contains(${item.priceCurrency})`) || $(parentElement).is(`:contains(${item.priceCurrencyReplace})`)) {  
            break; 
        } else {  
            parentElement = $(parentElement).parent(); 
        }  
    }  
    if ($(parentElement).find(`:contains(${item.line_price})`).length > 0) { // find by price  | $29 6 

        return $(parentElement).find(`:contains(${item.line_price})`).last().parent();

    }else if($(parentElement).find(`:contains(${item.priceCurrency})`).length > 0){ // find by price  | $29 USD
        
        return $(parentElement).find(`:contains(${item.priceCurrency})`).last().parent();

    }else{ // find by price  | $29,0
        
        return $(parentElement).find(`:contains(${item.priceCurrencyReplace})`).last().parent(); 

    }  
    
}



