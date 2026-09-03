<?php
/** Compact property gallery with native RealHomes image data and Fancybox hooks. */
$property_id = get_the_ID();
$login_required = 'yes' === inspiry_prop_detail_login() && ! is_user_logged_in();
if ( $login_required ) {
	return;
}

$gallery = rwmb_meta( 'REAL_HOMES_property_images', 'type=plupload_image&size=post-featured-image', $property_id );
$items   = array();
foreach ( (array) $gallery as $image_key => $image ) {
	$url = $image['full_url'] ?? $image['url'] ?? '';
	$src = $image['url'] ?? $url;
	if ( ! $url || ! $src ) {
		continue;
	}
	$items[] = array(
		'id'      => (int) ( $image['ID'] ?? $image['id'] ?? $image['attachment_id'] ?? ( is_numeric( $image_key ) && 0 < (int) $image_key ? $image_key : 0 ) ),
		'full'    => $url,
		'src'     => $src,
		'title'   => $image['title'] ?? '',
		'caption' => $image['caption'] ?? $image['description'] ?? '',
	);
}

if ( ! $items && has_post_thumbnail( $property_id ) ) {
	$image_id = get_post_thumbnail_id( $property_id );
	$items[]  = array(
		'id'      => $image_id,
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
			<?php
			$image_attrs = array(
				'alt'          => $item['title'] ?: get_the_title( $property_id ),
				'loading'      => 0 === $index ? 'eager' : 'lazy',
				'fetchpriority' => 0 === $index ? 'high' : 'low',
				'class'        => 'hrd-property-gallery__image',
			);
			$image_html = $item['id'] ? wp_get_attachment_image( $item['id'], 'post-featured-image', false, $image_attrs ) : '';
			if ( ! $image_html ) {
				$image_html = sprintf( '<img src="%s" alt="%s" loading="%s"%s class="hrd-property-gallery__image" />', esc_url( $item['src'] ), esc_attr( $image_attrs['alt'] ), esc_attr( $image_attrs['loading'] ), 0 === $index ? ' fetchpriority="high"' : '' );
			}
			// Some imported galleries have generated files but incomplete attachment metadata.
			$small_src = preg_replace( '/-1240x720(\.[a-z0-9]+)$/i', '-488x326$1', $item['src'] );
			if ( $small_src && $small_src !== $item['src'] ) {
				$image_html = preg_replace( '/\s+\/>$/', sprintf( ' srcset="%s 488w, %s 1240w" sizes="(max-width: 600px) 100vw, 1240px" />', esc_url( $small_src ), esc_url( $item['src'] ) ), $image_html );
			}
			echo $image_html;
			?>
			<?php if ( 0 === $index && 1 < $count ) : ?><span class="hrd-property-gallery__count"><?php echo esc_html( $count ); ?> <?php esc_html_e( 'photos', RH_TEXT_DOMAIN ); ?><small><?php esc_html_e( 'View all photos', RH_TEXT_DOMAIN ); ?></small></span><?php endif; ?>
		</a>
	<?php endforeach; ?>
</div>
