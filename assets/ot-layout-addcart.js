$.ajax({
    type    : 'GET',
    url     : '/cart.js',
    dataType: 'json'
}).done(itemCart => {
    console.log("variantID",variantID) 
    for(let i =0; i < itemCart.length ; i++){
        if(itemCart[i]['id'] = variantID){

        }
    } 
})

function otQuantityAddToCart(number,variantID,event){
    var params = {
        quantity: number,
        id: variantID
    }; 
    $(event.target).attr("disabled",true);
    $(event.target).html(`<i class="fa fa-spinner fa-pulse fa-fw "></i> Adding...`);
    $.ajax({
        type: 'POST',
        url: '/cart/add.js',
        dataType: 'json',
        data: params,
        success: function (cart) {
            var cartItemCounter = document.querySelector('span.cart-item-count');
            cartItemCounter.innerText = cart.quantity;
            $(event.target).attr("disabled",true);
            $(event.target).html(`<i class="fa fa-check" aria-hidden="true"></i> Added`);
            if(Shopify.shop == "fest4all-dk.myshopify.com"){
                window.location.href = 'https://'+window.location.hostname+"/cart"; 
            }
          
        },
        error: function (error) {
            $(event.target).attr("disabled",false);
            $(event.target).html(`Add to cart`);
        }
    });
}