<div class="wrap" style="margin-left: 10px;">
<?php echo screen_icon('plugins'); ?><h2><?php _e('Product Report','wpmarketplace');?></h2>
<?php if(isset($_REQUEST['post_id']) && $_REQUEST['post_id'] != "")  {
       

//single product month sales...
global $wpdb;
$id = $_REQUEST['post_id'];
$query = "select * from `{$wpdb->prefix}mp_order_items` where pid=$id";
$result = $wpdb->get_results( $query);
$product = array();

$cTotal = 0;
$cCount = 0;
$currency_sign = get_option('_wpmp_curr_sign','$');

foreach ($result as $oitem) {
    $order1 = new Order();
    $order = $order1->getOrder($oitem->oid);
    if($order->payment_status !='Completed'){
        continue;
    }
    
    $cart_data = unserialize($order->cart_data);
    $cart_data = $cart_data[$id];

    //echo "<pre> {$order->order_id}"; print_r($cart_data); echo "</pre>";
    $count = 0;
    $total = 0;
    if(isset($cart_data['item'])):
        foreach ($cart_data['item'] as $key => $val):
            if(!isset($val['coupon_amount']) || $val['coupon_amount'] == "") {
                $val['coupon_amount'] = 0;
            }

            if(!isset($val['discount_amount']) || $val['discount_amount'] == "") {
                $val['discount_amount'] = 0;
            }
            if(!isset($val['prices']) || $val['prices'] == "") {
                $val['prices'] = 0;
            }
            
            $count += $val['quantity'];
            $total += number_format((($cart_data['price'] + $val['prices'])*$val['quantity'])-$val['discount_amount'] - $val['coupon_amount'],2,".","");
        endforeach;

    else:
        if(!isset($cart_data['coupon_amount']) || $cart_data['coupon_amount'] == "") {
            $cart_data['coupon_amount'] = 0;
        }

        if(!isset($cart_data['discount_amount']) || $cart_data['discount_amount'] == "") {
            $cart_data['discount_amount'] = 0;
        }
        
        if(!isset($cart_data['prices']) || $cart_data['prices'] == "") {
            $cart_data['prices'] = 0;
        }

        $count = $cart_data['quantity'];
        $total = number_format((($cart_data['price'] + $cart_data['prices'])*$cart_data['quantity'])-$cart_data['discount_amount'] - $cart_data['coupon_amount'],2,".","");
    endif;
    //echo $count . " count <br>";
    $cCount += $count;
    $cTotal += $total;
    
    $date = strtotime(date('Y-m-d',$order->date));
    
    if(array_key_exists($date, $product)){
        $quantity = $product[$date]['quantity']+$count;
        $total = $product[$date]['total']+$total;
        $product[$date] = array(
            'quantity' => $quantity,
            'total' => $total
        );
    }
    else {
        $product[$date] = array(
            'quantity' => (int)$count,
            'total' => intval($total)
        );
    }
}

if(!empty($product)):
    
    $keys = array_keys($product);
    $cnt = count($keys);
    $tmp_arr = array();
    $i = $keys[0];
    while($i < $keys[$cnt-1]) {
        $tmp = strtotime('+1 day', $i);
        if(!array_key_exists($tmp, $product)){
            $tmp_arr[$tmp] =  array('quantity'=>0,'total'=>0);
            //array_push($tmp_arr, array($tmp => array('quantity'=>0,'total'=>0)) );
        }
        $i = $tmp;
    }
    foreach($tmp_arr as $key => $value){
       $product[$key] = $value;
    }
    ksort($product);
   
    
    
    foreach($product as $key => $value){
        //echo date('d/m/Y', $key) . "<br>";
        $order_counts[] = array($key,$value['quantity']);
        $order_amounts[] = array($key,$value['total']);
     }
     $order_amounts = json_encode($order_amounts);
     $order_counts = json_encode($order_counts);
    //echo "<pre>"; print_r($product); echo "</pre>";
    //echo "<pre>"; print_r($order_counts); echo "</pre>";
    //echo "<pre>"; print_r($order_amounts); echo "</pre>";
endif;
?>
    <h3><?php _e('Product Title : ','wpmarketplace'); echo get_the_title($id); ?></h3>
    <?php if(!empty($order_counts)): ?>
    
    <h3 style="text-align: center"><?php  _e('Number of Sales Graph','wpmarketplace'); echo " -- $cCount"; ?></h3>
    <div class="demo-container">
        <div id="placeholder1" class="demo-placeholder"></div>
        <div id="cart_legend"></div>
    </div>
    
    <h3 style="text-align: center;"><?php  _e('Sales Amount Graph','wpmarketplace'); echo " -- {$currency_sign}{$cTotal} "; ?></h3>
    <div class="demo-container">
        <div id="placeholder2" class="demo-placeholder"></div>
        <div id="cart_legend"></div>
    </div>
    
    
<script type="text/javascript">
    var d1 = <?php echo $order_counts; ?>;
    var d2 = <?php echo $order_amounts ?>;
    for (var i = 0; i < d1.length; ++i) d1[i][0] = d1[i][0] * 1000;
    for (var i = 0; i < d2.length; ++i) d2[i][0] = d2[i][0] * 1000;
    
    function weekendAreas(axes) {

        var markings = [],
                d = new Date(axes.xaxis.min);
        // go to the first Saturday

        d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
        d.setUTCSeconds(0);
        d.setUTCMinutes(0);
        d.setUTCHours(0);

        var i = d.getTime();

        // when we don't set yaxis, the rectangle automatically
        // extends to infinity upwards and downwards

        do {
                markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
                i += 7 * 24 * 60 * 60 * 1000;
        } while (i < axes.xaxis.max);

        return markings;
    }
    
    jQuery(document).ready(function($){
        var options = {
                        series: {
				lines: { show: true },
				points: { show: true }
                                //bars: {show:true,barWidth:5}
			},
			xaxis: {
				mode: "time",
                                timeformat: "%d/%m/%y",
				tickLength: 1
                        },
			selection: {
				mode: "x"
			},
			grid: {
				show: true,
                                aboveData: false,
                                color: '#aaa',
                                backgroundColor: '#fff',
                                borderWidth: 2,
                                borderColor: '#aaa',
                                clickable: false,
                                hoverable: true,
                                markings: weekendAreas
			}
		};

		var plot = $.plot("#placeholder1", [{ label: "Number of sales", data: d1}], options);
                var plot2 = $.plot("#placeholder2", [{ label: "Sales amount", data: d2}], options);
                $('#placeholder1').resize();
                $('#placeholder2').resize();
    });
    
    function showTooltip(x, y, contents) {
        jQuery('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 5,
		    padding: '5px 10px',
			border: '3px solid #3da5d5',
			background: '#288ab7'
        }).appendTo("body").fadeIn(200);
    }
    var previousPoint = null;
    jQuery("#placeholder1, #placeholder2").bind("plothover", function (event, pos, item) {
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;

                jQuery("#tooltip").remove();

                if (item.series.label=="Sales amount") {

                	var y = item.datapoint[1].toFixed(2);
                	showTooltip(item.pageX, item.pageY, item.series.label + " - " + "&#36;" + y);

                } else if (item.series.label=="Number of sales") {

                	var y = item.datapoint[1];
                	showTooltip(item.pageX, item.pageY, item.series.label + " - " + y);

                } else {

                	var y = item.datapoint[1];
                	showTooltip(item.pageX, item.pageY, y);

                }
            }
        }
        else {
            jQuery("#tooltip").remove();
            previousPoint = null;
        }
    });
    
    
</script>
<?php else: ?>
<div id='message' class='error'><?php _e('No Sales Record Found For This Product.','wpmarketplace'); ?></div>
    
<?php endif; ?>


<?php } 

else {
     echo "<div id='message' class='error'>".__('Please select a product to view sales report.','wpmarketplace')."</div>";
    }
?>  
</div>