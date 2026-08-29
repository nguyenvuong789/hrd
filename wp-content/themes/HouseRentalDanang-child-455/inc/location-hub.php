<?php
/**
 * District location hub helpers.
 *
 * All public inputs are resolved against fixed allowlists before querying.
 */
function hrd_is_location_hub_ajax_request() {
	return wp_doing_ajax()
		&& 'hrd_location_hub_load_more' === sanitize_key( wp_unslash( $_REQUEST['action'] ?? '' ) );
}

function hrd_get_location_hub_languages() {
	static $languages = null;
	if ( null !== $languages ) {
		return $languages;
	}

	$languages = array( 'en' );

	if ( function_exists( 'pll_languages_list' ) ) {
		$languages = pll_languages_list( array( 'fields' => 'slug' ) );
	} else {
		$wpml_languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
		if ( is_array( $wpml_languages ) ) {
			$languages = array_keys( $wpml_languages );
		}
	}

	$languages = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $languages ) ) ) );

	return $languages ?: array( 'en' );
}

function hrd_sanitize_location_hub_language( $language ) {
	$language = sanitize_key( (string) $language );

	return in_array( $language, hrd_get_location_hub_languages(), true ) ? $language : '';
}

function hrd_get_location_hub_default_language() {
	if ( function_exists( 'pll_default_language' ) ) {
		return sanitize_key( (string) ( pll_default_language( 'slug' ) ?: 'en' ) );
	}

	$language = apply_filters( 'wpml_default_language', null );

	return is_string( $language ) && $language ? sanitize_key( $language ) : 'en';
}

function hrd_get_location_hub_registry() {
	return array(
		'son-tra'      => array( 'term_slug' => 'son-tra' ),
		'ngu-hanh-son' => array( 'term_slug' => 'ngu-hanh-son' ),
		'hai-chau'     => array( 'term_slug' => 'hai-chau' ),
	);
}

function hrd_get_location_hub_section_definitions() {
	return array(
		'apartments' => 'apartment',
		'houses'     => 'houses',
		'villas'     => 'villas',
	);
}

function hrd_get_location_hub_sorts() {
	return array( 'price-asc', 'price-desc', 'date-asc', 'date-desc' );
}

function hrd_get_location_hub_base_term( $taxonomy, $slug ) {
	static $terms = array();

	$key = $taxonomy . ':' . $slug;
	if ( array_key_exists( $key, $terms ) ) {
		return $terms[ $key ];
	}

	$args = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
		'slug'       => $slug,
		'number'     => 1,
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$args['lang'] = hrd_get_location_hub_default_language();
	}

	$wpml_previous_language = null;
	$wpml_default_language  = null;
	if ( ! function_exists( 'pll_default_language' ) ) {
		$wpml_previous_language = apply_filters( 'wpml_current_language', null );
		$wpml_default_language  = hrd_get_location_hub_default_language();
		if ( $wpml_default_language && $wpml_default_language !== $wpml_previous_language ) {
			do_action( 'wpml_switch_language', $wpml_default_language );
		}
	}

	$found = get_terms( $args );

	if ( $wpml_previous_language && $wpml_previous_language !== $wpml_default_language ) {
		do_action( 'wpml_switch_language', $wpml_previous_language );
	}

	$terms[ $key ] = ! is_wp_error( $found ) && ! empty( $found ) ? $found[0] : null;

	return $terms[ $key ];
}

function hrd_translate_location_hub_term_id( $term_id, $taxonomy, $language ) {
	$term_id  = absint( $term_id );
	$language = hrd_sanitize_location_hub_language( $language );
	if ( ! $term_id || ! $language ) {
		return 0;
	}
	if ( $language === hrd_get_location_hub_default_language() ) {
		return $term_id;
	}

	if ( function_exists( 'pll_get_term' ) ) {
		$translated_id = pll_get_term( $term_id, $language );

		return $translated_id ? absint( $translated_id ) : 0;
	}

	$translated_id = apply_filters( 'wpml_object_id', $term_id, $taxonomy, false, $language );

	return $translated_id ? absint( $translated_id ) : 0;
}

function hrd_get_location_hub_term( $taxonomy, $slug, $language ) {
	$base_term = hrd_get_location_hub_base_term( $taxonomy, $slug );
	if ( ! $base_term instanceof WP_Term ) {
		return null;
	}

	$term_id = hrd_translate_location_hub_term_id( $base_term->term_id, $taxonomy, $language );
	$term    = $term_id ? get_term( $term_id, $taxonomy ) : null;

	return $term instanceof WP_Term && ! is_wp_error( $term ) ? $term : null;
}

function hrd_get_location_hub( $district, $language = '' ) {
	static $hubs = array();

	$district = sanitize_key( (string) $district );
	$registry = hrd_get_location_hub_registry();
	if ( ! isset( $registry[ $district ] ) ) {
		return null;
	}

	$language = hrd_sanitize_location_hub_language( $language ?: hrd_get_current_language() );
	if ( ! $language ) {
		return null;
	}
	$cache_key = $district . ':' . $language;
	if ( array_key_exists( $cache_key, $hubs ) ) {
		return $hubs[ $cache_key ];
	}

	$term = hrd_get_location_hub_term( 'property-city', $registry[ $district ]['term_slug'], $language );
	if ( ! $term instanceof WP_Term ) {
		$hubs[ $cache_key ] = null;

		return null;
	}

	$hubs[ $cache_key ] = array(
		'key'       => $district,
		'term_slug' => $registry[ $district ]['term_slug'],
		'term'      => $term,
	);

	return $hubs[ $cache_key ];
}

function hrd_get_location_hub_by_term( $term, $language = '' ) {
	if ( ! $term instanceof WP_Term || 'property-city' !== $term->taxonomy ) {
		return null;
	}

	$language = hrd_sanitize_location_hub_language( $language ?: hrd_get_current_language() );
	if ( ! $language ) {
		return null;
	}

	foreach ( array_keys( hrd_get_location_hub_registry() ) as $district ) {
		$hub = hrd_get_location_hub( $district, $language );
		if ( $hub && (int) $hub['term']->term_id === (int) $term->term_id ) {
			return $hub;
		}
	}

	return null;
}

function hrd_get_current_location_hub( $language = '' ) {
	if ( ! is_tax( 'property-city' ) ) {
		return null;
	}

	return hrd_get_location_hub_by_term( get_queried_object(), $language );
}

function hrd_is_location_hub() {
	return null !== hrd_get_current_location_hub();
}

/** Preserve compatibility while older cached templates still reference the scoped helper. */
function hrd_is_son_tra_location_hub() {
	$hub = hrd_get_current_location_hub();

	return $hub && 'son-tra' === $hub['key'];
}

function hrd_get_location_hub_nonce_action( $district, $language ) {
	$district = sanitize_key( (string) $district );
	$language = hrd_sanitize_location_hub_language( $language );
	if ( ! isset( hrd_get_location_hub_registry()[ $district ] ) || ! $language ) {
		return '';
	}

	return 'hrd_location_hub_load_more|' . $district . '|' . $language;
}

function hrd_get_location_hub_sections( $language = '' ) {
	static $cache = array();

	$language = hrd_sanitize_location_hub_language( $language ?: hrd_get_current_language() );
	if ( ! $language ) {
		$language = 'en';
	}
	if ( isset( $cache[ $language ] ) ) {
		return $cache[ $language ];
	}

	$sections = array();
	foreach ( hrd_get_location_hub_section_definitions() as $key => $slug ) {
		$sections[ $key ] = hrd_get_location_hub_term( 'property-type', $slug, $language );
	}

	$cache[ $language ] = $sections;

	return $cache[ $language ];
}

