<?php
/**
 * Child header keeps the parent structure while allowing browser zoom.
 *
 * @package Realhomes
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<meta name="format-detection" content="telephone=no">
	<script type="text/javascript">
		<?php
		$ajax_url          = admin_url( 'admin-ajax.php' );
		$wpml_current_lang = apply_filters( 'wpml_current_language', null );
		if ( $wpml_current_lang ) {
			$ajax_url = add_query_arg( 'wpml_lang', $wpml_current_lang, $ajax_url );
		}
		?>
		var ajaxurl = "<?php echo esc_url( $ajax_url ); ?>";
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
wp_body_open();

if ( is_page_template( 'templates/dashboard.php' ) ) {
	get_template_part( 'common/dashboard/header' );
} else {
	// Theme switches use a new theme-mod key; restore the legacy menu assignment before rendering.
	if ( function_exists( 'hrd_restore_legacy_menu_locations' ) ) {
		hrd_restore_legacy_menu_locations();
	}

	if ( INSPIRY_DESIGN_VARIATION === 'modern' ) {
		echo '<div class="rh_wrap rh_wrap_stick_footer">';
	}

	if (
		is_page_template( 'templates/half-map-layout.php' ) ||
		is_page_template( 'templates/properties-search-half-map.php' )
	) {
		echo '<div class="inspiry_half_map_header_wrapper">';
	}

	if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
		if ( function_exists( 'hfe_header_enabled' ) && true == hfe_header_enabled() ) {
			hfe_render_header();
		} else {
			get_template_part( 'assets/' . INSPIRY_DESIGN_VARIATION . '/partials/header' );
		}
	}
}
