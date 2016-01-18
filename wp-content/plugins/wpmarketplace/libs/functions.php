<?php

function wpmp_popular_files($start,$limit){
    global $wpdb;
    $files=$wpdb->get_results("select *, sum(oi.price) as price_total from {$wpdb->prefix}mp_orders o inner join {$wpdb->prefix}mp_order_items oi on oi.oid=o.order_id inner join {$wpdb->prefix}posts p on oi.pid=p.ID where p.post_type='wpmarketplace'and o.payment_status='Completed'  group by  oi.pid order by price_total desc limit $start, $limit");
    
    return $files;
}
//number of popular files
function wpmp_total_popular_files(){
    global $wpdb;
    $files=$wpdb->get_var("select distinct count(distinct pid) from {$wpdb->prefix}mp_orders o inner join {$wpdb->prefix}mp_order_items oi on oi.oid=o.order_id inner join {$wpdb->prefix}posts p on oi.pid=p.ID where p.post_type='wpmarketplace' and o.payment_status='Completed'");    
    
    return $files;
}
//number of total sales
function wpmp_total_purchase($pid=''){
     global $wpdb;
     if(!$pid) $pid = get_the_ID();
     $sales = $wpdb->get_var("select count(*) from {$wpdb->prefix}mp_orders o, {$wpdb->prefix}mp_order_items oi where oi.oid=o.order_id and oi.pid='$pid' and o.payment_status='Completed'");
     return $sales;
}
 
//the function for adding the product from the frontend
function wpmp_add_product(){ 
    if(isset($_POST['__product_wpmp']) && wp_verify_nonce($_POST['__product_wpmp'],'wpmp-product')&&$_POST['task']==''){ //echo "here";exit; 
        if( $_POST['post_type']=="wpmarketplace"){
            global $current_user, $wpdb;
            get_currentuserinfo();    
            $settings = get_option('_wpmp_settings');
            $pstatus=$settings['fstatus']?$settings['fstatus']:"draft";
            $my_post = array(
             'post_title' => $_POST['product']['post_title'],
             'post_content' => $_POST['product']['post_content'],
             'post_excerpt' => $_POST['product']['post_excerpt'],
             'post_status' => $pstatus,
             'post_author' => $current_user->ID,
             'post_type' => "wpmarketplace" 
             
            );

            if($_POST['id']){
              //update post
              $my_post['ID']=$_REQUEST['id'];
              wp_update_post( $my_post );
               $postid= $_REQUEST['id'];  
            }else{
              //insert post
              $postid=wp_insert_post( $my_post );
            }


            update_post_meta($postid,"wpmp_list_opts",$_POST['wpmp_list']);  

             //set the product type
            wp_set_post_terms($postid,$_POST['product_type'], "ptype"); 

            foreach($_POST['wpmp_list'] as $k=>$v){
                update_post_meta($postid,$k,$v);
             
            }


            if($_POST['wpmp_list']['fimage']){
              $wp_filetype = wp_check_filetype(basename($_POST['wpmp_list']['fimage']), null );
              $attachment = array(
                 'post_mime_type' => $wp_filetype['type'],
                 'post_title' => preg_replace('/\.[^.]+$/', '', basename($_POST['wpmp_list']['fimage'])),
                 'post_content' => '',
                 'guid' => $_POST['wpmp_list']['fimage'],
                 'post_status' => 'inherit'
              );
              $attach_id = wp_insert_attachment( $attachment, $_POST['wpmp_list']['fimage'], $postid );
              // you must first include the image.php file
              // for the function wp_generate_attachment_metadata() to work
              require_once( ABSPATH . 'wp-admin/includes/image.php' );
              $attach_data = wp_generate_attachment_metadata( $attach_id, $_POST['wpmp_list']['fimage'] );
              wp_update_attachment_metadata( $attach_id, $attach_data );
              
              set_post_thumbnail( $postid, $attach_id );
              
              
                
            }
            
            //send admin email
            if($pstatus=="draft"){
                //get user emai
                global $current_user;
                get_currentuserinfo();
                mail($current_user->user_email,"New Product Added","Your product is successfully added and is waiting to admin review. You will be notified if your product is accepetd or rejected.");
                
                //now send notification to site admin about newly added product
                $admin_email = get_bloginfo('admin_email');
                mail($admin_email,"Product Review", "New Product is added by user " .$current_user->user_login . ". Please review this product to add your store.");
                
                //add a new post meta to identify only drafted post
                if ( ! update_post_meta ($postid, '_z_user_review', '1' ) ){
                    add_post_meta( $postid, '_z_user_review', '1',true );
                }
            }
            
        }
          
        header("Location: ".$_SERVER['HTTP_REFERER']);
        die();
     }
}


