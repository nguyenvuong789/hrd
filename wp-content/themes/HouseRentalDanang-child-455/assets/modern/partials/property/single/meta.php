<?php
/**
 * Property meta of single property template.
 *
 * @package    realhomes
 * @subpackage modern
 */

global $post;
$property_id      = get_the_ID();
$post_meta_data   = get_post_custom( $property_id );
$size_postfix     = realhomes_get_area_unit( $property_id );
$lot_size_postfix = realhomes_get_lot_unit( $property_id );

// Meta config
$meta_fields = [
	'bedrooms' => [
		'id'       => 'REAL_HOMES_property_bedrooms',
		'label'    => 'inspiry_bedrooms_field_label',
		'default'  => esc_html__( 'Bedrooms', RH_TEXT_DOMAIN ),
		'icon'     => '/images/icons/icon-bed.svg',
		'post-fix' => ''
	],
	'bathrooms' => [
		'id'       => 'REAL_HOMES_property_bathrooms',
		'label'    => 'inspiry_bathrooms_field_label',
		'default'  => esc_html__( 'Bathrooms', RH_TEXT_DOMAIN ),
		'icon'     => '/images/icons/icon-shower.svg',
		'post-fix' => ''
	],
	'garage' => [
		'id'       => 'REAL_HOMES_property_garage',
		'label'    => 'inspiry_garages_field_label',
		'default'  => esc_html__( 'Garage', RH_TEXT_DOMAIN ),
		'icon'     => '/images/icons/icon-garage.svg',
		'post-fix' => ''
	],
	'year-built' => [
		'id'       => 'REAL_HOMES_property_year_built',
		'label'    => 'inspiry_year_built_field_label',
		'default'  => esc_html__( 'Year Built', RH_TEXT_DOMAIN ),
		'icon'     => '/images/icons/icon-calendar.svg',
		'post-fix' => ''
	],
	'area' => [
		'id'       => 'REAL_HOMES_property_size',
		'label'    => 'inspiry_area_field_label',
		'default'  => esc_html__( 'Area', RH_TEXT_DOMAIN ),
		'icon'     => '/images/icons/icon-area.svg',
		'post-fix' => $size_postfix,
	],
	'lot-size' => [
		'id'       => 'REAL_HOMES_property_lot_size',
		'label'    => 'inspiry_lot_size_field_label',
		'default'  => esc_html__( 'Lot Size', RH_TEXT_DOMAIN ),
		'icon'     => '/images/icons/icon-lot.svg',
		'post-fix' => $lot_size_postfix,
	],
];

// Add RVR fields if enabled
if ( inspiry_is_rvr_enabled() ) {
	$meta_fields += [
		'guests' => [
			'id'       => 'rvr_guests_capacity',
			'label'    => 'inspiry_rvr_guests_field_label',
			'default'  => esc_html__( 'Capacity', RH_TEXT_DOMAIN ),
			'icon'     => '/images/guests-icons.svg',
			'post-fix' => ''
		],
		'min-stay' => [
			'id'       => 'rvr_min_stay',
			'label'    => 'inspiry_rvr_min_stay_label',
			'default'  => esc_html__( 'Min Stay', RH_TEXT_DOMAIN ),
			'icon'     => '/images/icons/icon-min-stay.svg',
			'post-fix' => ''
		],
	];
}

// Reusable meta renderer
function hrd_property_meta_icon( $meta_key ) {
	$icons = array(
		'REAL_HOMES_property_bedrooms'  => 'icon-bed.svg',
		'REAL_HOMES_property_bathrooms' => 'icon-shower.svg',
		'REAL_HOMES_property_size'      => 'icon-area.svg',
	);

	if ( isset( $icons[ $meta_key ] ) ) {
		inspiry_safe_include_svg( '/images/icons/' . $icons[ $meta_key ] );
		return;
	}

	// Keep secondary detail fields visible when they do not have a shared rental icon.
	echo '<svg class="hrd-meta-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8"/><path d="M12 8v5M12 16h.01"/></svg>';
}

