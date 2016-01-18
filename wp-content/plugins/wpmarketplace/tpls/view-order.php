<?php
    
    global $wpdb;
    $order->items = unserialize($order->items);
    $oitems = $wpdb->get_results("select * from {$wpdb->prefix}mp_order_items where oid='{$order->order_id}'");
    $user = new WP_User( $order->uid );
    $role = $user->roles[0];
    $tax = $order1->wpmp_calculate_tax($order->order_id);
    
    $settings = maybe_unserialize(get_option('_wpmp_settings')); 
       
    
?>
<?php ob_start(); ?>
<table width="100%" cellspacing="0" class="widefat fixed">
<thead>
<tr><th align="left"><?php echo __("Item Name","wpmarketplace");?></th>
    <th align="left"><?php echo __("Unit Price","wpmarketplace");?></th>
    <th align="left"><?php echo __("Quantity","wpmarketplace");?></th>
    <th align="left"><?php echo __("Discount","wpmarketplace");?></th>
    <th align="left"><?php echo __("Coupon Code","wpmarketplace");?></th>
    <th align="left"><?php echo __("Coupon Discount","wpmarketplace");?></th>
    <th align="left"><?php echo __("Total","wpmarketplace");?></th>
    <th align="left"><?php echo __("Subtotal","wpmarketplace");?></th>
</tr>
</thead>
<?php 
$cart_data = unserialize($order->cart_data); 
//echo "<pre>"; print_r($cart_data); echo "</pre>";
if(is_array($cart_data) && !empty($cart_data)):
    $coupon_discount = 0;
    $role_discount = 0;
    $shipping = 0;
    $order_total = 0;
    foreach ($cart_data as $pid => $item):
        if(isset($item['item'])):
            foreach ($item['item'] as $id => $var):
                if(!isset($var['coupon_amount']) || $var['coupon_amount'] == "") {
                    $var['coupon_amount'] = 0;
                }

                if(!isset($var['discount_amount']) || $var['discount_amount'] == "") {
                    $var['discount_amount'] = 0;
                }
                if(!isset($var['prices']) || $var['prices']==""){
                    $var['prices'] = 0;
                }
                //echo $var['coupon_amount'] . ' ' . $var['discount_amount'] . "<br>";
            
            
                $coupon_discount += $var['coupon_amount'];
                $role_discount += $var['discount_amount'];
                $order_total += (($item['price'] + $var['prices']) * $var['quantity']) - $var['coupon_amount'] - $var['discount_amount'];
                $vari = isset($var['variations']) && !empty($var['variations']) ? implode(', ', $var['variations']) : ''
            ?>
                <tr>
                    <td><?php echo $item['post_title'] . '<br>' . $vari; ?></td>
                    <td><?php echo $currency_sign . $item['price']; ?></td>
                    <td><?php echo $var['quantity']; ?></td>
                    <td><?php echo $currency_sign . $var['discount_amount']; ?></td>
                    <td><?php echo $item['coupon']; ?></td>
                    <td><?php echo $currency_sign . $var['coupon_amount']; ?></td>
                    <td><?php echo $currency_sign ; echo ($item['price'] + $var['prices']) * $var['quantity']; ?></td>
                    <td><?php echo $currency_sign ; echo (($item['price'] + $var['prices']) * $var['quantity']) - $var['discount_amount'] - $var['coupon_amount']; ?></td>
                </tr>
            <?php
            endforeach;
        else:
            if(!isset($item['coupon_amount']) || $item['coupon_amount'] == "") {
                $item['coupon_amount'] = 0;
            }
            
            if(!isset($item['discount_amount']) || $item['discount_amount'] == "") {
                $item['discount_amount'] = 0;
            }
            
            if(!isset($item['prices']) || $item['prices'] == "") {
                $item['prices'] = 0;
            }
            
            //echo $item['coupon_amount'] . ' ' . $item['discount_amount'] . "<br>";
            
            $coupon_discount += $item['coupon_amount'];
            $role_discount += $item['discount_amount'];
            $order_total += (($item['price'] + $item['prices']) * $item['quantity']) - $item['coupon_amount'] - $item['discount_amount'];
            $vari = isset($item['variations']) && !empty($item['variations']) ? implode(', ', $item['variations']) : '';
        ?>
            <tr>
                <td><?php echo $item['post_title'] . '<br>' . $vari; ?></td>
                <td><?php echo $currency_sign . $item['price']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo $currency_sign . $item['discount_amount']; ?></td>
                <td><?php echo $item['coupon']; ?></td>
                <td><?php echo $currency_sign . $item['coupon_amount']; ?></td>
                <td><?php echo $currency_sign; echo ($item['price'] + $item['prices']) * $item['quantity']; ?></td>
                <td><?php echo $currency_sign; echo (($item['price'] + $item['prices']) * $item['quantity']) - $item['discount_amount'] - $item['coupon_amount']; ?></td>
            </tr>
        <?php
        endif;
    endforeach;
endif;
?>
</table>
<?php $content = ob_get_clean(); ?>




