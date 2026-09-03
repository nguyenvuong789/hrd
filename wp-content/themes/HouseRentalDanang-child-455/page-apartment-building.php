<?php
/**
 * Shared building detail template.
 *
 * Template Name: Apartment Building
 */

defined( 'ABSPATH' ) || exit;

$building_key = sanitize_key( get_post_meta( get_the_ID(), 'hrd_building_key', true ) );
$building     = hrd_get_building( $building_key );
if ( ! $building ) {
	status_header( 404 );
	get_template_part( '404' );
	exit;
}

get_header();
$name         = $building['name'];
$heading_name = $building['heading_name'] ?? $name;
$properties   = hrd_get_building_properties( $building_key );
$content      = hrd_get_building_content( get_the_ID() );
$text         = static function ( $key ) { return hrd_building_text( $key ); };
$field        = static function ( $key, $fallback ) use ( $content ) { return ! empty( $content[ $key ] ) ? $content[ $key ] : $fallback; };
$map_embed_url = esc_url_raw( $building['map_embed_url'] ?? '', array( 'https' ) );
$map_link_url  = esc_url_raw( $building['map_link_url'] ?? '', array( 'https' ) );
$has_map       = '' !== $map_embed_url && '' !== $map_link_url;
$entity_id    = trailingslashit( get_permalink() ) . '#apartment-complex';
$webpage_id   = trailingslashit( get_permalink() ) . '#webpage';
$language     = function_exists( 'pll_current_language' ) ? pll_current_language( 'locale' ) : get_locale();
$description = wp_strip_all_tags( $field( 'hero_summary', '' ) );
$webpage_schema = array(
	'@context'      => 'https://schema.org',
	'@type'         => 'WebPage',
	'@id'           => $webpage_id,
	'name'          => get_the_title(),
	'url'           => get_permalink(),
	'datePublished' => get_the_date( DATE_ISO8601 ),
	'dateModified'  => get_the_modified_date( DATE_ISO8601 ),
	'description'   => $description,
	'inLanguage'    => str_replace( '_', '-', $language ),
	'mainEntity'    => array( '@id' => $entity_id ),
	'author'        => array(
		'@id'   => trailingslashit( home_url( '/' ) ) . '#real-estate-agent',
		'@type' => 'Organization',
		'name'  => 'House Rental Danang Agency',
		'url'   => home_url( '/' ),
	),
	'publisher'     => array( '@id' => trailingslashit( home_url( '/' ) ) . '#real-estate-agent' ),
);
$schema     = array(
	'@context'         => 'https://schema.org',
	'@type'            => 'ApartmentComplex',
	'@id'              => $entity_id,
	'name'             => $name,
	'url'              => get_permalink(),
	'description'      => $description,
	'inLanguage'       => str_replace( '_', '-', $language ),
	'mainEntityOfPage' => array( '@id' => $webpage_id ),
	'address'          => array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => $building['street_address'] ?? '',
		'addressLocality' => $building['area'],
		'addressRegion'   => 'Da Nang',
		'addressCountry'  => 'VN',
	),
);
if ( ! empty( $building['developer'] ) ) {
	$schema['developer'] = array( '@type' => 'Organization', 'name' => $building['developer'] );
}
if ( ! empty( $building['amenities'] ) ) {
	$schema['amenityFeature'] = array_map(
		static function ( $amenity ) {
			return array( '@type' => 'LocationFeatureSpecification', 'name' => $amenity, 'value' => true );
		},
		$building['amenities']
	);
}
?>
<main class="rh_page rh_page--building" id="main-content">
	<script type="application/ld+json"><?php echo wp_json_encode( $webpage_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
	<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
	<section class="rh_section rh_wrap--padding rh_wrap--topPadding"><div class="rh_page__main">
		<nav class="hrd-building__breadcrumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $text( 'home' ) ); ?></a><span aria-hidden="true">/</span><a href="<?php echo esc_url( home_url( '/apartments/' ) ); ?>"><?php echo esc_html( $text( 'apartments' ) ); ?></a><span aria-hidden="true">/</span><span><?php echo esc_html( $name ); ?></span></nav>
		<section class="hrd-building__intro" aria-labelledby="building-title"><p class="hrd-building__eyebrow"><?php echo esc_html( $text( 'guide' ) . ' · ' . $building['area'] ); ?></p><h1 id="building-title"><?php echo esc_html( sprintf( $text( 'h1' ), $heading_name ) ); ?></h1><p class="hrd-building__lead"><?php echo wp_kses_post( $field( 'hero_summary', 'Explore rental options in ' . $name . ' and compare the latest matching homes in one place.' ) ); ?></p><div class="hrd-building__facts"><div><span><?php echo esc_html( $text( 'area' ) ); ?></span><strong><?php echo esc_html( $building['area'] ); ?></strong></div><div><span><?php echo esc_html( $text( 'layouts' ) ); ?></span><strong><?php echo esc_html( $building['layouts'] ); ?></strong></div><div><span><?php echo esc_html( $text( 'nearby' ) ); ?></span><strong><?php echo esc_html( $building['nearby'] ); ?></strong></div><div><span><?php echo esc_html( $text( 'availability' ) ); ?></span><strong><?php echo esc_html( $text( 'check' ) ); ?></strong></div></div></section>
		<section class="hrd-building__guide" aria-labelledby="building-guide-title"><div><p class="hrd-building__eyebrow"><?php echo esc_html( $text( 'overview' ) ); ?></p><h2 id="building-guide-title"><?php echo esc_html( sprintf( $text( 'guide_title' ), $name ) ); ?></h2><div class="hrd-building__copy"><?php echo wp_kses_post( wpautop( $field( 'overview', $text( 'guide_body' ) ) ) ); ?></div></div><aside><strong><?php echo esc_html( $text( 'help' ) ); ?></strong><p><?php echo esc_html( sprintf( $text( 'help_body' ), $building['area'] ) ); ?></p><a class="hrd-building__guide-link" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php echo esc_html( $text( 'help_cta' ) ); ?></a></aside></section>
<?php
$modules = array( array( 'gallery', 'photos', $name ), array( 'amenities', 'details', null ) );
foreach ( $modules as $module ) :
	$heading = 'photos' === $module[1] ? sprintf( $text( $module[1] ), $module[2] ) : $text( $module[1] );
	?>
		<section class="hrd-building__module hrd-building__module--<?php echo esc_attr( $module[0] ); ?>" aria-labelledby="building-<?php echo esc_attr( $module[0] ); ?>-title"><p class="hrd-building__eyebrow"><?php echo esc_html( $text( $module[0] ) ); ?></p><h2 id="building-<?php echo esc_attr( $module[0] ); ?>-title"><?php echo esc_html( $heading ); ?></h2><div class="hrd-building__copy"><?php echo wp_kses_post( wpautop( $field( $module[0], $text( 'empty' ) ) ) ); ?></div></section>
<?php endforeach; ?>
		<section class="hrd-building__module hrd-building__module--location" aria-labelledby="building-location-title"><p class="hrd-building__eyebrow"><?php echo esc_html( $text( 'location' ) ); ?></p><h2 id="building-location-title"><?php echo esc_html( $text( 'location' ) ); ?></h2><div class="hrd-building__location-layout<?php echo $has_map ? ' hrd-building__location-layout--with-map' : ''; ?>"><div class="hrd-building__copy hrd-building__location-copy"><?php echo wp_kses_post( wpautop( $field( 'location', $text( 'empty' ) ) ) ); ?></div><?php if ( $has_map ) : ?><div class="hrd-building__map"><div class="hrd-building__map-frame"><iframe src="<?php echo esc_url( $map_embed_url ); ?>" title="<?php echo esc_attr( $text( 'location' ) . ': ' . $name ); ?>" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div><a href="<?php echo esc_url( $map_link_url ); ?>"><?php echo esc_html( $text( 'map_open' ) ); ?></a></div><?php endif; ?></div></section>
		<section class="hrd-building__listings" id="available-apartments" aria-labelledby="available-title"><div class="hrd-building__section-head"><div><p class="hrd-building__eyebrow"><?php echo esc_html( $text( 'inventory' ) ); ?></p><h2 id="available-title"><?php echo esc_html( sprintf( $text( 'available' ), $name ) ); ?></h2><p class="hrd-building__inventory-note"><?php echo esc_html( $text( 'inventory_note' ) ); ?></p></div><p class="hrd-building__count"><?php echo esc_html( $properties->post_count . ' ' . $text( 1 === (int) $properties->post_count ? 'listing' : 'listings' ) ); ?></p></div><?php if ( $properties->have_posts() ) : ?><div class="rh_section__properties hrd-building__cards"><?php while ( $properties->have_posts() ) : $properties->the_post(); get_template_part( 'assets/modern/partials/properties/grid-card-1' ); endwhile; wp_reset_postdata(); ?></div><?php else : ?><div class="hrd-building__empty"><?php echo esc_html( $text( 'empty' ) ); ?></div><?php endif; ?></section>
		<section class="hrd-building__module" aria-labelledby="building-renting-title"><p class="hrd-building__eyebrow"><?php echo esc_html( $text( 'renting' ) ); ?></p><h2 id="building-renting-title"><?php echo esc_html( $text( 'renting' ) ); ?></h2><div class="hrd-building__copy"><?php echo wp_kses_post( wpautop( $field( 'renting_notes', $text( 'empty' ) ) ) ); ?></div></section><section class="hrd-building__module hrd-building__faq" aria-labelledby="building-faq-title"><p class="hrd-building__eyebrow"><?php echo esc_html( $text( 'faq' ) ); ?></p><h2 id="building-faq-title"><?php echo esc_html( $text( 'faq' ) ); ?></h2><?php echo wp_kses_post( hrd_render_building_faq( $field( 'faq', $text( 'empty' ) ) ) ); ?></section>
		<section class="hrd-building__next" aria-labelledby="next-title"><p class="hrd-building__eyebrow"><?php echo esc_html( $text( 'next_step' ) ); ?></p><h2 id="next-title"><?php echo esc_html( $text( 'next' ) ); ?></h2><p><?php echo esc_html( $text( 'next_body' ) ); ?></p><a class="rh_btn" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php echo esc_html( $text( 'contact' ) ); ?></a></section><section class="hrd-building__module hrd-building__related" aria-labelledby="building-related-title"><p class="hrd-building__eyebrow"><?php echo esc_html( $text( 'related' ) ); ?></p><h2 id="building-related-title"><?php echo esc_html( $text( 'related' ) ); ?></h2><div class="hrd-building__related-content"><?php echo wp_kses_post( hrd_render_building_related( $field( 'related', $text( 'empty' ) ) ) ); ?></div></section>
	</div></section>
</main>
<?php get_footer(); ?>
