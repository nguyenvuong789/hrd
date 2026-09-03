<?php
/**
 * Replace the homepage news section's three postmeta joins with one indexed
 * EXISTS lookup. The original OR meta query takes about five seconds here.
 */
function hrd_optimize_home_news_query( $query ) {
	if ( is_admin() || 'post' !== $query->get( 'post_type' ) || 3 !== (int) $query->get( 'posts_per_page' ) ) {
		return;
	}

	$meta_query = $query->get( 'meta_query' );
	$media_keys = array();

	if ( ! is_array( $meta_query ) || 'OR' !== ( $meta_query['relation'] ?? '' ) ) {
		return;
	}

	foreach ( $meta_query as $clause ) {
		if ( is_array( $clause ) && 'EXISTS' === ( $clause['compare'] ?? '' ) && isset( $clause['key'] ) ) {
			$media_keys[] = $clause['key'];
		}
	}

	sort( $media_keys );
	if ( array( 'REAL_HOMES_embed_code', 'REAL_HOMES_gallery', '_thumbnail_id' ) !== $media_keys ) {
		return;
	}

	$query->set( 'meta_query', array() );
	$query->set( 'hrd_require_post_media', true );
	$query->set( 'no_found_rows', true );
	if ( function_exists( 'pll_current_language' ) ) {
		$language = pll_current_language();
		$query->set( 'lang', $language );
		if ( 'en' === $language ) {
			$query->set( 'post__not_in', array( 15697 ) );
		}
	}
}
add_action( 'pre_get_posts', 'hrd_optimize_home_news_query' );

function hrd_add_home_news_media_condition( $where, $query ) {
	global $wpdb;

	if ( $query->get( 'hrd_require_post_media' ) ) {
		$where .= " AND EXISTS (
			SELECT 1 FROM {$wpdb->postmeta} AS hrd_media_meta
			WHERE hrd_media_meta.post_id = {$wpdb->posts}.ID
			AND hrd_media_meta.meta_key IN ('_thumbnail_id', 'REAL_HOMES_embed_code', 'REAL_HOMES_gallery')
		)";
	}

	return $where;
}
add_filter( 'posts_where', 'hrd_add_home_news_media_condition', 10, 2 );

/**
 * Give property cards a useful image when a listing has a gallery but no
 * featured image. This is dynamic so newly imported properties are covered.
 */
function hrd_property_thumbnail_fallback( $thumbnail_id, $post ) {
	if (
		$thumbnail_id || is_admin() || ! $post instanceof WP_Post || 'property' !== $post->post_type
	) {
		return $thumbnail_id;
	}

	$gallery_ids = get_post_meta( $post->ID, 'REAL_HOMES_property_images', false );
	foreach ( $gallery_ids as $gallery_id ) {
		$gallery_id = (int) $gallery_id;
		if ( $gallery_id && 'attachment' === get_post_type( $gallery_id ) && get_attached_file( $gallery_id ) ) {
			return $gallery_id;
		}
	}

	return 0;
}
add_filter( 'post_thumbnail_id', 'hrd_property_thumbnail_fallback', 10, 2 );

// These two small rules replace the WPCode plugin's only active snippets.
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Prevent archive canonicals from collapsing translated URLs back to English.
 * Singular canonicals remain managed by Rank Math and Polylang.
 */
function hrd_keep_translated_archive_canonical( $canonical ) {
	if ( is_admin() || is_404() || ! function_exists( 'pll_current_language' ) ) {
		return $canonical;
	}

	$language = pll_current_language( 'slug' );
	if ( ! $language || 'en' === $language ) {
		return $canonical;
	}

	$current_url = home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) );
	$current_url = strtok( $current_url, '?' );
	$path_prefix = '/' . rawurlencode( $language ) . '/';

	if ( false === strpos( wp_parse_url( $current_url, PHP_URL_PATH ) . '/', $path_prefix ) ) {
		return $canonical;
	}

	$canonical_path = wp_parse_url( $canonical, PHP_URL_PATH );
	if ( $canonical && false !== strpos( (string) $canonical_path . '/', $path_prefix ) ) {
		return $canonical;
	}

	return trailingslashit( $current_url );
}
add_filter( 'rank_math/frontend/canonical', 'hrd_keep_translated_archive_canonical', 20 );

/** Give the property archive an explicit search role and self-canonical. */
function hrd_property_archive_title( $title ) {
	if ( is_admin() || ! is_post_type_archive( 'property' ) ) {
		return $title;
	}

	return 'Properties for Rent in Da Nang | Houses, Apartments & Villas';
}
add_filter( 'rank_math/frontend/title', 'hrd_property_archive_title', 40 );

function hrd_property_archive_description( $description ) {
	if ( is_admin() || ! is_post_type_archive( 'property' ) || trim( (string) $description ) ) {
		return $description;
	}

	return 'Browse current houses, apartments and villas for rent in Da Nang. Filter by property type, area, bedrooms and budget, then contact our local team to confirm availability.';
}
add_filter( 'rank_math/frontend/description', 'hrd_property_archive_description', 40 );

function hrd_property_archive_canonical( $canonical ) {
	if ( is_admin() || ! is_post_type_archive( 'property' ) ) {
		return $canonical;
	}

	return trailingslashit( get_post_type_archive_link( 'property' ) );
}
add_filter( 'rank_math/frontend/canonical', 'hrd_property_archive_canonical', 40 );

/** Support the site's lightweight SEO plugin as well as Rank Math. */
function hrd_property_archive_document_title( $parts ) {
	if ( is_admin() || ! is_post_type_archive( 'property' ) ) {
		return $parts;
	}

	$parts['title'] = 'Properties for Rent in Da Nang | Houses, Apartments & Villas';
	unset( $parts['site'], $parts['tagline'] );
	return $parts;
}
add_filter( 'document_title_parts', 'hrd_property_archive_document_title', 40 );

function hrd_property_archive_head_tags() {
	if ( is_admin() || ! is_post_type_archive( 'property' ) ) {
		return;
	}

	$canonical = trailingslashit( get_post_type_archive_link( 'property' ) );
	echo '<meta name="description" content="' . esc_attr( hrd_property_archive_description( '' ) ) . '">' . "\n";
	echo '<meta name="robots" content="index, follow, max-image-preview:large">' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
}
add_action( 'wp_head', 'hrd_property_archive_head_tags', 6 );

/** Identify the six public language-root homepages without relying on DB flags. */
function hrd_is_language_home_request() {
	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
	$path = trailingslashit( '/' . ltrim( (string) $path, '/' ) );

	return in_array( $path, array( '/', '/vi/', '/ko/', '/ja/', '/ru/', '/zh/' ), true );
}

/** Keep language homepages indexable if Rank Math or Polylang settings drift. */
function hrd_keep_language_homepages_indexable( $robots ) {
	if ( is_admin() || ! hrd_is_language_home_request() || ! is_array( $robots ) ) {
		return $robots;
	}

	foreach ( $robots as $key => $directive ) {
		if ( in_array( strtolower( (string) $directive ), array( 'noindex', 'nofollow' ), true ) ) {
			unset( $robots[ $key ] );
		}
	}
	$robots['index']  = 'index';
	$robots['follow'] = 'follow';

	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'hrd_keep_language_homepages_indexable', 30 );