function hrd_get_location_hub_district_copy( $district, $language ) {
	$copy = array(
		'ngu-hanh-son' => array(
			'en' => array(
				'page_title' => 'Property for Rent in Ngu Hanh Son, Da Nang',
				'intro_lead_title' => 'Looking for property for rent in Ngu Hanh Son?',
				'intro_lead' => 'Compare current apartments, houses and villas across Ngu Hanh Son, including coastal neighborhoods south of central Da Nang. Use the sections below to review layouts, amenities and asking prices on one district page.',
				'intro_types_title' => 'Choose the right property type in Ngu Hanh Son',
				'intro_fit_title' => 'Which renters does Ngu Hanh Son suit?',
				'intro_fit' => 'Ngu Hanh Son gives renters more choice between beach-side apartment living, the cafes and streets around My An and An Thuong, and larger homes farther from the coast. It can suit beach-focused renters, remote workers and families, but compare road access, parking, construction activity and the daily trip to central Da Nang before choosing a property.',
				'section_label' => '%s for rent in Ngu Hanh Son',
				'meta_title' => 'Property for Rent in Ngu Hanh Son, Da Nang | House Rental Danang',
				'meta_desc' => 'Browse apartments, houses and villas for rent in Ngu Hanh Son, Da Nang, with current property details and local rental support.',
			),
			'vi' => array(
				'page_title' => 'Bất động sản cho thuê tại Ngũ Hành Sơn, Đà Nẵng',
				'intro_lead_title' => 'Đang tìm bất động sản cho thuê tại Ngũ Hành Sơn?',
				'intro_lead' => 'So sánh căn hộ, nhà và biệt thự tại Ngũ Hành Sơn, gồm các khu dân cư ven biển ở phía nam trung tâm Đà Nẵng. Dùng các mục bên dưới để xem bố cục, tiện nghi và khoảng giá trên cùng một trang khu vực.',
				'intro_types_title' => 'Chọn loại bất động sản phù hợp tại Ngũ Hành Sơn',
				'intro_fit_title' => 'Ngũ Hành Sơn phù hợp với ai?',
				'intro_fit' => 'Ngũ Hành Sơn cho người thuê nhiều lựa chọn giữa căn hộ gần biển, khu My An và An Thượng nhiều dịch vụ, cùng những căn nhà rộng hơn ở xa bờ biển. Khu vực này phù hợp với người ưu tiên biển, người làm việc từ xa và gia đình, nhưng nên kiểm tra đường đi, chỗ đỗ xe, công trình xây dựng và thời gian vào trung tâm trước khi chọn nhà.',
				'section_label' => '%s cho thuê tại Ngũ Hành Sơn',
				'meta_title' => 'Bất động sản cho thuê Ngũ Hành Sơn, Đà Nẵng | House Rental Danang',
				'meta_desc' => 'Xem căn hộ, nhà và biệt thự cho thuê tại Ngũ Hành Sơn, Đà Nẵng với thông tin hiện tại và hỗ trợ thuê nhà tại địa phương.',
			),
			'ko' => array(
				'page_title' => '다낭 응우한선 임대 부동산',
				'intro_lead_title' => '응우한선 지역의 임대 부동산을 찾고 계신가요?',
				'intro_lead' => '다낭 중심부 남쪽의 해안 주거지를 포함한 응우한선의 아파트, 주택과 빌라를 비교해 보세요. 아래 섹션에서 구조, 편의시설과 임대료를 한 지역 페이지에서 확인할 수 있습니다.',
				'intro_types_title' => '응우한선에서 알맞은 부동산 유형 선택하기',
				'section_label' => '응우한선 임대 %s',
				'meta_title' => '다낭 응우한선 임대 부동산 | House Rental Danang',
				'meta_desc' => '다낭 응우한선 지역의 아파트, 주택과 빌라 임대 매물을 현재 정보와 현지 임대 지원과 함께 확인하세요.',
			),
			'ja' => array(
				'page_title' => 'ダナン・グーハインソン区の賃貸物件',
				'intro_lead_title' => 'グーハインソン区で賃貸物件をお探しですか？',
				'intro_lead' => 'ダナン中心部の南にある海岸沿いの住宅地を含む、グーハインソン区のアパート、一戸建て、ヴィラを比較できます。下のセクションで間取り、設備、賃料の目安をご確認ください。',
				'intro_types_title' => 'グーハインソン区で適した物件タイプを選ぶ',
				'section_label' => 'グーハインソン区の賃貸%s',
				'meta_title' => 'ダナン・グーハインソン区の賃貸物件 | House Rental Danang',
				'meta_desc' => 'ダナンのグーハインソン区で借りられるアパート、一戸建て、ヴィラを最新情報と現地サポートとともにご覧ください。',
			),
			'ru' => array(
				'page_title' => 'Недвижимость в аренду в Нгу Хань Сон, Дананг',
				'intro_lead_title' => 'Ищете недвижимость в аренду в Нгу Хань Сон?',
				'intro_lead' => 'Сравните квартиры, дома и виллы в Нгу Хань Сон, включая прибрежные районы к югу от центра Дананга. В разделах ниже можно оценить планировки, удобства и стоимость на одной странице района.',
				'intro_types_title' => 'Выберите подходящий тип жилья в Нгу Хань Сон',
				'section_label' => '%s в аренду в Нгу Хань Сон',
				'meta_title' => 'Недвижимость в аренду в Нгу Хань Сон, Дананг | House Rental Danang',
				'meta_desc' => 'Квартиры, дома и виллы в аренду в районе Нгу Хань Сон, Дананг, с актуальными данными и помощью местной команды.',
			),
			'zh' => array(
				'page_title' => '岘港五行山郡出租房产',
				'intro_lead_title' => '正在寻找五行山郡出租房产？',
				'intro_lead' => '比较五行山郡的公寓、独栋住宅和别墅，包括岘港市中心以南的沿海社区。您可以在下方栏目中集中查看格局、设施和租金范围。',
				'intro_types_title' => '选择适合您的五行山郡房产类型',
				'section_label' => '五行山郡出租%s',
				'meta_title' => '岘港五行山郡出租房产 | House Rental Danang',
				'meta_desc' => '浏览岘港五行山郡的公寓、独栋住宅和别墅出租信息，获取当前房源详情和本地租房支持。',
			),
		),
		'hai-chau' => array(
			'en' => array(
				'page_title' => 'Property for Rent in Hai Chau, Da Nang',
				'intro_lead_title' => 'Looking for property for rent in Hai Chau?',
				'intro_lead' => 'Compare current apartments, houses and villas in Hai Chau, Da Nang\'s central urban district. Use the sections below to review layouts, amenities and asking prices on one district page.',
				'intro_types_title' => 'Choose the right property type in Hai Chau',
				'intro_fit_title' => 'Which renters does Hai Chau suit?',
				'intro_fit' => 'Hai Chau is the practical choice when work, schools, hospitals, markets and city services matter more than living beside the beach. Apartments can simplify central-city living, while houses may offer more space on quieter residential streets. Check traffic at your usual travel time, parking, noise and the exact river or bridge-side location before deciding.',
				'section_label' => '%s for rent in Hai Chau',
				'meta_title' => 'Property for Rent in Hai Chau, Da Nang | House Rental Danang',
				'meta_desc' => 'Browse apartments, houses and villas for rent in Hai Chau, Da Nang, with current property details and local rental support.',
			),
			'vi' => array(
				'page_title' => 'Bất động sản cho thuê tại Hải Châu, Đà Nẵng',
				'intro_lead_title' => 'Đang tìm bất động sản cho thuê tại Hải Châu?',
				'intro_lead' => 'So sánh căn hộ, nhà và biệt thự tại Hải Châu, khu đô thị trung tâm của Đà Nẵng. Dùng các mục bên dưới để xem bố cục, tiện nghi và khoảng giá trên cùng một trang khu vực.',
				'intro_types_title' => 'Chọn loại bất động sản phù hợp tại Hải Châu',
				'intro_fit_title' => 'Hải Châu phù hợp với ai?',
				'intro_fit' => 'Hải Châu phù hợp khi công việc, trường học, bệnh viện, chợ và các dịch vụ đô thị quan trọng hơn việc ở sát biển. Căn hộ thuận tiện cho nhịp sống trung tâm, còn nhà riêng có thể có thêm không gian trên các tuyến phố dân cư. Hãy kiểm tra giao thông vào giờ đi lại thường ngày, chỗ đỗ xe, tiếng ồn và vị trí cụ thể gần sông hoặc cầu trước khi quyết định.',
				'section_label' => '%s cho thuê tại Hải Châu',
				'meta_title' => 'Bất động sản cho thuê Hải Châu, Đà Nẵng | House Rental Danang',
				'meta_desc' => 'Xem căn hộ, nhà và biệt thự cho thuê tại Hải Châu, Đà Nẵng với thông tin hiện tại và hỗ trợ thuê nhà tại địa phương.',
			),
			'ko' => array(
				'page_title' => '다낭 하이쩌우 임대 부동산',
				'intro_lead_title' => '하이쩌우 지역의 임대 부동산을 찾고 계신가요?',
				'intro_lead' => '다낭의 중심 도심 지역인 하이쩌우의 아파트, 주택과 빌라를 비교해 보세요. 아래 섹션에서 구조, 편의시설과 임대료를 한 지역 페이지에서 확인할 수 있습니다.',
				'intro_types_title' => '하이쩌우에서 알맞은 부동산 유형 선택하기',
				'section_label' => '하이쩌우 임대 %s',
				'meta_title' => '다낭 하이쩌우 임대 부동산 | House Rental Danang',
				'meta_desc' => '다낭 하이쩌우 지역의 아파트, 주택과 빌라 임대 매물을 현재 정보와 현지 임대 지원과 함께 확인하세요.',
			),
			'ja' => array(
				'page_title' => 'ダナン・ハイチャウ区の賃貸物件',
				'intro_lead_title' => 'ハイチャウ区で賃貸物件をお探しですか？',
				'intro_lead' => 'ダナン中心部の市街地であるハイチャウ区のアパート、一戸建て、ヴィラを比較できます。下のセクションで間取り、設備、賃料の目安をご確認ください。',
				'intro_types_title' => 'ハイチャウ区で適した物件タイプを選ぶ',
				'section_label' => 'ハイチャウ区の賃貸%s',
				'meta_title' => 'ダナン・ハイチャウ区の賃貸物件 | House Rental Danang',
				'meta_desc' => 'ダナンのハイチャウ区で借りられるアパート、一戸建て、ヴィラを最新情報と現地サポートとともにご覧ください。',
			),
			'ru' => array(
				'page_title' => 'Недвижимость в аренду в Хай Чау, Дананг',
				'intro_lead_title' => 'Ищете недвижимость в аренду в Хай Чау?',
				'intro_lead' => 'Сравните квартиры, дома и виллы в Хай Чау, центральном городском районе Дананга. В разделах ниже можно оценить планировки, удобства и стоимость на одной странице района.',
				'intro_types_title' => 'Выберите подходящий тип жилья в Хай Чау',
				'section_label' => '%s в аренду в Хай Чау',
				'meta_title' => 'Недвижимость в аренду в Хай Чау, Дананг | House Rental Danang',
				'meta_desc' => 'Квартиры, дома и виллы в аренду в районе Хай Чау, Дананг, с актуальными данными и помощью местной команды.',
			),
			'zh' => array(
				'page_title' => '岘港海洲郡出租房产',
				'intro_lead_title' => '正在寻找海洲郡出租房产？',
				'intro_lead' => '比较岘港中心城区海洲郡的公寓、独栋住宅和别墅。您可以在下方栏目中集中查看格局、设施和租金范围。',
				'intro_types_title' => '选择适合您的海洲郡房产类型',
				'section_label' => '海洲郡出租%s',
				'meta_title' => '岘港海洲郡出租房产 | House Rental Danang',
				'meta_desc' => '浏览岘港海洲郡的公寓、独栋住宅和别墅出租信息，获取当前房源详情和本地租房支持。',
			),
		),
	);

	return $copy[ $district ][ $language ] ?? array();
}

