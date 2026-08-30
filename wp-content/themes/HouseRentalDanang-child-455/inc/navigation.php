<?php
function hrd_add_language_switcher_to_menu( $items, $args ) {
	if (
		! function_exists( 'pll_the_languages' ) ||
		! hrd_is_navigation_menu( $args )
	) {
		return $items;
	}

	$languages = pll_the_languages(
		array(
			'raw'                    => 1,
			'hide_if_empty'          => 0,
			'hide_if_no_translation' => 0,
		)
	);

	if ( empty( $languages ) || ! is_array( $languages ) ) {
		return $items;
	}

	$links        = array();
	$current_code = 'EN';
	$current_name = 'English';
	static $native_names = array(
		'en' => 'English',
		'vi' => 'Tiếng Việt',
		'ko' => '한국어',
		'ja' => '日本語',
		'ru' => 'Русский',
		'zh' => '简体中文',
	);
	foreach ( $languages as $language ) {
		$slug = $language['slug'] ?? '';
		$url  = $language['url'] ?? '';

		if ( empty( $slug ) ) {
			continue;
		}

		if ( empty( $url ) && function_exists( 'pll_home_url' ) ) {
			$url = pll_home_url( $slug );
		}

		if ( empty( $url ) ) {
			continue;
		}

		if ( ! empty( $language['current_lang'] ) ) {
			$current_code = strtoupper( $slug );
			$current_name = $native_names[ $slug ] ?? ( $language['name'] ?? $current_code );
		}

		$links[] = sprintf(
			'<a class="hrd-language-switcher__link%s" href="%s" lang="%s" hreflang="%s" aria-label="%s"><span class="hrd-language-switcher__code">%s</span><span class="hrd-language-switcher__name">%s</span>%s</a>',
			! empty( $language['current_lang'] ) ? ' is-current' : '',
			esc_url( $url ),
			esc_attr( $slug ),
			esc_attr( $slug ),
			esc_attr( $native_names[ $slug ] ?? ( $language['name'] ?? strtoupper( $slug ) ) ),
			esc_html( strtoupper( $slug ) ),
			esc_html( $native_names[ $slug ] ?? ( $language['name'] ?? strtoupper( $slug ) ) ),
			! empty( $language['current_lang'] ) ? '<span class="hrd-language-switcher__check" aria-hidden="true">✓</span>' : ''
		);
	}

	if ( empty( $links ) ) {
		return $items;
	}
	$language       = hrd_get_current_language();
	static $language_labels = array(
		'en' => 'Choose language', 'vi' => 'Chọn ngôn ngữ', 'ko' => '언어 선택',
		'ja' => '言語を選択', 'ru' => 'Выбрать язык', 'zh' => '选择语言',
	);

	return $items . sprintf(
		'<li class="menu-item hrd-language-switcher"><details><summary aria-label="%1$s"><span class="hrd-language-switcher__globe" aria-hidden="true"></span><span class="hrd-language-switcher__current-name">%2$s</span><span class="hrd-language-switcher__current-code" aria-hidden="true">%3$s</span></summary><div class="hrd-language-switcher__menu" role="menu">%4$s</div></details></li>',
		esc_attr( $language_labels[ $language ] ?? $language_labels['en'] ),
		esc_html( $current_name ),
		esc_html( $current_code ),
		implode( '', $links )
	);
}
add_filter( 'wp_nav_menu_items', 'hrd_add_language_switcher_to_menu', 20, 2 );

