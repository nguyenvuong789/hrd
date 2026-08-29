<?php
/** Compact property gallery with native RealHomes image data and Fancybox hooks. */
$property_id = get_the_ID();
$login_required = 'yes' === inspiry_prop_detail_login() && ! is_user_logged_in();
if ( $login_required ) {
	return;
}

$gallery = rwmb_meta( 'REAL_HOMES_property_images', 'type=plupload_image&size=post-featured-image', $property_id );
$items   = array();
foreach ( (array) $gallery as $image ) {
	$url = $image['full_url'] ?? $image['url'] ?? '';
	$src = $image['url'] ?? $url;
	if ( ! $url || ! $src ) {
		continue;
	}
	$items[] = array(
		'full'    => $url,
		'src'     => $src,
		'title'   => $image['title'] ?? '',
		'caption' => $image['caption'] ?? $image['description'] ?? '',
	);
}

if ( ! $items && has_post_thumbnail( $property_id ) ) {
	$image_id = get_post_thumbnail_id( $property_id );
	$items[]  = array(
		'full'    => wp_get_attachment_image_url( $image_id, 'full' ),
		'src'     => wp_get_attachment_image_url( $image_id, 'post-featured-image' ) ?: wp_get_attachment_image_url( $image_id, 'full' ),
		'title'   => get_the_title( $property_id ),
		'caption' => '',
	);
}

if ( ! $items ) {
	return;
}

$count = count( $items );
?>
<div class="hrd-property-gallery hrd-property-gallery--<?php echo esc_attr( min( $count, 5 ) ); ?>" data-gallery-count="<?php echo esc_attr( $count ); ?>">
	<?php foreach ( $items as $index => $item ) : ?>
		<a class="hrd-property-gallery__item hrd-property-gallery__item--<?php echo esc_attr( $index + 1 ); ?><?php echo 4 < $index ? ' hrd-property-gallery__item--hidden' : ''; ?>" href="<?php echo esc_url( $item['full'] ); ?>" data-fancybox="gallery" data-caption="<?php echo esc_attr( $item['caption'] ?: $item['title'] ); ?>">
			<img src="<?php echo esc_url( $item['src'] ); ?>" alt="<?php echo esc_attr( $item['title'] ?: get_the_title( $property_id ) ); ?>" <?php echo 0 === $index ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"'; ?> />
			<?php if ( 0 === $index && 1 < $count ) : ?><span class="hrd-property-gallery__count"><?php echo esc_html( $count ); ?> <?php esc_html_e( 'photos', RH_TEXT_DOMAIN ); ?><small><?php esc_html_e( 'View all photos', RH_TEXT_DOMAIN ); ?></small></span><?php endif; ?>
		</a>
	<?php endforeach; ?>
</div>
