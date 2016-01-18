<?php
function wpmp_show_cart(){
    global $wpdb;
    wpmp_calculate_discount();
    $cart_data = wpmp_get_cart_data();
    //echo "<pre>"; print_r($cart_data); echo "</pre>";
    include(WP_PLUGIN_DIR."/wpmarketplace/tpls/cart.php");
    return $cart;
}



//checking product coupon whether valid or not
function check_coupon($pid,$coupon){
    @extract(get_post_meta($pid,"wpmp_list_opts",true));
    
    if(is_array($coupon_code)){
        foreach($coupon_code as $key=> $val){
            if($val==$coupon)
                return $coupon_discount[$key];
                //return $coupon_code[$key];
        }
    }
    return 0;
}

function wpmp_add_to_cart(){ 
    if(isset($_POST['add_to_cart']) && $_POST['add_to_cart']=="add"){        
        global $wpdb, $post, $wp_query, $current_user;    
        $settings = maybe_unserialize(get_option('_wpmp_settings'));
        $pid = isset($_REQUEST['wpmp_add_to_cart']) ? $_REQUEST['wpmp_add_to_cart']:$_POST['pid'];
        $pid = apply_filters("wpmp_add_to_cart", $pid);
        if($pid<=0) return;
        
        $sales_price=0;
        @extract(get_post_meta($pid,"wpmp_list_opts",true));
         
        $cart_data = wpmp_get_cart_data();
        
        $q = $_REQUEST['quantity']?intval($_REQUEST['quantity']):1;
        if($q<1) $q = 1;
        
        //$q += $cart_data[$pid]['quantity'];           
        $base_price = wpmp_product_price($pid);    
        //
        if(!isset($_REQUEST['variation'])) {
            $_REQUEST['variation'] = "";
        }
        //if product id already exist :D
        if(array_key_exists($pid, $cart_data)){
            //print_r($cart_data); die('cart data');
            if(isset($cart_data[$pid]['multi']) && $cart_data[$pid]['multi']==1){
                $product_data = $cart_data[$pid]['item'];
                $check = false;
                foreach ($product_data as $key => $item):
                    //check same variation exist or not
                    if(wpmp_array_diff($item['variation'], $_REQUEST['variation'])==true){
                        //you are lucky, just incremnet qunatity value 
                        $cart_data[$pid]['item'][$key]['quantity'] += $q;
                        $cart_data[$pid]['quantity'] += $q;
                        $check = true;
                        break;
                    }
                endforeach;
                if($check == false){
                    //add this item as new item
                    
                    $cart_data[$pid]['item'][] = array(
                        'quantity'=>$q,
                        'variation'=>$_POST['variation']
                    );
                    $cart_data[$pid]['quantity'] += $q;
                    
                }
            }
            else {
                if(wpmp_array_diff($cart_data[$pid]['variation'] , $_REQUEST['variation'])==true){
                    //wow just increment product 
                    $cart_data[$pid]['quantity'] += $q; 
                }
                
                else {
                    //badluck implement new method
                    
                    //$q += $cart_data[$pid]['quantity'];
                    $old_qty = $cart_data[$pid]['quantity'];
                    $old_variation = $cart_data[$pid]['variation'];
                    $coupon = isset($cart_data[$pid]['coupon']) ? $cart_data[$pid]['coupon'] : '';
                    $coupon_amount = isset($cart_data[$pid]['coupon_amount']) ? $cart_data[$pid]['coupon_amount'] : '';
                    $discount_amount = isset($cart_data[$pid]['discount_amount']) ? $cart_data[$pid]['discount_amount'] : '';
                    $prices = isset($cart_data[$pid]['prices']) ? $cart_data[$pid]['prices'] : '';
                    $variations = isset($cart_data[$pid]['variations']) ? $cart_data[$pid]['variations'] : '';
                    $new_data = array(
                        'quantity'=>$q,
                        'variation'=>$_POST['variation'],
                    );
                    $cart_data[$pid] = array();
                    $cart_data[$pid]['multi'] = 1;
                    $cart_data[$pid]['quantity'] = $q+$old_qty;
                    $cart_data[$pid]['price'] = $base_price;
                    $cart_data[$pid]['coupon'] = $coupon;
                    $cart_data[$pid]['item'][] = array(
                        'quantity' => $old_qty, 
                        'variation' => $old_variation,
                    );
                    $cart_data[$pid]['item'][] = $new_data;
                    
                }
                
                
            }
        }
        
        else {
            //new item
            $cart_data[$pid] = array('quantity'=>$q,'variation'=>$_POST['variation'],'price'=>$base_price);       
        }
        
       // echo "<pre>";        print_r($cart_data); echo "</pre>";
        wpmp_update_cart_data($cart_data);
        
        wpmp_calculate_discount();
        
        //echo "<pre>";        print_r($cart_data); echo "</pre>";
        //die();
        $settings = get_option('_wpmp_settings');
        
        /* AJAX check  */
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo get_permalink($settings['page_id']);
            die();
        }
        
        if($settings['wpmp_after_addtocart_redirect']==1){
            header("location: ".get_permalink($settings['page_id']));
        }
        else header("location: ".$_SERVER['HTTP_REFERER']);
        die();
    }
    
}

