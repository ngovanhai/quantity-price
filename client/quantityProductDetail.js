 
let elementButtonAddToCart = $("[name=add],.single_add_to_cart_button,form[action='/cart/add'] .add-to-cart-btn,.AddtoCart");
elementButtonAddToCart.attr("disabled", true);
if(typeof window.settings != "undefined"){
    if(window.settings.elementProductPage != "NULL"){
        if(Shopify.shop == "native-essence-inc.myshopify.com"){
            $(window.settings.elementProductPage).first().after("<div class='groupsTable' style='width:100%'><div class='otTableOffer'></div><div class='otTableLimit'></div></div>");
        }else{
            $(window.settings.elementProductPage).first().before("<div class='groupsTable' style='width:100%'><div class='otTableOffer'></div><div class='otTableLimit'></div></div>");
        }
    }
}
if($(".groupsTable").length == 0){
    if($(".ot-main-add-cart,.product-form-product-template").length > 0){
        $(".ot-main-add-cart,.product-form-product-template").append("<div class='groupsTable' style='width:100%'><div class='otTableOffer'></div><div class='otTableLimit'></div></div>");
     }else{ 
        $("form[action='/cart/add']").append("<div class='groupsTable' style='width:100%'><div class='otTableOffer'></div><div class='otTableLimit'></div></div>"); 
    }
}


// bat event onchange
if($(".ot_quantity").length > 0){
    var elementvariantID = $(".ot_quantity") ;
}else{
    var elementvariantID = $("input[name^=id]:checked, select[name^=id], input[name=id], hidden[name^=id]") ;
}


var otProductID = meta.product.id;
var otVariantList = meta.product.variants;
var customerId;
var currentUrl = window.location.href;
var variantID;
variantID = $(elementvariantID).val();

if(Shopify.shop == "spiffdemo.myshopify.com"){
    if(typeof __st.pageurl.split("variant=")[1] != "undefined"){
        variantID =  __st.pageurl.split("variant=")[1];
    }if(typeof __st.pageurl.split("variantId=")[1] != "undefined"){
        variantID =  __st.pageurl.split("variantId=")[1];
    }else{
        variantID =  meta.product.variants[0]['id'] ;
    }
    
}

// check customer login 
if (typeof meta.page.customerId === "undefined" || !meta.page.customerId) {
    customerId = '';
} else {
    customerId = meta.page.customerId;
}
var otElementKeyUpInPut = $("[name='quantity'],.velaQtyNum,.velaQtyMinus,.velaQtyPlus,.product-form__quantity,.ot-quantity,.product-quantity,.js-qty__adjust,.plus,.minus, #quantity,.quantity-control-down,.quantity-control-up, .product-page-qty .plus_btn, .product-page-qty .minus_btn,.qty-btn-vertical .qty-down,.qty-btn-vertical .qty-up,.js--add,.js--minus, .form-field-input.form-field-select.form-field-filled[aria-label='Quantity']");
// listen even onchange variant
var interval_obj = setInterval(function(){ 
    if(currentUrl != window.location.href){
        currentUrl = window.location.href ;
        var fullUrlParts = document.location.href.split("variant="); 
        showOfferInProductDetail(parseInt(fullUrlParts["1"]));
        showLimitInProductDetail(parseInt(fullUrlParts["1"]));
    }
}, 500); 
showOfferInProductDetail(variantID);
showLimitInProductDetail(variantID); 

otElementKeyUpInPut.bind('click keyup', function(e){ 
    let elementQuantity;
     
	if($(".ot-quantity").length > 0){
		 elementQuantity = $(".ot-quantity").val();
	}else{
		 elementQuantity =  $("[name='quantity'],.product-quantity").val();
    } 
    if(parseInt($("[name='quantity'],.ot-quantity,.product-quantity").attr("step")) > 1){
         if(elementQuantity % parseInt($("[name='quantity'],.ot-quantity,.product-quantity").attr("step")) != 0){
            elementButtonAddToCart.attr("disabled", true);
            $(".ot_valid_product").html(`${window.settings.notificationMultiple}`+$("[name='quantity'],.ot-quantity,.product-quantity").attr("step")); 
        }else{
            let otQuantityDataCheckLimit = {  
                shop: Shopify.shop,
                action: 'getLimitByVariant',
                variantID: variantID,
                quantity: elementQuantity ,
                idProduct: otProductID,
                customerId:customerId
            };
            checkLimitByVariant(otQuantityDataCheckLimit,variantID);
        }
    }else{ 
        let otQuantityDataCheckLimit = { 
            shop: Shopify.shop,
            action: 'getLimitByVariant',
            variantID: variantID,
            quantity: elementQuantity ,
            idProduct: otProductID,
            customerId:customerId
        };
        checkLimitByVariant(otQuantityDataCheckLimit,variantID);
    } 
    
});
async function showLimitInProductDetail(variantID){
    // function check limit purchased in product detail
    let quantity = 1  
    // append style
    if($(".ot_valid_product").length == 0){
        $("[name='quantity']").last().after("<span class='ot_valid_product'></span>");
    } 
    let styles = `<style>
                    .ot_valid_product{
                        position: absolute;
                        color: red;
                        bottom: -21px;
                        left:0; 
                        font-size: 14px;
                        width: 300px;
                    } 
                </style>`; 
    $("body").append(styles); 
    $('.ot_valid_product').parent().css('position','relative');  
    // check limit quantity when focus input            
    quantity = $("[name='quantity']").last().val();
    let otQuantityDataCheckLimit = { 
        shop: Shopify.shop,
        action: 'getLimitByVariant',
        variantID: variantID,
        idProduct: otProductID,
        quantity:quantity,
        customerId:customerId
    }; 
    checkLimitByVariant(otQuantityDataCheckLimit,variantID);  
}  
function showOfferInProductDetail(variantID){  
    //remove custom variant o phien ban cu
    $("option").each(function (){
        if($(this).html().indexOf("and above)") > -1){
            $(this).remove();
        }
    });  
    var otQuantityData = {
        idProduct: otProductID,
        shop: Shopify.shop,
        action: 'getOfferByVariant',
        variantID: variantID,
        customerId:customerId 
    };  
    checkOfferByVariant(otQuantityData,variantID);
}

