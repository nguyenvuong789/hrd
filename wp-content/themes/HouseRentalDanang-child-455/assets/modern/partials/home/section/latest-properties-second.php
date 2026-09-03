<?php
/** Second homepage property feed, ported from the legacy HouseRentalDanang parent. */
$page_id = get_the_ID();
$query   = new WP_Query( hrd_get_second_home_property_args( $page_id ) );

if ( ! $query->have_posts() ) {
	return;
}

$border_class = 'diagonal-border' === get_post_meta( $page_id, 'inspiry_home_sections_border', true ) ? 'diagonal-mod' : 'flat-border';
$card_variant = get_option( 'inspiry_property_card_variation', '1' );
?>
<section class="rh_section rh_section--props_padding rh_latest-properties rh_latest-properties--second <?php echo esc_attr( $border_class ); ?>">
	<div class="diagonal-mod-background"></div>
	<div class="container-later-properties">
		<div class="wrapper-section-contents">
			<div>
				<?php
				inspiry_modern_home_heading(
					get_post_meta( $page_id, 'inspiry_home_properties_sub_title_2', true ),
					get_post_meta( $page_id, 'inspiry_home_properties_title_2', true ),
					get_post_meta( $page_id, 'inspiry_home_properties_desc_2', true )
				);
				?>
				<div class="rh_section__properties">
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<?php get_template_part( 'assets/modern/partials/properties/grid-card-' . $card_variant ); ?>
					<?php endwhile; ?>
				</div>
				<?php
				wp_reset_postdata();
				$view_more_url = get_post_meta( $page_id, 'inspiry_home_properties_link_view_more_2', true );
				$view_more_url = $view_more_url ? $view_more_url : home_url( '/apartments/' );
				if ( $view_more_url ) :
					?>
					<a class="rh_btn rh_btn_sfoi rh_btn__prop_search" href="<?php echo esc_url( $view_more_url ); ?>"><?php esc_html_e( 'View all apartments', RH_TEXT_DOMAIN ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
