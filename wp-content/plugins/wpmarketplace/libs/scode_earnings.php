<?php
global $wpdb, $current_user;
$uid = $current_user->ID;
$sql = "SELECT p.ID, p.post_title, i.price, i.quantity, i.coupon, i.coupon_amount, i.site_commission, o.order_id, o.date, o.items, o.cart_data,o.total,o.cart_discount, o.uid FROM `{$wpdb->prefix}posts` p "
. " INNER JOIN `{$wpdb->prefix}mp_order_items` i ON p.ID = i.pid AND p.post_author=$uid"
. " INNER JOIN `{$wpdb->prefix}mp_orders` o ON i.oid = o.order_id AND o.order_status='Completed' "
. " order by o.date desc";

//$sql =  "SELECT p.ID, p.post_title, i.price, i.quantity, i.coupon, i.coupon_amount, i.site_commission, o.order_id, o.date, o.items, o.cart_data,o.total,o.cart_discount, o.uid FROM `{$wpdb->prefix}posts` p "
//. ", `{$wpdb->prefix}mp_order_items` i,  `{$wpdb->prefix}mp_orders` o where p.ID = i.pid AND p.post_author=$uid and i.oid = o.order_id AND o.order_status='Completed'";
//echo $sql;
$sales = $wpdb->get_results($sql,ARRAY_A);  

//echo "<pre>"; print_r($sales); echo "</pre>";
//die();

$payout_duration=  intval(get_option("wpmp_payout_duration"));
//echo "payout duration : " . $payout_duration;
                         
$total_withdraws = 0;
$balance = 0;
$matured_balance = 0;
$pending_balance = 0;
$currency_sign = get_option('_wpmp_curr_sign','$');


                                                  
if(get_user_meta($uid,'w_req',true)==1) { delete_user_meta($uid,'w_req');


?>
<blockquote class="success"><b>Withdraw request</b><br/>Withdraw request submitted successfully.</blockquote>
<?php } 
 ob_start();
?>

<table class="table table-bordered table-striped" id="earnings">
<thead>
<tr>
    <th><?php echo __("Date","wpmarketplace");?></th>
    <th><?php echo __("Order ID","wpmarketplace");?></th>
    <th><?php echo __("Item","wpmarketplace");?></th>
    <th><?php echo __("Unit Price","wpmarketplace");?></th>
    <th><?php echo __("Quantity","wpmarketplace");?></th>
    <th><?php echo __("Total","wpmarketplace");?></th>
    <th><?php echo __("Role Discount","wpmarketplace");?></th>
    <th><?php echo __("Coupon Discount","wpmarketplace");?></th>
    <th><?php echo __("Subtotal","wpmarketplace");?></th>
    <th><?php echo __("Commission","wpmarketplace");?></th>
    <th><?php echo __("Earning","wpmarketplace");?></th>
</tr>
</thead>
<tbody>
<?php
$total_income = 0; //for this user
$total_site_income = 0;
$total_site_subtotal = 0;
$total_site_comission = 0;
$total_product_income = 0;
$matured_income = 0;

