<?php 
$html .= '
        <style>
            .quantity-break-header {
                font-size:'.$settings['table_heading_size'].'px;
                color:'.$settings['table_heading_color'].';
            }
            .priceGroupList {
                border: '.$settings['table_border_size'].'px solid '.$settings['table_border_color'].';
                width : '.$settings['table_width'].';
                font-size: '.$settings['table_text_size'].';
                color:'.$settings['table_text_color'].';
            }
            .priceGroupList tr td, .priceGroupList tr th {
                border: '.$settings['table_border_size'].'px solid '.$settings['table_border_color'].';
            }
        </style> 
    ';
if($settings['show_heading'] == 1){
    $html .= ' <h3 class="quantity-break-header">'.$settings['groups_heading'].'</h3> ';
}
$html .= '<table class="priceGroupList">
            <thead>
                <tr>
                    <th>'.$settings['group_table_heading'].'</th>' ;
                    if($settings['showColumnLayoutTable'] == 0){               
                        $html .= '<th>'.$settings['price_table_heading'].'</th>';
                    }
                    if($settings['showColumnLayoutTable'] == 1){               
                        $html .= '<th>'.$settings['residual_text'].'</th>';
                    }
                    if($settings['show_total_amount'] == 1){               
                        $html .= '<th>'.$settings['total_amount_text'].'</th>';
                    }
            $html .= '</tr></thead>'; 
            $html .= '<tbody>'; 
            foreach ($listOfferOfVariant as $k=>$offerOfVariant) {
                $offerOfVariant = cvf_convert_object_to_array($offerOfVariant);
                $getPricePerProduct = getPricePerProduct($settings,$offerOfVariant,$price,$maxDiscountForCustomerTag);
                $discountPerProduct = $getPricePerProduct['discountPerProduct'];
                $residualPerProduct = $getPricePerProduct['residualPerProduct'];
                $totalAmountProduct = $getPricePerProduct['totalAmountProduct'];
                $html .= '<tr>';
                    if($k < count($listOfferOfVariant) - 1){
                        $html .= '<td>'.$settings['table_text1'].' '.$offerOfVariant['number'].' - '.((int)$listOfferOfVariant[$k+1]['number'] - 1).' '.$settings ['table_text'].' </td>';
                    }else{
                        $html .= '<td>'.$settings['table_text1'].' '.$offerOfVariant['number'].' + '.$settings ['table_text'].' </td>';
                    }
                   
                    if($settings['showColumnLayoutTable'] == 0){
                        if($settings['show_percent'] != 0){
                            $html .= '<td class="">'.$discountPerProduct.'</td>';
                        }else{
                            $html .= '<td class="formatPrice_pricePerProduct money">'.sprintf("%.2f",round($discountPerProduct,2)).'</td>';
                        }
                    }else{
                        if($settings['show_percent'] != 0){
                            $html .= '<td class="">'.$residualPerProduct.'</td>';
                        }else{
                            $html .= '<td class="formatPrice_pricePerProduct money">'.sprintf("%.2f",round($residualPerProduct,2)).'</td>';
                        }
                    }
                    


                    if($settings['show_total_amount'] == 1){
                        $html .= '<td class="formatPrice money">'.$totalAmountProduct.'</td>'; 
                    } 
                $html .= '</tr>'; 
            }
            $html .= '</tbody>'; 
$html .= ' </table>';

function getPricePerProduct($settings,$offerOfVariant,$price,$maxDiscountForCustomerTag)
{ 
    global $shop; 
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