<?php
/**
 * Pilot named-building page for The Monarchy.
 *
 * Building registry and exact-meta listing query for named-building pages.
 *
 * @package HouseRentalDanangChild455
 *
 * Template Name: Apartment Building Pilot
 */

defined( 'ABSPATH' ) || exit;

get_header();

$building_key = sanitize_key( get_post_meta( get_the_ID(), 'hrd_building_key', true ) );

$buildings = array(
	'monarchy' => array(
		'name'    => 'The Monarchy',
		'area'    => 'Son Tra',
		'nearby'  => 'Han River · Dragon Bridge',
		'layouts' => '1–3 bedrooms',
	),
	'azura' => array(
		'name'    => 'Azura',
		'area'    => 'Son Tra',
		'nearby'  => 'Han River',
		'layouts' => '2 bedrooms',
	),
	'hiyori-garden-tower' => array(
		'name'    => 'Hiyori Garden Tower',
		'area'    => 'Son Tra',
		'nearby'  => 'Vo Van Kiet Street',
		'layouts' => '2 bedrooms',
	),
	'indochina-riverside-towers' => array(
		'name'    => 'Indochina Riverside Towers',
		'area'    => 'Hai Chau',
		'nearby'  => 'Han River',
		'layouts' => '2 bedrooms',
	),
	'the-filmore' => array(
		'name'    => 'The Filmore Da Nang',
		'area'    => 'Hai Chau',
		'nearby'  => 'Han River · Bach Dang Street',
		'layouts' => '1–3 bedrooms',
	),
	'fpt-plaza' => array(
		'name'    => 'FPT Plaza',
		'area'    => 'Ngu Hanh Son',
		'nearby'  => 'FPT City',
		'layouts' => 'Apartment layouts vary',
	),
	'ocean-suites' => array(
		'name'    => 'The Ocean Suites',
		'area'    => 'Ngu Hanh Son',
		'nearby'  => 'The Ocean Resort',
		'layouts' => '2 bedrooms',
	),
	'ocean-villas' => array(
		'name'    => 'The Ocean Villas',
		'area'    => 'Ngu Hanh Son',
		'nearby'  => 'The Ocean Resort',
		'layouts' => '4–6 bedrooms',
	),
);

if ( ! isset( $buildings[ $building_key ] ) ) {
	status_header( 404 );
	get_template_part( '404' );
	exit;
}

$building      = $buildings[ $building_key ];
$building_name = $building['name'];
$properties    = new WP_Query(
	array(
		'post_type'           => 'property',
		'post_status'         => 'publish',
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
			'ID'         => 'DESC',
		),
		'meta_query'          => array(
			array(
				'key'     => 'hrd_building_key',
				'value'   => $building_key,
				'compare' => '=',
			),
		),
	) 
);
?>

<main class="rh_page rh_page--building" id="main-content">
	<section class="rh_section rh_wrap--padding rh_wrap--topPadding">
		<div class="rh_page__main">
			<nav class="hrd-building__breadcrumbs" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<span aria-hidden="true">/</span>
				<a href="<?php echo esc_url( home_url( '/apartments/' ) ); ?>">Apartments</a>
				<span aria-hidden="true">/</span>
				<span><?php echo esc_html( $building_name ); ?></span>
			</nav>

			<section class="hrd-building__intro" aria-labelledby="building-title">
				<p class="hrd-building__eyebrow">Building guide · <?php echo esc_html( $building['area'] ); ?></p>
				<h1 id="building-title">Apartments for Rent in <?php echo esc_html( $building_name ); ?>, Da Nang</h1>
				<p class="hrd-building__lead">
					This page is a first listing shell for <?php echo esc_html( $building_name ); ?>. Detailed building information, fees, amenities and rental guidance will be added after the page structure is approved.
				</p>
				<div class="hrd-building__facts" id="building-facts">
					<div><span>Area</span><strong><?php echo esc_html( $building['area'] ); ?></strong></div>
					<div><span>Typical layouts</span><strong><?php echo esc_html( $building['layouts'] ); ?></strong></div>
					<div><span>Nearby</span><strong><?php echo esc_html( $building['nearby'] ); ?></strong></div>
					<div><span>Status</span><strong>Listing pilot</strong></div>
				</div>
			</section>

			<section class="hrd-building__guide" aria-labelledby="building-guide-title">
				<div>
					<p class="hrd-building__eyebrow">About this page</p>
					<h2 id="building-guide-title"><?php echo esc_html( $building_name ); ?> rental listings</h2>
					<p>Use the cards below to review the current inventory matched to this building. Exact availability, rent, utilities, deposit and lease terms must be confirmed before viewing or payment.</p>
				</div>
				<aside>
					<strong>Content placeholder</strong>
					<p>Building description, facilities, parking, management fees and FAQ will be filled after the template is approved.</p>
				</aside>
			</section>

			<section class="hrd-building__listings" id="available-apartments" aria-labelledby="available-title">
				<div class="hrd-building__section-head">
					<div>
						<p class="hrd-building__eyebrow">Current inventory</p>
						<h2 id="available-title">Available apartments in <?php echo esc_html( $building_name ); ?></h2>
					</div>
					<p class="hrd-building__count"><?php echo esc_html( $properties->post_count ); ?> matching listing<?php echo 1 === (int) $properties->post_count ? '' : 's'; ?></p>
				</div>

				<?php if ( $properties->have_posts() ) : ?>
					<div class="rh_section__properties hrd-building__cards">
						<?php
						while ( $properties->have_posts() ) :
							$properties->the_post();
							get_template_part( 'assets/modern/partials/properties/grid-card-1' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				<?php else : ?>
					<div class="hrd-building__empty">No current <?php echo esc_html( $building_name ); ?> listings are matched yet. Contact the local team for the latest availability.</div>
				<?php endif; ?>
			</section>

			<section class="hrd-building__next" aria-labelledby="next-title">
				<p class="hrd-building__eyebrow">Next step</p>
				<h2 id="next-title">Want more details about this building?</h2>
				<p>We can add verified building facts, rental FAQs, a map section and a dedicated contact CTA after this pilot structure is approved.</p>
				<a class="rh_btn" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Contact the local team</a>
			</section>
		</div>
	</section>
</main>

<?php get_footer(); ?>