//Send notification before delete product
//add_action( 'before_delete_post', 'notify_product_rejected' );
add_action('wp_trash_post', 'notify_product_rejected');
function notify_product_rejected($post_id){
    global $post_type;   
    if ( $post_type != 'wpmarketplace' ) return;

        $post = get_post($post_id);
        $post_meta = get_post_meta($post_id,"_z_user_review",true);
        if($post_meta != ""):

            $author = get_userdata($post->post_author);
            $author_email = $author->user_email;
            $email_subject = "Your product has been rejected.";

            ob_start(); ?>

            <html>
                <head>
                    <title>New post at <?php bloginfo( 'name' ) ?></title>
                </head>
                <body>
                    <p>
                        Hi <?php echo $author->user_firstname ?>,
                    </p>
                    <p>
                        Your product <?php the_title() ?> has been rejected.
                    </p>
                </body>
            </html>

            <?php

            $message = ob_get_contents();

            ob_end_clean();

            wp_mail( $author_email, $email_subject, $message );
        endif;
    
}

// SEND EMAIL ONCE POST IS PUBLISHED
add_action( 'publish_post', 'notify_product_accepted' );
function notify_product_accepted($post_id) {
    
    //only my custom post type
    global $post_type;   
    if ( $post_type != 'wpmarketplace' ) return;
    
    //echo "<pre>";    print_r($_POST); echo "</pre>";
    if( ( $_POST['post_status'] == 'publish' ) && ( $_POST['original_post_status'] != 'publish' )) {
        $post = get_post($post_id);
        $post_meta = get_post_meta($post_id,"_z_user_review",TRUE);
        if($post_meta != ""):
        
            $author = get_userdata($post->post_author);
            $author_email = $author->user_email;
            $email_subject = "Your post has been published.";

            ob_start(); ?>

            <html>
                <head>
                    <title>New post at <?php bloginfo( 'name' ) ?></title>
                </head>
                <body>
                    <p>
                        Hi <?php echo $author->user_firstname ?>,
                    </p>
                    <p>
                        Your product <a href="<?php echo get_permalink($post->ID) ?>"><?php the_title_attribute() ?></a> has been published.
                    </p>
                </body>
            </html>

            <?php

            $message = ob_get_contents();

            ob_end_clean();

            wp_mail( $author_email, $email_subject, $message );
        endif;
    }
    //wpmarket@wpmarketplaceplugin.com
}


