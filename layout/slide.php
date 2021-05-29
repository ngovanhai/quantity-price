 
<?php 
if($settings['show_heading'] == 1){
    $html .= ' <h3 class="quantity-break-header">'.$settings['groups_heading'].'</h3> ';
}
// APPEND LIB (CSS/JS)
$html .= ' 
        <script src="'.ROOTLINK.'assets/ot-layout-slide.js?v=4"></script>
    ';
$html .=' 
<link rel="stylesheet" type="text/css" href="'.ROOTLINK.'assets/slick/slick.css">
<link rel="stylesheet" type="text/css" href="'.ROOTLINK.'assets/slick/slick-theme.css"> 
<link rel="stylesheet" type="text/css" href="'.ROOTLINK.'assets/slick/slick-theme.css">
';
$html .= '<style>
    .slick-slide img {
        display: block;
        width:100%;
    }
    .slick-initialized .slick-slide{
        margin:0px 5px;
        padding: 0px 5px;
    }
    .listDiscount{
        text-align: center;
        border: 1px solid #a0a0a0;
        padding: 10px 0px;
    }
    .listDiscount p{
        margin-bottom:5px;
    }
    .quantity-slide{
        display:none;
    }
    .slick-prev, .slick-next {
        background: #ececec;
        width: 30px;
        height: 30px;
		display:none;
    }
    .quantity-slide .slick-prev:before {
        content: "<";
    }
    .quantity-slide .slick-prev:before, .slick-next:before {
        font-size: 20px;
        line-height: 1;
        opacity: .75;
        color: #525252;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .quantity-slide .slick-next:before {
        content: ">";
    }
    .listDiscount span{
        color: #da2f0c;
        font-weight: bold;
    }
    .listDiscount{
        cursor: pointer;
    }
</style>';
$html .= '
    <div class="quantity-slide">';
    foreach ($listOfferOfVariant as $offerOfVariant) {
        $offerOfVariant = cvf_convert_object_to_array($offerOfVariant);
        $getPricePerProduct = getPricePerProduct($settings,$offerOfVariant,$price,$maxDiscountForCustomerTag);
        $discountPerProduct = $getPricePerProduct['discountPerProduct'];
        $totalAmountProduct = $getPricePerProduct['totalAmountProduct'];
        $price_new = $getPricePerProduct['price_new'];
        $html .= '<div class="listDiscount" data-number="'.$offerOfVariant['number'].'">
            <p>'.$settings['table_text1'].' '.$offerOfVariant['number'].'  '.$settings ['table_text'].'</p>';
            if($settings['showColumnLayoutTable'] == 1){
                if($settings['show_percent'] == 0){
                    $html .= ' <p> <span class="formatPrice money">'.sprintf("%.2f",round($price_new,2)).'</span> '.$settings ['textAfter'].'</p>';
                }else{
                    $html .= ' <p> <span class="">'.$price_new.'%</span> '.$settings ['textAfter'].'</p>'; 
                }
            }else{
                if($settings['show_percent'] == 0){
                    $html .= ' <p> <span class="formatPrice money">'.sprintf("%.2f",round($discountPerProduct,2)).'</span> '.$settings ['textAfter'].'</p>';
                }else{
                    $html .= ' <p> <span class="">'.$discountPerProduct.'%</span> '.$settings ['textAfter'].'</p>'; 
                }
            }

        $html .= '</div>'; 
    }
   
    if($settings['autoplay'] == 1) {
        $autoplay = true;
    }else{
        $autoplay = false;
    }
$html .= '    </div>
    <script>
        var $= jQuery.noConflict();
        $(document).ready(function(){
            $.getScript("'.ROOTLINK.'/assets/slick/slick.min.js").done(function (script, textStatus) {
                $(".quantity-slide").show();
                $(".quantity-slide").slick({
                    infinite: true,
                    slidesToShow: '.$settings ['numberItem'].',
                    slidesToScroll: 1,
                    autoplay: '.$autoplay.',
                    autoplaySpeed: '.$settings ['speedSlide'].',
                    infinite: true,
                    arrows:false,
                    responsive: [
                        {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3,
                            infinite: true,
                            dots: true
                        }
                        },
                        {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll:1
                        }
                        },
                        {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                        } 
                    ]
                });
            });
        }); 
    </script>
