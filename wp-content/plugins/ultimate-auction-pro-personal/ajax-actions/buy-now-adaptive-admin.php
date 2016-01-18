<script type="text/javascript">
jQuery(document).ready(function($){
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	
	$('.adp_bin_frm').submit(function(e){
            
            e.preventDefault();
            
            //$.blockUI({ message: null });
	    $this = $(this);
            $this.find(".prog_img").html("<img src='<?php echo plugins_url('/img/ajax-loader.gif', dirname(__FILE__) );?>' />");       
	
	    var aucdata = $this.find('.adp_bin_btn_hdn').val();
	    
            var data = {
		action: 'wdm_ua_buy_now_adaptive',
                adp_bin_btn: 'adaptive_payment',
		auc_data_adm: aucdata
            }
            
            $.post(ajaxurl, data, function(rs) {
                
			//$.unblockUI();
                     $this.find(".prog_img").html("");
		     
			var response = JSON.parse(rs);	
			if( response['payment_status'] === "success" ){
                            //$.blockUI({ message: null });
                            $this.after('<span class="wdm-pp-redirect" style="color:blue;"><?php _e('Please wait, you will be redirected to PayPal now.', 'wdm-ultimate-auction'); ?></span>');
                            window.location.href= response['data'];
			}
			else{
			    alert( response['data'] );
                            //$.unblockUI();
			}
                       
		});
        });
});
</script>