function wpmp_remove_cart_item(){
    if(!isset($_REQUEST['wpmp_remove_cart_item']) || $_REQUEST['wpmp_remove_cart_item']<=0) return;    
    $cart_data = wpmp_get_cart_data();
    if(isset($_REQUEST['item_id'])){
        unset($cart_data[$_REQUEST['wpmp_remove_cart_item']]['item'][$_REQUEST['item_id']]);
        if(empty($cart_data[$_REQUEST['wpmp_remove_cart_item']]['item'])) {
            unset($cart_data[$_REQUEST['wpmp_remove_cart_item']]);
        }
    }
    else{   
        unset($cart_data[$_REQUEST['wpmp_remove_cart_item']]);    
    }
    wpmp_update_cart_data($cart_data);
    $ret['cart_subtotal'] = wpmp_get_cart_subtotal();
    $ret['cart_discount'] = wpmp_get_cart_discount();
    $ret['cart_total'] = wpmp_get_cart_total();
    die(json_encode($ret));
}

function wpmp_update_cart(){
    if(!isset($_REQUEST['wpmp_update_cart']) || (isset($_REQUEST['wpmp_update_cart']) && $_REQUEST['wpmp_update_cart']<=0)) return;
    //here i need to change...
//    echo "<pre>";
//    print_r($_POST['cart_items']);
//    echo "</pre>";
//    die();
    $data = $_POST['cart_items'];
    $cart_data = wpmp_get_cart_data(); //get previous cart data
    foreach ($cart_data as $pid => $cdt){
        if(isset($data[$pid]['coupon']) && trim($data[$pid]['coupon']) != '') {
            $cart_data[$pid]['coupon'] = stripslashes($data[$pid]['coupon']);
            
        }
        else {
            unset($cart_data[$pid]['coupon']);
        }
        if(isset($data[$pid]['item'])) {
            //print_r($data[$pid]['item']);
            foreach ($data[$pid]['item'] as $key => $val){
                if(isset($val['quantity'])) {
                    if($val['quantity']<1) $val['quantity'] = 1;
                    $val['quantity'] = intval($val['quantity']);
                    $cart_data[$pid]['item'][$key]['quantity'] = $val['quantity'];
                }
                
                if(isset($cart_data[$pid]['item'][$key]['coupon_amount'])) {
                    unset($cart_data[$pid]['item'][$key]['coupon_amount']);
                }
                if(isset($cart_data[$pid]['item'][$key]['discount_amount'])) {
                    unset($cart_data[$pid]['item'][$key]['discount_amount']);
                }
            }
        }
        else {
            if(isset($data[$pid]['quantity'])) {
                if($data[$pid]['quantity']<1) $data[$pid]['quantity'] = 1;
                $val['quantity'] = intval($val['quantity']);
                $cart_data[$pid]['quantity'] = $data[$pid]['quantity'];
            }
            
            if(isset($cart_data[$pid]['coupon_amount'])) {
                unset($cart_data[$pid]['coupon_amount']);
            }
        }
    }
    
    wpmp_update_cart_data($cart_data);
    
    
    $ret['cart_subtotal'] = wpmp_get_cart_subtotal();
    $ret['cart_discount'] = wpmp_get_cart_discount();
    $ret['cart_total'] = wpmp_get_cart_total();
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    die(json_encode($ret));
    }
    wpmp_show_cart();
}