function wpmp_weightndim($content){
    global $post;
    $pinf = get_post_meta($post->ID, 'wpmp_list_opts', true);
    if(isset($pinf['digital_activate'])||$pinf['weight']=='') return $content;
    $dim = ""; //"<h3>".__('Product Info')."</h3>";
    $dim .= "<table class='table'>";
    $dim .= "<tr><td>".__('Weight','wpmarketplace')."</td><td>{$pinf['weight']}</td></tr>";
    $dim .= "<tr><td>".__('Width','wpmarketplace')."</td><td>{$pinf['pwidth']}</td></tr>";
    $dim .= "<tr><td>".__('Height','wpmarketplace')."</td><td>{$pinf['pheight']}</td></tr>";
    $dim .= "</table>";
    return $dim.$content;
}
 
 ///for withdraw request
 function wpmp_withdraw_request(){
     global $wpdb, $current_user;
     
     $uid = $current_user->ID;

     if(isset($_POST['withdraw']) && $_POST['withdraw']==1){
    
        $amount = get_user_meta($uid,'marketplace_matured_balance',true);   
        //echo floatval($amount);
        //die();
        if($amount != '' && floatval($amount)>0 ){
            $wpdb->insert( 
                "{$wpdb->prefix}mp_withdraws",
                array( 
                    'uid' => $uid,
                     'date' => time(),
                     'amount' => $amount,
                     'status' => 0
                ), 
                array( 
                    '%d', 
                    '%d', 
                    '%f', 
                    '%d' 
                ) 
            );
            update_user_meta($uid,'marketplace_matured_balance',0);
        }
        
    header("Location: ".$_SERVER['HTTP_REFERER']);
    die();    
    }
     
 }
 
 //count the total number of product
 function total_product(){
   global $wpdb;
   $total_product=$wpdb->get_var("select count(ID) from {$wpdb->prefix}posts where post_type='wpmarketplace' and post_status='publish'");
   return $total_product;  
 }
 //featured products
 function feature_products($show=10){
    global $wpdb;
    $files=$wpdb->get_results("select * from {$wpdb->prefix}mp_feature_products fp inner join {$wpdb->prefix}posts p on p.ID=fp.productid where p.post_type='wpmarketplace' and ".time()." between startdate and enddate limit 0,{$show}");
    return $files;
 }
 
 function featured_products($show=10){
    global $wpdb;
    $files=$wpdb->get_results("select * from {$wpdb->prefix}mp_feature_products fp inner join {$wpdb->prefix}posts p on p.ID=fp.productid where p.post_type='wpmarketplace' and p.post_status='publish' and ".time()." between startdate and enddate limit 0,{$show}");
    return $files;
 }
 
 //top rated products
 function top_rate_products($show=10){
    global $wpdb;
    $querystr = "
    SELECT $wpdb->posts.* 
    FROM $wpdb->posts, $wpdb->postmeta
    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
    AND $wpdb->postmeta.meta_key = 'avg_rating' 
    AND $wpdb->posts.post_status = 'publish' 
    AND $wpdb->posts.post_type = 'wpmarketplace'
    ORDER BY $wpdb->postmeta.meta_value DESC  limit 0,{$show}
 ";

 $pageposts = $wpdb->get_results($querystr, OBJECT); 
 return $pageposts;
 }


 function wpmp_redirect($url){
     if(!headers_sent())
         header("location: ". $url);
     else
         echo "<script>location.href='{$url}';</script>";
     die();
 }
 function wpmp_js_redirect($url){

         echo "&nbsp;Redirecting...<script>location.href='{$url}';</script>";
     die();
 }


 function wpmp_members_page(){
     $settings = get_option('_wpmp_settings');
     return get_permalink($settings['members_page_id']);
 }
 
  function wpmp_orders_page(){
     $settings = get_option('_wpmp_settings');
     return get_permalink($settings['orders_page_id']);
  }

/**
 * Retrienve Site Commissions on User's Sales
 * @param null $uid
 * @return mixed
 */
function wpmp_site_commission($uid = null){
      global $current_user;
      $user = $current_user;
      if($uid) $user = get_userdata($uid);
      $comission = get_option("wpmp_user_comission");
      $comission =  $comission[$user->roles[0]];
      return $comission;
  }



function wpmp_get_user_earning(){

}


function wpmp_user_dashboard(){
    include(WPMP_BASE_DIR.'/tpls/dashboard.php');
    return $data;
}


function wpmp_product_price($pid){
    $pinfo = get_post_meta($pid,"wpmp_list_opts",true);
    $expire = FALSE;
    if(isset($pinfo['sales_price_expire'])) {
        //echo "Hello world";
        $today = strtotime(date('Y-m-d'));
        $end_date = strtotime($pinfo['sales_price_expire']);
        if($end_date<=$today){
            $expire = true;
        }
    }
    //else echo "Hello world 2";
    $price = floatval($pinfo['sales_price'])>0 && $pinfo['sales_price']<$pinfo['base_price'] && $expire==FALSE ?$pinfo['sales_price']:$pinfo['base_price'];
    return number_format($price,2,".","");
}