/**
 * Provide the decision-focused editorial layer for the English district hubs.
 * Listing cards remain separate so inventory can change without rewriting the guide.
 */
function hrd_location_hub_long_content( $district, $language ) {
	if ( 'en' !== hrd_sanitize_location_hub_language( $language ) ) {
		return '';
	}

	$content = array(
		'son-tra' => <<<'HTML'
<div class="hrd-location-hub__editorial">
<h2>Living in Son Tra: what the location changes</h2>
<p>Son Tra is the eastern side of Da Nang, so the main choice is not simply apartment versus house. It is how much beach access, city access and everyday space you want to trade against one another. A home near My Khe Beach can make morning walks and beach time easy, while a property farther inland may give you more room, easier parking or a different noise profile.</p>
<p>Use the listings above as a way to compare layouts and property features, then check the exact street before treating a location as a fit. Two properties described as “Son Tra” can feel very different depending on the road, building, parking arrangement and distance from the beach.</p>
<h2>What monthly budget should you screen first?</h2>
<p>As a first screening point, current public asking-price results show condos around <strong>₫14.6 million/month</strong> and houses around <strong>₫20.9 million/month</strong> in Son Tra. Treat these as indicative asking prices, not a guaranteed market average: furnished units, sea views, building services, bedrooms and lease length can move the figure substantially.</p>
<p>For a six- to twelve-month lease, budget beyond rent for electricity, water, internet, parking, management fees, cleaning and the deposit. Ask for the full monthly cost in writing before comparing two homes.</p>
<h2>Which part of Son Tra should you compare?</h2>
<h3>Beach-side streets and the eastern city edge</h3>
<p>Beach-side streets are the natural starting point for renters who value quick access to My Khe and the restaurants, cafes and services around the coastal strip. Apartments can be efficient here, especially for couples, solo renters and people who prefer building facilities over garden space. The trade-off is that traffic, construction and short-stay activity can vary from one block to the next.</p>
<h3>Local residential streets</h3>
<p>Moving away from the busiest coastal roads can change the balance toward privacy, storage and parking. Houses may make more sense for families, pet owners or renters who need separate work and living areas. Inspect the lane width, turning space, drainage after rain and the route you would use for daily errands rather than judging the address from the district name alone.</p>
<h2>Apartments, houses or villas in Son Tra?</h2>
<p>Choose an apartment when lift access, a compact commute and lower maintenance matter most. Choose a house when you need multiple bedrooms, private entrances, more storage or flexible daily living space. A villa is a different decision: the garden, terrace or pool may be valuable, but those features also create more maintenance questions and may place you farther from the most convenient streets.</p>
<p>For a useful comparison, record the same fields for every viewing: usable floor area, bedrooms, bathrooms, furniture, parking, management fees, utilities, internet, pet rules, maintenance responsibility and the earliest possible move-in date.</p>
<h2>What should a Son Tra viewing check?</h2>
<ul><li>Walk or drive the final 200–300 metres to the entrance, including the route at the time you normally travel.</li><li>Ask which recurring fees are separate from the advertised rent and which services are included.</li><li>Check window direction, ventilation, water pressure, mobile signal and noise with the doors and windows closed.</li><li>Confirm parking, access hours, guest rules, pets, repairs and the written handover inventory.</li><li>Ask the landlord or manager how temporary-residence registration will be handled before paying a deposit.</li></ul>
<h2>Continue comparing Da Nang rentals</h2>
<p>If Son Tra is not the right balance, compare <a href="/location/ngu-hanh-son/">Ngu Hanh Son rentals</a> for beach-side and southern residential options, or <a href="/location/hai-chau/">Hai Chau rentals</a> when central-city access is the priority. You can also browse the wider <a href="/apartments/">Da Nang apartment guide</a> before narrowing the property type.</p>
</div>
HTML,
		'ngu-hanh-son' => <<<'HTML'
<div class="hrd-location-hub__editorial">
<h2>Living in Ngu Hanh Son: the main rental decision</h2>
<p>Ngu Hanh Son covers a broad southern part of Da Nang, so “near the beach” does not describe every rental experience in the same way. The practical choice is between the active streets around My An and An Thuong, quieter residential pockets, and resort-style areas farther south. Each can work well, but the daily route, parking, services and maintenance expectations are different.</p>
<p>Start with the property cards above to compare the type of home you need. Then verify the exact address, road access and surrounding construction before comparing one monthly rent with another.</p>
<h2>What budget bands are worth comparing?</h2>
<p>Public asking-price results commonly place standard apartments below premium beachfront homes, while larger houses and villas can rise quickly with floor area, gardens, pools and services. One current market reference places two- to three-bedroom beachfront apartments around <strong>$1,000–$1,500/month</strong>; use that only as a premium-segment screening band and request a current quote for the exact unit.</p>
<p>Compare like with like: furnished versus unfurnished, apartment versus villa, and rent-only versus rent plus management, cleaning or resort fees. For a long-term move, calculate the six-month total rather than choosing on the headline monthly number.</p>
<h2>My An, An Thuong and the southern coast</h2>
<h3>My An and An Thuong</h3>
<p>These areas are useful for renters who want a mix of cafes, food, local services and access toward the beach. Apartments are often the easiest first comparison for couples, solo renters and remote workers because they can combine a compact footprint with furnished living. Check the building entrance, sound insulation, lift, parking and the lane outside; the same neighbourhood can shift quickly from lively to residential.</p>
<h3>Residential and resort-side areas</h3>
<p>Farther south, larger homes, gardens and resort-style facilities may become more important than walking to a cafe. This can suit families or renters who value space and shared amenities, but the commute to central Da Nang may be longer and daily errands may require a motorbike or car. Ask what the property management covers and whether facilities, cleaning, utilities or maintenance carry separate charges.</p>
<h2>Which property type fits your Ngu Hanh Son plan?</h2>
<p>An apartment is usually the efficient choice for a furnished move with limited maintenance. A house gives more separation between bedrooms, work and living areas and can be easier for families who need storage or a private entrance. A villa or resort home should be assessed as a lifestyle package rather than just a larger floor plan: outdoor areas, pools, security, parking and service arrangements all affect the real monthly commitment.</p>
<p>When comparing options, write down the door-to-door route to work, school or the beach, not only the map distance. Check the route in normal traffic and ask whether the advertised price includes building fees, internet, cleaning, utilities or access to shared facilities.</p>
<h2>Questions to ask before signing</h2>
<ul><li>Is the exact unit shown, including its floor, view, furniture and appliances?</li><li>What is included in rent, and what is billed separately each month?</li><li>Who handles repairs, air-conditioner servicing, water issues and appliance replacement?</li><li>Can the property provide temporary-residence registration and a written contract?</li><li>What happens if construction, road access or building services change during the lease?</li></ul>
<h2>Compare the next area</h2>
<p>Compare <a href="/location/son-tra/">Son Tra rentals</a> when eastern beach access is more important, or <a href="/location/hai-chau/">Hai Chau rentals</a> when your work and daily services are central. For a broader property-type comparison, read the <a href="/apartments/">Da Nang apartment guide</a> and then return to the listings above.</p>
</div>
HTML,
		'hai-chau' => <<<'HTML'
<div class="hrd-location-hub__editorial">
<h2>Living in Hai Chau: city access comes first</h2>
<p>Hai Chau is the central urban choice for renters whose normal week revolves around offices, schools, hospitals, markets, the riverfront and city services. It is not one uniform rental market: a river-facing tower, a busy commercial street and a quieter residential lane can produce very different noise, parking and access conditions.</p>
<p>Use the sections above to compare apartments, houses and villas, then judge each option by the route you will actually take every day. A slightly smaller home in the right street can be more practical than a larger property that creates a difficult commute or parking problem.</p>
<h2>What does a realistic Hai Chau budget look like?</h2>
<p>Current public asking-price results show condos around <strong>₫11.3 million/month</strong> as a useful initial benchmark for Hai Chau. That figure is not a promise or a district-wide average: central towers, serviced apartments, houses and newer furnished units can sit well above it.</p>
<p>When comparing six- to twelve-month options, add electricity, water, internet, parking, management fees and the deposit. A lower rent may stop being cheaper if it requires paid parking, frequent taxis or separate cleaning and maintenance.</p>
<h2>What kind of Hai Chau location are you choosing?</h2>
<h3>Central and riverfront apartments</h3>
<p>Apartments work well for renters who want lift access, a managed building and a shorter route to central destinations. River views, newer facilities and a prominent address may change the cost, while busy roads can affect noise and crossing times. Ask to see the exact unit and confirm management fees, parking, visitor access, generator coverage and the building's rules.</p>
<h3>Residential streets and larger homes</h3>
<p>Houses can offer more bedrooms, storage and private space, which may suit families or people who work from home. The trade-off is that maintenance, security, parking and utilities may be more hands-on. Inspect the lane at the time you commute, check whether a car can access the entrance and look for signs of damp, drainage or recent renovation.</p>
<h2>Hai Chau compared with the beach districts</h2>
<p>Choose Hai Chau when central access saves meaningful time or when schools, hospitals and city errands shape your routine. Compare Son Tra if beach access and the eastern side of the city matter more. Compare Ngu Hanh Son if you want southern coastal neighbourhoods, My An or An Thuong, or a larger resort-style setting. The right answer depends on your repeated journeys rather than a universal “best” district.</p>
<h2>What should be confirmed before a Hai Chau lease?</h2>
<ul><li>Measure the door-to-door trip at your normal departure time and check the return route.</li><li>Confirm parking type, monthly fee, motorbike access and whether visitors can enter easily.</li><li>Ask about street noise, nearby construction, drainage, water pressure and backup power.</li><li>Record furniture, appliances, repairs, deposit terms, notice period and handover condition in writing.</li><li>Confirm the landlord or manager can complete temporary-residence registration where required.</li></ul>
<h2>Plan your next rental comparison</h2>
<p>Browse <a href="/apartments/">apartments in Da Nang</a> for a wider guide to layouts and building types, then compare <a href="/location/son-tra/">Son Tra</a> and <a href="/location/ngu-hanh-son/">Ngu Hanh Son</a> if your priorities shift toward beach access or southern residential space.</p>
</div>
HTML,
	);

	return isset( $content[ $district ] ) ? wp_kses_post( $content[ $district ] ) : '';
}