function checkOfferByVariant(otQuantityData,variantID){
    otVariantList.forEach(function (element) {
        if (element.id == variantID) {
            otQuantityData.price = element.price;
            $.ajax({
                url: `${rootlinkQuantity}/quantity_break_v2.php`,
                type: 'GET',
                data: otQuantityData
            }).done((result) => {
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                $(".otTableOffer,.otTableOffer1").empty(); 
                if(Shopify.shop == "american-mask-company.myshopify.com"){
                    $(".otTableOffer1").first().append(result.html);
                }else{
                    $(".otTableOffer").last().append(result.html);
                } 
                formatMoneyByClass('formatPrice');
                formatMoneyByClass('formatPrice_pricePerProduct');
                formatMoneyByClass('formatPrice_priceAmountProduct');
                formatMoneyByClass('ot-card-total')  ;
                formatMoneyByClass('otTotalPrice');
                formatMoneyByClass('otPricePerProduct')  ;
            }).fail((error) => {
                console.log("ERROR: checkOfferByVariant in productDetail");
            });
        }
    })
} 

function formatMoneyByClass(formatPrice){
    for (i = 0; i < $(`.${formatPrice}`).length; ++i) {
        if(typeof conversionBearAutoCurrencyConverter != "undefined"){
             let newPriceConvert = conversionBearAutoCurrencyConverter.convert(parseFloat($(`.${formatPrice}`)[i].innerText));
            let typeCurrenCy = detectCurrenCy(newPriceConvert);
            if(typeCurrenCy != ""){
                $(`.${formatPrice}`)[i].innerText = newPriceConvert.default_format.replace(typeCurrenCy,newPriceConvert.amount).replace(/(<([^>]+)>)/gi, "");
            }else{
                $(`.${formatPrice}`)[i].innerText = Shopify.formatMoney($(`.${formatPrice}`)[i].innerText);
            }
        }else{
            $(`.${formatPrice}`)[i].innerText = Shopify.formatMoney($(`.${formatPrice}`)[i].innerText);
        }
    }
}
function checkLimitByVariant(otQuantityDataCheckLimit,variantID){
    otVariantList.forEach(function (element) {
        if (element.id == variantID) {
             $.ajax({
                url: `${rootlinkQuantity}/quantity_break_v2.php`,
                type: 'GET',
                data: otQuantityDataCheckLimit
            }).done((result) => {
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                
                $(".otTableLimit").empty();
                if(Shopify.shop  != "madhouse-it.myshopify.com"){
                    $(".otTableLimit").first().append(result.html); 
                }else{
                    $(".otTableLimit").last().append(result.html); 
                }
                
                if(result.checkLimit != true){
                    if(result.multiple != 0){
                        $("[name='quantity']").attr("step",result.multiple);
                        $("[name='quantity']").attr("min",0); 
                    } 
                    elementButtonAddToCart.attr("disabled", true);
                    $(".ot_valid_product").empty();
                    $(".ot_valid_product").append(result.checkLimit);
                }else{
                    elementButtonAddToCart.attr("disabled", false);
                    $(".ot_valid_product").empty();
                }
            }).fail((error) => {
                console.log("ERROR: checkLimitByVariant in product detail");
                elementButtonAddToCart.attr("disabled", false);
            });
        }
    })
}

function detectCurrenCy(data){
    if(data != ""){
        var split1 = data.default_format.split('{{');
        if(typeof split1[1] != "undefined"){
            split2 = split1[1].split('}}'); 
            if(typeof split2[0] != "undefined"){ 
                return '{{'+split2[0]+'}}';
            }else{
                return "";
            }
        }else{
            return "";
        }
    }else{
        return "";
    }
}