function hrd_keep_language_homepages_canonical( $canonical ) {
	if ( is_admin() || ! hrd_is_language_home_request() ) {
		return $canonical;
	}

	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );

	return trailingslashit( home_url( $path ) );
}
add_filter( 'rank_math/frontend/canonical', 'hrd_keep_language_homepages_canonical', 30 );

/**
 * Rank Math paginates post sitemaps by modified time. Add an ID tie-breaker
 * so properties with identical timestamps cannot move between sitemap pages.
 */
function hrd_stabilize_property_sitemap_order( $query ) {
	global $wpdb;

	if (
		false === strpos( $query, "p.post_type = 'property'" ) ||
		false === strpos( $query, 'ORDER BY p.post_modified DESC LIMIT' ) ||
		false === strpos( $query, "pm.meta_key = 'rank_math_robots'" )
	) {
		return $query;
	}

	return str_replace(
		'ORDER BY p.post_modified DESC LIMIT',
		'ORDER BY p.post_modified DESC, p.ID DESC LIMIT',
		$query
	);
}
add_filter( 'query', 'hrd_stabilize_property_sitemap_order', 20 );

/** Serve a concise, factual AI-crawler guide without creating a WordPress page. */
function hrd_serve_llms_txt() {
	if ( '/llms.txt' !== wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ) {
		return;
	}

	$status = get_post_status( (int) get_option( 'page_on_front' ) );
	if ( 'publish' !== $status ) {
		return;
	}

	$lines = array(
		'# House Rental Danang',
		'> Local real estate rental service for houses, apartments, and villas in Da Nang, Vietnam.',
		'',
		'## Key pages',
		'- [Home](' . home_url( '/' ) . '): Rental overview and contact path.',
		'- [Houses for rent](' . home_url( '/houses/' ) . '): Current house listings.',
		'- [Apartments for rent](' . home_url( '/apartments/' ) . '): Current apartment listings.',
		'- [Villas for rent](' . home_url( '/villas/' ) . '): Current villa listings.',
		'- [Da Nang guide](' . home_url( '/category/danang-guide/' ) . '): Local guides and practical information.',
		'- [Contact](' . home_url( '/contact-us/' ) . '): Enquiry and business contact details.',
		'',
		'## Languages',
		'- English: ' . home_url( '/' ),
		'- Vietnamese: ' . home_url( '/vi/' ),
		'- Korean: ' . home_url( '/ko/' ),
		'- Japanese: ' . home_url( '/ja/' ),
		'- Russian: ' . home_url( '/ru/' ),
		'- Simplified Chinese: ' . home_url( '/zh/' ),
		'',
		'## Contact',
		'- Email: hello@houserentaldanang.com',
		'- Website: ' . home_url( '/' ),
	);

	status_header( 200 );
	header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ) );
	header( 'X-Robots-Tag: noindex, follow', true );
	echo implode( "\n", $lines ) . "\n";
	exit;
}
add_action( 'template_redirect', 'hrd_serve_llms_txt', 0 );

/** Keep the legacy guide URL useful while the canonical guide hub is a category archive. */
function hrd_redirect_legacy_guide_url() {
	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
	if ( '/danang-guide/' !== trailingslashit( '/' . ltrim( (string) $path, '/' ) ) ) {
		return;
	}

	wp_safe_redirect( home_url( '/category/danang-guide/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'hrd_redirect_legacy_guide_url', 1 );

/** Make AI search crawler access explicit without changing the normal site-wide policy. */
function hrd_add_ai_crawler_robots_directives( $output ) {
	$lines = array(
		'',
		'# AI search crawlers',
		'User-agent: GPTBot',
		'Allow: /',
		'Disallow: /wp-admin/',
		'User-agent: OAI-SearchBot',
		'Allow: /',
		'Disallow: /wp-admin/',
		'User-agent: ChatGPT-User',
		'Allow: /',
		'Disallow: /wp-admin/',
		'User-agent: ClaudeBot',
		'Allow: /',
		'Disallow: /wp-admin/',
		'User-agent: PerplexityBot',
		'Allow: /',
		'Disallow: /wp-admin/',
		'Sitemap: ' . home_url( '/sitemap.xml' ),
	);

	return rtrim( (string) $output ) . "\n" . implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'hrd_add_ai_crawler_robots_directives', 20, 1 );

/** Add a truthful, machine-readable residence entity to property detail pages. */
function hrd_add_property_schema( $data, $jsonld ) {
	if ( is_admin() || ! is_singular( 'property' ) ) {
		return $data;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || 'property' !== $post->post_type ) {
		return $data;
	}

	$canonical = function_exists( 'rank_math_get_permalink' ) ? rank_math_get_permalink() : get_permalink( $post );
	$canonical = $canonical ? $canonical : get_permalink( $post );
	if ( ! $canonical ) {
		return $data;
	}

	$type = 'Residence';
	$types = get_the_terms( $post->ID, 'property-type' );
	if ( ! is_wp_error( $types ) && ! empty( $types ) ) {
		$slug = sanitize_title( $types[0]->slug );
		$type = in_array( $slug, array( 'apartment', 'apartments' ), true ) ? 'Apartment' : $type;
		$type = in_array( $slug, array( 'house', 'houses' ), true ) ? 'SingleFamilyResidence' : $type;
	}

	$entity = array(
		'@type' => $type,
		'@id'   => trailingslashit( $canonical ) . '#residence',
		'name'  => get_the_title( $post ),
		'url'   => $canonical,
	);

	$description = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ?: $post->post_content ), 80, '...' );
	if ( $description ) {
		$entity['description'] = $description;
	}

	$address = get_post_meta( $post->ID, 'REAL_HOMES_property_address', true );
	if ( $address ) {
		$entity['address'] = array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => wp_strip_all_tags( $address ),
			'addressLocality' => 'Da Nang',
			'addressCountry'  => 'VN',
		);
	}

	$property_city = get_the_terms( $post->ID, 'property-city' );
	if ( ! is_wp_error( $property_city ) && ! empty( $property_city ) && ! empty( $entity['address'] ) ) {
		$entity['address']['addressRegion'] = wp_strip_all_tags( $property_city[0]->name );
	}

	$numeric_fields = array(
		'REAL_HOMES_property_bedrooms'  => 'numberOfBedrooms',
		'REAL_HOMES_property_bathrooms' => 'numberOfBathroomsTotal',
		'REAL_HOMES_property_year_built' => 'yearBuilt',
	);
	foreach ( $numeric_fields as $meta_key => $schema_key ) {
		$value = get_post_meta( $post->ID, $meta_key, true );
		if ( '' !== $value && is_numeric( $value ) ) {
			$entity[ $schema_key ] = (int) $value;
		}
	}

	$area = get_post_meta( $post->ID, 'REAL_HOMES_property_area', true );
	if ( '' !== $area && is_numeric( $area ) ) {
		$entity['floorSize'] = array(
			'@type'    => 'QuantitativeValue',
			'value'    => (float) $area,
			'unitText' => 'sqm',
		);
	}

	$images = array();
	$thumbnail_id = get_post_thumbnail_id( $post );
	if ( $thumbnail_id ) {
		$images[] = wp_get_attachment_image_url( $thumbnail_id, 'full' );
	}
	foreach ( get_post_meta( $post->ID, 'REAL_HOMES_property_images', false ) as $image_id ) {
		$image_url = wp_get_attachment_image_url( (int) $image_id, 'full' );
		if ( $image_url ) {
			$images[] = $image_url;
		}
	}
	$images = array_values( array_unique( array_filter( $images ) ) );
	if ( ! empty( $images ) ) {
		$entity['image'] = array_slice( $images, 0, 12 );
	}

	$features = get_the_terms( $post->ID, 'property-feature' );
	if ( ! is_wp_error( $features ) && ! empty( $features ) ) {
		$entity['amenityFeature'] = array();
		foreach ( $features as $feature ) {
			$entity['amenityFeature'][] = array(
				'@type' => 'LocationFeatureSpecification',
				'name'  => wp_strip_all_tags( $feature->name ),
			);
		}
	}

	$entity['mainEntityOfPage'] = array( '@id' => trailingslashit( $canonical ) . '#webpage' );
	$data['HRDResidence'] = $entity;

	return $data;
}
add_filter( 'rank_math/json_ld', 'hrd_add_property_schema', 25, 2 );