function wpmp_get_cart_data(){
    global $current_user;
    if(is_user_logged_in()){    
        get_currentuserinfo();
        $cart_id = $current_user->ID."_cart";                
    } else {
    $cart_id = md5($_SERVER['REMOTE_ADDR'])."_cart";
    }
    $cart_data = maybe_unserialize(get_option($cart_id));
    
    //adjust cart id after user log in
    if(is_user_logged_in()&&!$cart_data){
        $cart_id = md5($_SERVER['REMOTE_ADDR'])."_cart";    
        $cart_data = maybe_unserialize(get_option($cart_id));
        delete_option($cart_id);
        $cart_id = $current_user->ID."_cart";                
        update_option($cart_id, $cart_data);
    }
    
    return $cart_data?$cart_data:array();
}

function wpmp_update_cart_data($cart_data){
    //echo "<pre>";
    //print_r($cart_data);
    //die();
    global $current_user;
    if(is_user_logged_in()){    
    get_currentuserinfo();
    $cart_id = $current_user->ID."_cart";       
    } else {
    $cart_id = md5($_SERVER['REMOTE_ADDR'])."_cart";
    }
   //check enable stock or not
   $settings = maybe_unserialize(get_option('_wpmp_settings')); 
   //check if quantity of product is more than stock
   
   //here i need to develop for multiple product
   if($settings['stock']['enable']==1){ 
        foreach($cart_data as $pid=>$cartitem){
            $post_meta=array();
            $post_meta=get_post_meta($pid,"wpmp_list_opts",true);
            if($post_meta['manage_stock']==1){
                if(isset($cartitem['item'])){
                    $cnt = 0;
                    //
                    foreach($cartitem['item'] as $a => $b):
                       $cnt += $b['quantity'];
                    endforeach;
                    if($cnt>$post_meta['stock_qty']){
                        $totstock = $post_meta['stock_qty'];
                        foreach($cartitem['item'] as $a => $b):
                            if($b['quantity']>$totstock){
                                $cart_data[$pid]['item'][$a]['qunatity'] = $totstock;
                                //now if another product exist 
                                //do whatever you want to do man
                                
                            }
                            $totstock -= $b['quantity'];
                            
                        endforeach;
                    }
                }
                else{
                    if($cartitem['quantity']>$post_meta['stock_qty']){
                        $cart_data[$pid]['quantity'] = $post_meta['stock_qty'];
                    }
                }
                
                
            }
        }
   }
    
    $cart_data = update_option($cart_id, $cart_data);
    return $cart_data;
}

function wpmp_get_cart_items(){
    global $current_user, $wpdb;    
    $cart_data = wpmp_get_cart_data();    
    return ($cart_data);
}

