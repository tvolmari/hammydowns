

<div class="step a-item" id="csor">
    
<form method='post' action='' id="order_form">
<?php
    $order = new Order();
    $order_info = $order->GetOrder($_SESSION['orderid']);
    $payment_method = $order_info->payment_method;
    $ship_method = $order_info->shipping_method;
    $ship_amount = $order_info->shipping_cost;
    $ooder_total = $order->CalcOrderTotal($_SESSION['orderid']);
?>

<?php
$settings = maybe_unserialize(get_option('_wpmp_settings'));
$currency_sign = get_option('_wpmp_curr_sign','$');
//print_r($currency_sign); echo "hello world";
 
//calculate shipping
if($settings['calc_shipping']==1){
    $ship=wpmp_calculate_shipping();
   
     $shipping_row = '<tr><td colspan=5 align=right>'.__('Shipping','wpmarketplace').' ('.$ship['method'].'):</td><td class="amt" id="s_cost">'.$currency_sign.number_format($ship['cost'],2,".","").'</td></tr>';
}else{
    $shipping_row="";
}

//calculate tax

$tax_summery=$order->wpmp_calculate_tax();

if(count($tax_summery)>0){
    foreach($tax_summery as $taxrow){
        $tax_row.="<tr><td colspan=5 align=right>".$taxrow['label'].":</td><td class='amt' >".$currency_sign.number_format($taxrow['rates'],2,".","")."</td></tr>"; 
    }
}else{
    $tax_row="";
}



 
 $grand_total = '<tr><td colspan=5 align=right>'.__('Grand Total:','wpmarketplace').'</td><td id="g_total" class="amt">'.$currency_sign.number_format($ooder_total,2,".","").'</td></tr>';
 
    global $wpdb;
    $cart_data = wpmp_get_cart_data();
    //print_r($cart_data);
    
    
$cart = "<table class='wpdm_cart'>"
        . "<tr class='cart_header'>"
        . "<th style='width:20px !important'></th>"
        . "<th>".__("Title","wpmarketplace")."</th>"
        . "<th>".__("Unit Price","wpmarketplace")."</th>"
        . "<th>".__("Role Discount","wpmarketplace")."</th>"
        . "<th> ".__("Coupon Code","wpmarketplace")."</th>"
        . "<th>".__("Quantity","wpmarketplace")."</th>"
        . "<th class='amt'>".__("Total","wpmarketplace")."</th>"
        . "</tr>";

