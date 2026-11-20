<?php
/**
 * EduPath AI Password Reset child-theme bootstrap.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_enqueue_scripts', static function() {
    wp_enqueue_style(
        'edupath-ai-parent',
        get_template_directory_uri() . '/style.css',
        array(),
        '3.1.1'
    );
    wp_enqueue_style(
        'edupath-ai-reset-child',
        get_stylesheet_uri(),
        array( 'edupath-ai-parent' ),
        '3.1.1'
    );
}, 20 );

require_once __DIR__ . '/inc/password-reset-ui.php';
