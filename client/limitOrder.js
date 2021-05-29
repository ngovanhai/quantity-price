var cartItem; 
 

// ---- limit init check cart change ------
if(typeof customerId == "undefined"){
    var customerId;
    if (typeof meta.page.customerId === "undefined" || !meta.page.customerId) {
        customerId = '';
    } else {
        customerId = meta.page.customerId;
    }
}
 
function limitInit() { 
    $.ajax({
        type: 'GET',
        url: '/cart.js',
        dataType: 'json'
    }).done(cart => {
        if(typeof window.allItemInCart == 'undefined') window.allItemInCart = {}; 
        if(JSON.stringify(window.allItemInCart) !== JSON.stringify(cart)){
            //if ajax cart change 
            var itemCart = JSON.parse(JSON.stringify(cart.items));
            if(itemCart.length > 0){
                for(let i =0; i < itemCart.length ; i++){
                    itemCart[i]['product_description'] = '';
                }
                if (typeof cart == "string") cart = JSON.parse(cart); 
                checkLimitVariantInCart(cart.items);
                cartItem = cart.items;  
            }  
            window.allItemInCart = cart;
        } 
    })
}
// append style
var styles = `<style> .ot-error,.ot-notification-multiple{  display  : block; color    : red; font-size: 13px; padding  : 5px; } </style>`;
$("body").append(styles);

function checkLimitVariantInCart(variantProductInCart){ 
    var inputCheckout;  
    if($(".ot-quantity-selector").length != 0){
        inputCheckout = $('.ot-quantity-selector');
    }else{
        inputCheckout = $('form[action="/cart"]').find("[name='updates[]']") ;
    }  
    if(inputCheckout.length == 0) inputCheckout = $(".cart-list li"); 
    for(let i=0; i < inputCheckout.length; i++){ 
        if(typeof inputCheckout.attr("data-product-id") != "undefined"){
            var variantID = $(inputCheckout[i]).attr("data-product-id");
        }else{
            var updateID = inputCheckout[i].id;  
            var splitHaiCham = updateID.split(':')[0];
            var variantID = splitHaiCham.split('_')[splitHaiCham.split('_').length -1];  
            if(variantID.indexOf("_") != -1){
                variantID = variantID.split('_')[1] ;
            } 
        }  
 
        $.ajax({
            url: `${rootlinkQuantity}/limitOrder.php`,
            type: 'POST',
            dataType: 'json', 
            data: {
                action: 'checkLimitVariantInCart', 
                shop: shop, 
                variant_id: variantID,
                customerId:customerId
            }
        }).done((respone) => {
            if (typeof respone == "string") {
                respone = JSON.parse(respone);
            }
            limitForVariant = respone.result;
            var settingsLimit = respone.settings;
            var isDisableCheckoutByMultiple = false;
            var isDisableCheckoutByLimit = false; 
            if(limitForVariant != null){
                if($(inputCheckout[i]).parent().find(".ot-error").length > 0){ $(inputCheckout[i]).parent().find(".ot-error").remove(); } 
                if(typeof variantProductInCart[i] != 'undefined'){
                    var quantityItemIncart = variantProductInCart[i]['quantity'];
                    if(limitForVariant.multiple != 0 && typeof limitForVariant.multiple != "undefined" && limitForVariant.multiple != null){ 
                        $(inputCheckout[i]).attr("step",limitForVariant.multiple);
                        $(inputCheckout[i]).attr("min",0);
                        variantProductInCart['multiple'] = limitForVariant.multiple;
                        if(parseInt($(inputCheckout[i]).val()) % limitForVariant.multiple != 0){ 
                            let html = settingsLimit.notificationMultiple.replace('{multiple}', limitForVariant.multiple);
                            $(inputCheckout[i]).after(`<span class='ot-notification-multiple'>${html}</span>`); 
                            isDisableCheckoutByMultiple = true;
                        }
                    } 
                    if (quantityItemIncart < parseInt(limitForVariant.min) && parseInt(limitForVariant.min) != 0) {
                        elementAppend = "#"+inputCheckout[i].id;
                        variantProductInCart['min'] = limitForVariant.min;
                        $(inputCheckout[i]).after("<span class='ot-error'>"+settingsLimit.min_text+" "+ limitForVariant.min+"</span>");   
                        isDisableCheckoutByLimit = true;
                    } else if(parseInt(quantityItemIncart) > parseInt(limitForVariant.max) && parseInt(limitForVariant.max) != 0){ 
                        $(inputCheckout[i]).after("<span class='ot-error'>"+settingsLimit.max_text+" "+ limitForVariant.max+"!</span>"); 
                        variantProductInCart['max'] = limitForVariant.max;
                        isDisableCheckoutByLimit = true;
                    }else{ 
                        isDisableCheckoutByLimit = false;
                    }
                }  
                if(isDisableCheckoutByMultiple == true || isDisableCheckoutByLimit == true) {
                    console.log("disabled");
                    $("[name=checkout],.btn-checkout").attr("disabled", true); 
                    $("[name=checkout],.btn-checkout").css("pointer-events", "none")
                }else{
                    $("[name=checkout],.btn-checkout").attr("disabled", false); 
                    $("[name=checkout],.btn-checkout").css("pointer-events", "all"); 
                }
            }
        })  
   }   
}
 