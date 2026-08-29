<?php
/**
 * Replace the generic property pin without modifying the map tiles or controls.
 */
function hrd_use_branded_single_property_map_marker() {
	if ( ! is_singular( 'property' ) || ! wp_script_is( 'property-open-street-map', 'enqueued' ) ) {
		return;
	}

	$marker_url = get_stylesheet_directory_uri() . '/images/hrd-map-marker.svg';
	$script     = sprintf(
		'if (typeof propertyMapData !== "undefined") { propertyMapData.icon = %1$s; propertyMapData.retinaIcon = %1$s; }',
		wp_json_encode( $marker_url )
	);

	wp_add_inline_script( 'property-open-street-map', $script, 'before' );
}
add_action( 'wp_enqueue_scripts', 'hrd_use_branded_single_property_map_marker', 100 );