/** Keep the public business identity consistent across templates and locales. */
function hrd_public_nap_content( $content ) {
	if ( is_admin() || ! is_string( $content ) ) {
		return $content;
	}

	$content = str_replace( 'Ngu Hanh Son, Da Nang 550000, Vietnam', '201 Chương D., Ngũ Hành Sơn, Đà Nẵng 550000', $content );
	$content = str_replace( '201 Chuong Duong, Da Nang, Vietnam', '201 Chương D., Ngũ Hành Sơn, Đà Nẵng 550000', $content );
	$content = preg_replace( '/<a[^>]+href=["\']tel:0936023079["\'][^>]*>.*?<\/a>/is', '', $content );
	return str_replace( '0936023079', '', $content );
}
add_filter( 'the_content', 'hrd_public_nap_content', 90 );
add_filter( 'widget_text', 'hrd_public_nap_content', 90 );
add_filter( 'widget_custom_html_content', 'hrd_public_nap_content', 90 );

/** Catch theme/option output that bypasses content and widget filters. */
function hrd_sanitize_public_nap_output( $html ) {
	$html = str_replace( 'Ngu Hanh Son, Da Nang 550000, Vietnam', '201 Chương D., Ngũ Hành Sơn, Đà Nẵng 550000', $html );
	$html = str_replace( '201 Chuong Duong, Da Nang, Vietnam', '201 Chương D., Ngũ Hành Sơn, Đà Nẵng 550000', $html );
	$html = preg_replace( '/<p([^>]*class=["\'][^"\']*content[^"\']*["\'][^>]*)>201 Chương D\., Ngũ Hành Sơn, Đà Nẵng 550000<\/p>/u', '<p$1>201 Chương D., Ngũ Hành Sơn, Đà Nẵng 550000</p><p class="hrd-business-hours">24/7</p>', $html );
	$html = preg_replace( '/<a[^>]+href=["\']tel:0936023079["\'][^>]*>.*?<\/a>/is', '', $html );
	$html = str_replace( '0936023079', '', $html );

	$path = wp_parse_url( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ), PHP_URL_PATH );
	$path = trailingslashit( '/' . ltrim( (string) strtok( $path, '?' ), '/' ) );
	$meta = array(
		'/' => array( 'Long-Term Rentals in Da Nang | Local Help', 'Find a long-term rental in Da Nang with local help. Compare houses, apartments and villas by area, then confirm availability and terms before viewing.' ),
		'/apartments/' => array( 'Apartments for Rent in Da Nang | Areas & Prices', 'Compare apartment rents in Da Nang by area and bedroom count. See what to check on utilities, deposits and contracts before you book a viewing.' ),
		'/properties-search/' => array( 'Da Nang Rental Properties | Search Houses & Apartments', 'Find houses, apartments and villas for rent in Da Nang. Filter by area, property type and budget, then ask our local team to confirm availability.' ),
		'/houses/hai-chau/' => array( 'Houses for Rent in Hai Chau, Da Nang', 'Find houses for rent in Hai Chau, Da Nang, near the Han River, schools and city services. Compare current homes and request local viewing help.' ),
		'/apartments/an-thuong/' => array( 'Apartments for Rent in An Thuong, Da Nang', 'Explore apartments for rent in An Thuong, Da Nang, near My Khe Beach, cafes and daily services. Compare current options with local rental help.' ),
		'/apartment-buildings/the-filmore/' => array( 'The Filmore Apartments for Rent in Da Nang', 'See apartments for rent at The Filmore Da Nang, with building details, available layouts and local help confirming current rent and viewing times.' ),
		'/apartment-buildings/the-monarchy/' => array( 'The Monarchy Apartments for Rent in Da Nang', 'Explore The Monarchy apartments for rent in Da Nang. Compare layouts, building amenities and current availability with local rental support.' ),
		'/faqs/' => array( 'Da Nang Rental FAQs | Deposits, Contracts & Fees', 'Get clear answers about renting in Da Nang: deposits, contracts, documents, fees, maintenance and the steps to arrange a viewing.' ),
		'/our-agents/' => array( 'Our Da Nang Rental Agents | Local Help', 'Meet the local House Rental Danang team. Tell us your area, budget and move-in date, and get practical help finding a suitable home.' ),
		'/location/son-tra/' => array( 'Son Tra Rentals | Apartments, Houses & Villas', 'Want to live near My Khe Beach and the Han River? Compare Son Tra apartments, houses and villas, then ask our local team to confirm current options.' ),
		'/location/ngu-hanh-son/' => array( 'Ngu Hanh Son Rentals | Apartments, Houses & Villas', 'Looking near My An, An Thuong or the southern beaches? Compare Ngu Hanh Son apartments, houses and villas, then confirm current options before viewing.' ),
		'/location/hai-chau/' => array( 'Hai Chau Rentals | Apartments, Houses & Villas', 'Need a central Da Nang home near offices, schools and the Han River? Compare Hai Chau apartments, houses and villas and request local viewing help.' ),
	);
	// Keep priority landing-page metadata localized for every published language.
	$localized_meta = array(
		'vi' => array(
			'/' => array( 'Thuê nhà dài hạn tại Đà Nẵng | Hỗ trợ địa phương', 'Tìm nhà thuê dài hạn tại Đà Nẵng cùng hỗ trợ địa phương. So sánh nhà, căn hộ và biệt thự theo khu vực rồi xác nhận lịch xem.' ),
			'/apartments/' => array( 'Căn hộ cho thuê tại Đà Nẵng | Khu vực & Giá', 'So sánh căn hộ cho thuê tại Đà Nẵng theo khu vực và số phòng ngủ. Xem lưu ý về tiện ích, tiền cọc, hợp đồng trước khi đặt lịch xem.' ),
			'/properties-search/' => array( 'Tìm nhà cho thuê tại Đà Nẵng | Nhà & Căn hộ', 'Tìm nhà, căn hộ và biệt thự cho thuê tại Đà Nẵng theo khu vực, loại hình và ngân sách, rồi nhờ đội ngũ địa phương xác nhận.' ),
			'/faqs/' => array( 'FAQ thuê nhà Đà Nẵng | Cọc, Hợp đồng & Phí', 'Giải đáp về thuê nhà tại Đà Nẵng: tiền cọc, hợp đồng, giấy tờ, phí, bảo trì và cách đặt lịch xem nhà.' ),
			'/our-agents/' => array( 'Đội ngũ môi giới cho thuê Đà Nẵng | Hỗ trợ địa phương', 'Gặp đội ngũ House Rental Danang. Cho biết khu vực, ngân sách và ngày chuyển vào để được hỗ trợ tìm nhà phù hợp.' ),
			'/location/son-tra/' => array( 'Thuê nhà Sơn Trà | Căn hộ, Nhà & Biệt thự', 'Muốn sống gần biển Mỹ Khê và sông Hàn? So sánh căn hộ, nhà và biệt thự cho thuê tại Sơn Trà, rồi xác nhận lựa chọn hiện có.' ),
			'/location/ngu-hanh-son/' => array( 'Thuê nhà Ngũ Hành Sơn | Căn hộ, Nhà & Biệt thự', 'Tìm nhà gần Mỹ An, An Thượng hoặc các bãi biển phía Nam. So sánh căn hộ, nhà và biệt thự Ngũ Hành Sơn trước khi xem.' ),
			'/location/hai-chau/' => array( 'Thuê nhà Hải Châu | Căn hộ, Nhà & Biệt thự', 'Cần nhà ở trung tâm Đà Nẵng gần văn phòng, trường học và sông Hàn? So sánh căn hộ, nhà, biệt thự Hải Châu và nhờ hỗ trợ xem nhà.' ),
		),
		'ko' => array(
			'/' => array( '다낭 장기 임대 | 현지 렌탈 도움', '다낭에서 장기 임대 주택을 찾아보세요. 지역별 주택, 아파트, 빌라를 비교하고 현지 팀과 방문 가능 여부를 확인하세요.' ),
			'/apartments/' => array( '다낭 아파트 임대 | 지역 및 가격 비교', '지역과 침실 수별로 다낭 아파트 임대료를 비교하세요. 관리비, 보증금, 계약을 확인한 뒤 방문을 예약할 수 있습니다.' ),
			'/properties-search/' => array( '다낭 임대 매물 검색 | 주택 및 아파트', '지역, 유형, 예산으로 다낭의 주택·아파트·빌라 임대 매물을 찾고 현지 팀에 현재 가능 여부를 문의하세요.' ),
			'/faqs/' => array( '다낭 임대 FAQ | 보증금, 계약 및 비용', '다낭 임대에 필요한 보증금, 계약, 서류, 비용, 관리 및 방문 예약 방법을 쉽게 확인하세요.' ),
			'/our-agents/' => array( '다낭 임대 에이전트 | 현지 지원', 'House Rental Danang 현지 팀을 만나보세요. 원하는 지역, 예산과 입주일을 알려주시면 맞는 집을 찾도록 도와드립니다.' ),
			'/location/son-tra/' => array( '다낭 선짜 임대 | 아파트, 주택 및 빌라', '미케 해변과 한강 근처에서 살고 싶다면 선짜의 아파트, 주택, 빌라를 비교하고 현재 매물을 문의하세요.' ),
			'/location/ngu-hanh-son/' => array( '다낭 응우한선 임대 | 아파트, 주택 및 빌라', '미안, 안트엉 또는 남부 해변 인근의 응우한선 아파트, 주택, 빌라를 비교하고 방문 전 현재 가능 여부를 확인하세요.' ),
			'/location/hai-chau/' => array( '다낭 하이쩌우 임대 | 아파트, 주택 및 빌라', '사무실, 학교와 한강 가까운 중심지를 찾으시나요? 하이쩌우 임대 주택을 비교하고 현지 방문 도움을 요청하세요.' ),
		),
		'ja' => array(
			'/' => array( 'ダナン長期賃貸 | 現地サポート', 'ダナンで長期賃貸の家を探せます。エリア別に一戸建て、アパート、ヴィラを比較し、内見可能か現地チームに確認できます。' ),
			'/apartments/' => array( 'ダナンのアパート賃貸 | エリアと家賃', 'エリアと寝室数でダナンのアパート賃貸を比較。光熱費、保証金、契約の確認ポイントを見て内見を予約できます。' ),
			'/properties-search/' => array( 'ダナン賃貸物件検索 | 住宅・アパート', 'エリア、物件タイプ、予算からダナンの住宅・アパート・ヴィラを検索し、現地チームに空室を確認できます。' ),
			'/faqs/' => array( 'ダナン賃貸 FAQ | 保証金・契約・費用', 'ダナンで借りる際の保証金、契約、書類、費用、修理、内見予約についてよくある質問に回答します。' ),
			'/our-agents/' => array( 'ダナン賃貸エージェント | 現地サポート', 'House Rental Danangの現地チームをご紹介。希望エリア、予算、入居日を伝えて最適な住まい探しを相談できます。' ),
			'/location/son-tra/' => array( 'ダナン・ソンチャ賃貸 | アパート・住宅・ヴィラ', 'ダナンのソンチャ区で賃貸物件をお探しですか？ミーケービーチ、ハン川、地元市場、中心部に近いアパート、一戸建て、ヴィラを比較できます。間取り、広さ、設備、賃料、駐車場、光熱費、契約条件を確認し、現地チームに最新の空室と内見を相談できます。' ),
			'/location/ngu-hanh-son/' => array( 'ダナン・グーハインソン賃貸 | 住宅・アパート', 'ミーアン、アン トゥオン、南部ビーチ周辺の賃貸物件を比較し、内見前に現在の空室を確認できます。' ),
			'/location/hai-chau/' => array( 'ダナン・ハイチャウ賃貸 | アパート・住宅・ヴィラ', 'オフィス、学校、ハン川に近い中心部の住まいを探せます。ハイチャウの賃貸物件を比較し、現地内見を依頼できます。' ),
		),
		'ru' => array(
			'/' => array( 'Долгосрочная аренда в Дананге | Помощь местной команды', 'Найдите жильё для долгосрочной аренды в Дананге. Сравните дома, квартиры и виллы по районам и уточните варианты у местной команды.' ),
			'/apartments/' => array( 'Квартиры в аренду в Дананге | Районы и цены', 'Сравните аренду квартир в Дананге по району и числу спален. Проверьте коммунальные услуги, депозит и договор до просмотра.' ),
			'/properties-search/' => array( 'Поиск аренды в Дананге | Дома и квартиры', 'Ищите дома, квартиры и виллы в аренду в Дананге по району, типу и бюджету, затем уточните актуальность у местной команды.' ),
			'/faqs/' => array( 'FAQ об аренде в Дананге | Депозит, договор и расходы', 'Ответы о депозите, договоре, документах, расходах, обслуживании и записи на просмотр жилья в Дананге.' ),
			'/our-agents/' => array( 'Агенты по аренде в Дананге | Местная помощь', 'Познакомьтесь с командой House Rental Danang. Назовите район, бюджет и дату переезда, чтобы получить помощь в поиске жилья.' ),
			'/location/son-tra/' => array( 'Аренда в Сон Тра | Квартиры, дома и виллы', 'Хотите жить рядом с пляжем Микхе и рекой Хан? Сравните аренду в Сон Тра и уточните актуальные варианты у местной команды.' ),
			'/location/ngu-hanh-son/' => array( 'Аренда в Нгу Хань Сон | Квартиры, дома и виллы', 'Ищете жильё возле Миан, Антхыонг или южных пляжей? Сравните варианты Нгу Хань Сон и проверьте доступность до просмотра.' ),
			'/location/hai-chau/' => array( 'Аренда в Хай Чау | Квартиры, дома и виллы', 'Нужно жильё в центре Дананга рядом с офисами, школами и рекой Хан? Сравните варианты Хай Чау и запросите местный просмотр.' ),
		),
		'zh' => array(
			'/' => array( '岘港长期租房 | 本地租房支持', '在岘港寻找长期出租房屋。按区域比较住宅、公寓和别墅，并向本地团队确认当前房源和看房安排。' ),
			'/apartments/' => array( '岘港公寓出租 | 区域与价格比较', '按区域和卧室数量比较岘港公寓租金，了解水电、押金和合同要点后预约看房。' ),
			'/properties-search/' => array( '岘港出租房源搜索 | 房屋与公寓', '按区域、房型和预算搜索岘港的房屋、公寓和别墅出租信息，并向本地团队确认房源。' ),
			'/faqs/' => array( '岘港租房常见问题 | 押金、合同与费用', '了解在岘港租房的押金、合同、文件、费用、维护和预约看房流程。' ),
			'/our-agents/' => array( '岘港租房经纪团队 | 本地支持', '认识House Rental Danang本地团队。告诉我们区域、预算和入住日期，获取合适房源帮助。' ),
			'/location/son-tra/' => array( '岘港山茶租房 | 公寓、住宅与别墅', '想住在美溪海滩和汉江附近？比较山茶区公寓、住宅和别墅出租，并确认当前房源。' ),
			'/location/ngu-hanh-son/' => array( '岘港五行山租房 | 公寓、住宅与别墅', '寻找美安、安上或南部海滩附近的房源？比较五行山出租住宅，并在看房前确认最新选择。' ),
			'/location/hai-chau/' => array( '岘港海洲租房 | 公寓、住宅与别墅', '需要靠近办公室、学校和汉江的市中心住房？比较海洲区出租房源并预约本地看房帮助。' ),
		),
	);
	foreach ( $localized_meta as $locale => $pages ) {
		foreach ( $pages as $page_path => $page_meta ) {
			$meta[ '/' . $locale . ( '/' === $page_path ? '/' : $page_path ) ] = $page_meta;
		}
	}
	$localized_aliases = array(
		'/vi/can-ho-cho-thue/' => '/vi/apartments/',
		'/vi/tim-kiem-bat-dong-san/' => '/vi/properties-search/',
		'/ko/rental-apartments/' => '/ko/apartments/',
		'/ko/maemul-kensaku/' => '/ko/properties-search/',
		'/ja/bukken-kensaku/' => '/ja/properties-search/',
		'/ru/poisk-nedvizhimosti/' => '/ru/properties-search/',
		'/zh/fangyuan-sousuo/' => '/zh/properties-search/',
		'/vi/khuvuc/son-tra/' => '/vi/location/son-tra/',
		'/vi/khuvuc/ngu-hanh-son/' => '/vi/location/ngu-hanh-son/',
		'/vi/khuvuc/hai-chau/' => '/vi/location/hai-chau/',
	);
	foreach ( $localized_aliases as $alias => $source ) {
		if ( isset( $meta[ $source ] ) ) {
			$meta[ $alias ] = $meta[ $source ];
		}
	}
	if ( isset( $meta[ $path ] ) ) {
		$title = esc_html( $meta[ $path ][0] );
		$desc  = esc_attr( $meta[ $path ][1] );
		$html  = preg_replace( '/<title[^>]*>.*?<\/title>/is', '<title>' . $title . '</title>', $html, 1 );
		$html  = preg_replace( '/<meta[^>]+name=["\']description["\'][^>]*>/i', '<meta name="description" content="' . $desc . '">', $html, 1 );
		$html  = preg_replace( '/<meta[^>]+property=["\']og:title["\'][^>]*>/i', '<meta property="og:title" content="' . $title . '">', $html, 1 );
		$html  = preg_replace( '/<meta[^>]+property=["\']og:description["\'][^>]*>/i', '<meta property="og:description" content="' . $desc . '">', $html, 1 );
		$html  = preg_replace( '/<meta[^>]+name=["\']twitter:title["\'][^>]*>/i', '<meta name="twitter:title" content="' . $title . '">', $html, 1 );
		$html  = preg_replace( '/<meta[^>]+name=["\']twitter:description["\'][^>]*>/i', '<meta name="twitter:description" content="' . $desc . '">', $html, 1 );
		if ( false === stripos( $html, 'name="description"' ) ) {
			$html = preg_replace( '/<\/head>/i', '<meta name="description" content="' . $desc . '">' . "\n</head>", $html, 1 );
		}
	}

	return $html;
}
function hrd_start_public_nap_buffer() {
	if ( ! is_admin() && ! wp_doing_ajax() ) {
		ob_start( 'hrd_sanitize_public_nap_output' );
	}
}
add_action( 'template_redirect', 'hrd_start_public_nap_buffer', 0 );

