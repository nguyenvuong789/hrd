<?php
/** Static localization guard. Run with: php scripts/check-homepage-localization.php */

$root    = dirname( __DIR__ );
$locales = array( 'en', 'vi', 'ko', 'ja', 'ru', 'zh' );
$errors  = array();

function hrd_localization_segment( $file, $start, $end ) {
	$source = file_get_contents( $file );
	$from   = false === $source ? false : strpos( $source, $start );
	$to     = false === $from ? false : strpos( $source, $end, $from );
	return false === $from || false === $to ? '' : substr( $source, $from, $to - $from );
}

function hrd_check_locales( $segment, $locales, $label, &$errors ) {
	foreach ( $locales as $locale ) {
		if ( false === strpos( $segment, "'{$locale}' => array(" ) ) {
			$errors[] = "{$label}: missing {$locale} locale";
		}
	}
}

$theme = $root . '/wp-content/themes/HouseRentalDanang-child-455';
$meta  = hrd_localization_segment(
	$theme . '/inc/polylang.php',
	'function hrd_translate_home_section_metadata',
	"add_filter( 'get_post_metadata', 'hrd_translate_home_section_metadata'"
);
hrd_check_locales( $meta, $locales, 'homepage metadata', $errors );

$required_meta = array(
	'inspiry_SFOI_title', 'inspiry_SFOI_description',
	'inspiry_home_properties_title', 'inspiry_home_properties_desc',
	'inspiry_home_properties_title_2', 'inspiry_home_properties_desc_2',
	'inspiry_home_partners_sub_title', 'inspiry_home_partners_title', 'inspiry_home_partners_desc',
	'inspiry_home_cta_contact_title', 'inspiry_home_cta_contact_desc',
	'inspiry_cta_contact_btn_one_title', 'inspiry_cta_contact_btn_two_title',
	'inspiry_home_news_sub_title', 'inspiry_home_news_title', 'inspiry_home_news_desc',
);
foreach ( $required_meta as $key ) {
	$count = substr_count( $meta, "'{$key}'" );
	if ( count( $locales ) !== $count ) {
		$errors[] = "homepage metadata: {$key} has {$count}/" . count( $locales ) . ' translations';
	}
}

$faq = hrd_localization_segment(
	$theme . '/inc/navigation.php',
	'function hrd_home_faq_markup',
	'// Polylang must not translate'
);
hrd_check_locales( $faq, $locales, 'homepage FAQ', $errors );

$footer = hrd_localization_segment(
	$theme . '/inc/content.php',
	'function hrd_footer_guide_links_shortcode',
	"add_shortcode( 'hrd_footer_guide_links'"
);
hrd_check_locales( $footer, $locales, 'footer guide links', $errors );

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}

echo 'Homepage localization guard passed for: ' . implode( ', ', $locales ) . PHP_EOL;