';
function getPricePerProduct($settings,$offerOfVariant,$price,$maxDiscountForCustomerTag)
{
    $result = array();
    $priceDiscountByTag = 0;
    $percentDiscountByTag = 0;
    if(!empty($maxDiscountForCustomerTag) && isset($maxDiscountForCustomerTag['discountType'])){
        if($offerOfVariant['discountType'] == 'price' && $maxDiscountForCustomerTag['discountType'] == 'price'){
            // tag PRICE discount PRICE 
            $priceDiscountByTag = $maxDiscountForCustomerTag['price'];
        }
        if($offerOfVariant['discountType'] == 'price' && $maxDiscountForCustomerTag['discountType'] == 'percent'){
            // tag PRICE discount PERCENT 
            $priceDiscountByTag = ($price*$maxDiscountForCustomerTag['price'])/100;
        }
        if($offerOfVariant['discountType'] == 'percent' && $maxDiscountForCustomerTag['discountType'] == 'percent'){
            //tag PERCENT discount PERCENT 
            $percentDiscountByTag = (($price- $price*($offerOfVariant['price']/100))*$maxDiscountForCustomerTag['price'])/$price;
        }
        if($offerOfVariant['discountType'] == 'percent' && $maxDiscountForCustomerTag['discountType'] == 'price'){
            // tag PERCENT discount PRICE 
            $percentDiscountByTag = ($maxDiscountForCustomerTag['price']/$price)*100;
        }
    } 
    if ($settings['show_percent'] == 1 && $offerOfVariant['discountType'] == 'percent') {
        $offerOfVariant['price'] = $offerOfVariant['price']  + $percentDiscountByTag;
        $discountPerProduct = $offerOfVariant['price'];
        $totalAmountProduct = $offerOfVariant['number'] * $price *((100-$offerOfVariant['price'])/100);
        $price_new = ( 100 - $discountPerProduct );
         
    }
    if ($settings['show_percent'] == 1 && $offerOfVariant['discountType'] == 'price') {
        $offerOfVariant['price'] = $offerOfVariant['price']  + $priceDiscountByTag;
        $discountPerProduct = sprintf("%.2f",round((( $offerOfVariant['price'])/$price)*100,2)); 
        $totalAmountProduct = $offerOfVariant['number'] * ($price - $offerOfVariant['price']);
        $price_new = ( 100 - $discountPerProduct );
        if($price_new < 0){
            $price_new = 0;
        }else{
            $price_new = $price_new;
        }
         
    }
    if ($settings['show_percent'] == 0 && $offerOfVariant['discountType'] == 'percent') {
        $offerOfVariant['price'] = $offerOfVariant['price']  + $percentDiscountByTag;
        $discountPerProduct = sprintf("%.2f",round(($price *($offerOfVariant['price']/100)),2)) ;   
        $totalAmountProduct = $offerOfVariant['number'] * ($price - $discountPerProduct);  
        $price_new = ( $price - $discountPerProduct );
     }
    if ($settings['show_percent'] == 0 && $offerOfVariant['discountType'] == 'price') { 
        $offerOfVariant['price'] = $offerOfVariant['price']  + $priceDiscountByTag;
        $discountPerProduct = sprintf("%.2f",round($offerOfVariant['price'],2)) ;
        $totalAmountProduct =  $offerOfVariant['number'] * ($price - $offerOfVariant['price']);
        $price_new = ( $price - $discountPerProduct );
    }
    $result['price_new'] = ($price_new > 0)? $price_new : "Free"; 
    $result['discountPerProduct'] = ($discountPerProduct > 0)? $discountPerProduct : 0; 
    $result['totalAmountProduct'] = ($totalAmountProduct > 0)? sprintf("%.2f",round($totalAmountProduct,2)) : 0 ;
    return $result;
}
?>