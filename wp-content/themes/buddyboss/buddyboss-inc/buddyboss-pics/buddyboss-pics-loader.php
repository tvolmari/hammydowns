<?php

/**
* buddyboss_pics is a BuddyPress plugin combining user activity feeds with photo uploading
*
* Since 3.0, we use the BP components API
*
* @package WordPress
* @subpackage BuddyBoss
* @since BuddyBoss 2.0
*/

// Indicate whether to show debug msgs on screen
if ( !defined('BUDDYBOSS_PICS_SLUG') )
	define( 'BUDDYBOSS_PICS_SLUG', 'pictures');

// DEFAULT CONFIGURATION OPTIONS
$buddyboss_pics_defaults = array(
	"MENU_NAME"		=> __( 'Photos' , 'buddyboss' )
);

if( !class_exists('BuddyBoss_Pics') ) {
	class BuddyBoss_Pics extends BP_Component {
		/**
		* BUDDYPRESS VARIABLES
		*
		* @since BuddyBoss 2.0
		*/

		/**
		* OPTIONS
		*
		* @since BuddyBoss 2.0
		*/
		private $options;

		/**
		 * SHOW INLINE COMMENTS PIC PAGE
		 *
		 * @since BuddyBoss 2.0
		 */
		public $redirect_single = false;
		public $show_single = false;

		/**
		 * PICTURE GRID TEMPLATE VARIABLS
		 *
		 * @since BuddyBoss 2.0
		 */
		public $grid_has_pics = false;
		public $grid_num_pics = 0;
		public $grid_current_pic = null;
		public $grid_pic_index = 0;
		public $grid_data = array();
		public $grid_html = null;
		public $grid_has_run = false;
		public $grid_pagination = null;
		public $grid_num_pages = 0;
		public $grid_current_page = 1;
		public $grid_pics_per_page = 15;

		/**
		* STORAGE
		*
		* @since BuddyBoss 2.0
		*/
		public $cache;

		/**
		* INITIALIZE CLASS
		*
		* @since BuddyBoss 2.0
		*/
		function __construct($options = null)
		{
			parent::start(
				BUDDYBOSS_PICS_SLUG,
				__( 'Photos', 'buddyboss' ),
				dirname( __FILE__ )
			);
		}

		/**
		 * SETUP BUDDYPRESS GLOBAL OPTIONS
		 *
		 * @since	BuddyBoss 2.0
		 */
		function setup_globals( $args = array() )
		{
			global $buddyboss_pics_defaults,  $activity_template;

			if (isset($options) && $options !=null)
			{
				$this->options = $options;
			}
			else
			{
				$this->options = $buddyboss_pics_defaults;
				buddyboss_log("PICS Using default config");
			}

			// Log
			buddyboss_log( $this->options );

			parent::setup_globals( array(
				'has_directory' => false
			) );
		}

		/**
		 * SETUP ACTIONS
		 *
		 * @since  BuddyBoss 3.0
		 */
		public function setup_actions()
		{
			global $buddyboss;

			// Add body class
			add_filter( 'body_class', array( $this, 'add_body_class' ) );

			// Caching
			$this->cache = get_transient('bbpics_cacher');
			add_action( 'wp_shutdown', array($this, 'shutdown') );

			// Globals
			add_action( 'bp_setup_globals',  array( $this, 'setup_globals' ) );

			// Menu
			add_action( 'bp_setup_nav', array( $this, 'setup_bp_menu' ), 100 );
			add_action( 'wp_before_admin_bar_render', array( $this, 'setup_wp_menu' ), 100 );

			// Add a query string to show inline content for single pictures
			$this->redirect_single = (  ( isset( $_GET['buddyboss_ajax_pic'] ) && $_GET['buddyboss_ajax_pic'] === 'true' ) );

			if ( $this->redirect_single === true )
			{
				add_filter( 'bp_activity_permalink_redirect_url', array( $this, 'single_pic_uri' ) );
			}

			// Show single picture without header or footer for inline lightbox
			$this->show_single = (  ( isset( $_GET['buddyboss_ajax_pic_page'] ) && $_GET['buddyboss_ajax_pic_page'] === 'true' ) );

			if ( $this->show_single === true )
			{
				add_filter( 'bp_activity_template_profile_activity_permalink', array( $this, 'single_pic_template' ) );
				add_action( 'after_setup_theme', array( $this, 'single_pic_remove_confirmation_js' ) );
			}

			// Front End Assets
			if ( ! is_admin() )
			{
				add_action( 'wp_enqueue_scripts', array( $this, 'buddyboss_pics_assets' ) );
			}

			parent::setup_actions();
		}

		/**
		 * LOAD ASSETS
		 *
		 * @since  BuddyBoss 3.0
		 */
		function buddyboss_pics_assets()
		{

			// Styles. Load FontAwesome and GoogleFonts first.
			wp_enqueue_style( 'buddyboss-pics-main', get_template_directory_uri() . '/buddyboss-inc/buddyboss-pics/css/buddyboss-pics.css', array( 'fontawesome', 'googlefonts' ), '3.1.9', 'all' );

			// Scripts.
			wp_enqueue_script( 'buddyboss-pics-klass', get_template_directory_uri() . '/buddyboss-inc/buddyboss-pics/js/klass.min.js', array( 'jquery' ), '0.8.7', true );
			wp_enqueue_script( 'buddyboss-pics-popup', get_template_directory_uri() . '/buddyboss-inc/buddyboss-pics/js/code.photoswipe.jquery-3.0.5.min.js', array( 'jquery' ), '0.8.7', true );

			wp_deregister_script( 'plupload' );
			wp_register_script( 'plupload', get_template_directory_uri() . '/buddyboss-inc/buddyboss-pics/js/plupload.full.min.js', array('jquery'), '1.2.1', false );
			wp_enqueue_script( 'plupload' );

			// Localization
			$params = apply_filters( 'buddyboss_pics_js_params', array(
				'filesize'            => apply_filters( 'buddyboss_pics_filesize', '10mb' ),
				'filetypes'           => apply_filters( 'buddyboss_pics_filetypes', 'jpg,jpeg,gif,png,bmp' ),
				'one_moment'          => __( 'One moment...', 'buddyboss' ),
				'upload_error'        => __( 'Error uploading photo', 'buddyboss' ),
				'upload_label'        => __( 'Upload a Picture', 'buddyboss' ),
				'cancel'              => __( 'Cancel', 'buddyboss' ),
				'failed'              => __( 'Failed', 'buddyboss' ),
				'add_photo'           => __( 'Add Photo', 'buddyboss' ),
				'photo_uploading'     => __( 'Photo is currently uploading, please wait!', 'buddyboss' ),
			) );
			wp_localize_script( 'buddyboss-buddypress-js', 'BuddyBossPicsOptions', $params );
		}

		/**
		 * SETUP MENU, ADD NAVIGATION OPTIONS
		 *
		 * @since	BuddyBoss 2.0
		 * @todo: cache the amount of pics
		 */
		function setup_bp_menu()
		{
			global $wpdb, $bp;

			if ( ! isset( $bp->displayed_user->id ) ) return;
			$photos_user_id = $bp->displayed_user->id;
			$activity_table = bp_core_get_table_prefix() . 'bp_activity';
			$activity_meta_table = bp_core_get_table_prefix() . 'bp_activity_meta';
			$groups_table = bp_core_get_table_prefix() . 'bp_groups';

			// Prepare a SQL query to retrieve the activity posts
			// that have pictures associated with them
			$sql = "SELECT COUNT(*) as photo_count FROM $activity_table a
							INNER JOIN $activity_meta_table am ON a.id = am.activity_id
							LEFT JOIN (SELECT activity_id, meta_key, meta_value FROM $activity_meta_table
							           WHERE meta_key = 'activityprivacy') am2 ON a.id = am2.activity_id
	  					LEFT JOIN (SELECT id FROM $groups_table WHERE status != 'public' ) grp ON a.item_id = grp.id
							WHERE a.user_id = %d
							AND (am.meta_key = 'buddyboss_pics_aid' OR am.meta_key = 'bboss_pics_aid')
							AND (a.component != 'groups' || a.item_id != grp.id)";

			$sql = $wpdb->prepare( $sql, $photos_user_id );

			buddyboss_log( ' MENU PHOTO COUNT SQL ' );
			buddyboss_log( $sql );
			$photos_cnt = $wpdb->get_var( $sql );

			/* Add 'Photos' to the main user profile navigation */
			bp_core_new_nav_item( array(
				'name' => sprintf( __( 'Photos <span>%d</span>', 'buddyboss' ), $photos_cnt),
				'slug' => BUDDYBOSS_PICS_SLUG,
				'position' => 80,
				'screen_function' => 'buddyboss_pics_screen_picture_grid',
				'default_subnav_slug' => 'my-gallery'
			) );

			$buddyboss_pics_link = $bp->displayed_user->domain . BUDDYBOSS_PICS_SLUG . '/';

			bp_core_new_subnav_item( array(
				'name' => __( 'Photos', 'buddyboss' ),
				'slug' => 'my-gallery',
				'parent_slug' => BUDDYBOSS_PICS_SLUG,
				'parent_url' => $buddyboss_pics_link,
				'screen_function' => 'buddyboss_pics_screen_picture_grid',
				'position' => 10
			) );
		}

		public function setup_wp_menu()
		{
			// Photos
			if ( is_user_logged_in() )
			{
				global $wp_admin_bar, $bp;

				$buddyboss_pics_link = $bp->loggedin_user->domain . BUDDYBOSS_PICS_SLUG . '/';

				$wp_admin_bar->add_menu( array(
					'parent' => 'my-account-buddypress',
					'id'     => 'my-account-photos',
					'title'  => __( 'Photos', 'buddyboss' ),
					'href'   => $buddyboss_pics_link
				) );

				$wp_admin_bar->add_menu( array(
					'parent' => 'my-account-photos',
					'id'     => 'my-account-photos-view',
					'title'  => __( 'View', 'buddyboss' ),
					'href'   => $buddyboss_pics_link
				) );
			}
		}

		/**
		* SAVES CACHE @ WP SHUTDOWN
		*
		* @since BuddyBoss 1.5
		*/
		function shutdown()
		{
			set_transient('bbpics_cacher', $this->cache);
		}

		/**
		* GET OPTION
		*
		* @since BuddyBoss 1.5
		*/
		function get_option($name)
		{
			if (isset($this->options[$name])) return $this->options[$name];
			return false;
		}

		/**
		* Add active wall class
		*
		* @since BuddyBoss 2.0
		*/
		function add_body_class( $classes )
		{
			$classes[] = 'buddyboss-pics-active';
			return $classes;
		}

		/**
		 * Filter current redirect URL to add single picture comment/view query string
		 *
		 * @since BuddyBoss 2.0
		 */
		public function single_pic_uri( $orig )
		{
			$parts = parse_url( $orig );
			$new = $parts['scheme'] . '://' . $parts['host'] . rtrim( $parts['path'], '/' ) . '/?buddyboss_ajax_pic_page=true';
			return $new;
		}

		/**
		 * Filter current template file to use header and footer free ajax template
		 *
		 * @since BuddyBoss 2.0
		 */
		public function single_pic_template( $tpl )
		{
			return '/buddyboss-inc/buddyboss-pics/templates/single-picture';
		}

		public function single_pic_remove_confirmation_js()
		{
			remove_action( 'wp_head', 'bp_core_confirmation_js', 100 );
		}

	}
} // end of BUDDYBOSS_PICS class