foreach($sales as $sale){ 
    //$sale->site_commission = $sale->site_commission?$sale->site_commission:$sale->price*$commission/100; 
    $cart_data = maybe_unserialize($sale['cart_data']);
    $cart_data = $cart_data[$sale['ID']];
    
        
    if( $sale['site_commission'] == NULL) {
         $sale['site_commission'] = 0;
    }
    

    if(isset($cart_data['item'])){
        //echo "<pre>"; print_r($cart_data); echo "</pre>";
        foreach ($cart_data['item'] as $a => $b):
            //echo $sale['order_id'] . ' ';
            //echo "<pre>"; print_r($b); echo "</pre>";
            if(!isset($b['coupon_amount']) || $b['coupon_amount'] == "") {
                $b['coupon_amount'] = 0;
            }

            if(!isset($b['discount_amount']) || $b['discount_amount'] == "") {
                $b['discount_amount'] = 0;
            }
            
            if(!isset($b['prices']) || $b['prices']==""){
                $b['prices'] = 0;
            }
            
            if(!isset($b['variations']) || empty($b['variations'])){
                $b['variations'] = array();
            }
            if(!isset($sale['site_commission']) || $sale['site_commission'] == "" || $sale['site_commission'] == null) {
                $sale['site_commission'] = 0;
            }
            
            if(!isset($b['quantity']) || empty($b['quantity'])){
                $b['quantity'] = 0;
            }
            
            $total = (($cart_data['price']+$b['prices'])*$b['quantity']);
            $subtotal = $total - $b['discount_amount'] - $b['coupon_amount'];
            $site_com = ($subtotal * $sale['site_commission']) / 100;
            $income = $subtotal - $site_com;
            $total_income += $income;
            $date = (int)$sale['date'] + ($payout_duration * 24 * 60 * 60);
           
            if( $date < time() ){
                //echo "<br>Pay date $date Order Id: " . $sale['order_id'] . " now = " . time();
                $matured_income += $income;
            }
            $total_site_comission += $site_com;
            $total_product_income += $subtotal;
            $total_site_income += $total;
            $total_site_subtotal  += $subtotal;
            
            echo "<tr>"
                    . "<td>".date("Y-m-d H:i",$sale['date'])."</td>"
                    . "<td>{$sale['order_id']}</td>"
                    . "<td>{$sale['post_title']}<br> " . implode(', ', $b['variations']) ."</td>
                       <td>".$currency_sign.number_format($cart_data['price'],2)."</td>
                       <td>{$b['quantity']}</td>
                       <td>{$currency_sign}".$total."</td>    
                       <td>{$currency_sign}{$b['discount_amount']}</td>
                       <td>{$currency_sign}{$b['coupon_amount']}</td>
                       <td>".$currency_sign.number_format($subtotal,2)."</td>
                       <td>".$currency_sign.number_format($site_com,2)."</td>
                       <td>".$currency_sign.number_format($income,2)."</td>
                 </tr>";
        
        
        
        endforeach;
    }    
    else{
        //echo "<pre>"; print_r($cart_data); echo "</pre>";
        if(!isset($cart_data['coupon_amount']) || $cart_data['coupon_amount'] == "") {
                $cart_data['coupon_amount'] = 0;
        }

        if(!isset($cart_data['discount_amount']) || $cart_data['discount_amount'] == "") {
            $cart_data['discount_amount'] = 0;
        }

        if(!isset($cart_data['variations']) || empty($cart_data['variations'])){
            $cart_data['variations'] = array();
        }
        
        if(!isset($cart_data['prices']) || $cart_data['prices'] == "") {
            $cart_data['prices'] = 0;
        }
        
        if(!isset($sale['site_commission']) || $sale['site_commission'] == "" || $sale['site_commission'] == null) {
            $sale['site_commission'] = 0;
        }
        if(!isset($cart_data['quantity']) || $cart_data['quantity'] == "") {
            $cart_data['quantity'] = 0;
        }
        
        $total = ($cart_data['price']+$cart_data['prices'])*$cart_data['quantity'];
        $subtotal = $total - $cart_data['discount_amount'] - $cart_data['coupon_amount'];
        $site_com = ($subtotal * $sale['site_commission']) / 100;
        $income = $subtotal - $site_com;
        $total_income += $income;
        $date = (int)$sale['date'] + ($payout_duration * 24 * 60 * 60) ;
        if(  $date < time() ){
            //echo "<br>Pay date $date Order Id: " . $sale['order_id'] . " now = " . time();
            $matured_income += $income;
        }
        $total_site_comission += $site_com;
        $total_product_income += $subtotal;
        $total_site_income += $total;
        $total_site_subtotal  += $subtotal;
            
        echo "<tr>"
            . "<td>".date("Y-m-d H:i",$sale['date'])."</td>"
            . "<td>{$sale['order_id']}</td>"
            . "<td>{$sale['post_title']}<br>" . implode(', ', $cart_data['variations']) . "</td>
               <td>".$currency_sign.number_format($cart_data['price'],2)."</td>
               <td>{$cart_data['quantity']}</td>
               <td>{$currency_sign}".($total)."</td>
               <td>{$currency_sign}{$cart_data['discount_amount']}</td>
               <td>{$currency_sign}{$cart_data['coupon_amount']}</td>
               <td>".$currency_sign.number_format($subtotal,2)."</td>
               <td>".$currency_sign.number_format($site_com,2)."</td>
               <td>".$currency_sign.number_format($income,2)."</td>
               
         </tr>";
    }
    
} 

