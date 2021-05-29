$(document).ready(function(){
    let elementQuantity 
	if($(".ot-quantity").length > 0){
		 elementQuantity = $(".ot-quantity") 
	}else{
		 elementQuantity =  $("[name='quantity'],.js-qty__adjust,.plus,.minus, #quantity, .product-page-qty .plus_btn, .product-page-qty .minus_btn,.qty-btn-vertical .qty-down,.qty-btn-vertical .qty-up,.js--add,.js--minus") 
    } 
    // elementQuantity.val(numberFirstDiscount)
    if(Shopify.shop != "contouree.myshopify.com"){
        if($(".listDiscount").length > 0){
            if(typeof $($(".listDiscount")[0]).attr("data-number") != "undefined"){
                let numberFirstDiscount = parseInt($($(".listDiscount")[0]).attr("data-number"));
                elementQuantity.val(numberFirstDiscount)
            }
        }
    }
    

    $(".listDiscount").click(function(){ 
        if(typeof $(this).attr("data-number") != "undefined"){
            let numberFirstDiscount = parseInt($(this).attr("data-number"));
            elementQuantity.val(numberFirstDiscount)
            if(Shopify.shop == "madhouse-it.myshopify.com" && typeof checkLimitByVariant != "undefined"){
                let otQuantityDataCheckLimit = {  
                    shop: Shopify.shop,
                    action: 'getLimitByVariant',
                    variantID: variantID,
                    quantity:  $("[name='quantity'],.product-quantity").val() ,
                    idProduct: otProductID,
                    customerId:customerId
                };
                checkLimitByVariant(otQuantityDataCheckLimit,variantID);
            }
        }
    })
})
 
