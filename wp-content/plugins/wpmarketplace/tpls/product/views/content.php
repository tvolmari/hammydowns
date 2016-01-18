<?php

$cnt = do_shortcode(wpautop($post->post_content));
$feature = featurelist_frontend($post->ID); 
$additional_tabs = array();
$additional_tabs['features'] = array('title'=>__('Features','wpmarketplace'), 'content'=>$feature);
$additional_tabs = apply_filters('wpmp_product_page_tabs',$additional_tabs); 
foreach ($additional_tabs as $tabid=>$tab){
    $tabs .= "<li><a data-toggle='tab' href='#$tabid'>".$tab['title']."</a></li>";
    $tab_contents .= "<div class='tab-pane' id='{$tabid}'>".$tab['content']."</div>";
}
$content = ' <br><br>
 
<ul class="nav nav-tabs" id="wpmp-tabs">
              <li class="active"><a data-toggle="tab" href="#desc">'.__('Description','wpmarketplace').'</a></li>
              '.$tabs.'
               
</ul> 

<div id="tab-content" class="tab-content">
 <div class="tab-pane active" id="desc">'.$cnt.'</div>
'.$tab_contents.'
</div>
 
  
';
