<?php
/**
 * Server-rendered district property location hub.
 *
 * @package HouseRentalDanangChild
 */

$language      = hrd_get_current_language();
$hub           = hrd_get_current_location_hub( $language );
$district      = $hub ? $hub['key'] : '';
$page          = hrd_get_location_hub_page();
$sort          = hrd_get_location_hub_sort();
$selected_sort = hrd_get_location_hub_sort_selection();
$sections      = hrd_get_location_hub_sections( $language );
$hub_url       = $hub ? get_term_link( $hub['term'] ) : new WP_Error();
if ( is_wp_error( $hub_url ) ) {
	$hub_url = home_url( '/' );
}

get_header();

$header_variation = get_option( 'inspiry_listing_header_variation' );
if ( empty( $header_variation ) || 'none' === $header_variation ) {
	get_template_part( 'assets/modern/partials/banner/header' );
} elseif ( 'banner' === $header_variation ) {
	get_template_part( 'assets/modern/partials/banner/taxonomy' );
}

if ( inspiry_show_header_search_form() ) {
	get_template_part( 'assets/modern/partials/properties/search/advance' );
}
?>

<section class="rh_section rh_section--flex rh_wrap--padding rh_wrap--topPadding hrd-location-hub">
	<?php do_action( 'inspiry_before_page_contents' ); ?>
	<div class="rh_page rh_page__listing_page listing__grid_fullwidth rh_page__main hrd-location-hub__main">
		<div class="rh_page__head hrd-location-hub__head">
			<?php if ( empty( $header_variation ) || 'none' === $header_variation ) : ?>
				<h1 class="rh_page__title rh_page__title_pad"><?php echo esc_html( hrd_location_hub_copy( 'page_title', $language, $district ) ); ?></h1>
			<?php endif; ?>
			<div class="rh_page__controls">
				<form class="rh_sort_controls hrd-location-hub__sort" action="<?php echo esc_url( $hub_url ); ?>" method="get">
					<label class="screen-reader-text" for="hrd-location-hub-sort"><?php echo esc_html( hrd_location_hub_copy( 'sort_label', $language, $district ) ); ?></label>
					<select name="sortby" id="hrd-location-hub-sort" class="inspiry_select_picker_trigger inspiry_bs_default_mod inspiry_bs_listing inspiry_bs_green" aria-label="<?php echo esc_attr( hrd_location_hub_copy( 'sort_label', $language, $district ) ); ?>" data-hub-sort data-hub-url="<?php echo esc_url( $hub_url ); ?>">
						<option value="default" <?php selected( 'default', $selected_sort ); ?>><?php echo esc_html( hrd_location_hub_copy( 'sort_default', $language, $district ) ); ?></option>
						<option value="price-asc" <?php selected( 'price-asc', $selected_sort ); ?>><?php echo esc_html( hrd_location_hub_copy( 'sort_price_asc', $language, $district ) ); ?></option>
						<option value="price-desc" <?php selected( 'price-desc', $selected_sort ); ?>><?php echo esc_html( hrd_location_hub_copy( 'sort_price_desc', $language, $district ) ); ?></option>
						<option value="date-asc" <?php selected( 'date-asc', $selected_sort ); ?>><?php echo esc_html( hrd_location_hub_copy( 'sort_date_asc', $language, $district ) ); ?></option>
						<option value="date-desc" <?php selected( 'date-desc', $selected_sort ); ?>><?php echo esc_html( hrd_location_hub_copy( 'sort_date_desc', $language, $district ) ); ?></option>
					</select>
					<noscript><button class="hrd-location-hub__sort-submit" type="submit"><?php echo esc_html( hrd_location_hub_copy( 'sort_apply', $language, $district ) ); ?></button></noscript>
				</form>
			</div>
		</div>

		<?php
		if ( function_exists( 'hrd_adsense_render_unit' ) ) {
			hrd_adsense_render_unit( '1224658117', 'listing-after-filters' );
		}
		?>

		<div class="rh_content rh_content_above_footer rh_page__content hrd-location-hub__intro">
			<p><strong><?php echo esc_html( hrd_location_hub_copy( 'intro_lead_title', $language, $district ) ); ?></strong> <?php echo esc_html( hrd_location_hub_copy( 'intro_lead', $language, $district ) ); ?></p>
			<h2><?php echo esc_html( hrd_location_hub_copy( 'intro_types_title', $language, $district ) ); ?></h2>
			<p><?php echo esc_html( hrd_location_hub_copy( 'intro_types', $language, $district ) ); ?></p>
			<?php if ( hrd_location_hub_copy( 'intro_fit', $language, $district ) ) : ?>
				<h2><?php echo esc_html( hrd_location_hub_copy( 'intro_fit_title', $language, $district ) ); ?></h2>
				<p><?php echo esc_html( hrd_location_hub_copy( 'intro_fit', $language, $district ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( hrd_location_hub_copy( 'intro_details_title', $language, $district ) ); ?></h2>
			<p><?php echo esc_html( hrd_location_hub_copy( 'intro_details', $language, $district ) ); ?></p>
			<p><?php echo esc_html( hrd_location_hub_copy( 'intro_contact', $language, $district ) ); ?> <a href="mailto:hello@houserentaldanang.com"><strong>hello@houserentaldanang.com</strong></a>.</p>
			<?php
			$term_description = $hub && $hub['term'] instanceof WP_Term ? $hub['term']->description : '';
			if ( $term_description ) {
				echo '<div class="hrd-location-hub__editorial">' . wp_kses_post( apply_filters( 'the_content', $term_description ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo hrd_location_hub_long_content( $district, $language ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
		<nav class="hrd-location-hub__nav" aria-label="<?php echo esc_attr( hrd_location_hub_copy( 'nav_label', $language, $district ) ); ?>">
			<?php foreach ( $sections as $section_key => $type_term ) : ?>
				<a href="#<?php echo esc_attr( $section_key ); ?>"><?php echo esc_html( hrd_location_hub_copy( $section_key, $language, $district ) ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="hrd-location-hub__sections" data-location-hub data-district="<?php echo esc_attr( $district ); ?>">
			<?php if ( empty( $sections ) ) : ?>
				<p class="hrd-location-hub__empty"><?php echo esc_html( hrd_location_hub_copy( 'empty', $language, $district ) ); ?></p>
			<?php endif; ?>
			<?php foreach ( $sections as $section_key => $type_term ) : ?>
				<?php
				$label = hrd_location_hub_copy( $section_key, $language, $district );
				$batch = hrd_get_location_hub_batch( $district, $section_key, $page, $sort, $language );
				?>
				<section id="<?php echo esc_attr( $section_key ); ?>" class="hrd-location-hub__section" data-hub-section="<?php echo esc_attr( $section_key ); ?>">
					<header class="hrd-location-hub__section-head">
						<h2><?php echo esc_html( sprintf( hrd_location_hub_copy( 'section_label', $language, $district ), $label ) ); ?></h2>
					</header>
					<div class="rh_page__listing rh_page__listing_grid hrd-location-hub__grid" data-hub-grid>
						<?php echo hrd_render_location_hub_cards( $batch['posts'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>

					<?php if ( empty( $batch['posts'] ) ) : ?>
						<p class="hrd-location-hub__empty"><?php echo esc_html( hrd_location_hub_copy( 'empty', $language, $district ) ); ?></p>
					<?php elseif ( $batch['has_more'] ) : ?>
						<div class="hrd-location-hub__actions">
							<button class="rh_btn rh_btn_sfoi rh_btn__prop_search hrd-location-hub__load-more" type="button" data-hub-load-more data-section="<?php echo esc_attr( $section_key ); ?>" data-page="<?php echo esc_attr( $page ); ?>">
								<span><?php echo esc_html( hrd_location_hub_copy( 'load_more', $language, $district ) ); ?></span>
							</button>
						</div>
					<?php endif; ?>
					<p class="hrd-location-hub__status" data-hub-status role="status" aria-live="polite"></p>
				</section>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ( is_active_sidebar( 'property-listing-sidebar' ) ) : ?>
		<div class="rh_page rh_page__sidebar">
			<?php get_sidebar( 'property-listing' ); ?>
		</div>
	<?php endif; ?>
</section>

<?php get_footer(); ?>
