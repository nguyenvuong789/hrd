<?php
/**
 * Template: Property Search Half Map
 *
 * Property search template.
 *
 * @package    realhomes
 * @subpackage modern
 */

get_header();

// Page Head.
$header_variation = get_option( 'inspiry_search_header_variation' );

if ( empty( $header_variation ) || ( 'none' === $header_variation ) ) {
	get_template_part( 'assets/modern/partials/banner/header' );
} elseif ( 'banner' === $header_variation ) {
	get_template_part( 'assets/modern/partials/banner/image' );
}

echo '</div>'; // close inspiry_half_map_header_wrapper in header.php


$number_of_properties = intval( get_option( 'theme_properties_on_search' ) );
if ( ! $number_of_properties ) {
	$number_of_properties = 6;
}

$paged = 1;
if ( get_query_var( 'paged' ) ) {
	$paged = get_query_var( 'paged' );
} elseif ( get_query_var( 'page' ) ) { // if is static front page
	$paged = get_query_var( 'page' );
}

$search_args = array(
	'post_type'      => 'property',
	'posts_per_page' => $number_of_properties,
	'paged'          => $paged,
);

if ( inspiry_is_search_page_map_visible() ) {

	$search_args['meta_query'] = array(
		array(
			'key'     => 'REAL_HOMES_property_address',
			'compare' => 'EXISTS',
		),
	);

}

/* Apply Search Filter */
$search_args = apply_filters( 'real_homes_search_parameters', $search_args );

/* Sort Properties */
$search_args = sort_properties( $search_args );

$search_query = new WP_Query( $search_args );

$language = function_exists( 'hrd_get_current_language' ) ? hrd_get_current_language() : 'en';
$search_titles = array(
	'en' => 'Find rentals in Da Nang',
	'vi' => 'Tìm nhà cho thuê tại Đà Nẵng',
	'ko' => '다낭 임대 주택 찾기',
	'ja' => 'ダナンの賃貸物件を探す',
	'ru' => 'Найти жильё в аренду в Дананге',
	'zh' => '查找岘港出租房源',
);
$get_content_position = get_post_meta( get_the_ID(), 'REAL_HOMES_content_area_above_footer', true );
?>

<div class="hrd-search-preamble">
	<h1 class="rh_page__title rh_page__title--search-hub">
		<span class="title"><?php echo esc_html( $search_titles[ $language ] ?? $search_titles['en'] ); ?></span>
	</h1>
	<?php if ( '1' !== $get_content_position && have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( get_the_content() ) : ?>
				<div class="rh_content rh_page__content rh_search_hub_content"><?php the_content(); ?></div>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>
</div>

    <section class="rh_section rh_section--flex<?php echo ( inspiry_show_search_form_widget() ) ? '' : ' rh_section__map_listing'; ?>">

        <div class="rh_page rh_page__listing_map">
            <?php get_template_part( 'assets/modern/partials/properties/map' ); ?>
        </div><!-- /.rh_page rh_page__listing_map -->

        <div class="rh_page rh_page__map_properties">
            <div class="rh_page__head">

                <?php
                $count_labels = array(
                    'en' => 'homes, apartments and villas found',
                    'vi' => 'nhà, căn hộ và biệt thự được tìm thấy',
                    'ko' => '주택, 아파트 및 빌라 검색 결과',
                    'ja' => '件の住宅・アパート・ヴィラが見つかりました',
                    'ru' => 'домов, квартир и вилл найдено',
                    'zh' => '套房屋、公寓和别墅',
                );
                ?>
                <p class="hrd-search-results-count">
                    <strong><?php echo esc_html( number_format_i18n( $search_query->found_posts ) ); ?></strong>
                    <?php echo esc_html( $count_labels[ $language ] ?? $count_labels['en'] ); ?>
                </p>
                <!-- /.rh_page__title -->

                <div class="rh_page__controls">
					<?php
						get_template_part( 'assets/modern/partials/properties/save-alert-button', '', array( 'search_args' => $search_args ) );
						get_template_part( 'assets/modern/partials/properties/sort-controls' );
					?>
                </div>
                <!-- /.rh_page__controls -->

            </div>
            <!-- /.rh_page__head -->

            <div class="rh_page__listing">
				<?php
				if ( $search_query->have_posts() ) : while ( $search_query->have_posts() ) : $search_query->the_post();
                        get_template_part( 'assets/modern/partials/properties/half-map-card' );
					endwhile;
					wp_reset_postdata();
				else :
					?>
                    <div class="rh_alert-wrapper">
						<?php
						$inspiry_search_template_no_result_text = get_option( 'inspiry_search_template_no_result_text' );
						if ( ! empty( $inspiry_search_template_no_result_text ) ) {

							?>
                            <h4 class="no-results"><?php echo inspiry_kses( $inspiry_search_template_no_result_text ) ?></h4>
							<?php
						} else {
							?>
                            <h4 class="no-results"><?php esc_html_e( 'No Results Found!', 'framework' ) ?></h4>
							<?php
						}
						?>
                    </div>
                <?php endif; ?>
            </div><!-- /.rh_page__listing -->

			<?php inspiry_theme_pagination( $search_query->max_num_pages ); ?>

        </div><!-- /.rh_page rh_page__map_properties -->

    </section>

<?php
if ('1' === $get_content_position ) {
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			?>
            <div class="rh_content rh_content_above_footer <?php if ( get_the_content() ) {echo esc_attr( 'rh_page__content' );} ?>">
				<?php the_content(); ?>
            </div><!-- /.rh_content -->
			<?php
		}
	}
}
?><!-- /.rh_section rh_wrap rh_wrap--padding -->

<?php
get_footer();
