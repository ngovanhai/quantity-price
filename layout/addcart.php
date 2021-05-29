<?php 
$html .= '
        <style>
           .otAddToCart{ 
               font-size:10px;
               padding:5px;
           }
        </style> 
    ';
$html .= ' 
    <script src="'.ROOTLINK.'assets/ot-layout-addcart.js?v='.time().'"></script>
 ';
if($settings['show_heading'] == 1){
    $html .= ' <h3 class="quantity-break-header">'.$settings['groups_heading'].'</h3> ';
}
$html .= '<table class="priceGroupList"> '; 
            $html .= '<tbody>'; 
            foreach ($listOfferOfVariant as $offerOfVariant) {
                $offerOfVariant = cvf_convert_object_to_array($offerOfVariant);
                $getPricePerProduct = getPricePerProduct($settings,$offerOfVariant,$price,$maxDiscountForCustomerTag);
                $discountPerProduct = $getPricePerProduct['discountPerProduct'];
                $residualPerProduct = $getPricePerProduct['residualPerProduct'];
                $totalAmountProduct = $getPricePerProduct['totalAmountProduct'];
                $numberQuantityDiscount = $offerOfVariant['number'];
                $html .= '<tr>';
                    $html .= '<td>'.$settings['table_text1'].' '.$numberQuantityDiscount.' '.$settings ['table_text'].'  '; 
                    if($settings['show_percent'] != 0){
                        $html .= ''.$discountPerProduct.' ';
                    }else{
                        if($settings['shop'] == "ab-hof-vinothek.myshopify.com"){
                            $html .= '<span class="formatPrice_pricePerProduct money">'.sprintf("%.2f",round($residualPerProduct,2)).'</span>';
                        }else{
                            $html .= '<span class="formatPrice_pricePerProduct money">'.sprintf("%.2f",round($discountPerProduct,2)).'</span>';
                        } 
                    } 
                   
                $html .= ' '.$settings ['textAfter'].'</td>'; 
                $html .= "
                    <td><a class='btn otAddToCart' onclick='otQuantityAddToCart($numberQuantityDiscount,$variantId,event)'>Add to cart</a></td>
                ";
                $html .="</tr>";
            }
            $html .= '</tbody>'; 
$html .= ' </table>';

function getPricePerProduct($settings,$offerOfVariant,$price,$maxDiscountForCustomerTag){ 
    $result = array();
    $offerOfVariant['price'] =  (float)$offerOfVariant['price'];
    $offerOfVariant['number'] = (float)$offerOfVariant['number'];
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
        $discountPerProduct = $offerOfVariant['price']."%";
        $residualPerProduct = (100 - $offerOfVariant['price'] )."%";
        $totalAmountProduct = $offerOfVariant['number'] * $price *((100-$offerOfVariant['price'])/100); 
    }
    if ($settings['show_percent'] == 1 && $offerOfVariant['discountType'] == 'price') { 
        $offerOfVariant['price'] = $offerOfVariant['price']  + $priceDiscountByTag;
        $discountPerProduct = sprintf("%.2f",round(((($offerOfVariant['price'])/$price)*100),2)); 
        if($discountPerProduct > 100){
            $discountPerProduct = "100%";
        }else{
            $discountPerProduct = $discountPerProduct."%";
        }
        $residualPerProduct =  (100 - sprintf("%.2f",round(((($offerOfVariant['price'])/$price)*100),2)))."%";
        $totalAmountProduct = $offerOfVariant['number'] * ($price - $offerOfVariant['price']);
    }
    if ($settings['show_percent'] == 0 && $offerOfVariant['discountType'] == 'percent') {
        $offerOfVariant['price'] = $offerOfVariant['price']  + $percentDiscountByTag;
        $residualPerProduct = sprintf("%.2f",round($price-($price *($offerOfVariant['price']/100)),2));
        $discountPerProduct = sprintf("%.2f",round(($price - $residualPerProduct),2));  
        $totalAmountProduct = $offerOfVariant['number'] * ($price - $discountPerProduct); 
    }
    if ($settings['show_percent'] == 0 && $offerOfVariant['discountType'] == 'price') { 
        $offerOfVariant['price'] = $offerOfVariant['price']  + $priceDiscountByTag;
        $residualPerProduct = sprintf("%.2f",round($price-$offerOfVariant['price'],2)) ;
        $discountPerProduct = sprintf("%.2f",round($price-$residualPerProduct,2)) ;
        $totalAmountProduct = $offerOfVariant['number'] * ($price - $discountPerProduct); 
    }
    $result['discountPerProduct'] = ($discountPerProduct)? $discountPerProduct : 0; 
    $result['residualPerProduct'] = ($residualPerProduct)? $residualPerProduct : 0; 
    $result['totalAmountProduct'] = ($totalAmountProduct)? sprintf("%.2f",round($totalAmountProduct,2)) : 0 ;
  
    return $result;
}
 ?>