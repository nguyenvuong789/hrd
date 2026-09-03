<?php
function hrd_optimize_frontend_assets() {
	// Dashicons are an admin UI dependency and are not used by public visitors.
	if ( ! is_user_logged_in() ) {
		wp_dequeue_style( 'dashicons' );
	}

	// RealHomes registers map assets globally, although the homepage has no map.
	// Keep them available on property/search/contact screens where map markup exists.
	if ( is_front_page() ) {
		foreach ( array( 'leaflet', 'leaflet-js', 'properties-mapbox', 'ajax-properties-mapbox', 'properties-open-street-map', 'ajax-properties-open-street-map' ) as $handle ) {
			wp_dequeue_script( $handle );
		}
		foreach ( array( 'leaflet', 'leaflet-css', 'mapbox-style-2-9-2', 'mapbox-style-3-3-1', 'leaflet-marker-cluster-css', 'leaflet-marker-cluster-default-css' ) as $handle ) {
			wp_dequeue_style( $handle );
		}
	}

	if ( ! is_singular( 'post' ) ) {
		wp_dequeue_style( 'kk-star-ratings' );
		wp_dequeue_script( 'kk-star-ratings' );
	}

	if ( is_front_page() || ! is_singular() || ! comments_open() || ! get_option( 'thread_comments' ) ) {
		wp_dequeue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'hrd_optimize_frontend_assets', PHP_INT_MAX );

/** Fetch Google Fonts without holding the first paint on a third-party CSS response. */
function hrd_non_blocking_google_fonts( $html, $handle, $href, $media ) {
	if ( false === strpos( $href, 'fonts.googleapis.com' ) ) {
		return $html;
	}

	$href = esc_url( $href );
	return sprintf(
		'<link rel="preload" as="style" href="%1$s" onload="this.onload=null;this.rel=\'stylesheet\'"><noscript><link rel="stylesheet" href="%1$s"></noscript>',
		$href
	);
}
add_filter( 'style_loader_tag', 'hrd_non_blocking_google_fonts', 20, 4 );

/** Map styles are below the fold on property pages; keep them out of first paint. */
function hrd_non_blocking_map_styles( $html, $handle, $href, $media ) {
	if ( 'leaflet' !== $handle ) {
		return $html;
	}

	return sprintf(
		'<link rel="preload" as="style" href="%1$s" onload="this.onload=null;this.rel=\'stylesheet\'"><noscript><link rel="stylesheet" href="%1$s"></noscript>',
		esc_url( $href )
	);
}
add_filter( 'style_loader_tag', 'hrd_non_blocking_map_styles', 20, 4 );

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