/**
 * Generate content for our picture grid
 *
 * @since BuddyBoss 2.0
 * @todo	Update the theme file (members/single/pictures.php) and create a Wordpress like loop for the images
 					e.g.
 					<?php if ( buddyboss_picgrid_has_pics() ): while( buddyboss_picgrid_has_pics() ): ?>
 						<?php buddyboss_picgrid_thumbnail(); ?>
 						- and -
 						<a href="<?php buddyboss_picgrid_fullsize_url(); ?>" title="<?php buddyboss_picgrid_image_title(); ?>">
 							<img src="<?php buddyboss_picgrid_thumbnail_url(); ?>" width="<?php buddyboss_picgrid_thumbnail_width(); ?>" height="<?php buddyboss_picgrid_thumbnail_height(); ?>" />
 						</a>
 					<?php endwhile; endif; ?>


 					(need to rename these for clarity, I think they're too long (JP))

 					* functions to create:

 					buddyboss_picgrid_has_pics()							For the if/while Wordpress style loop
 					buddyboss_picgrid_attachment_id()					Returns the ID of the current image
 					buddyboss_picgrid_thumbnail()							Echo '<li><a><img>' tags for you of the current thumbnail
 					buddyboss_picgrid_thumbnail_url()					Echos the url location of the current thumbnail
 					get_buddyboss_picgrid_thumbnail_url()			Returns the url location of the current thumbnail
 					buddyboss_picgrid_thumbnail_width()				Echos the current thumbnail width
 					get_buddyboss_picgrid_thumbnail_width()		Returns the current thumbnail width
 					buddyboss_picgrid_thumbnail_height()			Echos the current thumbnail height
 					get_buddyboss_picgrid_thumbnail_height()	Returns the current thumbnail height
 					buddyboss_picgrid_fullsize_url()					Echos the url location of the current full size image
 					get_buddyboss_picgrid_fullsize_url()			Returns the url location of the current thumbnail
 					buddyboss_picgrid_fullsize_width()				Echos the current full size image width
 					get_buddyboss_picgrid_fullsize_width()		Returns the current full size image width
 					buddyboss_picgrid_fullsize_height()				Echos the current full size image height
 					get_buddyboss_picgrid_fullsize_height()		Returns the current full size image height

 */