function hrd_location_hub_copy( $key, $language = '', $district = '' ) {
	$language = hrd_sanitize_location_hub_language( $language ?: hrd_get_current_language() );
	$copy     = array(
			'en' => array(
			'page_title' => 'Property for Rent in Son Tra, Da Nang',
			'intro_lead_title' => 'Looking for property for rent in Son Tra?',
			'intro_lead' => 'Compare current apartments, houses and villas near My Khe Beach, the Han River, local markets and central Da Nang. Listings help you understand layout, size, amenities and asking price. Availability can change, so ask our local team to confirm before arranging a viewing.',
			'intro_types_title' => 'Choose the right property type in Son Tra',
			'intro_fit_title' => 'Which renters does Son Tra suit?',
			'intro_fit' => 'Son Tra suits renters who want quick access to My Khe Beach, the Han River, seafood streets and daily services on the eastern side of Da Nang. Apartments are often the simplest starting point for beach access and lower maintenance; houses and villas can provide more privacy and space. Compare traffic, parking, construction noise and your regular route into the city before viewing.',
			'intro_types' => 'Apartments suit renters who want convenience and lower maintenance. Houses offer more privacy and everyday living space. Villas work well for larger layouts, gardens or pools. Use the sections below to compare each type without leaving this district page.',
			'intro_details_title' => 'What should you confirm before renting?',
			'intro_details' => 'Confirm monthly rent, deposit, contract length, utilities, furniture, pets, maintenance and move-in date. Share your budget, preferred area and bedroom count so we can check current options.',
			'intro_contact' => 'Contact our local rental team at',
			'apartments' => 'Apartments', 'houses' => 'Houses', 'villas' => 'Villas',
			'load_more' => 'Load more', 'loading' => 'Loading properties...', 'loaded' => 'More properties loaded.',
			'empty' => 'No properties are currently available in this section.', 'error' => 'Properties could not be loaded. Please try again.',
			'nav_label' => 'Browse property types', 'section_label' => '%s for rent in Son Tra',
			'sort_label' => 'Sort properties', 'sort_default' => 'Default order', 'sort_price_asc' => 'Price low to high',
			'sort_price_desc' => 'Price high to low', 'sort_date_asc' => 'Date old to new', 'sort_date_desc' => 'Date new to old', 'sort_apply' => 'Apply sort',
			'meta_title' => 'Property for Rent in Son Tra, Da Nang | House Rental Danang',
			'meta_desc' => 'Browse apartments, houses and villas for rent in Son Tra, Da Nang, with current property details and local rental support.',
		),
		'vi' => array(
			'page_title' => 'Bất động sản cho thuê tại Sơn Trà, Đà Nẵng',
			'intro_lead_title' => 'Đang tìm bất động sản cho thuê tại Sơn Trà?',
			'intro_lead' => 'Hãy so sánh căn hộ, nhà và biệt thự gần biển Mỹ Khê, sông Hàn, chợ địa phương và trung tâm Đà Nẵng. Các tin đăng giúp bạn hình dung bố cục, diện tích, tiện nghi và khoảng giá. Tình trạng thuê có thể thay đổi, vì vậy hãy nhờ đội ngũ địa phương xác nhận trước khi đặt lịch xem.',
			'intro_types_title' => 'Chọn loại bất động sản phù hợp tại Sơn Trà',
			'intro_fit_title' => 'Sơn Trà phù hợp với ai?',
			'intro_fit' => 'Sơn Trà phù hợp với người muốn dễ tiếp cận biển Mỹ Khê, sông Hàn, các tuyến phố hải sản và dịch vụ phía đông Đà Nẵng. Căn hộ thường là lựa chọn đơn giản cho người ưu tiên biển và ít bảo trì; nhà và biệt thự có thể đem lại thêm sự riêng tư và diện tích. Khi xem nhà, nên so sánh giao thông, chỗ đỗ xe, tiếng ồn xây dựng và tuyến đường đi làm thường xuyên.',
			'intro_types' => 'Căn hộ phù hợp với người muốn tiện lợi và ít phải bảo trì. Nhà riêng có nhiều sự riêng tư và không gian sinh hoạt hơn. Biệt thự phù hợp khi cần diện tích lớn, sân vườn hoặc hồ bơi. Dùng các mục bên dưới để so sánh từng loại ngay trên cùng trang khu vực.',
			'intro_details_title' => 'Cần xác nhận gì trước khi thuê?',
			'intro_details' => 'Hỏi rõ tiền thuê, tiền cọc, thời hạn hợp đồng, điện nước, nội thất, thú cưng, bảo trì và ngày chuyển vào. Hãy gửi ngân sách, khu vực mong muốn và số phòng ngủ để chúng tôi kiểm tra lựa chọn hiện tại.',
			'intro_contact' => 'Liên hệ đội ngũ địa phương qua',
			'apartments' => 'Căn hộ', 'houses' => 'Nhà', 'villas' => 'Biệt thự',
			'load_more' => 'Xem thêm', 'loading' => 'Đang tải bất động sản...', 'loaded' => 'Đã tải thêm bất động sản.',
			'empty' => 'Hiện chưa có bất động sản trong mục này.', 'error' => 'Không thể tải bất động sản. Vui lòng thử lại.',
			'nav_label' => 'Xem theo loại bất động sản', 'section_label' => '%s cho thuê tại Sơn Trà',
			'sort_label' => 'Sắp xếp bất động sản', 'sort_default' => 'Thứ tự mặc định', 'sort_price_asc' => 'Giá thấp đến cao',
			'sort_price_desc' => 'Giá cao đến thấp', 'sort_date_asc' => 'Cũ nhất đến mới nhất', 'sort_date_desc' => 'Mới nhất đến cũ nhất', 'sort_apply' => 'Áp dụng',
			'meta_title' => 'Bất động sản cho thuê Sơn Trà, Đà Nẵng | House Rental Danang',
			'meta_desc' => 'Xem căn hộ, nhà và biệt thự cho thuê tại Sơn Trà, Đà Nẵng với thông tin hiện tại và hỗ trợ thuê nhà tại địa phương.',
		),
		'ko' => array(
			'page_title' => '다낭 선짜 임대 부동산',
			'intro_lead_title' => '선짜 지역의 임대 부동산을 찾고 계신가요?',
			'intro_lead' => '미케 해변, 한강, 현지 시장과 다낭 중심부에 가까운 아파트, 주택과 빌라를 비교해 보세요. 매물 정보에서 구조, 면적, 편의시설과 임대료를 확인할 수 있습니다. 공실 여부는 바뀔 수 있으므로 방문 전에 현지 팀에 확인해 주세요.',
			'intro_types_title' => '선짜에서 알맞은 부동산 유형 선택하기',
			'intro_types' => '아파트는 편리함과 간단한 관리를 원하는 분께 적합합니다. 주택은 더 많은 사생활과 생활 공간을 제공합니다. 빌라는 넓은 구조, 정원 또는 수영장이 필요한 경우에 적합합니다. 아래 섹션에서 같은 지역 내 유형을 비교할 수 있습니다.',
			'intro_details_title' => '임대 전에 무엇을 확인해야 하나요?',
			'intro_details' => '월세, 보증금, 계약 기간, 공과금, 가구, 반려동물, 유지보수와 입주일을 확인하세요. 예산, 선호 지역과 필요한 침실 수를 알려주시면 현재 매물을 확인해 드립니다.',
			'intro_contact' => '현지 임대 지원 문의:',
			'apartments' => '아파트', 'houses' => '주택', 'villas' => '빌라',
			'load_more' => '더 보기', 'loading' => '매물을 불러오는 중...', 'loaded' => '매물을 더 불러왔습니다.',
			'empty' => '현재 이 섹션에 등록된 매물이 없습니다.', 'error' => '매물을 불러오지 못했습니다. 다시 시도해 주세요.',
			'nav_label' => '부동산 유형별 보기', 'section_label' => '선짜 임대 %s',
			'sort_label' => '매물 정렬', 'sort_default' => '기본 순서', 'sort_price_asc' => '가격 낮은 순',
			'sort_price_desc' => '가격 높은 순', 'sort_date_asc' => '오래된 순', 'sort_date_desc' => '최신순', 'sort_apply' => '적용',
			'meta_title' => '다낭 선짜 임대 부동산 | House Rental Danang',
			'meta_desc' => '다낭 선짜 지역의 아파트, 주택과 빌라 임대 매물을 현재 정보와 현지 임대 지원과 함께 확인하세요.',
		),
		'ja' => array(
			'page_title' => 'ダナン・ソンチャ区の賃貸物件',
			'intro_lead_title' => 'ソンチャ区で賃貸物件をお探しですか？',
			'intro_lead' => 'ミーケービーチ、ハン川、地元の市場、ダナン中心部に近いアパート、一戸建て、ヴィラを比較できます。各物件で間取り、広さ、設備、賃料の目安を確認してください。空室状況は変わるため、内見前に現地チームへご確認ください。',
			'intro_types_title' => 'ソンチャ区で適した物件タイプを選ぶ',
			'intro_types' => 'アパートは利便性と管理のしやすさを重視する方に向いています。一戸建てはプライバシーと生活空間を確保しやすく、ヴィラは広い間取り、庭、プールを希望する場合に適しています。下のセクションで同じ地区内の物件タイプを比較できます。',
			'intro_details_title' => '契約前に確認すること',
			'intro_details' => '月額賃料、保証金、契約期間、光熱費、家具、ペット、メンテナンス、入居日を確認してください。予算、希望エリア、寝室数をお知らせいただければ、最新の候補を確認します。',
			'intro_contact' => '現地の賃貸サポートはこちら:',
			'apartments' => 'アパート', 'houses' => '一戸建て', 'villas' => 'ヴィラ',
			'load_more' => 'さらに表示', 'loading' => '物件を読み込んでいます...', 'loaded' => '物件を追加しました。',
			'empty' => '現在このセクションに掲載中の物件はありません。', 'error' => '物件を読み込めませんでした。もう一度お試しください。',
			'nav_label' => '物件タイプから探す', 'section_label' => 'ソンチャ区の賃貸%s',
			'sort_label' => '物件の並び順', 'sort_default' => '標準順', 'sort_price_asc' => '価格の安い順',
			'sort_price_desc' => '価格の高い順', 'sort_date_asc' => '古い順', 'sort_date_desc' => '新しい順', 'sort_apply' => '適用',
			'meta_title' => 'ダナン・ソンチャ区の賃貸物件 | House Rental Danang',
			'meta_desc' => 'ダナンのソンチャ区で借りられるアパート、一戸建て、ヴィラを最新情報と現地サポートとともにご覧ください。',
		),
		'ru' => array(
			'page_title' => 'Недвижимость в аренду в Сон Тра, Дананг',
			'intro_lead_title' => 'Ищете недвижимость в аренду в Сон Тра?',
			'intro_lead' => 'Сравните квартиры, дома и виллы рядом с пляжем Ми Кхе, рекой Хан, местными рынками и центром Дананга. Объявления помогают оценить планировку, площадь, удобства и стоимость. Доступность меняется, поэтому уточняйте её у местной команды до просмотра.',
			'intro_types_title' => 'Выберите подходящий тип жилья в Сон Тра',
			'intro_types' => 'Квартиры подходят тем, кому важны удобство и простое обслуживание. Дома дают больше приватности и жилого пространства. Виллы подходят для больших планировок, сада или бассейна. В разделах ниже можно сравнить все типы в пределах одного района.',
			'intro_details_title' => 'Что проверить перед арендой?',
			'intro_details' => 'Уточните месячную плату, депозит, срок договора, коммунальные услуги, мебель, животных, обслуживание и дату заезда. Сообщите бюджет, желаемый район и число спален, чтобы мы проверили актуальные варианты.',
			'intro_contact' => 'Свяжитесь с местной командой по адресу',
			'apartments' => 'Квартиры', 'houses' => 'Дома', 'villas' => 'Виллы',
			'load_more' => 'Показать ещё', 'loading' => 'Загрузка объектов...', 'loaded' => 'Загружены дополнительные объекты.',
			'empty' => 'Сейчас в этом разделе нет доступных объектов.', 'error' => 'Не удалось загрузить объекты. Попробуйте ещё раз.',
			'nav_label' => 'Выбрать тип недвижимости', 'section_label' => '%s в аренду в Сон Тра',
			'sort_label' => 'Сортировка объектов', 'sort_default' => 'По умолчанию', 'sort_price_asc' => 'Сначала дешевле',
			'sort_price_desc' => 'Сначала дороже', 'sort_date_asc' => 'Сначала старые', 'sort_date_desc' => 'Сначала новые', 'sort_apply' => 'Применить',
			'meta_title' => 'Недвижимость в аренду в Сон Тра, Дананг | House Rental Danang',
			'meta_desc' => 'Квартиры, дома и виллы в аренду в районе Сон Тра, Дананг, с актуальными данными и помощью местной команды.',
		),
		'zh' => array(
			'page_title' => '岘港山茶郡出租房产',
			'intro_lead_title' => '正在寻找山茶郡出租房产？',
			'intro_lead' => '比较靠近美溪海滩、韩江、当地市场和岘港市中心的公寓、独栋住宅和别墅。房源信息可帮助您了解格局、面积、设施和租金范围。空置情况可能变化，看房前请向本地团队确认。',
			'intro_types_title' => '选择适合您的山茶郡房产类型',
			'intro_types' => '公寓适合重视便利和简单维护的租客。独栋住宅提供更多隐私和日常生活空间。别墅适合需要更大格局、花园或泳池的人士。您可以在下方栏目中比较同一区域内的不同房产类型。',
			'intro_details_title' => '租房前需要确认什么？',
			'intro_details' => '请确认月租、押金、合同期限、水电、家具、宠物、维修责任和入住日期。告诉我们您的预算、理想区域和卧室数量，我们会协助核实现有选择。',
			'intro_contact' => '联系本地租房团队：',
			'apartments' => '公寓', 'houses' => '独栋住宅', 'villas' => '别墅',
			'load_more' => '加载更多', 'loading' => '正在加载房源...', 'loaded' => '已加载更多房源。',
			'empty' => '此栏目目前没有可用房源。', 'error' => '无法加载房源，请重试。',
			'nav_label' => '按房产类型浏览', 'section_label' => '山茶郡出租%s',
			'sort_label' => '房源排序', 'sort_default' => '默认顺序', 'sort_price_asc' => '价格从低到高',
			'sort_price_desc' => '价格从高到低', 'sort_date_asc' => '日期从早到晚', 'sort_date_desc' => '日期从晚到早', 'sort_apply' => '应用',
			'meta_title' => '岘港山茶郡出租房产 | House Rental Danang',
			'meta_desc' => '浏览岘港山茶郡的公寓、独栋住宅和别墅出租信息，获取当前房源详情和本地租房支持。',
		),
	);

	$language = isset( $copy[ $language ] ) ? $language : 'en';
	$district = sanitize_key( (string) $district );
	if ( ! $district ) {
		$current_hub = hrd_get_current_location_hub( $language );
		$district    = $current_hub ? $current_hub['key'] : 'son-tra';
	}
	$district_copy = hrd_get_location_hub_district_copy( $district, $language );
	if ( array_key_exists( $key, $district_copy ) ) {
		return $district_copy[ $key ];
	}

	return $copy[ $language ][ $key ] ?? $copy['en'][ $key ] ?? '';
}