function wpmp_calculate_discount(){
    //echo "I'm here ";
    global $current_user;
    get_currentuserinfo();
    $role = $current_user->roles[0];
    $role = $role?$role:'guest';
    $discount_r = 0;
    $cart_items = wpmp_get_cart_items();
    $total = 0;
    
    if(is_array($cart_items)){
    //$lprices = array();
    foreach($cart_items as $pid=>$item)    {
    
        
        $cart_items[$pid]['ID'] = $pid;
        $cart_items[$pid]['post_title'] = get_the_title($pid);
        
        $prices=0;
        $variations="";
        $svariation = array();
        $lvariation = array();
        $lvariations = array();
        $lprices = array();
        
        $opt = get_post_meta($pid,"wpmp_list_opts",true);
        @extract($opt);
        
            foreach($variation as $key=>$value){
                foreach($value as $optionkey=>$optionvalue){
                  if($optionkey!="vname" && $optionkey != 'multiple'){
                      
                      if(isset($item['multi']) && ($item['multi'] == 1)){
           
                            foreach ($item['item'] as $a => $b) { //different variations, $b is single variation contain variation and quantity
                                if($b['variation']):
                                    //$lprices[$a] = 0;
                                    foreach ($b['variation'] as $c):
                                        if($c == $optionkey) {
                                            $lprices[$a] += $optionvalue['option_price'];
                                            $lvariation[$a][] = $optionvalue['option_name'].": ".($optionvalue['option_price']>0?'+':'').$currency_sign.number_format(doubleval($optionvalue['option_price']),2,".","");
                                        }
                                    endforeach;
                                endif;

                            }
                       }
                      
                      else{
                        if(isset($item['variation']))
                        foreach($item['variation'] as $var){                   
                            if($var==$optionkey){
                                $prices+=$optionvalue['option_price'];
                                $svariation[] = $optionvalue['option_name'].": ".($optionvalue['option_price']>0?'+':'').$currency_sign.number_format(doubleval($optionvalue['option_price']),2,".","");
                            }
                        }    
                     }
                     
                     
                }
            }     
        }
        
       
       
        
        if(trim($item['coupon'])!='') $valid_coupon=check_coupon($pid,$item['coupon']);
        else $valid_coupon = false;
        //echo $valid_coupon . ' :D ';
        
        if(!isset($item['multi'])){
            $cart_items[$pid]['prices'] = $prices;
            $cart_items[$pid]['variations'] = $svariation;
            if(is_numeric($valid_coupon) && $valid_coupon != false) {
                $cart_items[$pid]['coupon_amount'] =  (($item['price']+$prices)*$item['quantity']*$valid_coupon)/100;  
                $cart_items[$pid]['discount_amount'] = (((($item['price']+$prices)*$item['quantity'] ) - $cart_items[$pid]['coupon_amount'] ) * $opt['discount'][$role])/100 ;
                
            }
            else {
                $cart_items[$pid]['discount_amount'] = ((($item['price']+$prices)*$item['quantity'] )  * $opt['discount'][$role])/100 ;
            }
            if($valid_coupon == false) {
                $cart_items[$pid]['error'] = "No Valid Coupon Found";
            }
            else {
                unset($cart_items[$pid]['error']);
            }
            
        }
        elseif(isset($item['multi']) && $item['multi'] == 1) {
            
            foreach ($lprices as $key => $value):
                $cart_items[$pid]['item'][$key]['prices'] = $value;
                $cart_items[$pid]['item'][$key]['variations'] = $lvariation[$key];
                
                if($valid_coupon != 0) {
                    $cart_items[$pid]['item'][$key]['coupon_amount'] =   (($item['price']+$value)*$item['item'][$key]['quantity']*$valid_coupon)/100;
                    $cart_items[$pid]['item'][$key]['discount_amount'] =   (((($item['price']+$value)*$item['item'][$key]['quantity']) - $cart_items[$pid]['item'][$key]['coupon_amount'])* $opt['discount'][$role])/100 ;
                }
                else {
                    $cart_items[$pid]['item'][$key]['discount_amount'] =   ((($item['price']+$value)*$item['item'][$key]['quantity'])* $opt['discount'][$role])/100 ;
                }
                
                if($valid_coupon == false) {
                    $cart_items[$pid]['item'][$key]['error'] = "No Valid Coupon Found";
                }
                
            endforeach;
        }
        
        
        
        //
        
    }
    wpmp_update_cart_data($cart_items);
                }
}



function wpmpz_get_cart_total(){
    $cart_items = wpmp_get_cart_items();
 
    $total = 0;
    if(is_array($cart_items)){

        foreach($cart_items as $pid=>$item)    {
            if(isset($item['item'])){
                foreach ($item['item'] as $key => $val){
                    $role_discount = isset($val['discount_amount']) ? $val['discount_amount']: 0;
                    $coupon_discount = isset($val['coupon_amount']) ? $val['coupon_amount']: 0;
                    $total += (($item['price'] + $val['prices']) * $val['quantity']) - $role_discount - $coupon_discount; 
                }
            }
            else {
                $role_discount = isset($item['discount_amount']) ? $item['discount_amount']: 0;
                $coupon_discount = isset($item['coupon_amount']) ? $item['coupon_amount']: 0;
                $total += (($item['price'] + $item['prices'])* $item['quantity']) - $role_discount - $coupon_discount;
            }
        }
    
    }
    
    $total = apply_filters('wpmp_cart_subtotal',$total);
    return number_format($total,2,".","");
}