function buddyboss_pics_screen_picture_grid_content()
{
	global $bp, $wpdb, $buddyboss_pics;

	$wpdb->show_errors = BUDDYBOSS_DEBUG;

	//$img_size = is_active_sidebar( 'Profile' ) ? 'buddyboss_pic_med' : 'buddyboss_pic_wide';
	$img_size = 'buddyboss_pic_wide';

	$gallery_class = is_active_sidebar( 'Profile' ) ? 'gallery has-sidebar' : 'gallery';

	$user_id = $bp->displayed_user->id;
	$activity_table = bp_core_get_table_prefix() . 'bp_activity';
	$activity_meta_table = bp_core_get_table_prefix() . 'bp_activity_meta';
	$groups_table = bp_core_get_table_prefix() . 'bp_groups';

	$pages_sql = "SELECT COUNT(*) FROM $activity_table a
								INNER JOIN $activity_meta_table am ON a.id = am.activity_id
								LEFT JOIN (SELECT activity_id, meta_key, meta_value FROM $activity_meta_table
								           WHERE meta_key = 'activityprivacy') am2 ON a.id = am2.activity_id
								LEFT JOIN (SELECT id FROM $groups_table WHERE status != 'public' ) grp ON a.item_id = grp.id
								WHERE a.user_id = $user_id
								AND (am.meta_key = 'buddyboss_pics_aid' OR am.meta_key = 'bboss_pics_aid')
								AND (a.component != 'groups' || a.item_id != grp.id)";

	$buddyboss_pics->grid_num_pics = $wpdb->get_var($pages_sql);

	$buddyboss_pics->grid_current_page = isset( $_GET['page'] ) ? (int) $_GET['page'] : 1;

	// Prepare a SQL query to retrieve the activity posts
	// that have pictures associated with them
	$sql = "SELECT a.*, am.meta_value, am2.meta_value as privacy FROM $activity_table a
					INNER JOIN $activity_meta_table am ON a.id = am.activity_id
					LEFT JOIN (SELECT activity_id, meta_key, meta_value FROM $activity_meta_table
					           WHERE meta_key = 'activityprivacy') am2 ON a.id = am2.activity_id
					LEFT JOIN (SELECT id FROM $groups_table WHERE status != 'public' ) grp ON a.item_id = grp.id
					WHERE a.user_id = $user_id
					AND (am.meta_key = 'buddyboss_pics_aid' OR am.meta_key = 'bboss_pics_aid')
					AND (a.component != 'groups' || a.item_id != grp.id)
					ORDER BY a.date_recorded DESC";

	buddyboss_log("SQL: $sql");

	$pics = $wpdb->get_results($sql,ARRAY_A);

	$buddyboss_pics->grid_pagination = new BuddyBoss_Paginated( $pics, $buddyboss_pics->grid_pics_per_page, $buddyboss_pics->grid_current_page );

	buddyboss_log("RESULT: $pics");

	// If we have results let's print out a simple grid
	if ( !empty( $pics ) )
	{
		$buddyboss_pics->grid_had_pics = true;
		$buddyboss_pics->grid_num_pics = count( $pics );

		/**
		 * DEBUG
		 */
		// echo '<br/><br/><div style="display:block;background:#f0f0f0;border:2px solid #ccc;margin:20px;padding:15px;color:#333;"><pre>';
		// var_dump( $pics );
		// echo '</pre></div><hr/><br/><br/><br/><br/>';
		// die;
		/**/

		$html_grid = '<ul class="'.$gallery_class.'" id="buddyboss-pics-grid">'."\n";

		foreach( $pics as $pic )
		{
			/**
			 * DEBUG
			 */
			// echo '<br/><br/><div style="display:block;background:#f0f0f0;border:2px solid #ccc;margin:20px;padding:15px;color:#333;"><pre>';
			// var_dump( bp_activity_get_permalink($pic['id']), $pic );
			// echo '</pre></div><hr/><br/><br/><br/><br/>';
			// die;
			/**/

			//BP ACTIVITY PRIVACY FIX
			if( function_exists( 'bp_activity_privacy_add_js' ) ){
			$is_super_admin = is_super_admin();
			$bp_displayed_user_id = bp_displayed_user_id();
			$bp_loggedin_user_id = bp_loggedin_user_id();

			if( $pic['privacy'] == 'loggedin' && !$bp_loggedin_user_id )
						continue;
			if( $pic['privacy'] == 'friends' && !friends_check_friendship( $bp_loggedin_user_id, $bp_displayed_user_id ) && $bp_loggedin_user_id != $bp_displayed_user_id )
						continue;
			if( $pic['privacy'] == 'groupfriends' && (!friends_check_friendship( $bp_loggedin_user_id, $bp_displayed_user_id || !groups_is_user_member( $bp_loggedin_user_id, $bp_displayed_user_id )) ) )
						continue;
			if( $pic['privacy'] == 'grouponly' && !groups_is_user_member( $bp_loggedin_user_id, $bp_displayed_user_id ) )
						continue;
			if( $pic['privacy'] == 'groupmoderators' && !groups_is_user_mod( $bp_loggedin_user_id, $bp_displayed_user_id ) )
						continue;
			if( $pic['privacy'] == 'groupadmins' && !groups_is_user_admin( $bp_loggedin_user_id, $bp_displayed_user_id ) )
						continue;
			if( $pic['privacy'] == 'adminsonly' && !$is_super_admin )
						continue;
			if( $pic['privacy'] == 'onlyme' && $bp_loggedin_user_id != $bp_displayed_user_id )
						continue;
			}
			$attachment_id = isset($pic['meta_value']) ? (int)$pic['meta_value'] : 0;

			// Make sure we have a valid attachment ID
			if ( $attachment_id > 0 )
			{
				// Let's get the permalink of this attachment to show within a lightbox
				$permalink = bp_activity_get_permalink( $pic[ 'id' ] );
				$ajax_link = rtrim( $permalink, '/' ) . '/?buddyboss_ajax_pic=true';

				// Let's get the caption
				$action = '';
				if ( bp_has_activities( 'include='.$pic['id'] ) )
				{
					while ( bp_activities() )
					{
						bp_the_activity();
						$action = '<div class="buddyboss_pics_action">'. bp_get_activity_action() . '</div>';
					}
				}

				// Grab the image details
				$image = wp_get_attachment_image_src( $attachment_id, $img_size );

				// grab the thumbnail details
				$tn = wp_get_attachment_image_src( $attachment_id, 'buddyboss_pic_tn' );

				if ( is_array($tn) && !empty($tn) && isset($tn[0]) && $tn[0] != '' )
				{
					$buddyboss_pics->grid_data[] = array(
						'attachment'	=> $attachment_id,
						'action'      => $action,
						'image'				=> $image,
						'tn'					=> $tn,
						'permalink'		=> $permalink,
						'ajaxlink'		=> $ajax_link
					);

					$html_grid .= '<li class="gallery-item"><div><a rel="gal_item" href="' . $image[0] . '"><img src="'.$tn[0].'" width="'.$tn[1].'" height="'.$tn[2].'" /></a></div></li>'."\n";
				}
			}
		}

		$html_grid .= '</ul>'."\n\n";

		$buddyboss_pics->grid_html = $html_grid;

		$buddyboss_pics->grid_has_pics = true;
	}
	else {
		$buddyboss_pics->grid_has_pics = false;
		$buddyboss_pics->grid_num_pics = 0;
		$buddyboss_pics->grid_current_pic = null;
		$buddyboss_pics->grid_data = array();
		$buddyboss_pics->grid_html = null;
	}
}

