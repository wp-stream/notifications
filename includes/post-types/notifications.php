<?php
/**
 * @todo add php doc blocks
 */
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

	$columns_filter_hook = 'manage_edit-' . get_notifications_cpt_slug() . '_columns';
	$columns_action_hook = 'manage_' . get_notifications_cpt_slug() . '_posts_custom_column';

	add_action( 'init', $n( 'register_cpt' ) );
	add_action( 'save_post', $n( 'save' ) );

	// replace publish metabox
	add_action( 'add_meta_boxes_' . get_notifications_cpt_slug(), $n( 'replace_submit_div' ) );
	add_action( 'admin_head-post.php',                            $n( 'hide_publishing_actions' ) );
	add_action( 'admin_head-post-new.php',                        $n( 'hide_publishing_actions' ) );
	add_action( 'post_submitbox_misc_actions',                    $n( 'custom_publish_meta' ) );
	add_action( $columns_action_hook,                             $n( 'notifications_column_data' ), 10, 2 );

	add_filter( 'post_updated_messages',                          $n( 'custom_messages_for_notifications' ), 10, 1 );
	add_filter( 'gettext',                                        $n( 'change_submit_button_text' ), 10, 2 );
	add_filter( $columns_filter_hook,                             $n( 'notifications_columns_filter' ), 10, 1 );
	add_filter( 'display_post_states' ,                           $n( 'change_state_labels' ), 10, 1 );
	add_filter( 'views_edit-' . get_notifications_cpt_slug(),     $n( 'change_status_labels' ), 10, 1 );
	add_filter( 'post_row_actions',                               $n( 'clean_up_row_actions' ), 10, 2 );

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
		'show_in_admin_bar'   => false,
		'menu_position'       => null,
		'menu_icon'           => null,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
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

function custom_publish_meta() {
	$post = get_post();
	if ( get_post_type( $post ) === get_notifications_cpt_slug() ) {
		?>
		<div>
			<?php wp_nonce_field( 'notification_save_nonce', 'save_notification_status' ); ?>
			<div class="misc-pub-section misc-pub-section-notifications">
				<?php
				$val = ( get_post_status( $post->ID ) === 'publish' ) ? 'publish' : 'draft';
				?>
				<input type="radio" name="active_inactive" id="active_inactive-active" value="publish" <?php checked( $val, 'publish' ); ?>/>
				<label for="active_inactive-active" class="select-it"><?php esc_html_e( 'Active', 'stream_notifications' ); ?></label>
			</div>
			<div class="misc-pub-section misc-pub-section-notifications">
				<input type="radio" name="active_inactive" id="active_inactive-inactive" value="draft" <?php checked( $val,'draft' ); ?>/>
				<label for="active_inactive-inactive" class="select-it"><?php esc_html_e( 'Inactive', 'stream_notifications' ); ?></label>
			</div>
		</div>
		<?php
	}
}

/**
 * Hide publish actions with css
 * 
 * @return void
 */
function hide_publishing_actions() {
	$post = get_post();
	if ( get_post_type( $post ) === get_notifications_cpt_slug() ) {
		?>
		<style>
			.misc-pub-section:not(.misc-pub-section-notifications),
			#minor-publishing-actions{
				display:none;
			}
		</style>
		<?php
	}
}


/**
 * Replace defualt publishing metabox with custom
 * 
 * @return void
 */
function replace_submit_div() {
	remove_meta_box( 'submitdiv', get_notifications_cpt_slug(), 'side' );

	add_meta_box(
		'submitdiv',
		__( 'Notifications control', 'stream_notifications' ),
		'\post_submit_meta_box',
		get_notifications_cpt_slug(),
		'side',
		'high'
	);
}


/**
 * Add custom messages for notifications
 * 
 * @param  array $messages Array with all messages for post type
 * @return array           Updated array with all messages for notifications
 */