function hrd_localize_navigation_title( $title, $menu_item, $args ) {
	if ( ! hrd_is_navigation_menu( $args ) ) {
		return $title;
	}

	$language = hrd_get_current_language();
	static $labels = array(
		'vi' => array(
			'Home'              => 'Trang chủ',
			'About'             => 'Giới thiệu',
			'Guide'             => 'Cẩm nang',
			'Areas'             => 'Khu vực',
			'Homepage'          => 'Trang chủ',
			'Houses'            => 'Nhà',
			'Apartments'        => 'Căn hộ',
			'Villas'            => 'Biệt thự',
			'FAQs'              => 'Câu hỏi thường gặp',
			'Contact Us'        => 'Liên hệ',
			'About Us'          => 'Giới thiệu',
			'Contact'           => 'Liên hệ',
			'Danang Guide'      => 'Cẩm nang',
			'Other Useful Info' => 'Thông tin hữu ích khác',
		),
		'ko' => array(
			'Home'              => '홈',
			'About'             => '회사 소개',
			'Guide'             => '다낭 가이드',
			'Areas'             => '지역',
			'Homepage'          => '홈',
			'Houses'            => '주택',
			'Apartments'        => '아파트',
			'Villas'            => '빌라',
			'FAQs'              => '자주 묻는 질문',
			'Contact Us'        => '문의하기',
			'About Us'          => '회사 소개',
			'Contact'           => '문의',
			'Danang Guide'      => '다낭 가이드',
			'Other Useful Info' => '기타 유용한 정보',
		),
		'ja' => array(
			'Home' => 'ホーム', 'About' => '会社概要', 'Guide' => 'ダナンガイド', 'Areas' => 'エリア',
			'Homepage' => 'ホーム', 'Houses' => '一戸建て', 'Apartments' => 'アパート',
			'Villas' => 'ヴィラ', 'FAQs' => 'よくある質問', 'Contact Us' => 'お問い合わせ', 'About Us' => '会社概要',
			'Contact' => 'お問い合わせ', 'Danang Guide' => 'ダナンガイド',
			'Other Useful Info' => 'その他のお役立ち情報',
		),
		'ru' => array(
			'Home' => 'Главная', 'About' => 'О нас', 'Guide' => 'Гид по Данангу', 'Areas' => 'Районы',
			'Homepage' => 'Главная', 'Houses' => 'Дома', 'Apartments' => 'Квартиры',
			'Villas' => 'Виллы', 'FAQs' => 'Вопросы и ответы', 'Contact Us' => 'Контакты', 'About Us' => 'О нас',
			'Contact' => 'Контакты', 'Danang Guide' => 'Гид по Данангу',
			'Other Useful Info' => 'Другая полезная информация',
		),
		'zh' => array(
			'Home' => '首页', 'About' => '关于我们', 'Guide' => '岘港指南', 'Areas' => '区域',
			'Homepage' => '首页', 'Houses' => '独栋住宅', 'Apartments' => '公寓',
			'Villas' => '别墅', 'FAQs' => '常见问题', 'Contact Us' => '联系我们', 'About Us' => '关于我们',
			'Contact' => '联系我们', 'Danang Guide' => '岘港指南',
			'Other Useful Info' => '其他实用信息',
		),
	);

	$aliases = array(
		'Apartment' => 'Apartments',
		'Villa'      => 'Villas',
		'House'      => 'Houses',
		'FAQ'        => 'FAQs',
		'Contact Us' => 'Contact',
	);
	$lookup_title = $aliases[ $title ] ?? $title;

	if ( empty( $labels[ $language ] ) ) {
		return $title;
	}

	return $labels[ $language ][ $lookup_title ] ?? $labels[ $language ][ $title ] ?? $title;
}
add_filter( 'nav_menu_item_title', 'hrd_localize_navigation_title', 20, 3 );

function hrd_localize_navigation_url( $attributes, $menu_item, $args ) {
	if (
		! hrd_is_navigation_menu( $args ) ||
		! function_exists( 'pll_current_language' )
	) {
		return $attributes;
	}

	$language = pll_current_language();
	if ( 'post_type' === $menu_item->type && function_exists( 'pll_get_post' ) ) {
		$translated_id = pll_get_post( (int) $menu_item->object_id, $language );
		if ( $translated_id ) {
			$attributes['href'] = get_permalink( $translated_id );
		}
	} elseif ( 'taxonomy' === $menu_item->type && function_exists( 'pll_get_term' ) ) {
		$translated_id = pll_get_term( (int) $menu_item->object_id, $language );
		if ( $translated_id ) {
			$translated_url = get_term_link( $translated_id, $menu_item->object );
			if ( ! is_wp_error( $translated_url ) ) {
				$attributes['href'] = $translated_url;
			}
		}
	} elseif ( 'custom' === $menu_item->type && false !== strpos( $attributes['href'] ?? '', '/why-us/' ) ) {
		$translated_id = function_exists( 'pll_get_post' ) ? pll_get_post( 8953, $language ) : 0;
		if ( $translated_id ) {
			$attributes['href'] = get_permalink( $translated_id );
		}
	}

	return $attributes;
}
add_filter( 'nav_menu_link_attributes', 'hrd_localize_navigation_url', 20, 3 );