/**
 * Show the grid of uploaded pictures for a user
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_pics_screen_picture_grid()
{
	add_action( 'bp_template_content', 'buddyboss_pics_screen_picture_grid_content' );

	bp_core_load_template( apply_filters( 'buddyboss_pics_pictures', 'buddypress/members/single/pictures' ) );
}
$ii = 0;
/**
 * Check if a picture grid has pictures
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_has_pics()
{
	global $buddyboss_pics, $ii;
	$ii++; if ( $ii > 25 ) return false;
	if ( $buddyboss_pics->grid_has_run === false )
	{
		buddyboss_pics_screen_picture_grid_content();
		$buddyboss_pics->grid_has_run = true;
		return $buddyboss_pics->grid_has_pics;
	}

	if ( $buddyboss_pics->grid_has_pics === true )
	{
		if ( $buddyboss_pics->grid_has_run === true )
		{
			if ( $buddyboss_pics->grid_num_pics < $buddyboss_pics->grid_pic_index )
			{
				return false;
			}
		}


		$buddyboss_pics->grid_current_pic = $buddyboss_pics->grid_pagination->fetchPagedRow();

		if ( $buddyboss_pics->grid_current_pic === false )
		{
			return false;
		}

	}

	return $buddyboss_pics->grid_has_pics;
}

/**
 * Handles the enxt picture in the loop
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_the_pic()
{
	global $buddyboss_pics;

	buddyboss_setup_next_pic();
}

/**
 * Setup the next picture
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_setup_next_pic()
{
	global $buddyboss_pics;

	++$buddyboss_pics->grid_pic_index;
}

/**
 * buddyboss_pics_html_grid
 * buddyboss_pic_attachment_id
 * buddyboss_pic_image
 * buddyboss_pic_tn
 * buddyboss_pic_permalink
 */