function realhomes_render_property_meta_field( $meta_key, $meta_value, $label_option, $default_label, $icon_path, $postfix = '' ) {
	if ( empty( $meta_value ) ) {
		return;
	}

	$label       = get_option( $label_option );
	$label       = ! empty( $label ) ? esc_html( $label ) : $default_label;
	$is_size_key = ( 'REAL_HOMES_property_size' === $meta_key || 'REAL_HOMES_property_lot_size' === $meta_key );
	?>
    <div class="rh_property__meta prop_<?php echo esc_attr( str_replace( 'REAL_HOMES_property_', '', $meta_key ) ); ?>">
        <span class="rh_meta_titles"><?php echo $label; ?></span>
        <div>
	        <?php hrd_property_meta_icon( $meta_key ); ?>
            <span class="figure">
                <?php
                if ( $is_size_key ) {
	                echo realhomes_get_area_size( $meta_value );
                } else {
	                echo esc_html( $meta_value );
                }
                ?>
            </span>
	        <?php
	        if ( ! empty( $postfix ) ) {
		        if ( $is_size_key ) {
			        $postfix = realhomes_get_unit_label( $postfix );
		        }
		        ?>
                <span class="label">
                    <?php echo esc_html( $postfix ); ?>
                </span>
		        <?php
	        }
	        ?>
        </div>
    </div>
	<?php
}

?>
<div class="rh_property__row rh_property__meta_wrap">
	<?php
	if ( ! empty( $meta_fields ) ) {
		foreach ( $meta_fields as $config ) {
			$meta_key      = $config['id'];
			$label_option  = $config['label'];
			$default_label = $config['default'];
			$icon_path     = $config['icon'];
			$postfix       = $config['post-fix'] ?? '';
			$meta_value    = $post_meta_data[ $meta_key ][0] ?? '';

			realhomes_render_property_meta_field( $meta_key, $meta_value, $label_option, $default_label, $icon_path, $postfix );
		}
	}
	/**
	 * This hook can be used to display more property meta fields
	 */
	do_action( 'inspiry_additional_property_meta_fields', $property_id );

	/**
	 * Custom property fields
	 */
	if ( is_singular( 'property' ) ) {
		$custom_fields  = apply_filters(
			'inspiry_property_custom_fields', array(
				array(
					'tab'    => array(),
					'fields' => array(),
				),
			)
		);

		if ( isset( $custom_fields['fields'] ) && ! empty( $custom_fields['fields'] ) ) {
			$prefix    = 'REAL_HOMES_';
			$icons_dir = INSPIRY_THEME_DIR . '/icons/';
			$icons_uri = INSPIRY_DIR_URI . '/icons/';

			foreach ( $custom_fields['fields'] as $field ) {
				if ( isset( $field['display'] ) && true === $field['display'] ) {

					$meta_key = $prefix . inspiry_backend_safe_string( $field['id'] );

					if ( isset( $post_meta_data[ $meta_key ] ) && ! empty( $post_meta_data[ $meta_key ][0] ) ) {

						$field_label = ( ! empty( $field['postfix'] ) ) ? $field['postfix'] : '';
						?>
                        <div class="rh_property__meta <?php echo esc_attr( $meta_key ); ?>">
                            <span class="rh_meta_titles"><?php echo esc_html( $field['name'] ); ?></span>
                            <div>
								<?php
								if ( file_exists( $icons_dir . $field['icon'] . '.png' ) ) {

									$data_rjs = ( file_exists( $icons_dir . $field['icon'] . '@2x.png' ) ) ? '2' : '';

									echo '<img src="' . esc_url( $icons_uri . $field['icon'] ) . '.png" alt="icon" data-rjs="' . esc_attr( $data_rjs ) . '">';
								}
								?>

                                <span class="figure">
									<?php echo esc_html( $post_meta_data[ $meta_key ][0] ); ?>
								</span>
								<?php if ( ! empty( $field_label ) ) : ?>
                                    <span class="label">
										<?php echo esc_html( $field_label ); ?>
									</span>
								<?php endif; ?>
                            </div>
                        </div>
						<?php
					}
				}
			}
		}
	}
	?>
</div>
