<?php
function hrd_optimize_frontend_assets() {
	if ( ! is_singular( 'post' ) ) {
		wp_dequeue_style( 'kk-star-ratings' );
		wp_dequeue_script( 'kk-star-ratings' );
	}

	if ( is_front_page() || ! is_singular() || ! comments_open() || ! get_option( 'thread_comments' ) ) {
		wp_dequeue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'hrd_optimize_frontend_assets', PHP_INT_MAX );

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
