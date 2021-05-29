<?php 
 

// APPEND LIB (CSS/JS)
$html .= '  
        <script src="'.ROOTLINK.'assets/ot-layout-card.js?v='.time().'"></script> 
        <script>
        $(document).on("pagecreate", ".otTableOffer", function(){ 
          $(".listDiscount").on("taphold",function() // bind on method with taphold event to li
           {  
             $(this).hide(); // increase fontsize on tap
        });                       
        });
        
      </script>
    ';
$product = getProductByProductID($shopify,$productID,$fields = "title");
$productTitle = $product['title'];
// TITLE HEADER
if($settings['show_heading'] == 1){
    $html .= ' <h3 class="quantity-break-header">'.$settings['groups_heading'].'</h3> ';
}

// STYLE CUSTOM SETTING
$html .= '
    <style>
        .listDiscount{
            border: '.$settings['table_border_size'].'px solid '.$settings['table_border_color'].';
        }
        .ot-card-left {
            color: '.$settings['table_text_color'].';
        }
        .quantity-break-header {
            font-size:'.$settings['table_heading_size'].'px; 
            color:'.$settings['table_heading_color'].';
        }
    </style>  
';
 // LAYOUT HTML 
$html .= '
    <div class="quantity-card">';
    // $listOfferOfVariant : list offers in database
    foreach ($listOfferOfVariant as $k=>$offerOfVariant) {
        $offerOfVariant = cvf_convert_object_to_array($offerOfVariant);
        $getPricePerProduct = getPricePerProduct($settings,$offerOfVariant,$price,$maxDiscountForCustomerTag);
        $totalAmountProduct = $getPricePerProduct['totalAmountProduct'];
        $discountPercent    = $getPricePerProduct['discountPercent'];
        $price_new          = $getPricePerProduct['price_new'];
        if($k == 0){
            $otTotalPrice = $totalAmountProduct; 
            $otPricePerProduct = $price_new;
        }
        $html .= '<div class="listDiscount ';if($k == 0){ $html.='active'; }$html.='" >
                       <span class="ot-card-left"> <span>'.$offerOfVariant['number'].'</span> '.$settings['table_text1'].' '.$productTitle.''.$settings ['table_text'].'</span>'; 
                    $html .= '<span class="ot-card-discount-product">'.round($discountPercent).'% '.$settings['offLabel'].'</span>';
            $html .= ' <div class="ot-card-right">  <span class="ot-card-total money conversion-bear-money">'.$totalAmountProduct.'</span> '; 
            $html .= ' <p class="ot-card-price-product"> <span class="formatPrice money conversion-bear-money">'.sprintf("%.2f",round($price_new,2)).'</span> '.$settings ['textAfter'].'</p>'; 
            $html .= '  </div>  '; 
            $html .= '</div>'; 
    } 
        $html .= '</div>';
     $html .= '
            <style>
                .ot-card-total{
                    font-size: 1rem;
                    line-height: 1.3;
                    font-family: montserrat;
                    font-weight: 600;
                    padding-left: 1px;
                    letter-spacing: .03rem;
                    flex: 0 0 auto;
                    align-items: baseline;
                    box-sizing: inherit;
                    color: #757575;
                    text-align: center
                }
                .ot-card-discount-product {
                    font-size: 15px;
                    color: #fff;
                    background-color: #3fb1a5;
                    padding: 5px 10px;
                    border-radius: 5px;
                    font-weight: 400;
                    right: -2px;
                    bottom: 22px;
                }
                .ot-card-price-product{
                    font-size: .625rem;
                    color: #9e9e9e;
                    margin-top: .2rem;
                    padding-left: 1px;
                    flex: 1 0 20px;
                    text-align: right;
                    margin-bottom: 0px;
                }
                .listDiscount{
                    display: flex;
                    flex: 0 0 auto;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px 14px;
                    border: 1px solid #e0e0e0;
                    border-radius: 2px;
                    margin: 0 1% 2% 0;
                    transition: .2s ease;
                    cursor: pointer;
                    user-select: none;
                    background-color: #fff;
                    overflow: hidden;
                    position: relative;
                    flex-direction: row;
                    padding-right: 35px;
                    border: 1px solid #d9d9d9;
                }
                .listDiscount:hover,.quantity-card .active{
                    border: 1px solid #d9d9d9;
                    box-shadow: 0 0 0 1px #d9d9d9;
                    background-color: #f3f3f3;
                    
                }
                .quantity-card{ 
                 
                }
                .otTableOffer .otPricePerProduct{
                    font-style: italic;
                }
                .otTableOffer .otTotalPrice{
                    font-size: 1.5rem;
                    font-style: italic;
                    margin-right: 5px;
                }
                .ot-card-left span{ 
                    
                }
                .ot-card-left{
                    width: 60%;
                    /*   color: #00c167; 
                    flex: 0 0 auto;
                    padding: 0 10px 0 0;
                    margin: 0;
                    */
                }
                .ot-card-right{
                    padding-top: 5px;
                } 
                @media only screen and (max-width: 768px){
                    .quantity-card{
                        display:block;
                    }
                }
            </style> ';

function getPricePerProduct($settings,$offerOfVariant,$price,$maxDiscountForCustomerTag)
{
    $result = array();
     
    /*
    | TYPE OFFER : percent | price
    */
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
    if ($offerOfVariant['discountType'] == 'percent') {
        $offerOfVariant['price'] = (float)$offerOfVariant['price']  + (float)$percentDiscountByTag;
        // TYPE OFFER IS PERCENT
        $discountPerProduct = sprintf("%.2f",round(($price *((float)$offerOfVariant['price']/100)),2)) ;   
        $totalAmountProduct = (float)$offerOfVariant['number'] * ($price - $discountPerProduct);  
        $price_new = ( $price - $discountPerProduct );
        $discountPercent = (float)$offerOfVariant['price'];
        
    }
    if ($offerOfVariant['discountType'] == 'price') { 
        // TYPE OFFER IS PRICE 
        $offerOfVariant['price'] = (float)$offerOfVariant['price']  + (float)$priceDiscountByTag;
        $discountPerProduct = sprintf("%.2f",round((float)$offerOfVariant['price'],2)) ;
        $totalAmountProduct =  (float)$offerOfVariant['number'] * ($price - (float)$offerOfVariant['price']);
        $price_new = ( $price - $discountPerProduct );
        $discountPercent = sprintf(round((100 *((float)$offerOfVariant['price']/$price)))) ;
    }
    $result['price_new'] = ($price_new > 0)? $price_new : "Free"; 
    $result['discountPercent'] = ($discountPercent > 0)? $discountPercent : 0;
    $result['totalAmountProduct'] = ($totalAmountProduct > 0)? sprintf("%.2f",round($totalAmountProduct,2)) : 0 ;
    return $result;
}
?>