function hrd_get_location_hub_sort( $sort = null ) {
	if ( null === $sort ) {
		$sort = isset( $_GET['sortby'] ) ? wp_unslash( $_GET['sortby'] ) : get_option( 'theme_listing_default_sort', 'date-desc' );
	}

	$sort = sanitize_key( (string) $sort );
	if ( 'default' === $sort || '' === $sort ) {
		$sort = sanitize_key( (string) get_option( 'theme_listing_default_sort', 'date-desc' ) );
	}

	return in_array( $sort, hrd_get_location_hub_sorts(), true ) ? $sort : 'date-desc';
}

function hrd_get_location_hub_sort_selection() {
	$sort    = isset( $_GET['sortby'] ) ? sanitize_key( wp_unslash( $_GET['sortby'] ) ) : 'default';
	$allowed = array_merge( array( 'default' ), hrd_get_location_hub_sorts() );

	return in_array( $sort, $allowed, true ) ? $sort : 'default';
}

function hrd_get_location_hub_page() {
	return max( 1, absint( get_query_var( 'paged' ) ) );
}

function hrd_get_location_hub_query_args( $city_term_id, $type_term_id, $page, $sort, $language ) {
	$page      = min( 100, max( 1, absint( $page ) ) );
	$sort      = hrd_get_location_hub_sort( $sort );
	$language  = hrd_sanitize_location_hub_language( $language );
	$direction = '-asc' === substr( $sort, -4 ) ? 'ASC' : 'DESC';
	$args      = array(
		'post_type'              => 'property',
		'post_status'            => 'publish',
		'posts_per_page'         => 7,
		'offset'                 => ( $page - 1 ) * 6,
		'no_found_rows'          => true,
		'ignore_sticky_posts'    => true,
		'update_post_term_cache' => true,
		'update_post_meta_cache' => true,
		'tax_query'              => array(
			'relation' => 'AND',
			array( 'taxonomy' => 'property-city', 'field' => 'term_id', 'terms' => array( absint( $city_term_id ) ) ),
			array( 'taxonomy' => 'property-type', 'field' => 'term_id', 'terms' => array( absint( $type_term_id ) ) ),
		),
	);

	if ( function_exists( 'pll_languages_list' ) && $language ) {
		$args['lang'] = $language;
	}

	if ( 0 === strpos( $sort, 'price-' ) ) {
		$args['meta_key'] = 'REAL_HOMES_property_price';
		$args['orderby']  = array( 'meta_value_num' => $direction, 'ID' => $direction );
	} else {
		$args['orderby'] = array( 'date' => $direction, 'ID' => $direction );
	}

	return $args;
}