function get_buddyboss_pics_html_grid()
{
	return $buddyboss_pics->html_grid;
}
function get_buddyboss_pic_attachment_id()
{
	global $buddyboss_pics;

	if ( isset( $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['attachment_id'] ) )
		return $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['attachment_id'];

	return '';
}
function get_buddyboss_pic_image()
{
	global $buddyboss_pics;

	if ( isset( $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['image'] ) )
		return $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['image'];

	return array();
}
function get_buddyboss_pic_tn()
{
	global $buddyboss_pics;

	if ( isset( $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['tn'] ) )
		return $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['tn'];

	return array();
}
function get_buddyboss_pic_permalink()
{
	global $buddyboss_pics;

	if ( isset( $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['permalink'] ) )
		return $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['permalink'];

	return '';
}
function get_buddyboss_pic_ajaxlink()
{
	global $buddyboss_pics;

	if ( isset( $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['ajaxlink'] ) )
		return $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['ajaxlink'];

	return '';
}
function get_buddyboss_pic_link()
{
	global $buddyboss_pics;

	if ( isset( $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['image'][0] ) )
		return $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['image'][0];

	return '';
}
function get_buddyboss_pic_action()
{
	global $buddyboss_pics;

	if ( isset( $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['action'] ) )
		return $buddyboss_pics->grid_data[ $buddyboss_pics->grid_current_pic ]['action'];

	return '';
}
function buddyboss_pics_pagination()
{
	global $buddyboss_pics;

	echo $buddyboss_pics->grid_pagination->fetchPagedNavigation();
}
function buddyboss_pics_ajax_picture( $activity_id )
{
	global $bp, $wpdb, $buddyboss_pics;

	$user_id = (int)$bp->displayed_user->id;
	$activity_id = (int)$activity_id;
	$activity_table = bp_core_get_table_prefix() . 'bp_activity';
	$activity_meta_table = bp_core_get_table_prefix() . 'bp_activity_meta';

	$wpdb->show_errors = BUDDYBOSS_DEBUG;

	$sql = $wpdb->prepare( "SELECT a.*, am.meta_value FROM
					$activity_table a INNER JOIN $activity_meta_table am
					ON a.id = am.activity_id
					WHERE a.user_id = %d
					AND (meta_key = 'buddyboss_pics_aid' OR meta_key = 'bboss_pics_aid')
					AND activity_id = %d
					ORDER BY a.date_recorded DESC", $user_id, $activity_id );

	$pic_res = $wpdb->get_results( $sql, ARRAY_A );

	$html = '';

	// If we have results
	if ( !empty( $pic_res ) )
	{
		$pic = array_pop( $pic_res );

		$attachment_id = isset($pic['meta_value']) ? (int)$pic['meta_value'] : 0;

		// Make sure we have a valid attachment ID
		if ( $attachment_id > 0 )
		{
			$img = wp_get_attachment_image_src( $attachment_id, 'buddyboss_pic_large' );

			if ( is_array($img) && !empty($img) && isset($img[0]) && $img[0] != '' )
			{
				$html = '<img src="'. esc_url( $img[0] ) .'" />';
			}
		}
	}

	return $html;
}

/* FILTERS */
if ( BUDDYBOSS_PICS_ENABLED )
{
	add_filter( 'bp_activity_after_save', 'buddyboss_pics_input_filter'  );
	add_filter( 'bp_get_activity_action', 'buddyboss_pics_read_activity_filter' );
	add_filter( 'bp_get_activity_content_body', 'buddyboss_pics_read_content_filter' );
	// add_action( 'buddyboss_pics_pic_posted', 'buddyboss_pics_send_notification' );
}
else {
	add_filter( 'bp_get_activity_content_body', 'buddyboss_pics_off_read_content_filter' );
}

/**
 * This will save some data to the activity meta table when someone posts a picture
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_pics_input_filter( &$activity )
{
	global $bp, $buddyboss_wall;

	$user = $bp->loggedin_user;
	$new_action = $result = false;

	if ( strstr( $activity->content, 'class="buddyboss-pics-photo-link"' ) !== false
			 && isset($_POST['has_pic']) && isset($_POST['has_pic']['attachment_id']) )
	{
		$action  = '<a href="'.$user->domain.'">'.$user->fullname.'</a> '
			. __( 'posted a new picture', 'buddyboss' );

		$attachment_id = (int)$_POST['has_pic']['attachment_id'];

		bp_activity_update_meta( $activity->id, 'buddyboss_pics_action', $action );
		bp_activity_update_meta( $activity->id, 'buddyboss_pics_aid', $attachment_id );

    // Execute our after save action
		do_action( 'buddyboss_pics_pic_posted', $activity, $attachment_id, $action );

		// Prevent BuddyPress from sending notifications, we'll send our own

	}
}

/**
 * This filters pics actions, when reading an item it will convert it to use pics language
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_pics_read_activity_filter($action)
{
	global $activities_template;

	$current_activity = $activities_template->current_activity;

	$current_activity_id = $activities_template->activities[ $current_activity ]->id;

	$buddyboss_pics_action = bp_activity_get_meta( $current_activity_id, 'buddyboss_pics_action' );

	if ( ! $buddyboss_pics_action )
		$buddyboss_pics_action = bp_activity_get_meta( $current_activity_id, 'bboss_pics_action' );

	if ( $buddyboss_pics_action )
	{
		$with_meta = $buddyboss_pics_action  . ' <a class="activity-time-since"><span class="time-since">' . bp_core_time_since( bp_get_activity_date_recorded() ) . '</span></a>';

		if ( $with_meta )
			return $with_meta;

		return $buddyboss_pics_action;
	}

	return $action;
}

/**
 * This filters pics content, when reading an item it will convert it to use pics language
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_pics_read_content_filter( $filter )
{
	global $buddyboss_pics_img_size, $activities_template;

	$curr_id = $activities_template->current_activity;

	$act_id = (int)$activities_template->activities[$curr_id]->id;

	// Check for activity ID in $_POST if this is a single
	// activity request from a [read more] action
	if ( $act_id === 0 && ! empty( $_POST['activity_id'] ) )
	{
		$activity_array = bp_activity_get_specific( array(
			'activity_ids'     => $_POST['activity_id'],
			'display_comments' => 'stream'
		) );

		$activity = ! empty( $activity_array['activities'][0] ) ? $activity_array['activities'][0] : false;

		$act_id = (int)$activity->id;
	}

	// This should never happen, but if it does, bail.
	if ( $act_id === 0 )
	{
		return $filter;
	}

	$buddyboss_pics_aid = bp_activity_get_meta( $act_id, 'buddyboss_pics_aid' );

	// Support for legacy BuddyBoss (we used to store meta keys with bboss_ before normalizing)
	if ( ! $buddyboss_pics_aid )
		$buddyboss_pics_aid = bp_activity_get_meta( $act_id, 'bboss_pics_aid' );

	$img_size = is_active_sidebar( 'Profile' ) ? 'buddyboss_pic_med' : 'buddyboss_pic_wide';

	$image = wp_get_attachment_image_src( $buddyboss_pics_aid, $img_size );

	if ( ! empty( $image ) && is_array( $image ) && count( $image ) > 2 )
	{
		$src = $image[0];
		$w = $image[1];
		$h = $image[2];

		$full = wp_get_attachment_image_src( $buddyboss_pics_aid, 'full' );

		$width_markup = $w > 0 ? ' width="'.$w.'"' : '';

		if ( $full !== false && is_array( $full ) && count( $full ) > 2 )
		{
			$filter .= '<a class="buddyboss-pics-photo-wrap" href="'.$full[0].'">';
			$filter .= '<img data-permalink="'. bp_get_activity_thread_permalink() .'" class="buddyboss-pics-photo" src="'.$src.'"'.$width_markup.' /></a>';
		}
		else {
			$filter .= '<img data-permalink="'. bp_get_activity_thread_permalink() .'" class="buddyboss-pics-photo" src="'.$src.'"'.$width_markup.' /></a>';
		}
	}

	return $filter;
}

/**
 * This filters pics content when off, when reading an item it will convert it to the image filename
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_pics_off_read_content_filter( $filter )
{
	global $buddyboss_pics_img_size, $activities_template;

	$curr_id = $activities_template->current_activity;

	$act_id = $activities_template->activities[$curr_id]->id;

	$buddyboss_pics_aid = bp_activity_get_meta( $act_id, 'buddyboss_pics_aid' );

	// Support for legacy BuddyBoss (we used to store meta keys with bboss_ before normalizing)
	if ( ! $buddyboss_pics_aid )
		$buddyboss_pics_aid = bp_activity_get_meta( $act_id, 'bboss_pics_aid' );

	$image = wp_get_attachment_image_src( $buddyboss_pics_aid, 'full' );

	if ( $image !== false && is_array( $image ) && count( $image ) > 2 )
	{
		$src = $image[0];
		$w = $image[1];
		$h = $image[2];
		$filter .= '<a href="'. $image[0] .'" target="_blank">'. basename( $image[0] ) .'</a>';
	}

	return $filter;
}

/**
 * CLEANUP DATABASE AND RECONCILE WITH WP MEDIA LIBRARY
 * This ensures that attachments deleted from the WP-admin also
 * delete associated BP activity and BuddyBoss pics
 */
function buddyboss_pics_cleanup_db()
{
	global $wpdb, $bp;

  // We don't need to do anything unless BP Activity component is active
  if ( ! bp_is_active( 'activity' ) )
    return;

	$activity_table = $bp->activity->table_name;
	$activity_meta_table = $bp->activity->table_name_meta;
	$posts_table = $wpdb->posts;

	// Prepare a SQL query to retrieve the activity posts
	// that have pictures associated with them
	$all_aids_sql = "SELECT am.meta_value, am.activity_id FROM $activity_table a
									 INNER JOIN $activity_meta_table am ON a.id = am.activity_id
									 WHERE am.meta_key = 'buddyboss_pics_aid'";

	// Now perpare a SQL query to retrieve all attachments
	// that are BuddyBoss wall pictures AND are published in the media library
	$existing_sql = "SELECT am.meta_value FROM $activity_table a
									 INNER JOIN $activity_meta_table am ON a.id = am.activity_id
									 INNER JOIN $posts_table p ON am.meta_value = p.ID
									 WHERE am.meta_key = 'buddyboss_pics_aid'
									 AND p.post_status = 'inherit'
									 AND p.post_parent = 0";

	// Query the database for all attachment IDS
	$all_aids = (array) $wpdb->get_results( $all_aids_sql, ARRAY_A );

	// Query the database for all pics in the media library that are BuddyBoss pics
	$existing_aids = (array) $wpdb->get_col( $existing_sql );

	// If we have a result set
	if ( !empty( $all_aids ) )
	{
		// Prepare array
		$attachment_ids = $activity_ids = $aids2activity = array();
		foreach ( $all_aids as $aid )
		{
			$attachment_ids[] = $aid['meta_value'];
			$aids2activity[ $aid['meta_value'] ] = $activity_ids[] = $aid['activity_id'];
		}

		// Lets get the difference of our published vs. orphaned IDs
		$orphans = array_diff( $attachment_ids, $existing_aids );

		// Delete related activity stream posts
		if ( !empty( $orphans ) )
		{
			$orphan_acitivity_ids = array();

			foreach ( $orphans as $orphan )
			{
				if ( isset( $aids2activity[ $orphan ] ) )
				{
					$orphan_acitivity_ids[] = $aids2activity[ $orphan ];
				}
			}

			$orphan_acitivity_ids_string = implode( ',', $orphan_acitivity_ids );

			$sql = "DELETE FROM $activity_table WHERE id IN ($orphan_acitivity_ids_string)";

			$deleted = $wpdb->query( $sql );

			BP_Activity_Activity::delete_activity_item_comments( $orphan_acitivity_ids );
			BP_Activity_Activity::delete_activity_meta_entries( $orphan_acitivity_ids );
		}
	}
}

/**
 * HOOK INTO MEDIA LIBRARY ON ITEM DELETE
 */
function buddyboss_after_attachment_deleted()
{
	add_action( 'clean_post_cache', 'buddyboss_pics_cleanup_db' );
}
add_action( 'delete_attachment', 'buddyboss_after_attachment_deleted' );


/**
* ACTIVATION AND DEACTIVATION CODE
*/
function buddyboss_pics_on_activate()
{
	buddyboss_pics_cleanup_db();

	return 'The Picture Gallery was activated successfully';
}


/**
* Deregister the BuddyBoss Pics Component
*
* @since BuddyBoss 2.0
*/
function buddyboss_pics_on_deactivate()
{
	buddyboss_pics_cleanup_db();

	return '';
}

function buddyboss_pics_upload_dir( $filter )
{
	return $filter;
}

// AJAX update picture
function buddyboss_pics_post_photo()
{
	global $bp, $buddyboss;

	// Check the nonce
	check_admin_referer( 'post_update', '_wpnonce_post_update' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) )
	{
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	}

	if ( ! function_exists('media_handle_upload' ) )
	{
		require_once(ABSPATH . 'wp-admin/includes/admin.php');
	}

	add_filter( 'upload_dir', 'buddyboss_pics_upload_dir' );

	$aid = media_handle_upload( 'file', 0 );

	remove_filter( 'upload_dir', 'buddyboss_pics_upload_dir' );

	// Image rotation fix
	do_action( 'buddyboss_add_attachment', $aid );

	$attachment = get_post( $aid );

	$name = $url = null;

	if ( $attachment !== null )
	{
		$name = $attachment->post_title;

		$img_size = is_active_sidebar( 'Profile' ) ? 'buddyboss_pic_med' : 'buddyboss_pic_wide';

		$url_nfo = wp_get_attachment_image_src( $aid, $img_size );

		$url = is_array( $url_nfo ) && !empty( $url_nfo ) ? $url_nfo[0] : null;
	}

	$result = array(
		'status'					=> ( $attachment !== null ),
		'attachment_id'		=> $aid,
		'url'							=> $url,
		'name'						=> $name
	);

	echo htmlspecialchars( json_encode( $result ), ENT_NOQUOTES );

	exit(0);
}
add_action( 'wp_ajax_buddyboss_pics_post_photo', 'buddyboss_pics_post_photo' );

