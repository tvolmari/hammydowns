<script type="text/javascript">
jQuery(document).ready(function($){
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	
	$('#adp_bin_frm').submit(function(e){
            
            e.preventDefault();
            
            $.blockUI({ message: null });
            
	    var aucdata = <?php echo json_encode($auction_data); ?>;
	    
            var data = {
		action: 'wdm_ua_buy_now_adaptive',
                adp_bin_btn: 'adaptive_payment',
		auc_data: aucdata
            }
            
            $.post(ajaxurl, data, function(rs) {
                
			//$.unblockUI();
                       
			var response = JSON.parse(rs);	
			if( response['payment_status'] === "success" ){
                            //$.blockUI({ message: null });
                            $('#adp_bin_frm').after('<span class="wdm-pp-redirect"><?php _e('Please wait, you will be redirected to PayPal now.', 'wdm-ultimate-auction'); ?></span>');
                            window.location.href= response['data'];
                            
                            //$.unblockUI();
			}
			else{
			    alert( response['data'] );
                            $.unblockUI();
			}
                       
		});
        });
});
</script>