function wpmp_get_cart_subtotal(){
    $cart_items = wpmp_get_cart_items();
 
    $total = 0;
    if(is_array($cart_items)){

        foreach($cart_items as $pid=>$item)    {
            if(isset($item['item'])){
                foreach ($item['item'] as $key => $val){
                    $role_discount = isset($val['discount_amount']) ? $val['discount_amount']: 0;
                    $coupon_discount = isset($val['coupon_amount']) ? $val['coupon_amount']: 0;
                    $total += $item['price'] + $val['prices'] + $role_discount + $coupon_discount; 
                }
            }
            else {
                $role_discount = isset($item['discount_amount']) ? $item['discount_amount']: 0;
                $coupon_discount = isset($item['coupon_amount']) ? $item['coupon_amount']: 0;
                $total += $item['price'] + $item['prices'] + $role_discount + $coupon_discount;
            }
        }
    
    }
    
    $total = apply_filters('wpmp_cart_subtotal',$total);
    return number_format($total,2,".","");
}


//calculating discount
function wpmp_get_cart_discount(){
    global $current_user;
    get_currentuserinfo();
    $role = $current_user->roles[0];
    $role = $role?$role:'guest';
    //$subtotal = wpmp_get_cart_subtotal();
    $cart_items = wpmp_get_cart_items();
    $discount_r=0;
    
    //print_r($cart_items);
    foreach($cart_items as $pid=>$item){
               
       $opt = get_post_meta($pid,'wpmp_list_opts',true); 
       //print_r($opt);
       $prices=0;
       $lprices = array();
       
        @extract(get_post_meta($pid,"wpmp_list_opts",true));
        
            foreach($variation as $key=>$value){
                foreach($value as $optionkey=>$optionvalue){
                  if($optionkey!="vname" && $optionkey != 'multiple'){
                      if($item['variation']){
                        foreach($item['variation'] as $var){                   
                            if($var==$optionkey){
                                $prices+=$optionvalue['option_price'];
                                
                            }
                        }    
                     }
                     
                     elseif(isset($item['item']) && !empty ($item['item'])){
           
                            foreach ($item['item'] as $a => $b) { //different variations, $b is single variation contain variation and quantity
                                if($b['variation']):
                                    //$lprices[$a] = 0;
                                    foreach ($b['variation'] as $c):
                                        if($c == $optionkey) {
                                            $lprices[$a] += $optionvalue['option_price'];
                                        }
                                    endforeach;
                                endif;

                            }
                       }
                }
            }     
        }
       
       
       if(!empty($lprices)):
           foreach($lprices as $key => $val):
            $discount_r += ((($item['price']+$val)*$item['item'][$key]['quantity'])*$opt['discount'][$role])/100;
           endforeach;
       else:
           $discount_r +=  ((($item['price']+$prices)*$item['quantity'])*$opt['discount'][$role])/100;
       endif;
       
       
    }
    return number_format($discount_r,2,".","");
}
//calculating subtotal by subtracting discount
function wpmp_get_cart_total(){   
    return number_format((wpmp_get_cart_subtotal()-wpmp_get_cart_discount()),2,".","");
}

function wpmp_grand_total(){
    $tax=wpmp_calculate_tax();
    return number_format((wpmp_get_cart_subtotal()+$tax['rate']-wpmp_get_cart_discount()),2,".","");
}
//shipping calculation
function wpmp_calculate_shipping(){
    $ship=array();
    $order = new Order();
    $order_info=$order->GetOrder($_SESSION['orderid']);
    $ship['method']=$order_info->shipping_method;
    $ship['cost']=$order_info->shipping_cost;
    return $ship;
}
//tax calculation
function wpmp_calculate_tax(){
    $cartsubtotal=wpmp_get_cart_subtotal();
    $taxr=array();
    $order = new Order();
    $order_info=$order->GetOrder($_SESSION['orderid']);
    $bdata=unserialize($order_info->billing_shipping_data);
    $settings = maybe_unserialize(get_option('_wpmp_settings'));
    if($settings['tax']['enable']==1){
        if($settings['tax']['tax_rate']){
            foreach($settings['tax']['tax_rate'] as $key=> $rate){
                if($rate['country']){
                    foreach($rate['country'] as $r_country){
                        if($r_country==$bdata['shippingin']['country']){
                            $taxr['label']= $rate['label'];
                            $taxr['rate']= (($cartsubtotal*$rate['rate'])/100);
                            break;
                        }
                    } 
                }
            }
        }
    }
   
    return $taxr;
}

