<?php
/** Keep the 4.5.5 migration on the Classic stack; do not offer Elementor. */
function hrd_disable_elementor_requirements( $plugins ) {
	return array_values(
		array_filter(
			$plugins,
			static function ( $plugin ) {
				$slug = isset( $plugin['slug'] ) ? (string) $plugin['slug'] : '';
				return ! in_array( $slug, array( 'elementor', 'realhomes-elementor-addon' ), true );
			}
		)
	);
}
add_filter( 'realhomes_tgm_required_plugins', 'hrd_disable_elementor_requirements', 20 );

/** Preserve the existing menu assignments when switching from the legacy child theme. */
function hrd_restore_legacy_menu_locations() {
	$current_locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! empty( $current_locations ) ) {
		return;
	}

	$legacy_mods      = get_option( 'theme_mods_HouseRentalDanang-child', array() );
	$legacy_locations = isset( $legacy_mods['nav_menu_locations'] ) && is_array( $legacy_mods['nav_menu_locations'] )
		? $legacy_mods['nav_menu_locations']
		: array();

	if ( ! empty( $legacy_locations ) ) {
		set_theme_mod( 'nav_menu_locations', $legacy_locations );
	}
}
add_action( 'after_setup_theme', 'hrd_restore_legacy_menu_locations', 20 );

function hrd_menu_locations_compat( $locations ) {
	if ( ! empty( $locations ) ) {
		return $locations;
	}

	$legacy_mods = get_option( 'theme_mods_HouseRentalDanang-child', array() );
	return isset( $legacy_mods['nav_menu_locations'] ) && is_array( $legacy_mods['nav_menu_locations'] )
		? $legacy_mods['nav_menu_locations']
		: $locations;
}
add_filter( 'theme_mod_nav_menu_locations', 'hrd_menu_locations_compat', 20 );

/** Older Easy Real Estate lacks the method called by the 4.5.5 print customizer. */
function hrd_remove_broken_print_customizer() {
	remove_action( 'customize_register', 'realhomes_single_property_print_customizer' );
}
add_action( 'customize_register', 'hrd_remove_broken_print_customizer', 1 );

/** Keep older Easy Real Estate installs compatible when Turnstile is not configured. */
if ( ! function_exists( 'ere_turnstile_widget' ) ) {
	function ere_turnstile_widget( $widget_id = '', $args = array() ) {
		return;
	}
}

if ( ! function_exists( 'inspiry_property_qr_code' ) ) {
	/** Display the printable property QR code through an active HTTPS service. */
	function inspiry_property_qr_code() {
		$qr_code_url = add_query_arg(
			array(
				'size' => '90x90',
				'data' => get_the_permalink(),
			),
			'https://api.qrserver.com/v1/create-qr-code/'
		);

		printf(
			'<img class="only-for-print inspiry-qr-code" src="%s" alt="%s">',
			esc_url( $qr_code_url ),
			the_title_attribute( 'echo=0' )
		);
	}
}