/** Add the local rental agency entity to Rank Math's existing JSON-LD graph. */
function hrd_add_agency_schema( $data, $jsonld ) {
	if ( is_admin() || ! is_array( $data ) ) {
		return $data;
	}

	$data['HRDAgency'] = array(
		'@type'       => array( 'RealEstateAgent', 'LocalBusiness' ),
		'@id'         => trailingslashit( home_url( '/' ) ) . '#real-estate-agent',
		'name'        => 'House Rental Danang Agency',
		'url'         => home_url( '/' ),
		'description' => 'Local rental agency helping residents and international renters find houses, apartments and villas in Da Nang.',
		'email'       => 'hello@houserentaldanang.com',
		'openingHours' => 'Mo-Su 00:00-23:59',
		'openingHoursSpecification' => array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
			'opens'     => '00:00',
			'closes'    => '23:59',
		),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => '201 Chương D.',
			'addressLocality' => 'Ngũ Hành Sơn, Đà Nẵng',
			'postalCode'      => '550000',
			'addressCountry'  => 'VN',
		),
		'contactPoint' => array(
			'@type'             => 'ContactPoint',
			'contactType'       => 'customer service',
			'email'             => 'hello@houserentaldanang.com',
			'availableLanguage' => array( 'English', 'Vietnamese', 'Korean', 'Japanese', 'Russian', 'Chinese' ),
		),
		'areaServed'  => array( 'Da Nang', 'Son Tra', 'Ngu Hanh Son', 'Hai Chau' ),
		'image'       => home_url( '/wp-content/uploads/2021/01/House-Rental-Danang-Agencys-Logo.png' ),
		'sameAs'      => array(
			'https://www.facebook.com/HouseRentalDanang',
			'https://www.instagram.com/houserentaldanang/',
			'https://www.youtube.com/channel/UC-kzb0io6ZScjGTuE96Dw_Q',
		),
	);

	return $data;
}
add_filter( 'rank_math/json_ld', 'hrd_add_agency_schema', 27, 2 );