function wpmp_empty_cart(){
    global $current_user;
    if(is_user_logged_in()){    
    get_currentuserinfo();
    $cart_id = $current_user->ID."_cart";       
    } else {
    $cart_id = md5($_SERVER['REMOTE_ADDR'])."_cart";
    }
    delete_option($cart_id);
    if($_SESSION['orderid']){
        $_SESSION['orderid'] = '';
        unset($_SESSION['orderid']);
    }
}

function wpmp_checkout(){
        wp_enqueue_script('jquery');
        $settings = get_option('_wpmp_settings'); 
        include(WP_PLUGIN_DIR."/wpmarketplace/tpls/checkout.php");
}

function wpmp_addtocart_js(){
    if(get_option('wpmp_ajaxed_addtocart',0)==0) return;
?>
<script language="JavaScript">
<!--
  jQuery(function(){
       jQuery('.wpdm-pp-add-to-cart-link').click(function(){
            if(this.href!=''){
                var lbl;
                var obj = jQuery(this);
                lbl = jQuery(this).html();
                jQuery(this).html('<img src="<?php echo plugins_url();?>/wpdm-premium-packages/images/wait.gif"/> adding...');
                jQuery.post(this.href,function(){
                   obj.html('added').unbind('click').click(function(){ return false; });
                })
            
            }
       return false;     
       });
       
       jQuery('.wpdm-pp-add-to-cart-form').submit(function(){
           
           var form = jQuery(this);
           var fid = this.id;
           form.ajaxSubmit({
               'beforeSubmit':function(){                   
                  jQuery('#submit_'+fid).val('adding...').attr('disabled','disabled');
               },
               'success':function(res){
                   jQuery('#submit_'+fid).val('added').attr('disabled','disabled');
               }
           });
            
       return false;     
       });
  });
//-->
</script>
<?php    
}


function wpmp_buynow($content){    
    global $wpdb, $post, $wp_query, $current_user;    
    $settings = maybe_unserialize(get_option('_wpmp_settings'));
    if(!isset($wp_query->query_vars['wpmarketplace'])||$wp_query->query_vars['wpmarketplace']==''||!isset($_REQUEST['buy'])||$_REQUEST['buy']=='')
    return $content;    
    @extract(get_post_meta($post->ID,"wpmp_list_opts",true));
    wpmp_add_to_cart($post->ID, $_REQUEST['buy']);    
    return '';
}

function update_os(){
    global $wpdb;
    //order status change hook, order_id, new_status_message
    apply_filters("order_status_completed",$_POST['order_id'],$_POST['status']);
    $wpdb->update("{$wpdb->prefix}mp_orders",array('order_status'=>$_POST['status']),array('order_id'=>$_POST['order_id']));
    
    $settings = maybe_unserialize(get_option('_wpmp_settings'));
    
    
    //reduce stock 
    if($settings['stock']['enable']==1){  
        if($_POST['status']=="Completed"){
            if($settings['stock']['reduce_auto']==1)
                wpmp_reduce_stock($_POST['order_id']);
        }
    } 
    
    $siteurl=home_url("/");
    //email to customer of that order
    $userid=$wpdb->get_var("select uid from {$wpdb->prefix}mp_orders where order_id='".$_POST['order_id']."'");
    $user_info = get_userdata($userid);
    $admin_email=get_bloginfo("admin_email");
    //$from=home_url("/");
    $email = array();
    $subject="Order Status Changed";
    $message="The order {$_POST['order_id']} is changed to {$_POST['status']}"."\n Customer Name is ".$user_info->user_firstname." ".$user_info->lastname."\n Email is ".$user_info->user_email;
    $email['subject']=$subject;
    $email['body']=$message;
    $email['headers'] = 'From:  <'.$admin_email.'>' . "\r\n";
    $email = apply_filters("order_status_change_email", $email);    
    wp_mail($user_info->user_email,$email['subject'],$email['body'],$email['headers']);        
    //wp_mail($admin_email,$email['subject'],$email['body'],$email['headers']);
    //print_r($email);   
    die(__('Order status updated',"wpmarketplace"));
}

function update_ps(){
    global $wpdb;
    $wpdb->update("{$wpdb->prefix}mp_orders",array('payment_status'=>$_POST['status']),array('order_id'=>$_POST['order_id']));
    die(__('Payment status updated',"wpmarketplace"));
}

