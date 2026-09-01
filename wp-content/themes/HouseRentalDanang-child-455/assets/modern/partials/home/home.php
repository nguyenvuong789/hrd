<?php
/**
 * Homepage Template with the child-theme home guide section.
 *
 * @package realhomes
 * @subpackage modern
 */

get_header();

echo '<a class="skip-link screen-reader-text" href="#main-content">Skip to content</a>';
echo '<main id="main-content" class="hrd-home-main">';

$theme_homepage_module = get_post_meta( get_the_ID(), 'theme_homepage_module', true );

switch ( $theme_homepage_module ) {
	case 'properties-slider':
		get_template_part( 'assets/modern/partials/home/slider/property' );
		break;
	case 'properties-map':
		get_template_part( 'assets/modern/partials/home/slider/map' );
		break;
	case 'search-form-over-image':
		get_template_part( 'assets/modern/partials/home/slider/search-form-over-image' );
		break;
	case 'slides-slider':
		get_template_part( 'assets/modern/partials/home/slider/slides' );
		break;
	case 'revolution-slider':
		$rev_slider_alias = trim( get_post_meta( get_the_ID(), 'theme_rev_alias', true ) );
		if ( function_exists( 'putRevSlider' ) && ! empty( $rev_slider_alias ) ) {
			putRevSlider( $rev_slider_alias );
		} else {
			get_template_part( 'assets/modern/partials/banner/header' );
		}
		break;
	case 'simple-banner':
		get_template_part( 'assets/modern/partials/banner/image' );
		break;
	case 'contact-form-slider':
		get_template_part( 'assets/modern/partials/home/slider/contact-form-slider' );
		break;
	default:
		get_template_part( 'assets/modern/partials/banner/header' );
		break;
}

if ( 'search-form-over-image' !== $theme_homepage_module ) {
	$inspiry_show_home_search = get_post_meta( get_the_ID(), 'theme_show_home_search', true );
}

if ( is_active_sidebar( 'home-search-area' ) ) : ?>
	<div class="rh_prop_search rh_wrap--padding">
		<?php dynamic_sidebar( 'home-search-area' ); ?>
	</div>
	<?php
elseif ( ! empty( $inspiry_show_home_search ) && 'true' === $inspiry_show_home_search ) :
	get_template_part( 'assets/modern/partials/properties/search/advance' );
endif;

if ( function_exists( 'hrd_adsense_render_unit' ) ) {
	hrd_adsense_render_unit( '5174572061', 'home-below-search' );
}

$sections = array(
	'content'             => get_post_meta( get_the_ID(), 'theme_show_home_contents', true ),
	'latest-properties'   => get_post_meta( get_the_ID(), 'theme_show_home_properties', true ),
	'featured-properties' => get_post_meta( get_the_ID(), 'theme_show_featured_properties', true ),
	'testimonial'         => get_post_meta( get_the_ID(), 'inspiry_show_testimonial', true ),
	'cta'                 => get_post_meta( get_the_ID(), 'inspiry_show_cta', true ),
	'agents'              => get_post_meta( get_the_ID(), 'inspiry_show_agents', true ),
	'features'            => get_post_meta( get_the_ID(), 'inspiry_show_home_features', true ),
	'partners'            => get_post_meta( get_the_ID(), 'inspiry_show_home_partners', true ),
	'news'                => get_post_meta( get_the_ID(), 'inspiry_show_home_news_modern', true ),
	'cta-contact'         => get_post_meta( get_the_ID(), 'inspiry_show_home_cta_contact', true ),
);

$section_ordering = get_post_meta( get_the_ID(), 'inspiry_home_sections_order_default', true );
if ( ! empty( $section_ordering ) && 'default' === $section_ordering ) {
	$home_sections = explode( ',', 'content,latest-properties,featured-properties,testimonial,cta,agents,features,partners,news,cta-contact' );
} else {
	$home_sections = get_post_meta( get_the_ID(), 'inspiry_home_sections_order_mod', true );
	$home_sections = ! empty( $home_sections ) ? $home_sections : 'content,latest-properties,featured-properties,testimonial,cta,agents,features,partners,news,cta-contact';
	$home_sections = explode( ',', $home_sections );
}

if ( ! empty( $home_sections ) && is_array( $home_sections ) ) {
	$get_border_type = get_post_meta( get_the_ID(), 'inspiry_home_sections_border', true );
	$border_class    = is_rtl() && 'diagonal-border' === $get_border_type ? 'diagonal-mod-wrapper diagonal-rtl' : ( 'diagonal-border' === $get_border_type ? 'diagonal-mod-wrapper' : '' );
	?>
	<div class="wrapper-home-sections <?php echo esc_attr( $border_class ); ?>">
		<?php foreach ( $home_sections as $home_section ) :
			if ( isset( $sections[ $home_section ] ) && 'true' === $sections[ $home_section ] ) {
				get_template_part( 'assets/modern/partials/home/section/' . $home_section );
			}
		endforeach; ?>
	</div>
	<?php
}

if ( function_exists( 'hrd_home_faq_markup' ) ) {
	echo hrd_home_faq_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo '</main>';

get_footer();
