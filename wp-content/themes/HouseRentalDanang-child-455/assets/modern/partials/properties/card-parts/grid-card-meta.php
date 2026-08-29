<?php
// Match listing cards to the homepage card: labels, icons and values share one row.
$meta = array(
	'bedrooms' => array( 'Bedrooms', 'REAL_HOMES_property_bedrooms', '/images/icons/icon-bed.svg', '' ),
	'bathrooms' => array( 'Bathrooms', 'REAL_HOMES_property_bathrooms', '/images/icons/icon-shower.svg', '' ),
	'area' => array( 'Area', 'REAL_HOMES_property_size', '/images/icons/icon-area.svg', 'REAL_HOMES_property_size_postfix' ),
);
$selected = get_option( 'inspiry_property_card_meta', array_keys( $meta ) );
?>
<div class="rh_prop_card_meta_theme_stylish hrd-card-meta">
	<?php foreach ( $selected as $key ) :
		if ( empty( $meta[ $key ] ) ) continue;
		$item = $meta[ $key ];
		$value = get_post_meta( get_the_ID(), $item[1], true );
		if ( '' === (string) $value ) continue;
		$label = get_option( 'inspiry_' . $key . '_field_label' );
		$label = $label ? $label : __( $item[0], 'framework' );
		$postfix = $item[3] ? get_post_meta( get_the_ID(), $item[3], true ) : '';
	?>
		<div class="rh_prop_card__meta hrd-card-meta__<?php echo esc_attr( $key ); ?>">
			<span class="rh_meta_titles"><?php echo esc_html( $label ); ?></span>
			<div class="hrd-card-meta__value">
				<?php inspiry_safe_include_svg( $item[2] ); ?>
				<span class="figure"><?php echo esc_html( $value ); ?></span>
				<?php if ( $postfix ) : ?><span class="label"><?php echo esc_html( $postfix ); ?></span><?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
