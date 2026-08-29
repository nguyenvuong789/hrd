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
			'Homepage'          => 'Trang chủ',
			'Houses'            => 'Nhà',
			'Apartments'        => 'Căn hộ',
			'Villas'            => 'Biệt thự',
			'FAQs'              => 'FAQs',
			'About Us'          => 'Giới thiệu',
			'Contact'           => 'Liên hệ',
			'Danang Guide'      => 'Cẩm nang',
			'Other Useful Info' => 'Thông tin hữu ích khác',
		),
		'ko' => array(
			'Homepage'          => '홈',
			'Houses'            => '주택',
			'Apartments'        => '아파트',
			'Villas'            => '빌라',
			'FAQs'              => 'FAQs',
			'About Us'          => '회사 소개',
			'Contact'           => '문의',
			'Danang Guide'      => '다낭 가이드',
			'Other Useful Info' => '기타 유용한 정보',
		),
		'ja' => array(
			'Homepage' => 'ホーム', 'Houses' => '一戸建て', 'Apartments' => 'アパート',
			'Villas' => 'ヴィラ', 'FAQs' => 'よくある質問', 'About Us' => '会社概要',
			'Contact' => 'お問い合わせ', 'Danang Guide' => 'ダナンガイド',
			'Other Useful Info' => 'その他のお役立ち情報',
		),
		'ru' => array(
			'Homepage' => 'Главная', 'Houses' => 'Дома', 'Apartments' => 'Квартиры',
			'Villas' => 'Виллы', 'FAQs' => 'Вопросы и ответы', 'About Us' => 'О нас',
			'Contact' => 'Контакты', 'Danang Guide' => 'Гид по Данангу',
			'Other Useful Info' => 'Другая полезная информация',
		),
		'zh' => array(
			'Homepage' => '首页', 'Houses' => '独栋住宅', 'Apartments' => '公寓',
			'Villas' => '别墅', 'FAQs' => '常见问题', 'About Us' => '关于我们',
			'Contact' => '联系我们', 'Danang Guide' => '岘港指南',
			'Other Useful Info' => '其他实用信息',
		),
	);

	return $labels[ $language ][ $title ] ?? $title;
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
