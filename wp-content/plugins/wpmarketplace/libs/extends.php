<!--[if IE]>
<style>
ul#navigation { 
border-bottom: 1px solid #999999;
}
</style>
<![endif]-->
<style>
    .cart_form {
 margin:0 !important;
}
</style>
<div class="wrap">
<header>
  <div class="icon32" id="icon-options-general"><br></div><h2><?php echo __("Extends","wpmarketplace");?> <img style="display: none;" id="wdms_loading" src="images/loading.gif" /></h2>
</header>

<nav> 
    <ol>
        <?php
        $data = array(
            "wpmp_api_req"=>"getCategoryList"
            );
        
        $ch = curl_init();   
        curl_setopt($ch, CURLOPT_URL, "http://wpmarketplaceplugin.com");                                    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($ch, CURLOPT_HEADER, 0);  
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
        $json = curl_exec($ch);  
        curl_close($ch);
        
        $categories = json_decode($json);
        //print_r($output);
        $html = "";
        $cat_id = isset($_REQUEST['cat_id'])?$_REQUEST['cat_id']:null;
        
        foreach($categories as $category):
            if($category->term_id==386){
                continue;
            }
            if($cat_id != null && $cat_id==$category->term_id){
                $selected = "selected";
            }
            else if($cat_id == null){
                $selected = "selected";
                $cat_id = $category->term_id;
            }
            else {
                $selected = "";
            }
            $title = __($category->name,'wpmarketplace');
            $html .= <<<EOD
                <li class="{$selected}"><a href='edit.php?post_type=wpmarketplace&page=extends&cat_id=$category->term_id'>$title</a></li>
EOD;
        
        endforeach;
            //print_r($category);
        echo $html;
            
        ?>
    </ol>
</nav>
    
<div class="wp-marketplace" style="margin: 20px;">
    <?php
    if($cat_id == ""){
        $cat_id = $_REQUEST['cat_id'];
    }
    $data = array(
            "cat_id"=>$cat_id,
            //"count"=>3,
            "wpmp_api_req"=>"getProductList"
            );
        
        $ch = curl_init();   
        curl_setopt($ch, CURLOPT_URL, "http://wpmarketplaceplugin.com");                                    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($ch, CURLOPT_HEADER, 0);  
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
        $json = curl_exec($ch);  
        curl_close($ch);
        
        $output = json_decode($json,TRUE);

        $html = "";
        $post_extra = $output['post_extra'];
        
        foreach ($post_extra as $key => $value){
            $value['excerpt'] = strip_tags($value['excerpt']);
            $html .=<<<EOD
<div class='product-box'>

    <a class='animated rotateIn' href='{$value['link']}'>
            {$value['thumbnail']}
    </a>

    <div class='bubble animated bounceIn'>
        <div class='content'>
            <h3><a href="{$value['link']}" target='_blank'>{$value['title']}</a></h3>

            {$value['excerpt']}
        </div>
        <div class='footer1'>
            <ul>
                <li><i class='icon icon-eye-open'></i> Price: {$value['price']}</li>
                <li class='pull-right'>{$value['cart']}</li>
            </ul>
        </div>
    </div>
</div>
EOD;
            
        }
        echo $html;
    ?>
</div>
</div>