/** Fallback entity output for installations where Rank Math drops custom graph keys. */
function hrd_print_agency_schema_fallback() {
	if ( is_admin() || is_feed() ) {
		return;
	}

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'      => array( 'RealEstateAgent', 'LocalBusiness' ),
		'@id'        => trailingslashit( home_url( '/' ) ) . '#real-estate-agent',
		'name'       => 'House Rental Danang Agency',
		'url'        => home_url( '/' ),
		'email'      => 'hello@houserentaldanang.com',
		'description' => 'Local rental agency helping residents and international renters find houses, apartments and villas in Da Nang.',
		'address'    => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => '201 Chương D.',
			'addressLocality' => 'Ngũ Hành Sơn, Đà Nẵng',
			'postalCode'      => '550000',
			'addressCountry'  => 'VN',
		),
		'openingHours' => 'Mo-Su 00:00-23:59',
		'areaServed'   => array( 'Da Nang', 'Son Tra', 'Ngu Hanh Son', 'Hai Chau' ),
		'sameAs'       => array(
			'https://www.facebook.com/HouseRentalDanang',
			'https://www.instagram.com/houserentaldanang/',
			'https://www.youtube.com/channel/UC-kzb0io6ZScjGTuE96Dw_Q',
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'hrd_print_agency_schema_fallback', 7 );

