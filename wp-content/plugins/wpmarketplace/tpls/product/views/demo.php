<?php
if(isset($digital_activate)){ 
if($demo_site!=''||$demo_admin!=''){
$demo = <<<DEMO
<div class='product-demo-info' id='product-demo-info'>

<div class='btn-group'>
DEMO;

if($demo_site!='')
$demo .="<a href='{$demo_site}' class='btn btn-default'>".__("Front-end Demo","wpmarketplace")."</a>";

if($demo_admin!=''){
$demo .= "<a href='{$demo_admin}' class='btn btn-default'>".__("Admin Demo","wpmarketplace")."</a>";
}


$demo .= "</div><br/><br/>";

if($demo_admin!=''){
        $demo .= "<div class='input-prepend'><span class='add-on'><i class='icon icon-user glyphicon glyphicon-user'></i></span><input class='readonly' type='text' readonly='readonly' value='{$demo_username}' /></div>";
        $demo .= "<div class='input-prepend'><span class='add-on'><i class='icon icon-key glyphicon glyphicon-lock'></i></span><input class='readonly' type='text' readonly='readonly' value='{$demo_password}' /></div>";
    }
$demo .= "</div>";


}  
}
