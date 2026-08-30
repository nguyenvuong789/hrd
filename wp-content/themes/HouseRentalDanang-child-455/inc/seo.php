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
		'modern-property-child-slider',
		'property-detail-video-image',
		'agent-image',
		'partners-logo',
	);

	foreach ( $disabled_sizes as $size ) {
		unset( $sizes[ $size ] );
	}

	return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'hrd_disable_unused_image_sizes' );