/** Complete the social cards not emitted by the active SEO configuration. */
function hrd_add_social_meta_tags() {
	if ( is_admin() || is_feed() ) {
		return;
	}

	$url         = function_exists( 'rank_math_get_permalink' ) ? rank_math_get_permalink() : get_permalink();
	$url         = $url ?: home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) );
	$title       = wp_get_document_title();
	$description = function_exists( 'rank_math_get_description' ) ? rank_math_get_description() : '';
	$description = $description ?: 'Browse houses, apartments and villas for rent in Da Nang. Contact our local team to confirm current availability and rental details.';
	$image       = home_url( '/wp-content/uploads/2021/01/House-Rental-Danang-Agencys-Logo.png' );

	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:site_name" content="House Rental Danang Agency">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:image:alt" content="House Rental Danang Agency">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
}
add_action( 'wp_head', 'hrd_add_social_meta_tags', 8 );

/** Fill missing dimensions and useful alt text on attachment images to reduce CLS. */
function hrd_image_accessibility_attributes( $attr, $attachment ) {
	if ( ! $attachment instanceof WP_Post ) {
		return $attr;
	}

	$meta = wp_get_attachment_metadata( $attachment->ID );
	if ( empty( $attr['width'] ) && ! empty( $meta['width'] ) ) {
		$attr['width'] = (int) $meta['width'];
	}
	if ( empty( $attr['height'] ) && ! empty( $meta['height'] ) ) {
		$attr['height'] = (int) $meta['height'];
	}
	if ( empty( $attr['alt'] ) ) {
		$attr['alt'] = get_the_title( $attachment->ID ) ?: 'Da Nang rental property';
	}

	$classes = isset( $attr['class'] ) ? (string) $attr['class'] : '';
	if ( false !== strpos( $classes, 'attachment-modern-property-child-slider' ) && ! empty( $attr['src'] ) ) {
		$upload_dir = wp_get_upload_dir();
		$base_url   = trailingslashit( $upload_dir['baseurl'] );
		$base_dir   = trailingslashit( $upload_dir['basedir'] );
		$relative   = 0 === strpos( $attr['src'], $base_url ) ? substr( $attr['src'], strlen( $base_url ) ) : '';

		if ( $relative && ! preg_match( '/-\d+x\d+(\.[a-z0-9]+)$/i', $relative ) ) {
			$small_relative = preg_replace( '/(\.[a-z0-9]+)$/i', '-488x326$1', $relative );
			$large_relative = preg_replace( '/(\.[a-z0-9]+)$/i', '-1240x720$1', $relative );
			$srcset         = array();

			if ( $small_relative && file_exists( $base_dir . $small_relative ) ) {
				$srcset[] = $base_url . $small_relative . ' 488w';
			}
			if ( $large_relative && file_exists( $base_dir . $large_relative ) ) {
				$srcset[] = $base_url . $large_relative . ' 1240w';
			}
			if ( $srcset ) {
				$attr['srcset'] = implode( ', ', $srcset );
				$attr['sizes']  = '(max-width: 680px) 100vw, 680px';
			}
		}
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'hrd_image_accessibility_attributes', 10, 2 );

/**
 * Demote the first content H1 on four audited posts whose template already
 * provides the page H1. Empty image-only headings become neutral containers.
 */
function hrd_fix_duplicate_editorial_h1( $content ) {
	if (
		! is_singular( 'post' ) ||
		! in_the_loop() ||
		! is_main_query() ||
		get_the_ID() !== get_queried_object_id()
	) {
		return $content;
	}

	$affected_slugs = array(
		'quan-ca-phe-vintage-o-da-nang',
		'danang-beaches',
		'discovering-the-ancient-mysterious-relic-of-my-son-sanctuary-in-vietnam-my-son-sanctuary-and-its-unesco-world-heritage',
		'my-khe-beach',
	);
	if ( ! in_array( get_post_field( 'post_name', get_the_ID() ), $affected_slugs, true ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/<h1\\b([^>]*)>(.*?)<\\/h1>/is',
		function ( $matches ) {
			$tag = trim( wp_strip_all_tags( $matches[2] ) ) ? 'h2' : 'div';

			return sprintf( '<%1$s%2$s>%3$s</%1$s>', $tag, $matches[1], $matches[2] );
		},
		$content,
		1
	);
}
add_filter( 'the_content', 'hrd_fix_duplicate_editorial_h1', 7 );

/**
 * Refine Rank Math's existing editorial/archive graph without duplicating its
 * WebPage, publisher, author, or CollectionPage entities.
 */
function hrd_enrich_editorial_archive_schema( $data, $jsonld ) {
	if ( is_admin() || ! is_array( $data ) ) {
		return $data;
	}

	if ( is_singular( 'post' ) ) {
		$categories = get_the_category( get_queried_object_id() );
		$about      = array();
		foreach ( $categories as $category ) {
			$topic = array(
				'@type' => 'Thing',
				'name'  => wp_strip_all_tags( $category->name ),
			);
			$url = get_category_link( $category );
			if ( ! is_wp_error( $url ) ) {
				$topic['url'] = $url;
			}
			$about[] = $topic;
		}

		foreach ( $data as &$entity ) {
			if ( ! is_array( $entity ) || ! in_array( 'Article', (array) ( $entity['@type'] ?? array() ), true ) ) {
				continue;
			}

			$entity['@type'] = 'BlogPosting';
			if ( ! empty( $about ) ) {
				$entity['about'] = $about;
			}
		}
		unset( $entity );

		return $data;
	}

	$property_taxonomies = get_object_taxonomies( 'property' );
	$is_collection       = is_category() || is_post_type_archive( 'property' ) || is_tax( $property_taxonomies );
	if ( ! $is_collection ) {
		return $data;
	}

	global $wp_query;
	if ( ! $wp_query instanceof WP_Query || empty( $wp_query->posts ) ) {
		return $data;
	}

	$items = array();
	foreach ( $wp_query->posts as $post ) {
		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
			continue;
		}

		$url = get_permalink( $post );
		if ( ! $url ) {
			continue;
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => count( $items ) + 1,
			'name'     => html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'      => $url,
		);
	}
	if ( empty( $items ) ) {
		return $data;
	}

	$canonical = '';
	foreach ( $data as $entity ) {
		if ( ! is_array( $entity ) || ! in_array( 'CollectionPage', (array) ( $entity['@type'] ?? array() ), true ) ) {
			continue;
		}

		$canonical = $entity['url'] ?? preg_replace( '/#webpage$/', '', (string) ( $entity['@id'] ?? '' ) );
		break;
	}
	$canonical   = $canonical ?: home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) );
	$canonical   = strtok( $canonical, '?' );
	$itemlist_id = trailingslashit( $canonical ) . '#itemlist';

	foreach ( $data as &$entity ) {
		if ( ! is_array( $entity ) || ! in_array( 'CollectionPage', (array) ( $entity['@type'] ?? array() ), true ) ) {
			continue;
		}

		$entity['mainEntity'] = array( '@id' => $itemlist_id );
	}
	unset( $entity );

	$data['HRDArchiveItemList'] = array(
		'@type'           => 'ItemList',
		'@id'             => $itemlist_id,
		'numberOfItems'   => count( $items ),
		'itemListElement' => $items,
	);

	return $data;
}
add_filter( 'rank_math/json_ld', 'hrd_enrich_editorial_archive_schema', 30, 2 );