function hrd_get_location_hub_batch( $district, $section, $page, $sort, $language ) {
	static $cache = array();

	$district = sanitize_key( (string) $district );
	$language = hrd_sanitize_location_hub_language( $language );
	$sections = hrd_get_location_hub_sections( $language );
	$hub      = hrd_get_location_hub( $district, $language );
	$page     = absint( $page );
	if ( ! $hub || ! isset( $sections[ $section ] ) || ! $sections[ $section ] instanceof WP_Term || $page < 1 || $page > 100 ) {
		return array( 'posts' => array(), 'has_more' => false );
	}

	$sort = hrd_get_location_hub_sort( $sort );
	$key  = implode( ':', array( $district, $language, $section, $page, $sort ) );
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$wpml_previous_language = null;
	if ( ! function_exists( 'pll_languages_list' ) ) {
		$wpml_previous_language = apply_filters( 'wpml_current_language', null );
		if ( $language && $language !== $wpml_previous_language ) {
			do_action( 'wpml_switch_language', $language );
		}
	}

	$query = new WP_Query( hrd_get_location_hub_query_args( $hub['term']->term_id, $sections[ $section ]->term_id, $page, $sort, $language ) );

	if ( $wpml_previous_language && $wpml_previous_language !== $language ) {
		do_action( 'wpml_switch_language', $wpml_previous_language );
	}

	$cache[ $key ] = array(
		'posts'    => array_slice( $query->posts, 0, 6 ),
		'has_more' => $page < 100 && count( $query->posts ) > 6,
	);

	return $cache[ $key ];
}

