<div id="property-content-section-content" class="property-content-section <?php realhomes_printable_section( 'content' ); ?>">
	<h4 class="rh_property__heading">
		<?php
		$description_label = get_option( 'inspiry_description_property_label' );
		if ( $description_label ) {
			echo esc_html( $description_label );
		} else {
			esc_html_e( 'Description', RH_TEXT_DOMAIN );
		}
		?>
	</h4>
	<?php if ( function_exists( 'hrd_adsense_render_unit' ) ) : ?>
		<?php hrd_adsense_render_unit( '7609163717', 'property-description' ); ?>
	<?php endif; ?>
	<div class="rh_content">
		<?php the_content(); ?>
	</div>
</div>