function hrd_shared_testimonials_shortcode() {
	$content = get_post_field( 'post_content', 8745 );
	if ( empty( $content ) ) {
		return '';
	}

	$content = str_replace(
		"These first-hand stories describe renters' experiences with our team. We have kept the original wording wherever possible; property availability and market details mentioned in older reviews may have changed.",
		'',
		$content
	);

	return do_shortcode( $content );
}
add_shortcode( 'hrd_shared_testimonials', 'hrd_shared_testimonials_shortcode' );

/** Add a quiet, server-rendered renter guide between inventory and testimonials. */
function hrd_home_faq_markup() {
	$language = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
	$content  = array(
		'en' => array(
			'eyebrow' => 'Practical information for renters',
			'title'   => 'Renting in Da Nang, at a glance',
			'intro'   => 'Da Nang rentals range from compact studios and serviced apartments to family houses and private villas. The right choice depends on how close you want to be to the beach, central business areas, schools or quieter residential streets. Prices and availability move with the season, furnishing level, lease length and exact address, so online listings are a starting point rather than a promise of current stock. Most renters narrow the search by area and monthly budget first, then confirm utilities, parking, deposit terms, maintenance and temporary-residence requirements before viewing. Our local team can help compare options in Son Tra, Ngu Hanh Son, Hai Chau and nearby areas, then confirm the details directly with the listing contact.',
			'questions' => array(
				array( 'What types of homes can I rent in Da Nang?', 'You can rent furnished or unfurnished studios, residential and serviced apartments, townhouses, detached houses and private villas in Da Nang. Current listings show these property types across Son Tra, Ngu Hanh Son, Hai Chau and other urban areas. Availability varies by neighborhood, lease length, furnishings, pet policy and building rules, so confirm that the exact property is offered for long-term residential use.' ),
				array( 'How much does it cost to rent a house or apartment?', 'There is no single average rent for Da Nang. As recent advertised examples, one-bedroom apartments in popular beach areas were commonly listed around VND 9-15 million per month and two-bedroom apartments around VND 12-20 million; house listings covered a much wider range. Prices depend on the exact area, size, furnishing, services and lease length. Confirm rent, deposit, utilities, parking and management fees before deciding.' ),
				array( 'Which areas are popular with long-term renters?', 'My An-An Thuong, Son Tra and Hai Chau are common starting points for long-term renters. My An-An Thuong offers beach access and international services; Son Tra includes active beach neighborhoods and quieter residential pockets; Hai Chau suits renters who prioritize the city center. Familiar neighborhood names may differ from current administrative labels, so compare the exact map pin, commute, construction noise, parking and nearby services rather than choosing by district name alone.' ),
				array( 'Can foreigners rent property in Da Nang?', 'Foreigners commonly enter residential leases for apartments, houses and villas in Da Nang. Renting is different from Vietnam\'s more restricted rules on foreign property ownership. The Housing Law 2023 regulates house leases and core contract terms, but immigration status, residence reporting and building requirements can vary by case. Before paying a deposit, verify the landlord\'s authority, the final Vietnamese-language contract and how temporary residence will be registered.' ),
				array( 'How do I check whether a listing is still available?', 'Ask the agent or owner for same-day confirmation because an online listing or “available now” label is not conclusive. Send the property link or code, preferred move-in date and lease length, then request a current viewing time, live video call or in-person inspection. Confirm the exact unit, authorized landlord or agent, final price and refund terms in writing. Do not transfer a deposit solely from photographs or a copied listing.' ),
			),
		),
		'vi' => array(
			'eyebrow' => 'Thông tin thực tế cho người thuê',
			'title'   => 'Thuê nhà ở Đà Nẵng: những điều cần biết',
			'intro'   => 'Thị trường Đà Nẵng có studio, căn hộ dịch vụ, căn hộ ở, nhà phố, nhà gia đình và biệt thự riêng. Lựa chọn phù hợp phụ thuộc vào việc bạn muốn gần biển, trung tâm, trường học hay khu dân cư yên tĩnh. Giá và tình trạng trống thay đổi theo mùa, nội thất, thời hạn thuê và địa chỉ cụ thể, vì vậy tin đăng chỉ nên được xem là điểm bắt đầu. Người thuê thường chọn khu vực và ngân sách trước, sau đó xác nhận điện nước, chỗ đậu xe, tiền cọc, bảo trì và thủ tục tạm trú trước khi xem nhà. Đội ngũ địa phương có thể giúp bạn so sánh Son Tra, Ngu Hanh Son, Hai Chau và các khu vực lân cận, rồi kiểm tra lại thông tin với bên đăng tin.',
			'questions' => array(
				array( 'Có thể thuê những loại nhà nào ở Đà Nẵng?', 'Bạn có thể thuê studio, căn hộ ở, căn hộ dịch vụ, nhà phố, nhà riêng và biệt thự có hoặc không có nội thất tại Đà Nẵng. Các tin đăng hiện tại cho thấy những loại hình này tại Son Tra, Ngu Hanh Son, Hai Chau và những khu đô thị khác. Tình trạng trống thay đổi theo khu vực, thời hạn thuê, nội thất, quy định vật nuôi và nội quy tòa nhà, vì vậy cần xác nhận căn cụ thể có cho thuê dài hạn hay không.' ),
				array( 'Giá thuê nhà hoặc căn hộ ở Đà Nẵng khoảng bao nhiêu?', 'Đà Nẵng không có một mức giá thuê trung bình phù hợp cho mọi loại nhà. Theo các mức chào thuê gần đây, căn hộ một phòng ngủ tại những khu biển phổ biến thường khoảng 9-15 triệu đồng mỗi tháng và căn hai phòng ngủ khoảng 12-20 triệu đồng; giá nhà riêng có biên độ rộng hơn nhiều. Giá thực tế phụ thuộc địa chỉ, diện tích, nội thất, dịch vụ và thời hạn thuê. Hãy xác nhận tiền thuê, tiền cọc, điện nước, gửi xe và phí quản lý.' ),
				array( 'Khu vực nào được người thuê dài hạn lựa chọn nhiều?', 'My An-An Thuong, Son Tra và Hai Chau là những điểm bắt đầu phổ biến với người thuê dài hạn. My An-An Thuong thuận tiện cho biển và dịch vụ quốc tế; Son Tra có cả khu biển sôi động lẫn các khu dân cư yên tĩnh hơn; Hai Chau phù hợp với người ưu tiên trung tâm. Tên khu vực quen dùng có thể khác tên hành chính hiện tại, vì vậy nên kiểm tra đúng vị trí bản đồ, tuyến đi lại, công trình, chỗ đậu xe và dịch vụ xung quanh.' ),
				array( 'Người nước ngoài có thể thuê nhà ở Đà Nẵng không?', 'Người nước ngoài thường ký hợp đồng thuê căn hộ, nhà và biệt thự tại Đà Nẵng. Việc thuê nhà khác với các quy định hạn chế hơn về sở hữu nhà ở của người nước ngoài tại Việt Nam. Luật Nhà ở 2023 điều chỉnh hợp đồng thuê và các nội dung chính, nhưng tình trạng nhập cảnh, khai báo lưu trú và quy định của từng tòa nhà có thể khác nhau. Trước khi đặt cọc, hãy kiểm tra quyền cho thuê, hợp đồng tiếng Việt và cách đăng ký tạm trú.' ),
				array( 'Làm sao biết tin đăng còn trống?', 'Hãy yêu cầu chủ nhà hoặc môi giới xác nhận trong ngày vì trạng thái “đang trống” trên mạng không phải lúc nào cũng còn chính xác. Gửi đường dẫn hoặc mã căn, ngày dự kiến chuyển vào và thời hạn thuê, sau đó yêu cầu lịch xem mới nhất, cuộc gọi video trực tiếp hoặc xem nhà tại chỗ. Hãy xác nhận đúng căn, người có quyền cho thuê, giá cuối cùng và điều kiện hoàn tiền bằng văn bản. Không nên chuyển tiền cọc chỉ dựa trên ảnh hoặc tin đăng được sao chép.' ),
			),
		),
		'ja' => array(
			'eyebrow' => '入居者向けの実用情報', 'title' => 'ダナンの賃貸：基本情報',
			'intro' => 'ダナンではスタジオ、サービスアパート、一戸建て、ヴィラなどを選べます。家賃と空室状況はエリア、家具、契約期間、住所で変わるため、内見前に条件を確認しましょう。',
			'questions' => array(
				array( 'ダナンではどんな家を借りられますか？', 'スタジオ、住宅用・サービスアパート、一戸建て、タウンハウス、ヴィラなどがあります。長期居住、ペット、駐車場、建物規則を物件ごとに確認してください。' ),
				array( '家賃はいくらですか？', '人気のビーチエリアでは1ベッドルームが月900万〜1,500万ドン、2ベッドルームが月1,200万〜2,000万ドンほどの掲載例があります。家賃、保証金、光熱費、駐車場、管理費を確認してください。' ),
				array( '長期滞在者に人気のエリアは？', 'ミーアン・アン Thuong、ソンチャ、ハイチャウがよく検討されます。地図位置、通勤、騒音、駐車場、周辺サービスを比較しましょう。' ),
				array( '外国人もダナンで借りられますか？', '外国人はアパート、家、ヴィラの居住用賃貸契約を結ぶことが一般的です。貸主の権限、契約書、滞在登録の方法を確認してください。' ),
				array( '掲載物件が空いているか確認するには？', '物件リンクまたは番号、入居日、契約期間を送り、当日の空室と内見時間を確認してください。最終価格と返金条件も書面で確認しましょう。' ),
			),
		),
		'ru' => array(
			'eyebrow' => 'Практическая информация для арендаторов', 'title' => 'Аренда в Дананге: главное',
			'intro' => 'В Дананге можно выбрать студию, сервисные апартаменты, дом или виллу. Цена и наличие зависят от района, мебели, срока аренды и точного адреса.',
			'questions' => array(
				array( 'Какое жильё можно снять в Дананге?', 'Доступны студии, жилые и сервисные квартиры, таунхаусы, отдельные дома и виллы. Уточняйте долгосрочную аренду, парковку и правила здания.' ),
				array( 'Сколько стоит аренда?', 'В популярных районах у пляжа встречались объявления примерно от 9–15 млн донгов за однокомнатную квартиру и 12–20 млн за двухкомнатную. Уточняйте депозит, коммунальные услуги, парковку и сборы.' ),
				array( 'Какие районы выбирают арендаторы на долгий срок?', 'Часто начинают с My An–An Thuong, Son Tra и Hai Chau. Сравнивайте точку на карте, дорогу до работы, шум, парковку и услуги поблизости.' ),
				array( 'Могут ли иностранцы арендовать жильё?', 'Иностранцы обычно заключают договоры аренды квартир, домов и вилл. Проверьте полномочия арендодателя, договор и регистрацию временного проживания.' ),
				array( 'Как проверить актуальность объявления?', 'Попросите подтверждение в тот же день, отправив ссылку или код объекта, дату въезда и срок аренды. Подтвердите точный объект, цену и условия возврата письменно.' ),
			),
		),
		'zh' => array(
			'eyebrow' => '租客实用信息', 'title' => '岘港租房：快速了解',
			'intro' => '岘港的出租房源包括单间、公寓、住宅和别墅。价格与空置情况会受到区域、家具、租期和具体地址影响，因此看房前请确认完整条件。',
			'questions' => array(
				array( '在岘港可以租到哪些房子？', '常见选择包括单间、住宅公寓、服务式公寓、联排住宅、独立房屋和别墅。请确认长期居住、宠物、停车和楼宇规定。' ),
				array( '租金大约是多少？', '热门海滩区域的一居室近期挂牌示例约为每月900万至1500万越南盾，两居室约为1200万至2000万越南盾。请确认租金、押金、水电、停车和管理费。' ),
				array( '长期租客常选择哪些区域？', 'My An–An Thuong、Son Tra 和 Hai Chau 是常见起点。请比较地图位置、通勤、施工噪音、停车和周边服务。' ),
				array( '外国人可以在岘港租房吗？', '外国人通常可以签订公寓、房屋和别墅的居住租约。支付押金前，请核实出租权、最终合同以及临时居住登记方式。' ),
				array( '如何确认房源仍然有效？', '请要求房东或中介当天确认。发送房源链接或编号、入住日期和租期，并确认具体房源、最终价格和退款条件。' ),
			),
		),
		'ko' => array(
			'eyebrow' => '임차인을 위한 실용 정보', 'title' => '다낭 임대 주택 한눈에 보기',
			'intro' => '다낭에서는 스튜디오, 서비스 아파트, 주택과 빌라를 선택할 수 있습니다. 임대료와 공실은 지역, 가구, 계약 기간과 정확한 주소에 따라 달라집니다.',
			'questions' => array(
				array( '다낭에서는 어떤 집을 빌릴 수 있나요?', '스튜디오, 주거용·서비스 아파트, 타운하우스, 단독주택과 빌라가 있습니다. 장기 거주, 반려동물, 주차와 건물 규정을 확인하세요.' ),
				array( '임대료는 얼마인가요?', '인기 해변 지역의 최근 예시로 원룸은 월 900만~1,500만 동, 투룸은 월 1,200만~2,000만 동 정도였습니다. 보증금과 공과금도 확인하세요.' ),
				array( '장기 임차인이 많이 찾는 지역은 어디인가요?', 'My An–An Thuong, Son Tra와 Hai Chau가 자주 검토됩니다. 지도 위치, 통근, 소음, 주차와 주변 서비스를 비교하세요.' ),
				array( '외국인도 다낭에서 임대할 수 있나요?', '외국인은 아파트, 주택과 빌라를 임대하는 경우가 많습니다. 임대 권한, 계약서와 임시 거주 등록 방법을 확인하세요.' ),
				array( '매물이 아직 가능한지 어떻게 확인하나요?', '매물 링크나 번호, 입주일과 계약 기간을 보내 당일 공실과 방문 시간을 확인하세요. 최종 가격과 환불 조건도 서면으로 확인하세요.' ),
			),
		),
	);
	$section = $content[ $language ] ?? $content['en'];
	if ( ! isset( $content[ $language ] ) ) {
		return '';
	}

	$output = '<section class="hrd-home-faq" aria-labelledby="hrd-home-faq-title"><div class="hrd-home-faq__intro"><p class="hrd-home-faq__eyebrow">' . esc_html( $section['eyebrow'] ) . '</p><h2 id="hrd-home-faq-title">' . esc_html( $section['title'] ) . '</h2><p>' . esc_html( $section['intro'] ) . '</p></div><div class="hrd-home-faq__items">';
	foreach ( $section['questions'] as $question ) {
		$output .= '<details class="hrd-home-faq__item"><summary>' . esc_html( $question[0] ) . '</summary><div class="hrd-home-faq__answer"><p>' . wp_kses_post( $question[1] ) . '</p></div></details>';
	}

	return $output . '</div></section>';
}

// Polylang must not translate this machine-readable theme setting.
function hrd_keep_header_variation_value() {
	return 'three';
}
add_filter( 'option_inspiry_header_mod_variation_option', 'hrd_keep_header_variation_value', 99 );
