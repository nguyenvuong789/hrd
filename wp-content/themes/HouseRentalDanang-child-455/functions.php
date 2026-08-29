<?php
/**
 * HouseRentalDanang child theme bootstrap for RealHomes 4.5.5.
 *
 * Module order intentionally follows the pre-refactor functions.php order so
 * hook registration priorities and runtime behavior remain unchanged.
 */
$hrd_modules = array(
    'compatibility.php',
    'analytics.php',
    'seo.php',
    'assets.php',
    'adsense.php',
    'content.php',
    'navigation.php',
    'polylang.php',
    'homepage.php',
    'property-cards.php',
    'location-hub.php',
    'rental-form.php',
    'contact.php',
);
foreach ( $hrd_modules as $hrd_module ) {
    require_once __DIR__ . '/inc/' . $hrd_module;
}
unset( $hrd_modules, $hrd_module );
