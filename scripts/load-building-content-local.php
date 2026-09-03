<?php
/** Load an approved-shape building JSON draft into the English Local WP post meta. */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Run this file through WP-CLI eval-file.\n" );
	exit( 1 );
}

$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
if ( 'hrd.local' !== $home_host ) {
	WP_CLI::error( 'Refusing to write outside hrd.local.' );
}

$page_path = isset( $args[0] ) ? trim( (string) $args[0], '/' ) : '';
$json_path = isset( $args[1] ) ? (string) $args[1] : '';
$dry_run   = isset( $args[2] ) && 'dry-run' === $args[2];

if ( '' === $page_path || '' === $json_path ) {
	WP_CLI::error( 'Usage: wp eval-file scripts/load-building-content-local.php apartment-buildings/<page-slug> /absolute/path/content-en.json [dry-run]' );
}

$resolved_json_path = realpath( $json_path );
if ( false === $resolved_json_path || ! is_readable( $resolved_json_path ) ) {
	WP_CLI::error( 'The English content JSON file is not readable.' );
}

$decoded = json_decode( (string) file_get_contents( $resolved_json_path ), true );
if ( ! is_array( $decoded ) || JSON_ERROR_NONE !== json_last_error() ) {
	WP_CLI::error( 'The English content file is not valid JSON.' );
}

$expected_fields = array( 'hero_summary', 'overview', 'gallery', 'amenities', 'location', 'renting_notes', 'faq', 'related' );
$actual_fields   = array_keys( $decoded );
sort( $expected_fields );
sort( $actual_fields );

if ( $expected_fields !== $actual_fields ) {
	WP_CLI::error( 'The JSON must contain exactly the eight shared building fields.' );
}

foreach ( $decoded as $field => $value ) {
	if ( ! is_string( $value ) ) {
		WP_CLI::error( sprintf( 'Field %s must be a string.', $field ) );
	}
	$decoded[ $field ] = wp_kses_post( $value );
}

$page = get_page_by_path( $page_path, OBJECT, 'page' );
if ( ! $page ) {
	WP_CLI::error( sprintf( 'No Local page found at /%s/.', $page_path ) );
}

if ( 'page-apartment-building.php' !== get_page_template_slug( $page->ID ) ) {
	WP_CLI::error( 'The target page does not use page-apartment-building.php.' );
}

$encoded  = wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
$meta_key = 'hrd_building_content_en';
$previous = get_post_meta( $page->ID, $meta_key, true );

if ( $dry_run ) {
	WP_CLI::success( sprintf( 'Validated English JSON for %s (%s). No data changed.', get_the_title( $page ), get_permalink( $page ) ) );
	return;
}

if ( is_string( $previous ) && '' !== $previous && $previous !== $encoded ) {
	update_post_meta( $page->ID, $meta_key . '_local_backup', wp_slash( $previous ) );
}

update_post_meta( $page->ID, $meta_key, wp_slash( $encoded ) );

WP_CLI::success( sprintf( 'Loaded English building content for %s: %s', get_the_title( $page ), get_permalink( $page ) ) );
