<?php
/**
 * Content header for property listing pages.
 */

$header_variation = get_option( 'inspiry_listing_header_variation' );
?>

<div class="rh_page__head">
	<?php if ( empty( $header_variation ) || 'none' === $header_variation ) : ?>
		<h1 class="rh_page__title rh_page__title_pad">
			<?php echo inspiry_get_exploded_heading( get_the_title( get_the_ID() ) ); ?>
		</h1>
	<?php endif; ?>

	<div class="rh_page__controls">
		<?php get_template_part( 'assets/modern/partials/properties/sort-controls' ); ?>
		<?php if ( empty( $header_variation ) || 'none' === $header_variation ) : ?>
			<?php get_template_part( 'assets/modern/partials/properties/view-buttons' ); ?>
		<?php endif; ?>
	</div>
</div>