$sql = "select sum(amount) from {$wpdb->prefix}mp_withdraws 
                      where uid=$uid";

$total_withdraws = $wpdb->get_var($sql); 
$balance = $total_income-$total_withdraws;
$matured_balance=$matured_income - $total_withdraws;


//finding pending balance
$pending_balance=$balance-$matured_balance;

?>

</tbody>
 <tfoot>
    <tr>
        <th colspan="5"> </th>
        <th><?php echo get_option('_wpmp_curr_sign','$').number_format($total_site_income,2); ?></th>
        <th colspan="2"> </th>
        <th><?php echo get_option('_wpmp_curr_sign','$').number_format($total_site_subtotal,2); ?></th>
        <th><?php echo get_option('_wpmp_curr_sign','$').number_format($total_site_comission,2); ?></th>
        <th><?php echo get_option('_wpmp_curr_sign','$').number_format($total_income,2); ?></th>
    </tr>
    </tfoot>
</table>

<?php 
$content = ob_get_clean();
update_user_meta($uid,'marketplace_matured_balance',  floatval($matured_balance));
?>


<script type="text/javascript" charset="utf-8">
            /* Table initialisation */
            jQuery(document).ready(function() {
                
                jQuery.extend( jQuery.fn.dataTableExt.oStdClasses, {
                    "sSortAsc": "header headerSortDown",
                    "sSortDesc": "header headerSortUp",
                    "sSortable": "header"
                } );
                /* API method to get paging information */
                            jQuery.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
                            {
                                return {
                                    "iStart":         oSettings._iDisplayStart,
                                    "iEnd":           oSettings.fnDisplayEnd(),
                                    "iLength":        oSettings._iDisplayLength,
                                    "iTotal":         oSettings.fnRecordsTotal(),
                                    "iFilteredTotal": oSettings.fnRecordsDisplay(),
                                    "iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
                                    "iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
                                };
                            }
/* Bootstrap style pagination control */
            jQuery.extend( jQuery.fn.dataTableExt.oPagination, {
                "bootstrap": {
                    "fnInit": function( oSettings, nPaging, fnDraw ) {
                        var oLang = oSettings.oLanguage.oPaginate;
                        var fnClickHandler = function ( e ) {
                            if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
                                fnDraw( oSettings );
                            }
                        };

                        jQuery(nPaging).addClass('pagination').append(
                            '<ul class="pager">'+
                                '<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
                                '<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
                            '</ul>'
                        );
                        var els = jQuery('a', nPaging);
                        jQuery(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
                        jQuery(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
                    },

                    "fnUpdate": function ( oSettings, fnDraw ) {
                        var oPaging = oSettings.oInstance.fnPagingInfo();
                        var an = oSettings.aanFeatures.p;
                        var i, sClass, iStart, iEnd, iHalf=Math.floor(oPaging.iTotalPages/2);

                        if ( oPaging.iTotalPages < 5) {
                            iStart = 1;
                            iEnd = oPaging.iTotalPages;
                        }
                        else if ( oPaging.iPage <= iHalf ) {
                            iStart = 1;
                            iEnd = 5;
                        } else if ( oPaging.iPage >= (5-iHalf) ) {
                            iStart = oPaging.iTotalPages - 5 + 1;
                            iEnd = oPaging.iTotalPages;
                        } else {
                            iStart = oPaging.iPage - Math.ceil(5/2) + 1;
                            iEnd = iStart + 5 - 1;
                        }

                        for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
                            // Remove the middle elements
                            jQuery('li:gt(0)', an[i]).filter(':not(:last)').remove();

                            // Add the new list items and their event handlers
                            for ( i=iStart ; i<=iEnd ; i++ ) {
                                sClass = (i==oPaging.iPage+1) ? 'class="active"' : '';
                                jQuery('<li '+sClass+'><a href="#">'+i+'</a></li>')
                                    .insertBefore('li:last', an[i])
                                    .bind('click', function () {
                                        oSettings._iDisplayStart = (parseInt(jQuery('a', this).text(),10)-1) * oPaging.iLength;
                                        fnDraw( oSettings );
                                    } );
                            }

                            // Add / remove disabled classes from the static elements
                            if ( oPaging.iPage === 0 ) {
                                jQuery('li:first', an[i]).addClass('disabled');
                            } else {
                                jQuery('li:first', an[i]).removeClass('disabled');
                            }

                            if ( oPaging.iPage === oPaging.iTotalPages-1 ) {
                                jQuery('li:last', an[i]).addClass('disabled');
                            } else {
                                jQuery('li:last', an[i]).removeClass('disabled');
                            }
                        }

                    }
                }
            } );
            jQuery('#earnings').dataTable( {
                    //"sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
                    "sPaginationType": "bootstrap"
                } );
            } );
        </script>