class BuddyBoss_Paginated
{
	private $rs;                  					//result set
	private $pageSize;                      //number of records to display
	private $pageNumber;                    //the page to be displayed
	private $rowNumber;                     //the current row of data which must be less than the pageSize in keeping with the specified size
	private $offSet;
	private $layout;

	function __construct( $obj, $displayRows = 10, $pageNum = 1 )
	{
		$this->setRs( $obj );
		$this->setPageSize( $displayRows );
		$this->assignPageNumber( $pageNum );
		$this->setRowNumber( 0 );
		$this->setOffSet( ( $this->getPageNumber() - 1 ) * ( $this->getPageSize() ) );
	}

	//implement getters and setters
	public function setOffSet( $offSet )
	{
		$this->offSet = $offSet;
	}

	public function getOffSet()
	{
		return $this->offSet;
	}


	public function getRs()
	{
		return $this->rs;
	}

	public function setRs( $obj )
	{
		$this->rs = $obj;
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function setPageSize( $pages )
	{
		$this->pageSize = $pages;
	}

	//accessor and mutator for page numbers
	public function getPageNumber()
	{
		return $this->pageNumber;
	}

	public function setPageNumber( $number )
	{
		$this->pageNumber = $number;
	}

	//fetches the row number
	public function getRowNumber()
	{
		return $this->rowNumber;
	}

	public function setRowNumber( $number )
	{
		$this->rowNumber = $number;
	}

	public function fetchNumberPages()
	{
		if ( !$this->getRs() )
		{
			return false;
		}

		$pages = ceil( count( $this->getRs() ) / (float) $this->getPageSize() );
		return $pages;
	}

	//sets the current page being viewed to the value of the parameter
	public function assignPageNumber($page) {
		if(($page <= 0) || ($page > $this->fetchNumberPages()) || ($page == "")) {
			$this->setPageNumber(1);
		}
		else {
			$this->setPageNumber($page);
		}
		//upon assigning the current page, move the cursor in the result set to (page number minus one) multiply by the page size
		//example  (2 - 1) * 10
	}

	public function fetchPagedRow()
	{
		if( ( !$this->getRs() ) || ( $this->getRowNumber() >= $this->getPageSize() ) )
		{
			return false;
		}

		$this->setRowNumber( $this->getRowNumber() + 1 );
		$index = $this->getOffSet();
		$this->setOffSet( $this->getOffSet() + 1 );
		return $index;
	}

	public function isFirstPage()
	{
		return ( $this->getPageNumber() <= 1 );
	}

	public function isLastPage()
	{
		return ( $this->getPageNumber() >= $this->fetchNumberPages() );
	}

	public function fetchPagedLinks($parent, $queryVars)
	{
		$currentPage = $parent->getPageNumber();
		$str = "<div class='pagination'>";

		if( !$parent->isFirstPage() )
		{
			if( $currentPage != 1 && $currentPage != 2 && $currentPage != 3 )
			{
				$str .= "<a href='?page=1$queryVars' title='Start' class='pag-first'>". __( 'First' , 'buddyboss' ) . " (1)</a>";
			}
		}

		for( $i = $currentPage - 2; $i <= $currentPage + 2; $i++ )
		{
			//if i is less than one then continue to next iteration
			if( $i < 1 )
			{
				continue;
			}

			if( $i > $parent->fetchNumberPages() )
			{
				break;
			}

			if( $i == $currentPage )
			{
				$str .= "<span class='current'>$i</span>";
			}
			else {
				$str .= "<a class='pag-page' href=\"?page=$i$queryVars\">$i</a>";
			}



			if ( $currentPage != $parent->fetchNumberPages() || $i != $parent->fetchNumberPages() )
				$str .= '';

		}//end for

		if ( !$parent->isLastPage() )
		{
			if( $currentPage != $parent->fetchNumberPages() && $currentPage != $parent->fetchNumberPages() -1 && $currentPage != $parent->fetchNumberPages() - 2 )
			{
				$str .= "<a class='pag-last' href=\"?page=".$parent->fetchNumberPages()."$queryVars\" title=\"Last\">" . __( 'Last' , 'buddyboss' ) . " (".$parent->fetchNumberPages().")</a>";
			}
		}

		$str .= "</div>";

		return $str;
	}

	public function fetchPagedNavigation( $queryVars = "" )
	{
		if ( count( $this->getRs() ) > $this->getPageSize() )
			return $this->fetchPagedLinks( $this, $queryVars );
	}

} //end BuddyBoss_Paginated
?>