<?php
$settings = get_option('_wpmp_settings');
$currency_sign = get_option('_wpmp_curr_sign','$');
 
$cart = "<div class='wp-marketplace'>"
        . "<form method='post' class='abc' action='' name='cart_form' id='cart_form'>"
        . "<input type='hidden' name='wpmp_update_cart' value='1' />"
        . "<table class='wpdm_cart'>"
        . "<tr class='cart_header'>"
        . "<th style='width:20px !important'></th>"
        . "<th>".__("Title","wpmarketplace")."</th>"
        . "<th>".__("Unit Price","wpmarketplace")."</th>"
        . "<th> ".__("Role Discount","wpmarketplace")."</th>"
        . "<th> ".__("Coupon Code","wpmarketplace")."</th>"
        . "<th>".__("Quantity","wpmarketplace")."</th>"
        . "<th class='amt'>".__("Total","wpmarketplace")."</th>"
        . "</tr>";

if(is_array($cart_data)){
    //print_r($cart_data);
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
wpmpz_get_cart_total();
$extra_row = '';
$cart .= apply_filters('wpmp_cart_extra_row',$extra_row);

$cart .= "

<tr><td colspan=6 align=right class='text-right'>".__("Total:","wpmarketplace")."</td><td class='amt' id='wpmp_cart_total'>".     $currency_sign.number_format((double)str_replace(',','',wpmpz_get_cart_total()),2)."</td></tr>
<tr><td colspan=2><button type='button' class='btn btn-info ' onclick='location.href=\"".$settings['continue_shopping_url']."\"'><i class='icon-white icon-repeat glyphicon glyphicon-repeat'></i> ".__("Continue Shopping","wpmarketplace")."</button></td><td colspan=4 align=right class='text-right'><button class='btn btn-primary' type='button' onclick='document.getElementById(\"cart_form\").submit();'><i class='icon-white icon-edit glyphicon glyphicon-edit'></i> ".__("Update Cart","wpmarketplace")."</button> <button class='btn btn-success' type='button' onclick='location.href=\"".get_permalink($settings['check_page_id'])."\"'><i class='glyphicon glyphicon-shopping-cart icon-white icon-shopping-cart'></i> ".__("Checkout","wpmarketplace")."</button></td></tr>
</table>

</form></div>

<script language='JavaScript'>
<!--
    function  wpmp_pp_remove_cart_item(id){
    
           if(!confirm('Are you sure?')) return false;
           jQuery('#cart_item_'+id+' *').css('color','#ccc');
           jQuery.post('".home_url('?wpmp_remove_cart_item=')."'+id
           ,function(res){ 
           var obj = jQuery.parseJSON(res);
           
           jQuery('#cart_item_'+id).fadeOut().remove(); 
           jQuery('#wpmp_cart_total').html(obj.cart_total); 
           jQuery('#wpmp_cart_discount').html(obj.cart_discount); 
           jQuery('#wpmp_cart_subtotal').html(obj.cart_subtotal); });
           return false;
    }
    function  wpmp_pp_remove_cart_item2(id,item){
           if(!confirm('Are you sure?')) return false;
           jQuery('#cart_item_'+id+'_'+item+' *').css('color','#ccc');
           jQuery.post('".home_url('?wpmp_remove_cart_item=')."'+id + '&item_id='+item  
           ,function(res){ 
           var obj = jQuery.parseJSON(res);
           
           jQuery('#cart_item_'+id+'_'+item).fadeOut().remove(); 
           jQuery('#wpmp_cart_total').html(obj.cart_total); 
           jQuery('#wpmp_cart_discount').html(obj.cart_discount); 
           jQuery('#wpmp_cart_subtotal').html(obj.cart_subtotal); });
           return false;
    }
    
jQuery(function(){
    jQuery('.ttip').tooltip();
});
      
//-->
</script>

";

if(count($cart_data)==0) $cart = __("No item in cart.","wpmarketplace")."<br/><a href='".$settings['continue_shopping_url']."'>".__("Continue shopping","wpmarketplace")."</a>";