<style type="text/css">
div.dataTables_length label {
   /* width: 460px;*/
    float: left;
    text-align: left;
}
 
div.dataTables_length select {
    width: 75px;
}
 
div.dataTables_filter label {
    float: right;
   /* width: 460px;*/
}
 
div.dataTables_info {
    padding-top: 8px;
}
 
div.dataTables_paginate {
    float: right;
    margin: 0;
}
 
table {
    margin: 1em 0;
    clear: both;
}
.center{
    text-align: center;
}
.table th,
.table td{
    font-size: 10pt;
}
.npm{
    padding-left: 20px;
}
.npm .span4,
.npm .span2{
    padding: 0;
}
.npm .panel-body{
    font-weight: 900;
}
</style>
<br>
<div class="container-fluid">
    <div class="row row-fluid npm">
        
        <div class="col-md-2 span2">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __("Total Sales","wpmarketplace");?> </div>
                <div class="panel-body">
                  <?php echo $currency_sign . number_format($total_product_income,2); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 span2">
            <div class="panel panel-primary">
                <div class="panel-heading"><?php echo __("Earning","wpmarketplace");?></div>
                <div class="panel-body">
                  <?php echo $currency_sign . number_format($total_income,2); ?>
                </div>
            </div>
        </div>
        
    
        
        <div class="col-md-2 span2">
            <div class="panel panel-info">
                <div class="panel-heading"><?php echo __("Withdrawn","wpmarketplace");?></div>
                <div class="panel-body">
                  <?php echo $currency_sign . number_format($total_withdraws,2); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-2 span2">
            <div class="panel panel-warning">
                <div class="panel-heading"><?php echo __("Pending","wpmarketplace");?></div>
                <div class="panel-body">
                  <?php echo $currency_sign . number_format($pending_balance,2);?>
                </div>
            </div>
        </div>
        
    
        
        <div class="col-md-4 span4">
            <div class="panel panel-success">
                <div class="panel-heading"><?php echo __("Matured","wpmarketplace");?></div>
                <div class="panel-body">
                  
                    
                    <form action="" method="post" style="margin:0;padding:0">
                       <?php echo $currency_sign . number_format($matured_balance,2); ?>
                        <input type="hidden" name="withdraw" value="1">
                        <input type="hidden" name="withdraw_amount" value="<?php echo $matured_balance;?>">
                        <button <?php if($matured_balance<=0){?>disabled="disabled" <?php } ?>  class="btn btn-success btn-mini btn-xs pull-right" type="submit">
                            <?php echo __("Withdraw","wpmarketplace");?></button>
                    </form> 
                </div>
            </div>
        </div>
        
       
    </div>    




<?php echo $content;
?>
</div>

