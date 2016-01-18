<?php

/**
 * Admin/Network admin Users list helper
 * 
 */
class BP_Member_Type_User_Page {

	/**
	 *
	 * @var BP_Member_Type_Generator_Admin_User_List_Helper
	 */
	private static $instance = null;
	private $post_type = '';
	private $message = '';

	private function __construct() {

		$this->post_type = bmt_get_post_type();

		$this->init();
	}

	/**
	 * 
	 * @return BP_Member_Type_Admin_List_Helper
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	private function init() {
		add_filter( 'manage_users_columns', array( $this, 'user_role_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'user_role_row'), 999, 3 );
	}
	
	/**
	 * Add Member Type column to the WordPress Users table
	 *
	 * @param array $columns Users table columns
	 * @return array $columns
	 */
	public static function user_role_column( $columns = array() ) {
		$columns['member_type'] = __( 'Member Type',  'bp-member-types' );
		return $columns;
	}
	
	/**
	 * Return member type for display in the WordPress Users list table
	 *
	 * @param string $empty
	 * @param string $column_name
	 * @param int $user_id
	 *
	 * @return string Displayable bbPress user role
	 */
	public static function user_role_row( $empty = '', $column_name, $user_id ){
		// Only looking for member type user role column
        switch ( $column_name ) {
            case 'member_type':
                // Get the member type
                $member_type = bp_get_member_type( $user_id );
                if( isset($member_type) && !empty($member_type) ){
                    return ucfirst($member_type);
                }
                break;

            default:
                break;
        }
	}

}

BP_Member_Type_User_Page::get_instance();