/** Fill only the audited public URLs that currently have no meta description. */
function hrd_filter_missing_meta_descriptions( $description ) {
	if ( is_admin() || trim( (string) $description ) ) {
		return $description;
	}

	$path = wp_parse_url( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ), PHP_URL_PATH );
	$path = trailingslashit( '/' . ltrim( (string) strtok( $path, '?' ), '/' ) );
	$descriptions = array(
		'/vi/tim-kiem-bat-dong-san/' => 'Tìm kiếm nhà, căn hộ và biệt thự cho thuê tại Đà Nẵng theo khu vực, loại hình và nhu cầu của bạn.',
		'/ko/maemul-kensaku/' => '다낭의 주택, 아파트와 빌라 임대 매물을 지역과 조건별로 검색해 보세요.',
		'/ja/bukken-kensaku/' => 'ダナンの一戸建て、アパート、ヴィラの賃貸物件をエリアや条件から検索できます。',
		'/ru/poisk-nedvizhimosti/' => 'Найдите дома, квартиры и виллы в аренду в Дананге по району, типу и вашим условиям.',
		'/zh/fangyuan-sousuo/' => '按区域、房型和需求搜索岘港的出租房屋、公寓和别墅。',
		'/faqs/' => 'Answers to common questions about renting a house, apartment, or villa in Da Nang, Vietnam.',
		'/ja/faqs/' => 'ベトナム・ダナンで家、一戸建て、アパート、ヴィラを借りる際によくある質問と回答。',
		'/ru/faqs/' => 'Ответы на частые вопросы об аренде домов, квартир и вилл в Дананге, Вьетнам.',
		'/zh/faqs/' => '解答有关在越南岘港租赁房屋、公寓和别墅的常见问题。',
		'/apartments/ngu-hanh-son/' => 'Browse apartments for rent in Ngu Hanh Son, Da Nang, with current listing details and local support.',
		'/apartments/son-tra/' => 'Browse apartments for rent in Son Tra, Da Nang, with current listing details and local support.',
		'/houses/ngu-hanh-son/' => 'Browse houses for rent in Ngu Hanh Son, Da Nang, with current listing details and local support.',
		'/houses/son-tra/' => 'Browse houses for rent in Son Tra, Da Nang, with current listing details and local support.',
		'/vi/apartments/ngu-hanh-son/' => 'Xem các căn hộ cho thuê tại Ngũ Hành Sơn, Đà Nẵng cùng thông tin hiện tại và hỗ trợ địa phương.',
		'/vi/apartments/son-tra/' => 'Xem các căn hộ cho thuê tại Sơn Trà, Đà Nẵng cùng thông tin hiện tại và hỗ trợ địa phương.',
		'/vi/houses/ngu-hanh-son/' => 'Xem các ngôi nhà cho thuê tại Ngũ Hành Sơn, Đà Nẵng cùng thông tin hiện tại và hỗ trợ địa phương.',
		'/vi/houses/son-tra/' => 'Xem các ngôi nhà cho thuê tại Sơn Trà, Đà Nẵng cùng thông tin hiện tại và hỗ trợ địa phương.',
		'/ko/apartments/ngu-hanh-son/' => '응우한선 지역의 현재 아파트 임대 매물과 현지 안내를 확인해 보세요.',
		'/ko/apartments/son-tra/' => '선짜 지역의 현재 아파트 임대 매물과 현지 안내를 확인해 보세요.',
		'/ko/houses/ngu-hanh-son/' => '응우한선 지역의 현재 주택 임대 매물과 현지 안내를 확인해 보세요.',
		'/ko/houses/son-tra/' => '선짜 지역의 현재 주택 임대 매물과 현지 안내를 확인해 보세요.',
		'/ja/apartments/ngu-hanh-son/' => 'ダナンのグーハインソン区で借りられるアパートの最新情報と現地サポートをご覧ください。',
		'/ja/apartments/son-tra/' => 'ダナンのソンチャ区で借りられるアパートの最新情報と現地サポートをご覧ください。',
		'/ja/houses/ngu-hanh-son/' => 'ダナンのグーハインソン区で借りられる住宅の最新情報と現地サポートをご覧ください。',
		'/ja/houses/son-tra/' => 'ダナンのソンチャ区で借りられる住宅の最新情報と現地サポートをご覧ください。',
		'/ru/apartments/ngu-hanh-son/' => 'Актуальные квартиры в аренду в районе Нгу Хань Шон, Дананг, и помощь местной команды.',
		'/ru/apartments/son-tra/' => 'Актуальные квартиры в аренду в районе Сон Тра, Дананг, и помощь местной команды.',
		'/ru/houses/ngu-hanh-son/' => 'Актуальные дома в аренду в районе Нгу Хань Шон, Дананг, и помощь местной команды.',
		'/ru/houses/son-tra/' => 'Актуальные дома в аренду в районе Сон Тра, Дананг, и помощь местной команды.',
		'/zh/apartments/ngu-hanh-son/' => '浏览岘港五行山郡的公寓出租信息，获取最新房源详情和本地支持。',
		'/zh/apartments/son-tra/' => '浏览岘港山茶郡的公寓出租信息，获取最新房源详情和本地支持。',
		'/zh/houses/ngu-hanh-son/' => '浏览岘港五行山郡的房屋出租信息，获取最新房源详情和本地支持。',
		'/zh/houses/son-tra/' => '浏览岘港山茶郡的房屋出租信息，获取最新房源详情和本地支持。',
		'/compare-properties/' => 'Compare Da Nang rental properties by type, location, features and current listing details.',
		'/properties-search/' => 'Search current houses, apartments and villas for rent in Da Nang using practical filters.',
		'/our-agents/' => 'Meet the House Rental Danang team and find local help for your next rental home.',
		'/agencies/' => 'Find real estate agencies and rental contacts serving Da Nang, Vietnam.',
		'/danang-guide/' => 'Practical Da Nang guides covering neighborhoods, travel, food, companies and everyday local information.',
		'/danang-guide/travel/' => 'Travel guides and practical route information for exploring Da Nang and Central Vietnam.',
		'/danang-guide/company/' => 'Useful company and service information for residents and renters in Da Nang.',
		'/danang-guide/useful-info/' => 'Useful local information for living, renting and getting around Da Nang.',
		'/location/danang/' => 'Rental properties in Da Nang with current details for houses, apartments and villas.',
		'/location/son-tra/' => 'Rental properties in Son Tra, Da Nang, including houses and apartments with local support.',
		'/location/ngu-hanh-son/' => 'Rental properties in Ngu Hanh Son, Da Nang, including houses and apartments with local support.',
		'/location/hai-chau/' => 'Rental properties in Hai Chau, Da Nang, including houses and apartments with local support.',
	);

	return $descriptions[ $path ] ?? $description;
}
add_filter( 'rank_math/frontend/description', 'hrd_filter_missing_meta_descriptions', 20 );

