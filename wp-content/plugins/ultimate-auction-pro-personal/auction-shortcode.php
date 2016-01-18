<?php
add_action('wp_enqueue_scripts', 'wdm_ua_enqueue_script_style');

function wdm_ua_enqueue_script_style(){
	wp_enqueue_style( 'wdm_slider_css', plugins_url( 'slider/jquery.bxslider.css', __FILE__ ) );
	wp_enqueue_script( 'wdm-slider-js', plugins_url( 'slider/jquery.bxslider.min.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'wdm-block-ui-js', plugins_url( 'js/wdm-jquery.blockUI.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_style( 'wdm_lightbox_css', plugins_url( 'lightbox/jquery.fs.boxer.css', __FILE__ ) );
	wp_enqueue_script( 'wdm-lightbox-js', plugins_url( 'lightbox/jquery.fs.boxer.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'wdm-custom-js', plugins_url( 'js/wdm-custom-js.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_style( 'wdm_auction_front_end_styling', plugins_url( 'css/ua-front-end.css', __FILE__ ) );
}

function wdm_auction_listing($wdm_cat_args=""){
	
	$GLOBALS['gb_cat_args'] = $wdm_cat_args;
	/**
	 * Download Digital Product File if user authenticated
	 */
	
	//wp_enqueue_style( 'wdm_auction_front_end_styling', plugins_url( 'css/ua-front-end.css', __FILE__ ) );
	
	ob_start();

	$chk_watchlist			 = "n";
	//get currency code
	$currency_code			 = substr( get_option( 'wdm_currency' ), -3 );
	$currency_code_display	 = '';
	$currency_symbol='';
	preg_match( '/-([^ ]+)/', get_option( 'wdm_currency' ), $matches );
	
	if(isset($matches[ 1 ]))
		$currency_symbol = $matches[ 1 ];
	
	if(empty($currency_symbol)){
		$currency_symbol = $currency_code.' ';
	}
	else{
		if ( $currency_symbol == '$' || $currency_symbol == 'kr' ) {
			$currency_code_display = $currency_code;
		}	
	}
	
	//file auction listing page
	require_once('auction-feeder-page.php');

	$auc_sc = ob_get_contents();

	ob_end_clean();

	return $auc_sc;
}

//shortcode to display entire auction posts on the site
add_shortcode( 'wdm_auction_listing', 'wdm_auction_listing' );

function wdm_auction_dashboard_login( $ext_html ) {

	$login_screen			 = '';
	$db_url					 = get_option( 'wdm_dashboard_page_url' );
	//get custom login and registration url if set
	$wdm_login_url			 = get_option( 'wdm_login_page_url' );
	$wdm_registration_url	 = get_option( 'wdm_register_page_url' );

	if ( empty( $wdm_login_url ) ) {

		if ( $ext_html === 'bid' ) {
			$wdm_login_url = wp_login_url( $_SERVER[ 'REQUEST_URI' ] );
		} elseif ( $ext_html === 'watchlist' ) {
			$wdm_login_url = wp_login_url( $_SERVER[ 'REQUEST_URI' ] . "&add_to_watch=y" );
		} elseif ( $ext_html === 'review' ) {
			$wdm_login_url = wp_login_url( $_SERVER[ 'REQUEST_URI' ] . "&rnr=shw" );
		} else {
			$wdm_login_url = wp_login_url( $db_url );
		}
	}
	if ( empty( $wdm_registration_url ) ) {
		$wdm_registration_url = wp_registration_url();
	}

	//if($ext_html === 'watchlist'){
	//	$wdm_login_url = wp_login_url( $_SERVER['REQUEST_URI']."&add_to_watch=y");
	//}
	//elseif($ext_html === 'review'){
	//	$wdm_login_url = wp_login_url( $_SERVER['REQUEST_URI']."&rnr=shw");
	//}
	//if($ext_html === 'bid'){
	 if($ext_html === 'bid'){
	$login_screen .= '<div class="wdm_ua_pop_up_cmn"><center><div class="ua_login_popup_text">';
	$login_screen .= __( "Please sign in to place your bid or buy the product.", "wdm-ultimate-auction" );
	$login_screen .= '</div>';
	$login_screen .= '<a class="wdm-login-ua-db wdm_ua_login_db" href="' . $wdm_login_url . '" title="' . __( 'Login', 'wdm-ultimate-auction' ) . '">' . __( "Login", "wdm-ultimate-auction" ) . '</a></center></div>';
	// }
	//else{
	//   $login_screen .= '<div class="wdm_ua_pop_up_cmn"><center><div class="ua_login_popup_text">';
	//   $login_screen .= __("Please sign in to view your dashboard.", "wdm-ultimate-auction");
	//   $login_screen .= '</div>';
	//   $login_screen .= '<a class="wdm-login-ua-db wdm_ua_login_db" href="'.wp_login_url($db_url).'" title="Login">'.__("Login", "wdm-ultimate-auction").'</a></center></div>';
	//}
	}
    else{
       $login_screen .= '<div class="wdm_ua_pop_up_cmn"><center><div class="ua_login_popup_text">';
       $login_screen .= __("Please sign in to view your dashboard.", "wdm-ultimate-auction");
       $login_screen .= '</div>';
       $login_screen .= '<a class="wdm-login-ua-db wdm_ua_login_db" href="'.wp_login_url($db_url).'" title="Login">'.__("Login", "wdm-ultimate-auction").'</a></center></div>';
    }

	$login_screen .= '<div class="wdm_ua_pop_up_cmn" style="margin-top:40px;"><center><div class="ua_login_popup_text">';
	$login_screen .= __( "Don't have an account?", "wdm-ultimate-auction" );
	$login_screen .= '</div>';

	$login_screen .= '<a class="wdm-login-ua-db wdm_ua_reg_db" href="' . $wdm_registration_url . '" title="' . __( 'Register', 'wdm-ultimate-auction' ) . '">' . __( "Register now", "wdm-ultimate-auction" ) . '</a></center></div>';

	return $login_screen;
}

function wdm_ua_add_html_on_feed( $ext_html ) {

	$dat_html	 = '';
	$db_url		 = get_option( 'wdm_dashboard_page_url' );

	if ( $ext_html === '' || empty( $ext_html ) ) {
		if ( is_user_logged_in() /* && (current_user_can('add_ultimate_auction') || current_user_can('administrator')) */ )
			$dat_html .= '<a href="' . $db_url . '" target="_blank" style="float: right;" class="wdm_db_auc_link">' . __( "My Dashboard", "wdm-ultimate-auction" ) . '</a>';
		else
			$dat_html .= '<a href="#ua_login_popup" style="float: right;" class="login_popup_boxer wdm_db_auc_link">' . __( "My Dashboard", "wdm-ultimate-auction" ) . '</a>';
	}

	if ( $ext_html === 'watchlist' ) {
		$dat_html .= '<div id="ua_login_popup_w" style="display: none;"><div class="ua_popup_content">';
		$dat_html .= wdm_auction_dashboard_login( $ext_html );
		$dat_html .= '</div></div>';
	} elseif ( $ext_html === 'review' ) {
		$dat_html .= '<div id="ua_login_popup_r" style="display: none;"><div class="ua_popup_content">';
		$dat_html .= wdm_auction_dashboard_login( $ext_html );
		$dat_html .= '</div></div>';
	} else {
		$dat_html .= '<div id="ua_login_popup" style="display: none;"><div class="ua_popup_content">';
		$dat_html .= wdm_auction_dashboard_login( $ext_html );
		$dat_html .= '</div></div>';
	}
	//$dat_html .= '<script>jQuery(document).ready(function($){ $(".login_popup_boxer").boxer(); })</script>';

	return $dat_html;
}
?>