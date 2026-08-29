<?php
/**
 * Property location taxonomy routing.
 *
 * @package HouseRentalDanangChild
 */

if ( 'modern' === INSPIRY_DESIGN_VARIATION && hrd_is_location_hub() ) {
	get_template_part( 'assets/modern/partials/taxonomy/location-hub' );
} else {
	get_template_part( 'assets/' . INSPIRY_DESIGN_VARIATION . '/partials/taxonomy/property-city' );
}
