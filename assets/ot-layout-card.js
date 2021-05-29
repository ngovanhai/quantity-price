$(document).ready(function(){
    /*
    |   Element input quantity
        Auto    : $("[name='quantity']")
        Custom  : $(".ot-quantity")
     */ 
    var OTElementQuantity 
	if($(".ot-quantity").length > 0){
		 OTElementQuantity = $(".ot-quantity") 
	}else{
		 OTElementQuantity =  $("[name='quantity'],.js-qty__adjust,.plus,.minus, #quantity, .product-page-qty .plus_btn, .product-page-qty .minus_btn,.qty-btn-vertical .qty-down,.qty-btn-vertical .qty-up,.js--add,.js--minus") 
    }
    var otQuantityProduct = OTElementQuantity.val();
    // DISCOUNT DEFAULT ACTIVE
    if(typeof window.OTSettings.autoChangeQuantityLayoutCard != "undefined" && window.OTSettings.autoChangeQuantityLayoutCard == 1){
        let numberFirstDiscount = $(this).find(".ot-card-left span").html()
        OTElementQuantity.val(numberFirstDiscount)
    }
   

    // ON CHANGE DISCOUNT
     
    $(".listDiscount").on("touchstart mousedown touchend mouseup click", function(){
        $(".listDiscount").removeClass("active");
        $(this).addClass("active");        
        if(typeof window.OTSettings.autoChangeQuantityLayoutCard != "undefined" && window.OTSettings.autoChangeQuantityLayoutCard == 1){
            OTElementQuantity.val($(this).find(".ot-card-left span").html())
        }
         updateNoticeInBellowLayout($(this));
         
 
     })
    $('.listDiscount').bind('taphold', function(e) {
        $(".listDiscount").removeClass("active");
        $(this).addClass("active");
        if(typeof window.OTSettings.autoChangeQuantityLayoutCard != "undefined" && window.OTSettings.autoChangeQuantityLayoutCard == 1){
            OTElementQuantity.val($(this).find(".ot-card-left span").html())
        }
        // APPEND NOTICE BELLOW LAYOUT
        updateNoticeInBellowLayout($(this))

        // CHANGE QUANTITY INPUT
      
    })
   
    // ONE CHANGE QUANTITY
    $("[name='quantity']").bind('click keyup change', function(){   
        $(".listDiscount").each(function(index,value){
            if($(this).find(".ot-card-left span").html() == $("[name='quantity']").val()){
                $(".listDiscount").removeClass("active");
                $(this).addClass("active");
                updateNoticeInBellowLayout($(this))
            }
        })
    })
})

function updateNoticeInBellowLayout(elementCurrent){
    // APPEND NOTICE BELLOW LAYOUT
   
    let otTotalPrice = elementCurrent.find(".ot-card-total").html()
    let otPricePerProduct = elementCurrent.find(".ot-card-price-product .formatPrice").html()
    $(".otTotalPrice").empty(otTotalPrice);
    $(".otPricePerProduct").empty(otTotalPrice);
    $(".otTotalPrice").append(otTotalPrice);
    $(".otPricePerProduct").append(otPricePerProduct);
}