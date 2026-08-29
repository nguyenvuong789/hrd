<?php
/**
 * Google AdSense loader and deliberate manual placements.
 */

const HRD_ADSENSE_CLIENT = 'ca-pub-7774947205200624';

/** Load AdSense once site-wide for manual units and the restrained Auto ads setup. */
function hrd_enqueue_adsense() {
	if ( is_admin() || is_feed() || wp_doing_ajax() ) {
		return;
	}

	wp_enqueue_script(
		'hrd-adsense',
		'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode( HRD_ADSENSE_CLIENT ),
		array(),
		null,
		false
	);
}
add_action( 'wp_enqueue_scripts', 'hrd_enqueue_adsense', 20 );

/** Add Google's required crossorigin attribute to the AdSense loader. */
function hrd_adsense_script_tag( $tag, $handle ) {
	if ( 'hrd-adsense' !== $handle ) {
		return $tag;
	}

	return str_replace( ' src=', ' async crossorigin="anonymous" src=', $tag );
}
add_filter( 'script_loader_tag', 'hrd_adsense_script_tag', 10, 2 );

/**
 * Render one responsive ad unit per named placement.
 *
 * @param string $slot      Google AdSense slot ID.
 * @param string $placement Placement-specific CSS modifier.
 */
function hrd_adsense_render_unit( $slot, $placement ) {
	static $rendered_placements = array();

	$slot      = preg_replace( '/\D/', '', (string) $slot );
	$placement = sanitize_html_class( $placement );
	if ( empty( $slot ) || empty( $placement ) || isset( $rendered_placements[ $placement ] ) ) {
		return;
	}
	$rendered_placements[ $placement ] = true;
	?>
	<div class="hrd-adsense-unit hrd-adsense-unit--<?php echo esc_attr( $placement ); ?>" aria-label="Advertisement">
		<ins class="adsbygoogle"
			 style="display:block"
			 data-ad-client="<?php echo esc_attr( HRD_ADSENSE_CLIENT ); ?>"
			 data-ad-slot="<?php echo esc_attr( $slot ); ?>"
			 data-ad-format="auto"
			 data-full-width-responsive="true"></ins>
		<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
	</div>
	<?php
}

/** Inject the guide unit after the third paragraph on standard blog posts. */
function hrd_adsense_in_guide( $content ) {
	if (
		! is_singular( 'post' ) ||
		! in_the_loop() ||
		! is_main_query() ||
		get_the_ID() !== get_queried_object_id()
	) {
		return $content;
	}

	ob_start();
	hrd_adsense_render_unit( '4067328351', 'guide-in-article' );
	$ad_markup = ob_get_clean();

	$paragraph_count = 0;
	$content         = preg_replace_callback(
		'/<\/p>/i',
		static function ( $match ) use ( &$paragraph_count, $ad_markup ) {
			$paragraph_count++;
			return 3 === $paragraph_count ? $match[0] . $ad_markup : $match[0];
		},
		$content
	);

	return $paragraph_count >= 3 ? $content : $content . $ad_markup;
}
add_filter( 'the_content', 'hrd_adsense_in_guide', 15 );
