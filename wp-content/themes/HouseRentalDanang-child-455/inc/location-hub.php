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
				'meta_title' => 'Ngu Hanh Son Rentals | Apartments, Houses & Villas',
				'meta_desc' => 'Looking near My An, An Thuong or the southern beaches? Compare Ngu Hanh Son apartments, houses and villas, then confirm current options before viewing.',
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
				'meta_title' => 'Hai Chau Rentals | Apartments, Houses & Villas',
				'meta_desc' => 'Need a central Da Nang home near offices, schools and the Han River? Compare Hai Chau apartments, houses and villas and request local viewing help.',
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
	$language = hrd_sanitize_location_hub_language( $language );
	if ( 'en' !== $language ) {
		$localized = array(
			'vi' => array(
				'son-tra' => '<div class="hrd-location-hub__editorial"><h2>Sống và thuê nhà ở Sơn Trà có gì khác?</h2><p>Sơn Trà phù hợp với người muốn gần biển Mỹ Khê, sông Hàn và các dịch vụ phía Đông Đà Nẵng. Cùng một quận nhưng căn hộ gần biển, nhà trong hẻm và khu ven sông có thể khác nhau rõ về tiếng ồn, chỗ để xe và thời gian di chuyển.</p><h2>Nên chọn căn hộ, nhà hay biệt thự?</h2><p>Căn hộ thường tiện cho người độc thân, cặp đôi hoặc người làm việc từ xa cần nội thất và ít bảo trì. Nhà phù hợp gia đình cần nhiều phòng, kho chứa hoặc lối đi riêng. Biệt thự đem lại sân vườn và không gian rộng hơn nhưng cần hỏi kỹ về hồ bơi, bảo trì, vệ sinh và phí dịch vụ.</p><h2>Checklist trước khi đặt cọc ở Sơn Trà</h2><ul><li>Xác nhận đúng căn, tầng, hướng cửa sổ, nội thất và chỗ đậu xe.</li><li>Hỏi rõ tiền điện, nước, internet, phí quản lý, vệ sinh và tiền cọc.</li><li>Kiểm tra tiếng ồn, công trình, thoát nước và đường đi vào tại giờ bạn thường di chuyển.</li><li>Ghi bằng văn bản trách nhiệm sửa chữa, thời hạn báo trước và đăng ký tạm trú.</li></ul><p>Nếu cần trung tâm hơn, hãy so sánh <a href="/vi/khuvuc/hai-chau/">nhà cho thuê Hải Châu</a>. Nếu muốn khu ven biển phía Nam, xem <a href="/vi/khuvuc/ngu-hanh-son/">nhà cho thuê Ngũ Hành Sơn</a>.</p></div>',
				'ngu-hanh-son' => '<div class="hrd-location-hub__editorial"><h2>Ngũ Hành Sơn phù hợp với ai?</h2><p>Ngũ Hành Sơn trải dài từ Mỹ An, An Thượng đến các khu dân cư và khu nghỉ dưỡng phía Nam. Người thuê nên so sánh cả tuyến đi làm, trường học, chợ, bãi đỗ xe và khoảng cách thực tế thay vì chỉ nhìn tên phường.</p><h2>Chọn loại nhà nào cho kế hoạch dài hạn?</h2><p>Căn hộ thuận tiện cho người muốn nhà có sẵn nội thất và ít việc bảo trì. Nhà riêng cho gia đình nhiều phòng hoặc cần không gian làm việc. Biệt thự và nhà phong cách nghỉ dưỡng có thêm sân, hồ bơi hoặc tiện ích chung nhưng thường phát sinh phí quản lý, vệ sinh và di chuyển.</p><h2>Cần hỏi gì trước khi ký hợp đồng?</h2><ul><li>Giá thuê đã gồm phí quản lý, internet, vệ sinh, tiện ích và chỗ đậu xe chưa?</li><li>Ai chịu trách nhiệm sửa máy lạnh, thiết bị, thấm dột và hư hỏng?</li><li>Hợp đồng có hỗ trợ đăng ký tạm trú và ghi rõ điều kiện hoàn cọc không?</li><li>Hãy đi thử tuyến từ nhà đến nơi làm việc hoặc bãi biển vào giờ cao điểm.</li></ul><p>So sánh thêm <a href="/vi/khuvuc/son-tra/">Sơn Trà</a> nếu ưu tiên phía Đông và biển, hoặc <a href="/vi/khuvuc/hai-chau/">Hải Châu</a> nếu cần trung tâm thành phố.</p></div>',
				'hai-chau' => '<div class="hrd-location-hub__editorial"><h2>Thuê nhà ở Hải Châu: ưu tiên kết nối trung tâm</h2><p>Hải Châu phù hợp với người đi làm, gia đình và người cần gần trường học, bệnh viện, chợ, văn phòng và sông Hàn. Căn hộ ven sông, tuyến phố kinh doanh và hẻm dân cư có trải nghiệm khác nhau về tiếng ồn, chỗ đậu xe và lối vào.</p><h2>Căn hộ hay nhà riêng ở Hải Châu?</h2><p>Căn hộ thường có thang máy, bảo vệ và tiện ích quản lý. Nhà riêng cho nhiều phòng, kho chứa và sự riêng tư hơn nhưng cần hỏi kỹ về bảo trì, an ninh, ngập nước và chỗ để xe. Hãy xem đúng căn và kiểm tra đường đi vào ở giờ bạn thường về nhà.</p><h2>5 điểm cần xác nhận trước khi cọc</h2><ul><li>Phí điện, nước, internet, quản lý, gửi xe và vệ sinh.</li><li>Nội thất, thiết bị, tình trạng bàn giao và trách nhiệm sửa chữa.</li><li>Tiếng ồn đường phố, công trình gần nhà, thoát nước và áp lực nước.</li><li>Thời hạn hợp đồng, báo trước, hoàn cọc và đăng ký tạm trú.</li><li>Thời gian di chuyển thực tế đến nơi làm việc, trường học hoặc bệnh viện.</li></ul><p>Nếu muốn gần biển hơn, so sánh <a href="/vi/khuvuc/son-tra/">Sơn Trà</a> và <a href="/vi/khuvuc/ngu-hanh-son/">Ngũ Hành Sơn</a>.</p></div>',
			),
			'ko' => array(
				'son-tra' => '<div class="hrd-location-hub__editorial"><h2>선짜에서 임대할 때 확인할 점</h2><p>선짜는 미케 해변, 한강과 다낭 동쪽 생활권을 원하는 임차인에게 잘 맞습니다. 해변 인근 아파트와 주거 골목의 소음, 주차, 통근 시간은 크게 다를 수 있으므로 정확한 주소를 확인하세요.</p><h2>아파트, 주택, 빌라 중 무엇이 맞을까요?</h2><p>아파트는 가구와 관리가 편해 1인, 커플, 원격 근무자에게 실용적입니다. 주택은 가족에게 더 많은 방과 수납을 제공합니다. 빌라는 정원이나 수영장이 있지만 관리비와 청소, 유지보수 조건을 함께 확인해야 합니다.</p><h2>계약 전 체크리스트</h2><ul><li>정확한 호수, 층, 가구, 주차와 입주일을 확인하세요.</li><li>전기, 수도, 인터넷, 관리비, 청소비와 보증금을 서면으로 받으세요.</li><li>소음, 공사, 배수와 골목 진입을 평소 이동 시간에 점검하세요.</li><li>수리 책임, 해지 통지와 임시거주 등록 조건을 확인하세요.</li></ul></div>',
				'ngu-hanh-son' => '<div class="hrd-location-hub__editorial"><h2>응우한선은 어떤 임차인에게 맞나요?</h2><p>응우한선은 미안과 안트엉부터 남부 주거·리조트 지역까지 넓습니다. 지도상의 거리보다 직장, 학교, 해변과 마켓까지 실제 이동 시간과 주차를 비교하는 것이 중요합니다.</p><h2>장기 임대용 주택 선택</h2><p>아파트는 가구와 관리가 간편하고, 주택은 가족과 재택근무에 필요한 분리된 공간을 제공합니다. 빌라와 리조트형 주택은 공간과 공용시설이 좋지만 관리·청소·교통 비용이 추가될 수 있습니다.</p><h2>계약 전에 물어볼 질문</h2><ul><li>관리비, 인터넷, 청소, 주차가 월세에 포함되나요?</li><li>에어컨, 가전, 누수와 수리는 누가 담당하나요?</li><li>계약서와 보증금 반환 조건, 임시거주 등록이 명확한가요?</li></ul></div>',
				'hai-chau' => '<div class="hrd-location-hub__editorial"><h2>하이쩌우 임대의 핵심은 도심 접근성입니다</h2><p>하이쩌우는 사무실, 학교, 병원, 시장과 한강에 가까운 도심 생활을 원하는 사람에게 적합합니다. 강변 타워와 상업 거리, 주거 골목은 소음과 주차 조건이 다르므로 현장을 확인하세요.</p><h2>아파트와 주택 비교</h2><p>아파트는 엘리베이터와 관리 서비스를 이용하기 쉽고, 주택은 더 많은 방과 수납, 사생활을 제공합니다. 주택을 볼 때는 골목 진입, 배수, 보안과 수리 책임을 함께 점검하세요.</p><h2>보증금 전에 확인할 항목</h2><ul><li>전기·수도·인터넷·관리비·주차비와 청소비.</li><li>가구, 가전, 인수 상태와 수리 담당자.</li><li>소음, 공사, 배수, 수압과 통근 경로.</li><li>계약 기간, 통지 기간, 보증금 반환과 임시거주 등록.</li></ul></div>',
			),
			'ja' => array(
				'son-tra' => <<<'HTML'
<div class="hrd-location-hub__editorial">
<h2>ソンチャで暮らす：立地によって変わること</h2>
<p>ソンチャはダナンの東側に位置するため、住まい選びは単にアパートメントか一戸建てかを決めるだけではありません。ビーチへの近さ、市内へのアクセス、日常生活に使えるスペースのどれをどの程度重視するかを考えることが大切です。ミーケービーチ近くの住まいなら、朝の散歩やビーチで過ごす時間を楽しみやすくなります。一方、内陸側の物件では、より広い空間、駐車のしやすさ、異なる騒音環境を得られる場合があります。</p>
<p>上記の物件情報を使って間取りや設備を比較し、候補を絞り込む前に、必ず実際の通りを確認してください。同じ「ソンチャ」と紹介されている物件でも、道路の状況、建物、駐車方法、ビーチまでの距離によって、住み心地は大きく異なります。</p>
<h2>まず確認すべき月額予算は？</h2>
<p>最初の目安として、現在公開されている希望賃料を見ると、ソンチャではコンドミニアムが<strong>月1,460万ドン前後</strong>、一戸建てが<strong>月2,090万ドン前後</strong>です。ただし、これは契約済み賃料ではなく、あくまで参考となる希望価格です。家具付きかどうか、海の眺望、建物のサービス、寝室数、契約期間によって、金額は大きく変わります。</p>
<p>6〜12か月の契約では、家賃以外に電気、水道、インターネット、駐車場、管理費、清掃費、保証金も見込んでください。2つの住まいを比較する前に、毎月かかる総費用を必ず書面で確認しましょう。</p>
<h2>ソンチャのどのエリアを比較すべきですか？</h2>
<h3>ビーチ沿いの通りと市街地の東端</h3>
<p>ミーケービーチや、海岸沿いに集まるレストラン、カフェ、各種サービスへのアクセスを重視するなら、ビーチ沿いの通りから探し始めるのがおすすめです。このエリアでは、特にカップル、一人暮らしの方、庭付きの広さよりも建物内の設備を重視する方にとって、アパートメントが効率的な選択肢になります。ただし、交通量、建設工事、短期滞在者の多さは、通りや区画ごとに異なります。</p>
<h3>地元の住宅街</h3>
<p>海岸沿いの交通量が多い道路から離れると、プライバシー、収納、駐車のしやすさを重視しやすくなります。家族で暮らす方、ペットを飼っている方、仕事用と生活用のスペースを分けたい方には、一戸建てが適している場合があります。住所の地区名だけで判断せず、路地の幅、車の切り返しスペース、雨後の排水、日常の買い物や用事で使うルートを実際に確認してください。</p>
<h2>ソンチャではアパートメント、一戸建て、ヴィラのどれを選ぶべきですか？</h2>
<p>エレベーター、通勤・通学のしやすさ、管理の手間の少なさを最優先するなら、アパートメントを選びましょう。複数の寝室、専用玄関、十分な収納、柔軟に使える生活空間が必要なら、一戸建てが適しています。ヴィラは、庭、テラス、プールに価値を感じるかどうかで判断が変わります。魅力的な設備である一方、メンテナンスに関する確認事項が増え、利便性の高い通りから離れた場所になる可能性もあります。</p>
<p>比較しやすくするために、内見したすべての物件で同じ項目を記録してください。使用可能床面積、寝室数、バスルーム数、家具、駐車場、管理費、光熱費、インターネット、ペット規定、メンテナンスの責任範囲、最短入居可能日などを確認しましょう。</p>
<h2>ソンチャでの内見時に確認すべきこと</h2>
<ul><li>普段移動する時間帯に、物件の入口までの最後の200〜300mを歩く、または車で通る。</li><li>掲載賃料とは別に毎月かかる費用と、賃料に含まれるサービスを確認する。</li><li>ドアと窓を閉めた状態で、窓の向き、換気、水圧、携帯電話の電波、騒音を確認する。</li><li>駐車場、出入り可能な時間、来客規則、ペット、修理、引き渡し時の備品リストを確認する。</li><li>保証金を支払う前に、一時滞在登録をどのように行うのか、オーナーまたは管理会社に確認する。</li></ul>
<h2>ソンチャの長期賃貸の相場はいくらですか？</h2>
<p>公開されている物件情報は希望賃料を示すものであり、実際に契約された賃料ではありません。家具付きアパートメント、サービスアパートメント、一戸建て、ヴィラは、寝室数、床面積、契約期間、含まれるサービスごとに分けて比較してください。</p>
<table><thead><tr><th>区分</th><th>比較前に確認すること</th></tr></thead><tbody><tr><td>ワンルーム / 1ベッドルーム</td><td>一般住宅かサービスアパートメントか、光熱費、清掃、駐車場。</td></tr><tr><td>2ベッドルーム</td><td>実際に独立した2つ目の寝室があるか、床面積、バスルーム数、管理費。</td></tr><tr><td>3ベッドルーム以上</td><td>一戸建てかアパートメントか、収納、エレベーター、キッチン、メンテナンス。</td></tr><tr><td>一戸建て / ヴィラ</td><td>路地へのアクセス、専用駐車場、プール・庭の維持費、修理費。</td></tr></tbody></table>
<h2>ソンチャと周辺エリアの比較</h2>
<table><thead><tr><th>エリア</th><th>適している方</th><th>考慮すべき点</th></tr></thead><tbody><tr><td>ソンチャ</td><td>ダナン東部のビーチや川沿いを中心に生活したい方。</td><td>橋周辺の渋滞、建設工事のばらつき、駐車、排水。</td></tr><tr><td>グーハインソン</td><td>南側のビーチ沿いやリゾートエリアへのアクセスを重視する方。</td><td>ソンチャ北部や市内中心部のオフィスまでの移動が長くなる場合がある。</td></tr><tr><td>ハイチャウ</td><td>中心部のオフィスや市街地での用事を重視する方。</td><td>ビーチへの直接アクセスが少なく、駐車環境も異なる。</td></tr></tbody></table>
<h2>ダナンの賃貸物件を引き続き比較する</h2>
<p>ソンチャが希望するバランスに合わない場合は、ビーチ沿いや南側の住宅エリアを探せる<a href="/ja/location/ngu-hanh-son/">グーハインソンの賃貸物件</a>をご覧ください。市内中心部へのアクセスを優先するなら、<a href="/ja/location/hai-chau/">ハイチャウの賃貸物件</a>も候補になります。物件タイプを絞り込む前に、まずはより広い<a href="/ja/apartments/">ダナンのアパートメントガイド</a>を確認し、希望するエリアと住まいのタイプを整理しましょう。</p>
</div>
HTML,
				'ngu-hanh-son' => '<div class="hrd-location-hub__editorial"><h2>グーハインソンはどんな方に向いていますか？</h2><p>グーハインソンは、ミーアンやアン トゥオンから南部の住宅・リゾートエリアまで広がります。地図上の距離だけでなく、職場、学校、ビーチ、買い物への実際の移動時間と駐車条件を比較してください。</p><h2>長期賃貸の物件タイプを選ぶ</h2><p>アパートは家具と管理の手間を抑えやすく、一戸建ては家族や在宅勤務に必要な空間を確保できます。ヴィラやリゾート型住宅では、管理費、清掃、共用施設、交通費も含めて比較しましょう。</p><h2>契約前に確認する質問</h2><ul><li>管理費、インターネット、清掃、駐車場は家賃に含まれますか？</li><li>エアコン、家電、水漏れなどの修理は誰が担当しますか？</li><li>保証金返金条件と居住登録は契約書に明記されていますか？</li></ul></div>',
				'hai-chau' => '<div class="hrd-location-hub__editorial"><h2>ハイチャウ賃貸は中心部へのアクセスが重要</h2><p>ハイチャウは、オフィス、学校、病院、市場、ハン川に近い都市生活を求める方に向いています。川沿いのタワー、商業通り、住宅街では騒音と駐車条件が異なるため、現地確認が欠かせません。</p><h2>アパートと一戸建てを比較する</h2><p>アパートはエレベーターや管理サービスを利用しやすく、一戸建ては部屋数、収納、プライバシーを確保できます。一戸建てでは路地へのアクセス、排水、防犯、修理責任も確認してください。</p><h2>保証金を払う前の確認事項</h2><ul><li>光熱費、インターネット、管理費、駐車場、清掃費。</li><li>家具、家電、引き渡し状態、修理担当。</li><li>騒音、工事、排水、水圧、通勤ルート。</li><li>契約期間、解約予告、保証金返金、居住登録。</li></ul></div>',
			),
			'ru' => array(
				'son-tra' => '<div class="hrd-location-hub__editorial"><h2>Что проверить при аренде в Сон Тра</h2><p>Сон Тра подходит тем, кому важны пляж Микхе, река Хан и восточная часть Дананга. Даже внутри одного района квартиры у моря и дома на жилых улицах заметно различаются по шуму, парковке и времени в пути.</p><h2>Квартира, дом или вилла?</h2><p>Квартира удобна для одного человека, пары или удалённой работы благодаря мебели и простому обслуживанию. Дом даёт семье больше комнат и места для хранения. Вилла предлагает сад или бассейн, но требует уточнить уборку, обслуживание и дополнительные платежи.</p><h2>Проверка перед внесением депозита</h2><ul><li>Уточните конкретный объект, этаж, мебель, парковку и дату въезда.</li><li>Запросите письменно расходы на электричество, воду, интернет, управление и уборку.</li><li>Проверьте шум, стройки, дренаж и подъезд в обычное время поездок.</li><li>Закрепите ответственность за ремонт, срок уведомления и регистрацию проживания.</li></ul></div>',
				'ngu-hanh-son' => '<div class="hrd-location-hub__editorial"><h2>Кому подходит Нгу Хань Сон?</h2><p>Нгу Хань Сон включает Миан, Антхыонг, жилые кварталы и курортную зону южнее. Сравнивайте не только расстояние на карте, но и реальное время до работы, школы, пляжа и магазинов, а также условия парковки.</p><h2>Выбор жилья для долгосрочной аренды</h2><p>Квартира упрощает переезд и обслуживание. Дом даёт семье или работающим из дома отдельные комнаты. Виллы и курортные резиденции предлагают больше пространства и удобств, но могут требовать оплаты управления, уборки и транспорта.</p><h2>Вопросы до подписания договора</h2><ul><li>Включены ли управление, интернет, уборка и парковка?</li><li>Кто отвечает за кондиционеры, технику, протечки и ремонт?</li><li>Указаны ли возврат депозита и регистрация проживания в договоре?</li></ul></div>',
				'hai-chau' => '<div class="hrd-location-hub__editorial"><h2>Главное преимущество аренды в Хай Чау — доступ к центру</h2><p>Хай Чау подходит тем, кому важно жить рядом с офисами, школами, больницами, рынками и рекой Хан. Башни у реки, торговые улицы и жилые переулки отличаются по шуму и парковке, поэтому объект стоит проверить лично.</p><h2>Квартира или отдельный дом?</h2><p>Квартира удобна лифтом и управлением здания. Дом даёт больше комнат, хранения и приватности, но требует проверки подъезда, дренажа, безопасности и ответственности за ремонт.</p><h2>Что проверить перед депозитом</h2><ul><li>Коммунальные услуги, интернет, управление, парковка и уборка.</li><li>Мебель, техника, состояние передачи и ремонт.</li><li>Шум, стройки, дренаж, давление воды и маршрут до работы.</li><li>Срок договора, уведомление, возврат депозита и регистрация.</li></ul></div>',
			),
			'zh' => array(
				'son-tra' => '<div class="hrd-location-hub__editorial"><h2>住在山茶区，真正影响租房体验的是什么？</h2><p>山茶区位于岘港东侧。选择房源时，重点不只是公寓还是独立住宅，而是您更看重海滩距离、市中心通勤，还是日常居住空间。靠近美溪海滩的房源方便晨间散步和海边生活；稍微远离热门沿海街道，则可能获得更安静的环境、更宽敞的空间或更方便的停车条件。</p><p>浏览房源时，可以先比较户型、面积和设施，但最终一定要确认具体街道。同样标注为“山茶区”的两套房，可能因为道路宽度、建筑管理、停车方式、周边施工和离海距离而呈现完全不同的居住体验。</p><h2>筛选山茶区房源时，预算应该怎么看？</h2><p>公开挂牌信息可作为初步参考：山茶区公寓的挂牌租金约为每月 <strong>1,460 万越南盾</strong>，独立住宅约为每月 <strong>2,090 万越南盾</strong>。这些数字不是保证价格，也不代表整个区域的平均水平。家具配置、海景、楼龄、卧室数量、物业服务和租期都会影响最终报价。</p><p>如果计划租住六至十二个月，除了月租，还应将电费、水费、网络、停车费、物业管理费、清洁费和押金纳入预算。比较房源前，最好要求房东或经纪人书面列出每月固定费用和可能产生的额外费用。</p><h2>山茶区哪些位置值得重点比较？</h2><h3>美溪海滩周边与沿海街区</h3><p>如果您重视步行到海滩、咖啡馆、餐厅和日常服务，沿海街区通常是首选。这里的公寓适合单身人士、情侣和希望使用电梯、安保或公共设施的租客。需要注意的是，不同街区的车流、短租活动和施工噪音可能差别很大。</p><h3>本地住宅街与较安静的区域</h3><p>离开繁忙的沿海主路后，居住环境往往更安静，也可能拥有更好的储物和停车条件。独立住宅更适合家庭、养宠人士，或需要把工作区与生活区分开的租客。看房时应检查巷道宽度、车辆转弯空间、雨后排水和日常采购路线。</p><h2>山茶区公寓、独立住宅和别墅怎么选？</h2><p>如果您更看重电梯、物业管理、通勤便利和较少的维护工作，公寓通常更合适。需要多个卧室、独立入口、储物空间或灵活家庭布局时，可以优先比较独立住宅。别墅可能提供花园、露台或泳池，但这些设施也意味着更多清洁、维护和费用问题。</p><p>每次看房都建议记录同一组信息：实际使用面积、卧室和卫生间数量、家具、电器、停车、物业费、水电网络、宠物规定、维修责任以及最早入住日期。这样才能真正比较不同房源的总成本和居住价值。</p><h2>山茶区看房时应该检查什么？</h2><ul><li>步行或驾车检查入口前最后 200–300 米，包括平时上下班时段的路线。</li><li>确认广告租金之外还有哪些固定费用，以及哪些服务已经包含。</li><li>检查窗户朝向、通风、水压、手机信号，以及关闭门窗后的实际噪音。</li><li>确认停车、出入时间、访客规定、宠物、维修流程和书面交接清单。</li><li>支付押金前，确认房东或物业如何办理外国人临时居住登记。</li></ul><h2>继续比较岘港其他租房区域</h2><p>如果山茶区不完全符合需求，可以查看<a href="/zh/location/ngu-hanh-son/">五行山区出租房源</a>，比较美安、安上和南部沿海生活；如果工作、学校和城市服务更集中在市中心，则可查看<a href="/zh/location/hai-chau/">海洲区出租房源</a>。还可以浏览<a href="/zh/apartments/">岘港公寓出租</a>，先确定适合自己的房屋类型。</p></div>',
				'ngu-hanh-son' => '<div class="hrd-location-hub__editorial"><h2>五行山区适合哪些租客？</h2><p>五行山区从美安、安上延伸到南部住宅和度假区域。不要只比较地图距离，还要检查到工作地点、学校、海滩和市场的实际时间以及停车条件。</p><h2>长期租房类型怎么选？</h2><p>公寓家具齐全、维护简单；独立住宅适合需要多个房间的家庭或远程工作者；别墅和度假住宅空间更大，但可能产生管理、清洁、公共设施和交通费用。</p><h2>签合同前要问的问题</h2><ul><li>管理费、网络、清洁和停车是否包含在租金内？</li><li>空调、电器、漏水和维修由谁负责？</li><li>押金退还和居住登记是否写入合同？</li></ul></div>',
				'hai-chau' => '<div class="hrd-location-hub__editorial"><h2>海洲区租房的重点是市中心通勤</h2><p>海洲区适合需要靠近办公室、学校、医院、市场和汉江的人。江景公寓、商业街和住宅巷道在噪音与停车方面不同，建议在常用出行时间实地检查。</p><h2>公寓还是独立住宅？</h2><p>公寓通常有电梯和物业管理，独立住宅提供更多房间、储物和隐私。看住宅时还要检查巷道入口、排水、安全和维修责任。</p><h2>支付押金前确认</h2><ul><li>水电、网络、管理、停车和清洁费用。</li><li>家具、电器、交接状态和维修负责人。</li><li>噪音、施工、排水、水压和通勤路线。</li><li>合同期限、提前通知、押金退还和居住登记。</li></ul></div>',
			),
		);
		return isset( $localized[ $language ][ $district ] ) ? wp_kses_post( $localized[ $language ][ $district ] ) : '';
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
			'meta_title' => 'Son Tra Rentals | Apartments, Houses & Villas',
			'meta_desc' => 'Want to live near My Khe Beach and the Han River? Compare Son Tra apartments, houses and villas, then ask our local team to confirm current options.',
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
			'intro_fit_title' => 'ソンチャはどんな人に向いていますか？',
			'intro_fit' => 'ソンチャはダナン東部に位置し、ミーケービーチ、ハン川沿い、シーフード店が集まる通り、日常生活に必要なサービスへ短時間でアクセスしたい方に適しています。ビーチへのアクセスと管理のしやすさを重視するなら、アパートメントから探し始めるのが効率的です。一戸建てやヴィラなら、より広いスペースとプライバシーを確保できます。内見の前に、交通量、駐車場、建設工事による騒音、普段の市内への移動ルートも比較しておきましょう。',
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
			'meta_desc' => 'ダナンのソンチャ区で賃貸物件をお探しですか？ミーケービーチ、ハン川、地元市場、中心部に近いアパート、一戸建て、ヴィラを比較できます。間取り、広さ、設備、賃料、駐車場、光熱費、契約条件を確認し、現地チームに最新の空室と内見を相談できます。',
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
			'intro_fit_title' => '山茶区适合哪些长期租客？',
			'intro_fit' => '山茶区适合希望兼顾美溪海滩、汉江和岘港市区通勤的租客。公寓通常维护简单，适合单身人士、情侣和远程工作者；独立住宅与别墅则更适合需要多个卧室、私人入口、储物或户外空间的家庭。看房前应重点比较交通、停车、周边施工、街道噪音以及每天实际使用的通勤路线。',
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
/** Preserve safe formatting before WordPress stores taxonomy descriptions. */
function hrd_taxonomy_description_allowed_html( $tags, $context ) {
	if ( 'pre_term_description' !== $context ) {
		return $tags;
	}

	$tags['h2'] = array();
	$tags['h3'] = array();
	$tags['div'] = array( 'class' => true, 'id' => true );
	$tags['table'] = array( 'class' => true );
	$tags['thead'] = array();
	$tags['tbody'] = array();
	$tags['tr'] = array();
	$tags['th'] = array( 'scope' => true, 'colspan' => true, 'rowspan' => true );
	$tags['td'] = array( 'colspan' => true, 'rowspan' => true );

	return $tags;
}
add_filter( 'wp_kses_allowed_html', 'hrd_taxonomy_description_allowed_html', 10, 2 );

function hrd_register_taxonomy_description_hooks_for( $taxonomy ) {
	$taxonomy_object = get_taxonomy( $taxonomy );
	if ( ! $taxonomy_object || ! $taxonomy_object->show_ui ) {
		return;
	}

	static $registered = array();
	if ( isset( $registered[ $taxonomy ] ) ) {
		return;
	}
	$registered[ $taxonomy ] = true;

	add_action( "{$taxonomy}_add_form_fields", 'hrd_taxonomy_description_editor' );
	add_action( "{$taxonomy}_edit_form_fields", 'hrd_taxonomy_description_editor' );
}
function hrd_register_taxonomy_description_hooks() {
	foreach ( get_taxonomies( array( 'show_ui' => true ), 'names' ) as $taxonomy ) {
		hrd_register_taxonomy_description_hooks_for( $taxonomy );
	}
}
add_action( 'init', 'hrd_register_taxonomy_description_hooks', 20 );
add_action( 'registered_taxonomy', 'hrd_register_taxonomy_description_hooks_for', 20 );

/** Add a Visual/Text editor for taxonomy descriptions. */
function hrd_taxonomy_description_editor( $term = null ) {
	$is_edit = $term instanceof WP_Term;
	$value   = $is_edit ? html_entity_decode( (string) $term->description, ENT_QUOTES, get_bloginfo( 'charset' ) ?: 'UTF-8' ) : '';

	if ( $is_edit ) {
		echo '<tr class="form-field hrd-taxonomy-editor-row"><th scope="row"><label for="hrd-taxonomy-description">' . esc_html__( 'Description', RH_TEXT_DOMAIN ) . '</label></th><td>';
	} else {
		echo '<div class="form-field hrd-taxonomy-editor-row"><label for="hrd-taxonomy-description">' . esc_html__( 'Description', RH_TEXT_DOMAIN ) . '</label>';
	}

	wp_editor(
		$value,
		'hrd-taxonomy-description',
		array(
			'textarea_name' => 'description',
			'media_buttons' => false,
			'quicktags'     => true,
			'tinymce'       => array(
				'wpautop'          => true,
				'forced_root_block' => 'p',
			),
			'teeny'         => false,
		)
	);

	echo '<p class="description">' . esc_html__( 'Use the Visual tab for formatted headings, links, lists and tables. The Text tab accepts HTML.', RH_TEXT_DOMAIN ) . '</p>';
	echo $is_edit ? '</td></tr>' : '</div>';
}
function hrd_taxonomy_description_editor_admin_styles( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'edit-tags.php', 'term.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! taxonomy_exists( $screen->taxonomy ) ) {
		return;
	}

	wp_add_inline_style( 'dashicons', '.term-description-wrap{display:none!important}.hrd-taxonomy-editor-row .wp-editor-wrap{max-width:900px}.hrd-taxonomy-editor-row .description{margin-top:8px}' );
	// Remove the core textarea so only the Visual/Text editor submits description.
	wp_add_inline_script( 'jquery', 'jQuery(function($){$(\'.term-description-wrap\').remove();});' );
}
add_action( 'admin_enqueue_scripts', 'hrd_taxonomy_description_editor_admin_styles' );
