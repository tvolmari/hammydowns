<?php if (!did_action('get_header')) do_action('get_header'); ?>
<!DOCTYPE html>
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE > 8]>
<html class="ie" <?php language_attributes(); ?>>
<![endif]-->
<!--[if ! IE  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" type="image/x-icon">
<!-- BuddyPress and bbPress Stylesheets are called in wp_head, if plugins are activated -->
<?php wp_head(); ?>
<?php if ( function_exists( 'bp_head' ) ) bp_head(); ?>

<style type="text/css">
html,body {
	margin: 0px !important;
	overflow-x: hidden;
	overflow-y: auto;
	width: 100%;
	height: 100%;
	background: #000;
}
body .buddyboss-popup {
	-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
	filter: alpha(opacity=0);
	opacity: 0;
	-webkit-transition: opacity 0.5s;
	-moz-transition: opacity 0.5s;
	-o-transition: opacity 0.5s;
	-ms-transition: opacity 0.5s;
	transition: opacity 0.5s;
}
body.init-load .buddyboss-popup {
	-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
	filter: alpha(opacity=100);
	opacity: 1;
}
</style>

</head>

<body <?php body_class() ?>>

	<div id="lightbox-popup" class="activity buddyboss-activity-ajax buddyboss-popup">
		<div class="entry-content">

			<div id="buddypress" class="mfp-figure">
				<div class="buddyboss-popup-content buddyboss-popup-height">
					<?php if ( bp_has_activities( 'display_comments=stream&include=' . bp_current_action() ) ) : ?>
						<?php echo buddyboss_pics_ajax_picture( bp_current_action() ) ?>
					<?php endif; ?>
				</div>
        <div class="buddyboss-popup-comments buddyboss-popup-height">
					<?php if ( bp_has_activities( 'display_comments=threaded&include=' . bp_current_action() ) ) : ?>

						<div class="activity">

						<ul id="activity-stream" class="activity-list item-list">
						<?php while ( bp_activities() ) : bp_the_activity(); ?>

							<?php bp_get_template_part( 'activity/entry' ) ?>

						<?php endwhile; ?>
						</ul>
						</div>

					<?php endif; ?>
				</div><!-- .buddyboss-popup-comments -->

			</div><!-- #buddypress.mfp-figure -->
		</div><!-- .entry-content -->
	</div> <!-- .buddyboss-popup -->

	<script type="text/javascript">
		jQuery( document ).ready( function($) {
			jQuery( 'a.confirm').click( function(e) {
				e.preventDefault();
				if ( confirm( '<?php _e( 'Are you sure?', 'buddyboss' ) ?>' ) ) {
					top.location = $(this).attr('href');
				}
			});
		});
	</script>

	<script type='text/javascript'>
		jQuery(document).ready(function($){
			var $win         = $(window),
					$body        = $('body').removeClass('no-js'),
					$div_popup   = $('.buddyboss-popup'),
					$div_height  = $('.buddyboss-popup-height'),
					$div_content = $('.buddyboss-popup-content'),
					$div_image   = $('.buddyboss-popup-image'),
					win_h        = $win.height(),
					win_w				 = $win.width(),
					win_timer;

			// Adjust positioning and size
			var adjust_sizes = function() {

				// console.log( 'adjust_sizes()' );
				// console.log( win_h );
				// console.log( $body.height() );
				// console.log( $('html').height() );

				// Make sure the body is 100% min height
				if ( $body.height() < win_h ) {
					$body.css({ height: win_h });
				}

				// Make sure the popup wrap and divs is 100% min height
				if ( $div_popup.height() < win_h ) {
					$div_popup.css({ height: win_h });
				}
				else {
					$div_popup.css({ height: 'auto' });
				}
		    $div_height.css({'height': (win_h) + 'px'});
		    $div_content.css({'line-height': (win_h) + 'px'});
		    $div_image.css({'max-height': (win_h) + 'px'});
			}

			// Checks if the window width/height changed, updates values and calls adjust_sizes()
			var check_sizes = function() {
				if ( win_h != $win.height() || win_w != $win.width() ) {
					win_h = $win.height();
					win_w = $win.width();
					adjust_sizes();
				}
			}

			// First run on page load
			adjust_sizes();

			$body.addClass( 'init-load' );

			// Set and interval to check for window resizing
			// This is better than $(window).on('resize')
			win_timer = setInterval( check_sizes, 222 );
		});
	</script>

</body>
</html>