if(is_array($cart_data)){
    //print_r($cart_items);
foreach($cart_data as $item){
    //echo "<pre>" ;  print_r($item); echo "</pre>";
    //filter for adding various message after cart item
    $cart_item_info="";
    $cart_item_info = apply_filters("wpmp_cart_item_info", $cart_item_info, $item['ID']);
    if(isset($item['item']) && !empty($item['item'])):
        
        foreach ($item['item'] as $key => $var):
        //echo "<pre>" ;  print_r($item['item']); echo "</pre>";
            if(isset($var['coupon_amount']) && $var['coupon_amount'] != ""){
                $discount_amount=$var['coupon_amount'];
                $discount_style="style='color:#008000; text-decoration:underline;'";
                $discount_title='Discounted $'.$discount_amount." for coupon code '{$item['coupon']}'";
            } else{ 
                $discount_amount="";
                $discount_style="";
                $discount_title="";
                
            }
            
            if($var['error'] != ""){
                $coupon_style="style='border:1px solid #ff0000;'";
                $title=$var['error'];
                
            } else {
                $coupon_style="";
                $title="";
                
            }    
        
            if($var['variations'])
                $variation = "<small><i>".implode(", ",$var['variations'])."</i></small>";
            $cart .= "<tr id='cart_item_{$item['ID']}_{$key}'>"
                . "<td>"
                    . "<a class='wpmp_cart_delete_item' href='#' onclick='return wpmp_pp_remove_cart_item2({$item['ID']},{$key})'>"
                        . "<i class='icon icon-trash glyphicon glyphicon-trash'></i>"
                    . "</a>"
                . "</td>"
                . "<td class='cart_item_title'>{$item['post_title']}<br>$variation".$cart_item_info ."</td>"
                . "<td class='cart_item_unit_price' $discount_style ><span class='ttip' title='$discount_title'>".$currency_sign.number_format($item[price],2,".","")."</span></td>"
                . "<td class='' >"  .$currency_sign.number_format($var['discount_amount'],2,'.','') . "</td>"
                . "<td><input style='$coupon_style' title='$title' type='text' name='cart_items[$item[ID]][coupon]' value='{$item['coupon']}' id='$item[ID]' class='ttip' size=3 /></td>"
                . "<td class='cart_item_quantity'><input type='text' name='cart_items[$item[ID]][item][$key][quantity]' value='{$item['item'][$key]['quantity']}' size=3 /></td>"
                . "<td class='cart_item_subtotal amt'>".$currency_sign.number_format((($item['price']+$var['prices'])*$var['quantity'])-$var['discount_amount'] - $var['coupon_amount'],2,".","")."</td>"
                . "</tr>";
        endforeach;
        
        
    else:
        //echo "<pre>";        print_r($item); echo "</pre>";
    if($item['variations'])
    $variations .= "<small><i>".implode(", ",$item['variations'])."</i></small>";     
    
    if($item['coupon_amount']){
        $discount_amount=$item['coupon_amount'];
        $discount_style="style='color:#008000; text-decoration:underline;'";
        $discount_title='Discounted $'.$discount_amount." for coupon code '{$item['coupon']}'";
        
    } else{ 
        $discount_amount="";
        $discount_style="";
        $discount_title="";
        
    }
    if($item['error']){
        $coupon_style="style='border:1px solid #ff0000;'";
        $title=$item['error'];
        
    } else {
        $coupon_style="";
        $title="";
        
    }
        
    $cart .= "<tr id='cart_item_{$item[ID]}'>"
    . "<td>"
        . "<a class='wpmp_cart_delete_item' href='#' onclick='return wpmp_pp_remove_cart_item($item[ID])'>"
            . "<i class='icon icon-trash glyphicon glyphicon-trash'></i>"
        . "</a>"
    . "</td>"
    . "<td class='cart_item_title'>$item[post_title]<br>$variations".$cart_item_info."</td>"
    . "<td class='cart_item_unit_price' $discount_style ><span class='ttip' title='$discount_title'>".$currency_sign.number_format($item[price],2,".","")."</span></td>"
    . "<td class=''>".$currency_sign.number_format($item['discount_amount'],2,'.','')."</td>"
    . "<td><input style='$coupon_style' title='$title' type='text' name='cart_items[$item[ID]][coupon]' value='$item[coupon]' id='$item[ID]' class='ttip' size=3 /></td>"
    . "<td class='cart_item_quantity'><input type='text' name='cart_items[$item[ID]][quantity]' value='$item[quantity]' size=3 /></td>"
    . "<td class='cart_item_subtotal amt'>".$currency_sign.number_format((($item['price']+$item['prices'])*$item['quantity'])-$item['coupon_amount'] - $item['discount_amount'],2,".","")."</td>"
    . "</tr>";
    endif;
    
}}
$extra_row = '';
$cart .= apply_filters('wpmp_ckeckout_extra_row',$extra_row);
$cart .= "

<tr><td colspan=5 align=right>".__("Cart Subtotal:","wpmarketplace")."</td><td class='amt' id='wpmp_cart_subtotal'>".$currency_sign.wpmpz_get_cart_total()."</td></tr>
".$tax_row."


".$shipping_row."
".$grand_total."
</table>





";
echo $cart;
?>
 <p id="order_comments_field" class="form-row notes">
                    <label  class="" for="order_comments"><?php echo __("Order Notes","wpmarketplace"); ?></label>
                    <textarea rows="2" cols="5" placeholder="Notes about your order, e.g. special notes for delivery." id="order_comments" class="input-text" name="order_comments"></textarea>
 </p>
<button id="order_back" class="button btn" type="button"><?php echo __("Back","wpmarketplace");?></button> 
<button id="order_btn" class="button btn btn-success" type="submit"><?php echo __("Place Order","wpmarketplace");?></button>
<input type='hidden' name='payment_system' id="payment_system" value='<?php echo $payment_method;?>' />
<input type='hidden' name='order_total' id="order_total" value='<?php echo $payment_method;?>' />
<input type='hidden' name='ship_method' id="ship_method" value='<?php echo $ship_method;?>' />
<input type='hidden' name='ship_currency' id="ship_currency" value='' />
<input type='hidden' name='ship_amount' id="ship_amount" value='<?php echo $ship_amount;?>' />
</form>
<div id="wpmpplaceorder"></div>
</div>