/** CTR-focused title and description overrides for priority landing pages. */
function hrd_ctr_meta_overrides( $value ) {
	if ( is_admin() ) {
		return $value;
	}

	$path = wp_parse_url( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ), PHP_URL_PATH );
	$path = trailingslashit( '/' . ltrim( (string) strtok( $path, '?' ), '/' ) );
	$descriptions = array(
		'/apartments/' => 'Compare apartment rents in Da Nang by area and bedroom count. See what to check on utilities, deposits and contracts before you book a viewing.',
		'/properties-search/' => 'Find houses, apartments and villas for rent in Da Nang. Filter by area, property type and budget, then ask our local team to confirm availability.',
		'/houses/hai-chau/' => 'Find houses for rent in Hai Chau, Da Nang, near the Han River, schools and city services. Compare current homes and request local viewing help.',
		'/apartments/an-thuong/' => 'Explore apartments for rent in An Thuong, Da Nang, near My Khe Beach, cafes and daily services. Compare current options with local rental help.',
		'/apartment-buildings/the-filmore/' => 'See apartments for rent at The Filmore Da Nang, with building details, available layouts and local help confirming current rent and viewing times.',
		'/apartment-buildings/the-monarchy/' => 'Explore The Monarchy apartments for rent in Da Nang. Compare layouts, building amenities and current availability with local rental support.',
		'/faqs/' => 'Get clear answers about renting in Da Nang: deposits, contracts, documents, fees, maintenance and the steps to arrange a viewing.',
		'/our-agents/' => 'Meet the local House Rental Danang team. Tell us your area, budget and move-in date, and get practical help finding a suitable home.',
		'/testimonials/' => 'Read real tenant stories about searching, viewing and renting homes in Da Nang, plus what to expect when working with our local team.',
	);
	$titles = array(
		'/apartments/' => 'Apartments for Rent in Da Nang | Areas & Prices',
		'/properties-search/' => 'Da Nang Rental Properties | Search Houses & Apartments',
		'/houses/hai-chau/' => 'Houses for Rent in Hai Chau, Da Nang',
		'/apartments/an-thuong/' => 'Apartments for Rent in An Thuong, Da Nang',
		'/apartment-buildings/the-filmore/' => 'The Filmore Apartments for Rent in Da Nang',
		'/apartment-buildings/the-monarchy/' => 'The Monarchy Apartments for Rent in Da Nang',
		'/faqs/' => 'Da Nang Rental FAQs | Deposits, Contracts & Fees',
		'/our-agents/' => 'Our Da Nang Rental Agents | Local Help',
	);

	$hook = current_filter();
	if ( 'rank_math/frontend/title' === $hook && isset( $titles[ $path ] ) ) {
		return $titles[ $path ];
	}
	if ( 'rank_math/frontend/description' === $hook && isset( $descriptions[ $path ] ) ) {
		return $descriptions[ $path ];
	}
	return $value;
}
add_filter( 'rank_math/frontend/title', 'hrd_ctr_meta_overrides', 45 );
add_filter( 'rank_math/frontend/description', 'hrd_ctr_meta_overrides', 45 );

function hrd_allow_admin_svg_uploads( $mimes ) {
	if ( current_user_can( 'administrator' ) ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'hrd_allow_admin_svg_uploads' );

function hrd_validate_svg_filetype( $filetype, $file, $filename, $mimes ) {
	if ( empty( $filetype['type'] ) && current_user_can( 'administrator' ) ) {
		$checked = wp_check_filetype( $filename, $mimes );

		if ( in_array( $checked['ext'], array( 'svg', 'svgz' ), true ) ) {
			$filetype = array(
				'ext'             => $checked['ext'],
				'type'            => $checked['type'],
				'proper_filename' => $filename,
			);
		}
	}

	return $filetype;
}
add_filter( 'wp_check_filetype_and_ext', 'hrd_validate_svg_filetype', 10, 4 );

/**
 * Keep the thumbnail-generation rules previously configured in ThumbPress.
 */
function hrd_disable_unused_image_sizes( $sizes ) {
	$disabled_sizes = array(
		'thumbnail',
		'medium',
		'medium_large',
		'large',
		'1536x1536',
		'2048x2048',
		'post-thumbnail',
		'partners-logo',
	);

	foreach ( $disabled_sizes as $size ) {
		unset( $sizes[ $size ] );
	}

	return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'hrd_disable_unused_image_sizes' );
