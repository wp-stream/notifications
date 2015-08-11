<?php
/**
 * Register sub-menu under Stream
 */
namespace Stream\Stream_Notifications\Notifications_Admin;

use Stream\Stream_Notifications\Notifications;

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

    add_action( 'admin_init', $n( 'register_notifications_submenu' ) );

    do_action( 'stream_notifications_cpt_register' );
}

/**
 * Register submenu for notifications
 *
 * @uses add_submenu_page()
 * @uses sprintf()
 * @uses Stream\Stream_Notifications\Notifications\get_notifications_cpt_slug()
 *
 * @return void
 */
function register_notifications_submenu() {
    add_submenu_page(
        'wp_stream',
        __( 'Notifications', 'stream_notifications' ),
        __( 'Notifications', 'stream_notifications' ),
        'manage_options',
        sprintf( 'edit.php?post_type=%s', Notifications\get_notifications_cpt_slug() )
    );
}