function wpmp_addtocart_link($id){
    $pinfo = get_post_meta($id,"wpmp_list_opts",true);
    @extract($pinfo);
    $settings = isset($pinfo['settings'])?$pinfo['settings']:array();
    $cart_enable="";
    
    if(isset($settings['stock']['enable'])&&$settings['stock']['enable']==1){
        if($manage_stock==1){
            if($stock_qty>0)$cart_enable=""; else $cart_enable=" disabled ";
        }
    }
    $cart_enable = apply_filters("wpmp_cart_enable", $cart_enable,$id);
    
    if(isset($pinfo['price_variation'])&&$pinfo['price_variation'])
        $html = "<a href='".get_permalink($id)."' class='btn btn-info btn-small cart_form'><i class='glyphicon glyphicon-shopping-cart icon-shopping-cart icon-white'></i> ".__("Add to Cart","wpmarketplace")."</a>";
    else{
        $html = <<<PRICE
                        <form method="post" action="" name="cart_form" class="cart_form">
                        <input type="hidden" name="add_to_cart" value="add">
                        <input type="hidden" name="pid" value="$id">
                         

PRICE;
        $html.='<button '.$cart_enable.' class="btn btn-info btn-small" type="submit" ><i class="glyphicon glyphicon-shopping-cart icon-shopping-cart icon-white"></i> '.__("Add to Cart","wpmarketplace").'</button></form>';

    }
    return $html;
}


function wpmp_all_products($params){
    include(WPMP_BASE_DIR.'tpls/catalog.php');
	 
}









function wpmp_all_feature_products($params){
    include(WPMP_BASE_DIR.'tpls/catalog_feature.php');
	 
}

//delete product from front-end
function wpmp_delete_product(){
    if(is_user_logged_in()&&isset($_GET['dproduct'])){
        global $current_user;
        $pid = intval($_GET['dproduct']);
        $pro = get_post($pid);
        
        if($current_user->ID==$pro->post_author){
            wp_update_post(array('ID'=>$pid,'post_status'=>'trash'));
            $settings = get_option('_wpmp_settings');
            if($settings['frontend_product_delete_notify']==1){
                wp_mail(get_option('admin_email'),"I had to delete a product","Hi, Sorry, but I had to delete following product for some reason:<br/>{$pro->post_title}","From: {$current_user->user_email}\r\nContent-type: text/html\r\n\r\n");
            }
            $_SESSION['dpmsg'] = 'Product Deleted';
            header("location: ".$_SERVER['HTTP_REFERER']);
            die();
        } 
    }
}

function wpmp_order_completed_mail(){
    
}
 
 function wpmp_head(){
    ?>
    
        <script language="JavaScript">
         <!--
         var wpmp_base_url = '<?php echo plugins_url('/wpmarketplace/'); ?>';
         jQuery(function(){
             jQuery('.wpmp-thumbnails a').lightBox({fixedNavigation:true});
         });  
         //-->
         </script>
    
    <?php 
 }
 
 function wpmp_product_report_scripts(){
     wp_enqueue_script(
		'flot',
		WP_PLUGIN_URL . '/wpmarketplace/js/jquery.flot.js',
		array( 'jquery' )
	);
     wp_enqueue_script(
		'float-resize',
		WP_PLUGIN_URL . '/wpmarketplace/js/jquery.flot.resize.js',
		array( 'jquery' )
	);
     wp_enqueue_script(
		'float-time',
		WP_PLUGIN_URL . '/wpmarketplace/js/jquery.flot.time.js',
		array( 'jquery' )
	);
     $path = WP_PLUGIN_URL . '/wpmarketplace/js/excanvas.min.js';
     echo '<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="'.$path.'"></script><![endif]-->';
     //
     /*
     add_action('wp_head',function(){
         $path = WP_PLUGIN_URL . '/wpmarketplace/js/excanvas.min.js';
         echo '<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="'.$path.'"></script><![endif]-->';
     });
      */
 }
 
 function wpmp_product_report_styles(){
     wp_enqueue_style(
		'float-css',
		WP_PLUGIN_URL . '/wpmarketplace/css/admin/product_report.css'
	);
 }
 