<div class="wrap">
    <div class="icon32"><img src='<?php echo plugins_url('wpmarketplace/images/order.png'); ?>' /></div>
    <h2>View Order <img id="lng" style="display: none;" src="images/loading.gif" /></h2>
    <div id="msg" style="padding: 5px 10px;display: none;" class="message updated"><?php echo __("Message", "wpmarketplace"); ?></div>
    <div style="float: right;width:400px;">
        <table class="widefat fixed">
            <tr><th align="left" colspan="2"><?php echo __("Order Summary", "wpmarketplace"); ?></th></tr>
            <tr><td><?php echo __("Order ID:", "wpmarketplace"); ?></td><td><?php echo $order->order_id; ?></td></tr>
            <tr><td><?php echo __("Coupon Discount:", "wpmarketplace"); ?></td><td><?php echo $currency_sign . $coupon_discount; ?></td></tr>
            <tr><td><?php echo __("Role Discount:", "wpmarketplace"); ?></td><td><?php echo $currency_sign . $role_discount; ?></td></tr>
            <?php
            if (count($tax) > 0) {
                foreach ($tax as $taxrow) {
                    ?>
                    <tr><td><?php echo $taxrow['label']; ?></td><td><?php echo $currency_sign . $taxrow['rates']; ?></td></tr>
                    <?php
                }
            }
            ?>
            <tr><td><?php echo __("Shipping:", "wpmarketplace"); ?></td><td><?php echo $currency_sign . $order->shipping_cost; ?></td></tr>
            <?php $ret = '';
            $ret = apply_filters('wpmp_admin_order_details',$ret,$order->order_id);
            if($ret != '') echo $ret;
            ?> 
            <tr><td><?php echo __("Order Total:", "wpmarketplace"); ?></td><td><?php echo $currency_sign . number_format($order->total, 2); ?></td></tr>
            <tr><td><?php echo __("Order Date:", "wpmarketplace"); ?></td><td><?php echo date("M d, Y", $order->date); ?></td></tr>
        </table>
    </div>
    <div style="float: left;width: 500px;">
        <table class="widefat fixed">
            <tr><th align="left" colspan="2"><?php echo __("Customer Info", "wpmarketplace"); ?></th></tr>
            <tr><td><?php echo __("Customer ID:", "wpmarketplace"); ?></td><td><?php echo $user->ID; ?></td></tr>
            <tr><td><?php echo __("Customer Name:", "wpmarketplace"); ?></td><td><?php echo $user->display_name; ?></td></tr>
            <tr><td><?php echo __("Customer Email:", "wpmarketplace"); ?></td><td><?php echo $user->user_email; ?></td></tr>
        </table>
    </div>
    <div style="clear: both;"></div>
    <h2 style="font-size: 12pt"><?php echo __("Order Items", "wpmarketplace"); ?></h2>

    <?php echo $content; ?>

    <br />
    <b><?php echo __("Order Status:", "wpmarketplace"); ?> 
        <select id="osv" name="order_status">                                    
            <option <?php if ($order->order_status == 'Pending') echo 'selected="selected"'; ?> value="Pending">Pending</option>
            <option <?php if ($order->order_status == 'Processing') echo 'selected="selected"'; ?> value="Processing">Processing</option>
            <option <?php if ($order->order_status == 'Completed') echo 'selected="selected"'; ?> value="Completed">Completed</option>
            <option <?php if ($order->order_status == 'Canceled') echo 'selected="selected"'; ?> value="Canceled">Canceled</option>
        </select>
    </b>   <input type="button" id="update_os" class="button button-secondary" value="Update">
    &nbsp;
    <b><?php echo __("Payment Status:", "wpmarketplace"); ?> 
        <select id="psv" name="payment_status">                                    
            <option <?php if ($order->payment_status == 'Pending') echo 'selected="selected"'; ?> value="Pending">Pending</option>
            <option <?php if ($order->payment_status == 'Processing') echo 'selected="selected"'; ?> value="Processing">Processing</option>
            <option <?php if ($order->payment_status == 'Completed') echo 'selected="selected"'; ?> value="Completed">Completed</option>
            <option <?php if ($order->payment_status == 'Canceled') echo 'selected="selected"'; ?> value="Canceled">Canceled</option>
        </select>
    </b>   <input id="update_ps" type="button" class="button button-secondary" value="Update">
    <input id="reduce_stock" type="button" class="button button-secondary" value="Reduce Stock">
    <input id="restore_stock" type="button" class="button button-secondary" value="Restore Stock">

</div>
<br /><br />
<?php
     //if($settings['stock']['reduce_auto']==1) echo "Automatic reduce stock is enabled";
?>
<script language="JavaScript">
<!--
  jQuery(function(){
     
      jQuery('#update_os').click(function(){
          jQuery('#lng').fadeIn();
          jQuery.post(ajaxurl,{action:'wpmp_ajax_call',execute:'update_os',order_id:'<?php echo $_GET[id]; ?>',status:jQuery('#osv').val()},function(res){
              jQuery('#msg').html(res).fadeIn();
              jQuery('#lng').fadeOut();
          });
      });
      
      jQuery('#update_ps').click(function(){
          jQuery('#lng').fadeIn();
          jQuery.post(ajaxurl,{action:'wpmp_ajax_call',execute:'update_ps',order_id:'<?php echo $_GET[id]; ?>',status:jQuery('#psv').val()},function(res){
              jQuery('#msg').html(res).fadeIn();
              jQuery('#lng').fadeOut();
          });
      });
      //reduce stock
      jQuery('#reduce_stock').click(function(){
          jQuery('#lng').fadeIn();
          jQuery.post(ajaxurl,{action:'wpmp_ajax_call',execute:'wpmp_reduce_stock',order_id:'<?php echo $_GET[id]; ?>'},function(res){
              jQuery('#msg').html(res).fadeIn();
              jQuery('#lng').fadeOut();
          });
      });
      //restore stock
      jQuery('#restore_stock').click(function(){
          jQuery('#lng').fadeIn();
          jQuery.post(ajaxurl,{action:'wpmp_ajax_call',execute:'wpmp_restore_stock',order_id:'<?php echo $_GET[id]; ?>'},function(res){
              jQuery('#msg').html(res).fadeIn();
              jQuery('#lng').fadeOut();
          });
      });
      
      
  });
//-->
</script>