function ajaxinit(){
if(isset($_POST['action']) && $_POST['action']=='wpmp_pp_ajax_call'){    
    if(function_exists($_POST['execute']) && $_POST['execute'] === 'PayNow')
        call_user_func('PayNow',$_POST);
        else
        echo __("function not defined!","wpmarketplace");
        
    die();
}
}
  
function PayNow($post_data){    
    global $wpdb,$current_user;
    get_currentuserinfo();
    $order = new Order();
    $corder = $order->GetOrder($post_data['order_id']);    
    $payment = new Payment();
    if($post_data['payment_method']=='')  $post_data['payment_method'] = $corder->payment_method;
    $payment->InitiateProcessor($post_data['payment_method']);
    $payment->Processor->OrderTitle = 'WPMP Order# '.$corder->order_id;
    $payment->Processor->InvoiceNo = $corder->order_id;
    $payment->Processor->Custom = $corder->order_id;
    $payment->Processor->Amount = number_format($corder->total,2,".","");
    echo $payment->Processor->ShowPaymentForm(1);      
} 
function ProcessOrder(){                                                                       
    global $current_user;
    get_currentuserinfo();
    $order = new Order();    
    if(preg_match("@\/payment\/([^\/]+)\/([^\/]+)@is",$_SERVER['REQUEST_URI'],$process)){
        $gateway = $process[1];
        $page = $process[2];        
        $_POST['invoice'] = array_shift(explode("_",$_POST['invoice']));
        $odata = $order->GetOrder($_POST['invoice']);        
        $current_user = get_userdata($odata->uid);
        $uname = $current_user->display_name;
        $uid = $current_user->ID;
        $email = $current_user->user_email;
                
        $myorders = get_option('_wpmp_users_orders',true);
        if($page=='notify'){
        if(!$uid) {
        $uname = str_replace(array("@",'.'),'',$_POST['payer_email']);   
        $password = $_POST['invoice'];
        $email = $_POST['payer_email'];
        $uid = wp_create_user($uname,$password,$_POST['payer_email']);
        $logininfo = "
         Username: $uname<br/>
         Password: $password<br/>
        ";
        }    
            
        
        $order->Update(array('order_status'=>$_POST['payment_status'],'payment_status'=>$_POST['payment_status'],'uid'=>$uid), $_POST['invoice']);        
        
        $sitename = get_option('blogname');
        $message = <<<MAIL
                    Hello {$uname},<br/>
                    Thanks for your business with us.<br/>                    
                    Please <a href="{$myorders}">click here</a> to view your purchased items.<br/>
                    {$myorders} <br/>
                    {$logininfo}                    
                    <br/><br/>
                    Regards,<br/>
                    Admin<br/>
                    <b>{$sitename}</b>
                    
MAIL;
        $headers = 'From: '.get_option('blogname').' <'.get_option('admin_email').'>' . "\r\n\\";
        wp_mail( $email, "You order on ".get_option('blogname'), $message, $headers, $attachments );        
        die("OK");
        }
       
        if($page=='return'&&$_POST['payment_status']=='Completed'){
            if(!$current_user->ID){
            $uname = str_replace(array("@",'.'),'',$_POST['payer_email']);   
            $password = $_POST['invoice'];
            $creds = array();
            $creds['user_login'] = $uname;
            $creds['user_password'] = $password;
            $creds['remember'] = true;
            $user = wp_signon( $creds, false );        
            }            
            die("<script>location.href='$myorders';</script>");
        } 
        
        die();
    }
}

function get_all_coupon($data){
    $total = 0;
    foreach($data as $pid => $item){
        $valid_coupon=check_coupon($pid,$item['coupon']);       
        if($valid_coupon != 0) {
            
            $total +=  ($item['item_total']*$item['quantity']*($valid_coupon/100));
        }
    }
    return $total;
    
}

function wpmp_clear_user_cartdata($user_login, $user) {
   delete_option($user->ID."_cart");
}
add_action('wp_login', 'wpmp_clear_user_cartdata', 10, 2);


function wpmp_array_diff($a, $b){
//    echo "<pre>";
//    print_r($a);
//    print_r($b);
//    echo "</pre>";
//    die();
    if(is_array($a)&&is_array($b)){
        if(count($a) != count($b)) {
            return false;
        }
        else {
            sort($a); sort($b);
            return $a == $b  ;
        }
    
    }
    else if($a == "" && $b == ""){
        return true;
    }
}