function hrd_get_location_hub_schema_posts() {
	$language = hrd_get_current_language();
	$hub      = hrd_get_current_location_hub( $language );
	if ( ! $hub ) {
		return array();
	}

	$page     = hrd_get_location_hub_page();
	$sort     = hrd_get_location_hub_sort();
	$posts    = array();

	foreach ( array_keys( hrd_get_location_hub_sections( $language ) ) as $section ) {
		$batch = hrd_get_location_hub_batch( $hub['key'], $section, $page, $sort, $language );
		$posts = array_merge( $posts, $batch['posts'] );
	}

	return $posts;
}

function hrd_render_location_hub_cards( $posts ) {
	if ( empty( $posts ) ) {
		return '';
	}

	global $post;
	ob_start();
	foreach ( $posts as $hub_post ) {
		if ( ! $hub_post instanceof WP_Post || 'property' !== $hub_post->post_type || 'publish' !== $hub_post->post_status ) {
			continue;
		}
		$post = $hub_post;
		setup_postdata( $post );
		get_template_part( 'assets/modern/partials/properties/grid-card-1' );
	}
	wp_reset_postdata();

	return ob_get_clean();
}

function hrd_prepare_location_hub_main_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_tax( 'property-city' ) ) {
		return;
	}

	$current_term = $query->get_queried_object();
	if ( ! hrd_get_location_hub_by_term( $current_term, hrd_get_current_language() ) ) {
		return;
	}

	$query->set( 'posts_per_page', 6 );
	$sort      = hrd_get_location_hub_sort();
	$direction = '-asc' === substr( $sort, -4 ) ? 'ASC' : 'DESC';
	if ( 0 === strpos( $sort, 'price-' ) ) {
		$query->set( 'meta_key', 'REAL_HOMES_property_price' );
		$query->set( 'orderby', array( 'meta_value_num' => $direction, 'ID' => $direction ) );
	} else {
		$query->set( 'orderby', array( 'date' => $direction, 'ID' => $direction ) );
	}
}
add_action( 'pre_get_posts', 'hrd_prepare_location_hub_main_query', 30 );

function hrd_enqueue_location_hub_assets() {
	$language = hrd_get_current_language();
	$hub      = hrd_get_current_location_hub( $language );
	if ( ! $hub ) {
		return;
	}

	$nonce_action = hrd_get_location_hub_nonce_action( $hub['key'], $language );
	$script_path  = get_stylesheet_directory() . '/js/location-hub.js';
	wp_enqueue_script( 'hrd-location-hub', get_stylesheet_directory_uri() . '/js/location-hub.js', array(), (string) filemtime( $script_path ), true );
	wp_localize_script(
		'hrd-location-hub',
		'hrdLocationHub',
		array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'action'   => 'hrd_location_hub_load_more',
			'nonce'    => wp_create_nonce( $nonce_action ),
			'district' => $hub['key'],
			'language' => $language,
			'sort'     => hrd_get_location_hub_sort(),
			'strings'  => array(
				'loading' => hrd_location_hub_copy( 'loading', $language, $hub['key'] ),
				'loaded'  => hrd_location_hub_copy( 'loaded', $language, $hub['key'] ),
				'empty'   => hrd_location_hub_copy( 'empty', $language, $hub['key'] ),
				'error'   => hrd_location_hub_copy( 'error', $language, $hub['key'] ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'hrd_enqueue_location_hub_assets', 40 );

function hrd_ajax_load_location_hub_properties() {
	$district     = sanitize_key( wp_unslash( $_POST['district'] ?? '' ) );
	$raw_language = sanitize_key( wp_unslash( $_POST['language'] ?? '' ) );
	$pll_language = sanitize_key( wp_unslash( $_POST['lang'] ?? '' ) );
	$language     = hrd_sanitize_location_hub_language( $raw_language );
	$error        = hrd_location_hub_copy( 'error', $language ?: 'en', $district );
	$registry     = hrd_get_location_hub_registry();
	if ( ! $language || ! isset( $registry[ $district ] ) || ! hrd_get_location_hub( $district, $language ) ) {
		wp_send_json_error( array( 'message' => $error ), 400 );
	}
	if ( function_exists( 'pll_languages_list' ) && $pll_language !== $language ) {
		wp_send_json_error( array( 'message' => $error ), 400 );
	}

	$nonce_action = hrd_get_location_hub_nonce_action( $district, $language );
	if ( ! $nonce_action || ! check_ajax_referer( $nonce_action, 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => $error ), 403 );
	}

	$section  = sanitize_key( wp_unslash( $_POST['section'] ?? '' ) );
	$sort     = sanitize_key( wp_unslash( $_POST['sort'] ?? '' ) );
	$page     = absint( $_POST['page'] ?? 0 );
	$sections = hrd_get_location_hub_section_definitions();
	if ( ! isset( $sections[ $section ] ) || ! in_array( $sort, hrd_get_location_hub_sorts(), true ) || $page < 1 || $page > 100 ) {
		wp_send_json_error( array( 'message' => $error ), 400 );
	}

	// RealHomes card translations depend on the request's active language.
	do_action( 'wpml_switch_language', $language );

	$batch = hrd_get_location_hub_batch( $district, $section, $page, $sort, $language );
	wp_send_json_success(
		array(
			'district' => $district,
			'html'     => hrd_render_location_hub_cards( $batch['posts'] ),
			'has_more' => (bool) $batch['has_more'],
			'page'     => $page,
		)
	);
}
add_action( 'wp_ajax_hrd_location_hub_load_more', 'hrd_ajax_load_location_hub_properties' );
add_action( 'wp_ajax_nopriv_hrd_location_hub_load_more', 'hrd_ajax_load_location_hub_properties' );

function hrd_redirect_location_hub_type_query() {
	if ( ! hrd_is_location_hub() || ! isset( $_GET['type'] ) ) {
		return;
	}

	$current_url = home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) );
	wp_safe_redirect( remove_query_arg( 'type', $current_url ), 301, 'HouseRentalDanang location hub' );
	exit;
}
add_action( 'template_redirect', 'hrd_redirect_location_hub_type_query', 1 );