if ( ! function_exists( 'inspiry_enqueue_child_styles' ) ) {
	function inspiry_enqueue_child_styles() {
		if ( is_admin() ) {
			return;
		}

		$hrd_is_vietnamese = function_exists( 'pll_current_language' )
			? 'vi' === pll_current_language()
			: 0 === strpos( (string) get_locale(), 'vi_' );
		if ( $hrd_is_vietnamese ) {
			wp_enqueue_style(
				'hrd-be-vietnam-pro',
				'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap',
				array(),
				null
			);
		}
		$hrd_css_path = get_stylesheet_directory() . '/css/main.css';
		wp_enqueue_style(
			'hrd-child',
			get_stylesheet_directory_uri() . '/css/main.css',
			$hrd_is_vietnamese ? array( 'hrd-be-vietnam-pro' ) : array(),
			(string) filemtime( $hrd_css_path )
		);
		wp_add_inline_style(
			'hrd-child',
			'.home .rh_mod_sfoi_wrapper .SFOI__title{color:#fff!important;text-shadow:0 1px 3px rgba(0,0,0,.35)!important}' .
			'.hrd-long-guide__content{color:#536264;font-family:var(--hrd-font-ui);font-size:14.5px;line-height:2}' .
			'.hrd-long-guide__content p,.hrd-long-guide__content li{font:inherit}' .
			'.hrd-long-guide__content h2{margin:0 0 20px;color:#183036;font:600 36px/1.5 var(--hrd-font-ui);letter-spacing:-.025em}' .
			'.hrd-long-guide__content h3{margin:0 0 20px;color:#183036;font:600 18px/1.5 var(--hrd-font-ui)}' .
			'.hrd-long-guide__content table{font:inherit}' .
			'.tax-property-city .hrd-location-hub__intro .hrd-long-guide__content p,.tax-property-city .hrd-location-hub__intro .hrd-long-guide__content li{font:inherit}' .
			'.tax-property-city .hrd-location-hub__intro .hrd-long-guide__content h2{font-size:36px;line-height:1.5}' .
			'.tax-property-city .hrd-location-hub__intro .hrd-long-guide__content h3{font-size:18px;line-height:1.5}' .
			'.tax-property-city .hrd-location-hub__intro{max-width:1170px}' .
			'.tax-property-city .hrd-location-hub__intro > p,.tax-property-city .hrd-location-hub__intro > ul,.tax-property-city .hrd-location-hub__intro > ol{max-width:none}' .
			'@media(max-width:767px){.hrd-long-guide__content h2{font-size:30px;line-height:1.35}.hrd-long-guide__content h3{font-size:18px}}' .
			'.home .rh_latest-properties--second .rh_section__properties{display:flex;flex-wrap:wrap;justify-content:center;align-items:flex-start}' .
			'.home .rh_latest-properties--second .rh_prop_card--listing{width:33.3333%}' .
			'@media(max-width:1023px){.home .rh_latest-properties--second .rh_prop_card--listing{width:50%}}' .
			'@media(max-width:767px){.home .rh_latest-properties--second .rh_prop_card--listing{width:100%}}' .
			'.rh_prop_card .rh_prop_card__details .hrd-card-meta__area .hrd-card-meta__value{gap:5px;min-width:0}' .
			'.rh_prop_card .rh_prop_card__details .hrd-card-meta__area .figure{font-size:17px!important}' .
			'.rh_prop_card .rh_prop_card__details .hrd-card-meta__area .label{font-size:11px!important;letter-spacing:-.01em}' .
			'.rh_prop_card .rh_prop_card__priceLabel{margin-top:0!important}'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'inspiry_enqueue_child_styles', PHP_INT_MAX );

function hrd_enqueue_child_script() {
	if ( is_admin() ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/js/child-custom.js';
	wp_enqueue_script(
		'hrd-child-custom',
		get_stylesheet_directory_uri() . '/js/child-custom.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'hrd_enqueue_child_script', PHP_INT_MAX );

/** Keep long listing-guide pages compact while preserving the full editorial copy. */
function hrd_is_long_listing_guide_page() {
	if ( is_admin() || ! is_page() ) {
		return false;
	}

	$slug = get_post_field( 'post_name', get_queried_object_id() );

	return in_array( $slug, array( 'apartments', 'villas', 'houses' ), true );
}

function hrd_wrap_long_listing_guide( $content ) {
	if ( ! hrd_is_long_listing_guide_page() || false !== strpos( $content, 'hrd-long-guide' ) ) {
		return $content;
	}

	$guide_id = 'hrd-listing-guide-content-' . absint( get_queried_object_id() );

	return hrd_render_long_guide( $content, $guide_id );
}
add_filter( 'the_content', 'hrd_wrap_long_listing_guide', 99 );

function hrd_render_long_guide( $content, $guide_id ) {
	return '<div class="hrd-long-guide"><div id="' . esc_attr( $guide_id ) . '" class="hrd-long-guide__content">' . $content . '</div><button class="hrd-long-guide__toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr( $guide_id ) . '"><span class="hrd-long-guide__button"><span class="hrd-long-guide__closed">Show more</span><span class="hrd-long-guide__open">Show less</span><span class="hrd-long-guide__chevron" aria-hidden="true"></span></span></button></div>';
}

/** Keep district editorial guides compact while preserving their HTML. */
function hrd_wrap_location_long_guide( $content ) {
	if ( false !== strpos( $content, 'hrd-long-guide' ) || '' === trim( wp_strip_all_tags( $content ) ) ) {
		return $content;
	}

	$guide_id = 'hrd-location-guide-content-' . absint( get_queried_object_id() );

	return hrd_render_long_guide( $content, $guide_id );
}

/** Add the matching live-site toggle behavior without another asset request. */
function hrd_output_listing_collapsible_script() {
	if ( ! hrd_is_long_listing_guide_page() && ! hrd_is_location_hub() ) {
		return;
	}
	?>
	<script id="hrd-listing-collapsible-script">
	document.addEventListener('click', function (event) {
		var toggle = event.target.closest('.hrd-long-guide__toggle');
		if (!toggle) {
			return;
		}

		var guide = toggle.closest('.hrd-long-guide');
		var expanded = toggle.getAttribute('aria-expanded') === 'true';

		toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
		guide.classList.toggle('is-expanded', !expanded);

		if (expanded) {
			var top = guide.getBoundingClientRect().top + window.scrollY - 24;
			window.scrollTo({ top: top, behavior: 'auto' });
		}
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'hrd_output_listing_collapsible_script', 99 );

/** Start the homepage LCP background request before CSS and inline styles are parsed. */
function hrd_preload_homepage_hero() {
	if ( ! is_front_page() ) {
		return;
	}

	$hero_url = content_url( '/uploads/2020/11/House-For-Rent-in-Da-Nang-1_6228dcde9c2575cf745522fb5abd323a-1.jpg' );
	printf(
		'<link rel="preload" href="%s" as="image" fetchpriority="high">' . "\n",
		esc_url( $hero_url )
	);
}
add_action( 'wp_head', 'hrd_preload_homepage_hero', 1 );

/** Start the hero request from the response headers, before buffered HTML arrives. */
function hrd_send_homepage_hero_preload_header() {
	if ( ! is_front_page() || headers_sent() ) {
		return;
	}

	$hero_url = content_url( '/uploads/2020/11/House-For-Rent-in-Da-Nang-1_6228dcde9c2575cf745522fb5abd323a-1.jpg' );
	header( 'Link: <' . esc_url_raw( $hero_url ) . '>; rel=preload; as=image; fetchpriority=high', false );
}
add_action( 'send_headers', 'hrd_send_homepage_hero_preload_header' );

/** Warm the only third-party connection needed for above-the-fold typography. */
function hrd_add_font_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'hrd_add_font_resource_hints', 10, 2 );

if ( ! function_exists( 'inspiry_load_translation_from_child' ) ) {
	function inspiry_load_translation_from_child() {
		load_child_theme_textdomain( 'framework', get_stylesheet_directory() . '/languages' );
	}

add_action( 'after_setup_theme', 'inspiry_load_translation_from_child' );
}

/** Let the parent theme's WPML-compatible page lookup resolve Polylang pages. */
function hrd_translate_wpml_object_id_with_polylang( $object_id, $object_type, $return_original_if_missing, $language = null ) {
	if ( 'page' !== $object_type || ! function_exists( 'pll_get_post' ) ) {
		return $object_id;
	}

	$language     = $language ?: hrd_get_current_language();
	$translated_id = pll_get_post( (int) $object_id, $language );

	return $translated_id ?: ( $return_original_if_missing ? $object_id : null );
}
add_filter( 'wpml_object_id', 'hrd_translate_wpml_object_id_with_polylang', 10, 4 );
