<?php

/* ADMIN OPTIONS */

/**
 * Add BuddyBoss to the Appearance menu
 */
function buddyboss_admin_menu()
{
	global $wp_admin_bar;

	add_theme_page( 'BuddyBoss Settings', 'BuddyBoss', 'manage_options', 'buddyboss_settings_menu', 'buddyboss_general_settings' );
}
add_action('admin_menu', 'buddyboss_admin_menu');

/**
 * General Settings Page
 */
function buddyboss_general_settings()
{
	global $buddyboss;

	$error = false;
	$error_messages = array();

	$updated = false;
	$updated_messages = array();

	// Check to see if we're activating the wall component.
	$wall_activate_log = '';

	// Let's check the state we should enable
	if (isset($_POST['wall']))
	{
		// 0 = false = not enabled
		// 1 = false - enabled
		$state  = ( intval($_POST["wall"]) === 1 );

		// Based on the state posted and the current state, return error
		// or success messages and perform required actions

		if ( $state === TRUE && !function_exists( 'bp_head' ) )
		{
			update_option( 'buddyboss_wall_on', 0 );
			$error_messages[] = "Wall cannot be turned on without BuddyPress activated.";
			$error = true;
		}
		elseif ( $state === TRUE && ( !bp_is_active( 'friends' ) || !bp_is_active( 'activity' ) ) )
		{
			update_option( 'buddyboss_wall_on', 0 );
			$error_messages[] = "Wall cannot be turned on without activity and the friend's component activated.";
			$error = true;
		}
		else {
			update_option( 'buddyboss_wall_on', $state );
			$state_lbl = ($state === TRUE)? "enabled": "disabled";
			$updated_messages[] = "User Wall Posting is <strong>$state_lbl</strong>.";
			$updated = true;

			if ($state == 1) $wall_activate_log = buddyboss_wall_on_activate();
			if ($state == 0) $wall_activate_log = buddyboss_wall_on_deactivate();

		}
	}

	// Check to see if we're activating the picture component.
	$pics_activate_log = '';

	// Let's check the state we should enable
	if (isset($_POST['pics']))
	{
		// 0 = false = not enabled
		// 1 = false - enabled
		$state = ( intval($_POST["pics"]) === 1 );

		// Based on the state posted and the current state, return error
		// or success messages and perform required actions

		if ( $state === TRUE && !function_exists( 'bp_head' ) )
		{
			update_option( 'buddyboss_pics_on', 0 );
			$error_messages[] = "Photo Uploading cannot be turned on without BuddyPress activated.";
			$error = true;
		}
		elseif ( $state === TRUE && ( !bp_is_active( 'activity' ) ) )
		{
			update_option( 'buddyboss_pics_on', 0 );
			$error_messages[] = "Photo Uploading cannot be turned on without the activity component activated.";
			$error = true;
		}
		else {
			update_option( 'buddyboss_pics_on', $state );
			$state_lbl = ($state === TRUE)? "enabled": "disabled";
			$updated_messages[] = "User Photo Uploading is <strong>$state_lbl</strong>.";
			$updated = true;

			if ($state == 1)
			{
				$pics_activate_log = function_exists( 'buddyboss_pics_on_activate' )
													 ? buddyboss_pics_on_activate()
													 : '';
			}
			elseif ( $state == 0 )
			{
				$pics_activate_log = function_exists( 'buddyboss_pics_on_deactivate' )
													 ? buddyboss_pics_on_deactivate()
													 : '';
			}

		}
	}

	// Set default message if not already defined
	$message = '';

	if ( $updated || $error )
	{
		$message = '<div id="message">';

		if ( $error && ! empty( $error_messages ) )
		{
			$message .= '<div class="error"><p>'.implode( '</p><p>', $error_messages ).'</p></div><!-- /.error -->';
		}

		if ( $updated && ! empty( $updated_messages ) )
		{
			$message .= '<div class="updated"><p>'.implode( '</p><p>', $updated_messages ).'</p></div><!-- /.updated -->';
		}

		$message .= '</div> <!-- /#message -->';
	}

	// Hide BuddyPress dependant settings (Profile Walls and Photo Uploading) if BuddyPress is disabled
	$bp_is_enabled = function_exists( 'bp_is_active' );
	$bpdisabledhide = isset($_POST['value']) ? $_POST['value'] : '';
	$bpdisabledmessage = isset($_POST['value']) ? $_POST['value'] : '';
	
	if (!function_exists('bp_is_active'))  $bpdisabledhide = 'style="display:none"';
	if (!function_exists('bp_is_active'))  $bpdisabledmessage = '<div id="message" class="updated"><p>Install and activate the <a href="'.admin_url().'/plugin-install.php?tab=search&type=term&s=buddypress&plugin-search-input=Search+Plugins">BuddyPress plugin</a> to enable Profile Walls and Photo Uploading.</p></div>';


	// Prepare HTML for radio button status
	$wall_on_status = (get_option("buddyboss_wall_on", 0) == 1 && function_exists("friends_get_alphabetically") == TRUE) ? "checked": "";
	$wall_off_status = (get_option("buddyboss_wall_on", 0) == 0) ? "checked": "";

	$pics_on_status = (get_option("buddyboss_pics_on", 0) == 1) ? "checked": "";
	$pics_off_status = (get_option("buddyboss_pics_on", 0) == 0) ? "checked": "";


	// Get the URL for the Appearance > Customize screen
	$customize_url = admin_url( 'customize.php' );

	// Get the URL for the Slides screen
	$slides_url = admin_url( 'edit.php?post_type=buddyboss_slides' );

	// Get the URL for the Settings > BuddyPress > Components screen
	$buddypress_components_url = admin_url( 'options-general.php?page=bp-components' );


	// Echo the HTML for the admin panel
	$html = <<<EOF

	<div class="wrap">

		<style type="text/css">
			.buddyboss_divider {
				width: 100%;
				height: 1px;
				line-height: 0;
				overflow: hidden;
				background: #ddd;
				margin: 20px 0 25px;
			}
		</style>

		<h2>BuddyBoss Theme Settings</h2>

		$message

		<div class="welcome-panel">
			<div class="welcome-panel-content">
				
				<h3>Welcome to BuddyBoss</h3>
				<p class="about-description">Thanks for purchasing BuddyBoss! Here are some links to get you started:</p>

				<div class="welcome-panel-column-container">

					<div class="welcome-panel-column">
						<h4>Get Started</h4>
						<a class="button button-primary button-hero" href="http://www.buddyboss.com/tutorials/buddyboss-setup/" target="_blank">Setup Instructions</a>
						<p><a href="http://www.buddyboss.com/affiliates/affiliate-program/" target="_blank">Earn money with our affiliate program!</a></p>
					</div>


					<div class="welcome-panel-column welcome-panel-last">
						<h4>Need some help?</h4>
							<ul>
								<li><a href="http://www.buddyboss.com/faq/" target="_blank">Frequently Asked Questions</a></li>
								<li><a href="http://www.buddyboss.com/support-forums/" target="_blank">Support Forums</a></li>
								<li><a href="http://www.buddyboss.com/release-notes/" target="_blank">Current Version &amp; Release Notes</a></li>
								<li><a href="http://www.buddyboss.com/updating/" target="_blank">How to Update</a></li>
								<li><a href="http://www.buddyboss.com/child-themes/" target="_blank">Guide to Child Themes</a></li>
							</ul>
					</div>

				</div>

			</div>
		</div>

		<div class="buddyboss_divider"></div>

		<h3>Theme Customizations</h3>

		<p>
			To change the <strong>logo</strong>, <strong>colors</strong>, and <strong>fonts</strong> head over to <em><a href="$customize_url">Appearance &rarr; Customize</a></em>.
		</p>

		<div class="buddyboss_divider"></div>

		<h3>Homepage Slider</h3>

		<p>
			To add <strong>slides</strong> to your homepage, head over to the <em><a href="$slides_url">Slides</a> menu</em>.
		</p>

		<div class="buddyboss_divider"></div>

EOF;

	if ( $bp_is_enabled )
	{
		$html .= <<<EOF

	<form action="" method="post">
		<!-- div will hide Wall and Photo uploading if BuddyPress is disabled -->
		<div $bpdisabledhide>

			<h3>User Wall Posting</h3>
			<p>
				Make sure BuddyPress <strong>Friend Connections</strong> and <strong>Activity Streams</strong> are enabled for the Wall to function. Go to <em><a href="$buddypress_components_url">Settings &rarr; BuddyPress &rarr; Components</a></em>.
			</p>

			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label>
								<input type="radio" name="wall" value="1" $wall_on_status /> &nbsp;Allow users to have Profile Walls, News Feeds, and Likes.
							</label>
							<br />
							<label>
								<input type="radio" name="wall" value="0" $wall_off_status /> &nbsp;Disable
							</label>
						</th>
					</tr>
				</tbody>
			</table>
			<br/>

			<h3>User Photo Uploading</h3>

			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label>
								<input type="radio" name="pics" value="1" $pics_on_status /> &nbsp;Allow users to upload photos.
							</label>
							<br />
							<label>
								<input type="radio" name="pics" value="0" $pics_off_status /> &nbsp;Disable
							</label>
						</th>
					</tr>
				</tbody>
			</table>

		</div><!-- end bpdisabled -->

		<p class="submit">
			<input type="submit" class="button button-primary" name="buddyboss_admin_update" value="Save configuration" />
		</p>

		</form>

		<div class="buddyboss_divider"></div>

EOF;
	}

	$html .= <<<EOF

		$bpdisabledmessage

	</div><!-- end .wrap -->
	<div class="clear"></div>
EOF;
	echo $html;
}
?>