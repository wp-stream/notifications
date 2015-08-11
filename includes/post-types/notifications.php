<?php
namespace Stream\Stream_Notifications\Notifications;


$notifications_page_slug = 'stream_notifications';

/**
 * Default setup routine
 *
 * @uses add_action()
 * @uses do_action()
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'register_cpt' ) );

	do_action( 'stream_notifications_cpt_register' );
}


/**
* Registers a new post type
* @uses $wp_post_types Inserts new post type object into the list
*
* @param string  Post type key, must not exceed 20 characters
* @param array|string  See optional args description above.
* @return object|WP_Error the registered post type object, or an error object
*/
function register_cpt() {

	$labels = array(
		'name'                => __( 'Notifications', 'stream_notifications' ),
		'singular_name'       => __( 'Notification', 'stream_notifications' ),
		'add_new'             => _x( 'Add New Notification', 'stream_notifications', 'stream_notifications' ),
		'add_new_item'        => __( 'Add New Notification', 'stream_notifications' ),
		'edit_item'           => __( 'Edit Notification', 'stream_notifications' ),
		'new_item'            => __( 'New Notification', 'stream_notifications' ),
		'view_item'           => __( 'View Notification', 'stream_notifications' ),
		'search_items'        => __( 'Search Notifications', 'stream_notifications' ),
		'not_found'           => __( 'No Notifications found', 'stream_notifications' ),
		'not_found_in_trash'  => __( 'No Notifications found in Trash', 'stream_notifications' ),
		'parent_item_colon'   => __( 'Parent Notification:', 'stream_notifications' ),
		'menu_name'           => __( 'Notifications', 'stream_notifications' ),
	);

	$args = array(
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => 'description',
		'taxonomies'          => array(),
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => null,
		'show_in_nav_menus'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'has_archive'         => true,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite'             => true,
		'capability_type'     => 'post',
		'supports'            => array(
			'title', 'revisions'
			)
	);

	register_post_type( get_notifications_cpt_slug(), $args );
}

/**
 * Return Stream Notifications CPT slug
 *
 * @return slug    Notification CPT slug
 */
function get_notifications_cpt_slug() {
	return 'stream-notifications';
}