function custom_messages_for_notifications( $messages ) {
	$post = get_post();
	if ( ! is_a( $post, 'WP_Post' ) ) {
		return $messages;
	}

	// store post ID;
	$post_id = $post->ID;

	if( $post->post_type === get_notifications_cpt_slug() ) {
		$messages[ get_notifications_cpt_slug() ] = array(
			0 => '',
			1 => __( 'Notification updated.' ),
			2 => __( 'Custom field updated.' ),
			3 => __( 'Custom field deleted.' ),
			4 => __( 'Notification updated.' ),

			5 => isset( $_GET['revision'] ) ? sprintf( __('Notification restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Notification activated.' ),
			7 => __( 'Notification saved.' ),
			8 => __( 'Notification submitted' ),
			9 => sprintf( __( 'Notification scheduled for: <strong>%1$s</strong>.'),
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Notification deactivated.' ),
		);
	}

	return $messages;
}




function save( $post_id ) {
	// Check if our nonce is set.
	if ( ! isset( $_POST['save_notification_status'] ) ) {
		return $post_id;
	}

	$nonce = $_POST['save_notification_status'];

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $nonce, 'notification_save_nonce' ) ) {
		return $post_id;
	}

	// If this is an autosave, our form has not been submitted,
	// so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// Check the user's permissions.
	if ( 'page' === $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	}

	$post = get_post();
	// Sanitize the user input.
	if( isset( $_POST[ 'active_inactive' ] ) ) {
		$status = sanitize_text_field( $_POST['active_inactive'] );

		/**
		 * from http://codex.wordpress.org/Function_Reference/wp_update_post
		 */
		if ( ! wp_is_post_revision( $post_id ) && $post->post_status !== $status ) {
			$update_post['ID'] = $post_id;
			$update_post['post_status'] = $status;

			// unhook this function so it doesn't loop infinitely
			remove_action( 'save_post', __NAMESPACE__ . '\save' );

			// update the post, which calls save_post again
			wp_update_post( $update_post );

			// re-hook this function
			add_action( 'save_post', __NAMESPACE__ . '\save' );
		}
	}
}



function change_submit_button_text( $translation, $text ) {
	$post = get_post();

	if( ! is_a( $post, 'WP_Post' ) ) {
		return $translation;
	}

	if( $post->post_type === get_notifications_cpt_slug() ) {
		if( 'Publish' === $text || 'Update' === $text ) {
			return __( 'Save Changes', 'stream_notifications' );
		}
	}

	return $translation;
}



function notifications_column_data( $column, $post_id ) {
	$post = get_post( $post_id );
	echo ( $post->post_status !== 'draft' ) ? esc_html__( 'Active', 'stream_notifications' ) : esc_html__( 'Inactive', 'stream_notifications' );
}


function notifications_columns_filter( $columns ) {

	$new_columns = array(
		'status' => __( 'Status', 'stream_notifications' ),
	);

    return array_merge( $columns, $new_columns );
}



function change_state_labels( $post_states ) {
	$post = get_post();
	if( isset( $post_states['draft'] ) && $post->post_type === get_notifications_cpt_slug() ) {
		$post_states['draft'] = __( 'Inactive', 'stream_notifications' );
	}

	if( isset( $post_states['publish'] ) && $post->post_type === get_notifications_cpt_slug() ) {
		$post_states['publish'] = __( 'Active', 'stream_notifications ');
	}

	return $post_states;
}


function change_status_labels( $views ) {
	if( isset( $views['draft'] ) ) {
		$views['draft'] = str_replace( 'Draft', 'Inactive', $views['draft'] );
	}

	if( isset( $views['publish'] ) ) {
		$views['publish'] = str_replace( 'Published', 'Active', $views['publish'] );
	}

	return $views;
}


function clean_up_row_actions( $actions, $post ) {
	if( $post->post_type === get_notifications_cpt_slug() ) {
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['view'] );
	}

	return $actions;
}