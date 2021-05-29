var shop = $(".shopName").html();
$(".saveGroupPrice").click(function (){
    var data  = {};
    data.productId = $(".productList").val();
    data.action = "addGroupPrice";
    data.shop = shop;
    data.groups = [];
    $(".number").each(function (){
        var number = $(this).val();
        var price = $(this).closest("tr").find(".price").val();
        if(number && price){
            var group = {
                number: number,
                price: price
            };
            data.groups.push(group);
        }
    });
    data.variantId = $(".variantList").val();
    $.get("services.php", data, function(response){
        console.log(response);
    });
});