add_action("wp_ajax_wpmp_delete_frontend_order", "wpmp_delete_frontend_order");
add_action("wp_ajax_nopriv_wpmp_delete_frontend_order", "wpmp_delete_frontend_order");

function wpmp_delete_frontend_order() {

    if (!wp_verify_nonce($_REQUEST['nonce'], "delete_order")) {
        exit("No naughty business please");
    }

    $result['type'] = 'failed';
    global $wpdb;
    $order_id = esc_attr($_REQUEST['order_id']);
    $ret = $wpdb->query(
            $wpdb->prepare(
                    "
            DELETE FROM {$wpdb->prefix}mp_orders
             WHERE order_id = %s
            ", $order_id
            )
    );
    if ($ret) {
        //echo $ret;
        $ret = $wpdb->query(
                $wpdb->prepare(
                        "
            DELETE FROM {$wpdb->prefix}mp_order_items
             WHERE oid = %s
            ", $order_id
                )
        );
        //echo $ret;
        if ($ret)
            $result['type']='success';
    }



    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $result = json_encode($result);
        echo $result;
    } else {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    }

    die();
}

function wpmp_plugin_active($plugin = "wpmarketplace/wpmarketplace.php") {
    //$ret =  get_option( 'active_plugins', array() );
    //print_r($ret);
    return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}
//wpmp_plugin_active();

/*
 * Category Fix
 */
function wpmp_prouduct_meta($excerpt){
    
    global $post;
    if(get_post_type()!='wpmarketplace') return $excerpt;
    if(is_single()) return;
    ob_start();
?>    
<div class="wp-marketplace">
    <div class="well">
        <span class="pull-left">
            <?php _e('Price: ','wpmarketplace'); if(function_exists('wpmp_product_price')) echo get_option('_wpmp_curr_sign','$').wpmp_product_price(get_the_ID()); ?>
        </span>
        <span class="pull-right">
            <?php if(function_exists('wpmp_addtocart_link')) echo wpmp_addtocart_link(get_the_ID()); ?>
        </span>
        <div class="clearfix"></div>
    </div>
    
</div>

<?php 
    $test = ob_get_clean();
    return $excerpt . $test;
}

add_filter("the_content","wpmp_prouduct_meta");
add_filter("the_filter","wpmp_prouduct_meta");

add_shortcode('wpmp-category-list','wpmp_category_list_sc');

function wpmp_category_list_sc($atts,$content=null){
    extract( shortcode_atts( array(
		'cols' => '1'
	), $atts ) );
    
    $args = array(
        'orderby'       => 'name', 
        'order'         => 'ASC',
        'hide_empty'    => false, 
        'fields'        => 'all', 
        'hierarchical'  => true, 
        'pad_counts'    => true, 
    ); 
    
    $terms = get_terms('ptype',$args);
    $ret = '<div class="wp-marketplace"><ul style="list-style:none; list-style-type:none; margin-left:0px;">';
    foreach ($terms as $term) {
        //Always check if it's an error before continuing. get_term_link() can be finicky sometimes
        $term_link = get_term_link( $term, 'ptype' );
        if( is_wp_error( $term_link ) )
            continue;
        //We successfully got a link. Print it out.
        $ret .= '<li class="col-md-'.(12/(int)$cols).'" style="margin-left:0px;"><a href="' . $term_link . '">' . $term->name . ' (' . $term->count . ')</a></li>';
    }
    $ret .= '</ul></div>';
    return $ret;
}