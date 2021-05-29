<?php 
$html .= '
        <style>
            .limit-header{
                font-size:'.$settings['table_heading_size'].'px;
                color:'.$settings['table_heading_color'].';
            }
            .productLimits{
                border: '.$settings['table_border_size'].'px solid '.$settings['table_border_color'].';
                width : '.$settings['table_width'].';
                font-size: '.$settings['table_text_size'].';
                color:'.$settings['table_text_color'].';
            }
             .productLimits tr td,.productLimits tr th{
                border: '.$settings['table_border_size'].'px solid '.$settings['table_border_color'].';
            }
        </style> 
    ';
if($settings['show_heading'] == 1){
    $html .= ' <h3 class="limit-header" style="margin-top:5px;">'.$settings['limits_heading'].'</h3> ';
}
$html .= '<table class="productLimits">
            <tbody>
                <tr>
                    <th>'.$settings['min_table_heading'].'</th>
                    <th>'.$settings['max_table_heading'].'</th> ';

$html .= '          </tr>';
    $html .= '   <tr>';
    if($limitByvariant['min'] == 0){$limitByvariant['min'] = "No limit";} 
    $html .= '        <td class="ot-min">'.$limitByvariant['min'].'</td>';
    if($limitByvariant['max'] == 0){$limitByvariant['max'] = "No limit";} 
    $html .= '      <td>'.$limitByvariant['max'].'</td>'; 
    $html .= '  </tr> ';  
    $html .= '</tbody>
        </table> 
    '; 
 
 ?>