/** Consolidate primary property-type archives into their editorial listing pages. */
function hrd_redirect_primary_property_type_archives() {
	if ( is_admin() || ! is_tax( 'property-type' ) ) {
		return;
	}

	$term = get_queried_object();
	if ( ! $term instanceof WP_Term ) {
		return;
	}

	$map      = array( 'apartment' => 4631, 'houses' => 69, 'villas' => 8639 );
	$base_id  = 0;
	$base_slug = '';
	foreach ( $map as $slug => $page_id ) {
		$base_term = hrd_get_location_hub_base_term( 'property-type', $slug );
		if ( $base_term instanceof WP_Term && (int) $base_term->term_id === (int) $term->term_id ) {
			$base_id   = (int) $base_term->term_id;
			$base_slug = $slug;
			break;
		}
		if ( function_exists( 'pll_get_term' ) ) {
			foreach ( pll_languages_list( array( 'fields' => 'slug' ) ) as $language ) {
				$translated_id = (int) pll_get_term( $base_term->term_id ?? 0, $language );
				if ( $translated_id && $translated_id === (int) $term->term_id ) {
					$base_id   = $translated_id;
					$base_slug = $slug;
					break 2;
				}
			}
		}
	}

	if ( ! $base_id || ! $base_slug ) {
		return;
	}

	$language = hrd_get_current_language();
	$page_id  = $map[ $base_slug ];
	if ( function_exists( 'pll_get_post' ) && $language ) {
		$translated_page_id = (int) pll_get_post( $page_id, $language );
		if ( ! $translated_page_id ) {
			return;
		}
		$page_id = $translated_page_id;
	}

	$permalink = get_permalink( $page_id );
	if ( ! $permalink ) {
		return;
	}
	$target = trailingslashit( $permalink );

	wp_safe_redirect( $target, 301, 'HouseRentalDanang property-type consolidation' );
	exit;
}
add_action( 'template_redirect', 'hrd_redirect_primary_property_type_archives', 1 );

function hrd_404_empty_location_hub_page() {
	$language = hrd_get_current_language();
	$hub      = hrd_get_current_location_hub( $language );
	$page     = hrd_get_location_hub_page();
	if ( ! $hub || $page < 2 ) {
		return;
	}

	$sort     = hrd_get_location_hub_sort();
	if ( $page <= 100 ) {
		foreach ( array_keys( hrd_get_location_hub_sections( $language ) ) as $section ) {
			$batch = hrd_get_location_hub_batch( $hub['key'], $section, $page, $sort, $language );
			if ( ! empty( $batch['posts'] ) ) {
				return;
			}
		}
	}

	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}
add_action( 'template_redirect', 'hrd_404_empty_location_hub_page', 2 );

function hrd_get_location_hub_menu_paths( $language ) {
	static $paths = array();

	$language = hrd_sanitize_location_hub_language( $language );
	if ( isset( $paths[ $language ] ) ) {
		return $paths[ $language ];
	}

	$paths[ $language ] = array();
	foreach ( hrd_get_location_hub_section_definitions() as $section => $term_slug ) {
		$urls      = array();
		$base_page = get_page_by_path( $section, OBJECT, 'page' );
		if ( $base_page instanceof WP_Post ) {
			$urls[]        = get_permalink( $base_page );
			$translated_id = function_exists( 'pll_get_post' )
				? pll_get_post( $base_page->ID, $language )
				: apply_filters( 'wpml_object_id', $base_page->ID, 'page', false, $language );
			if ( $translated_id ) {
				$urls[] = get_permalink( $translated_id );
			}
		}

		$base_term       = hrd_get_location_hub_base_term( 'property-type', $term_slug );
		$translated_term = hrd_get_location_hub_term( 'property-type', $term_slug, $language );
		foreach ( array( $base_term, $translated_term ) as $term ) {
			if ( $term instanceof WP_Term ) {
				$term_url = get_term_link( $term );
				if ( ! is_wp_error( $term_url ) ) {
					$urls[] = $term_url;
				}
			}
		}

		foreach ( array_filter( $urls ) as $url ) {
			$path = untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) );
			if ( $path ) {
				$paths[ $language ][ $path ] = $section;
			}
		}
	}

	return $paths[ $language ];
}

function hrd_get_location_hub_menu_section( $menu_item, $url, $language ) {
	$definitions = hrd_get_location_hub_section_definitions();
	$object_id   = absint( $menu_item->object_id ?? 0 );
	$url_host    = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$home_host   = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	if ( $url_host && $home_host && $url_host !== $home_host ) {
		return '';
	}

	if ( 'taxonomy' === $menu_item->type && 'property-type' === $menu_item->object && $object_id ) {
		foreach ( $definitions as $section => $term_slug ) {
			$base_term       = hrd_get_location_hub_base_term( 'property-type', $term_slug );
			$translated_term = hrd_get_location_hub_term( 'property-type', $term_slug, $language );
			if ( ( $base_term instanceof WP_Term && $object_id === (int) $base_term->term_id ) || ( $translated_term instanceof WP_Term && $object_id === (int) $translated_term->term_id ) ) {
				return $section;
			}
		}
	}

	if ( 'post_type' === $menu_item->type && 'page' === $menu_item->object && $object_id ) {
		foreach ( array_keys( $definitions ) as $section ) {
			$base_page = get_page_by_path( $section, OBJECT, 'page' );
			if ( ! $base_page instanceof WP_Post ) {
				continue;
			}

			$translated_id = function_exists( 'pll_get_post' )
				? pll_get_post( $base_page->ID, $language )
				: apply_filters( 'wpml_object_id', $base_page->ID, 'page', true, $language );
			if ( $object_id === (int) $base_page->ID || ( $translated_id && $object_id === (int) $translated_id ) ) {
				return $section;
			}
		}
	}

	$path  = untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) );
	$paths = hrd_get_location_hub_menu_paths( $language );

	return $paths[ $path ] ?? '';
}

function hrd_location_hub_menu_anchors( $attributes, $menu_item, $args ) {
	if ( ! hrd_is_location_hub() || ! hrd_is_navigation_menu( $args ) ) {
		return $attributes;
	}

	$section = hrd_get_location_hub_menu_section( $menu_item, $attributes['href'] ?? $menu_item->url, hrd_get_current_language() );
	if ( ! $section ) {
		return $attributes;
	}

	$hub_url = get_term_link( get_queried_object() );
	if ( ! is_wp_error( $hub_url ) ) {
		$attributes['href'] = $hub_url . '#' . $section;
	}

	return $attributes;
}
add_filter( 'nav_menu_link_attributes', 'hrd_location_hub_menu_anchors', 40, 3 );

function hrd_location_hub_rank_math_title( $title ) {
	$hub = hrd_get_current_location_hub();

	return $hub ? hrd_location_hub_copy( 'meta_title', '', $hub['key'] ) : $title;
}
add_filter( 'rank_math/frontend/title', 'hrd_location_hub_rank_math_title', 40 );

function hrd_location_hub_rank_math_description( $description ) {
	$hub = hrd_get_current_location_hub();

	return $hub ? hrd_location_hub_copy( 'meta_desc', '', $hub['key'] ) : $description;
}
add_filter( 'rank_math/frontend/description', 'hrd_location_hub_rank_math_description', 40 );
