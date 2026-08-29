<?php
/** Languages whose property content intentionally mirrors the English listing. */
function hrd_get_shared_property_languages() {
	return array( 'vi', 'ko', 'ja', 'ru', 'zh' );
}

/** Return the active Polylang language, with English as the frontend fallback. */
function hrd_get_current_language() {
	return function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
}

/** Check whether a menu filter is running for either public navigation menu. */
function hrd_is_navigation_menu( $args ) {
	return isset( $args->theme_location ) && in_array( $args->theme_location, array( 'main-menu', 'responsive-menu' ), true );
}

function hrd_get_freshness_notice( $context ) {
	$language = hrd_get_current_language();
	static $notices = array(
		'property' => array(
			'en' => 'Availability and pricing may have changed since publication. Please contact <a href="mailto:hello@houserentaldanang.com">hello@houserentaldanang.com</a> to confirm current details.',
			'vi' => 'Tình trạng còn trống và giá thuê có thể đã thay đổi kể từ khi bài được đăng. Vui lòng liên hệ <a href="mailto:hello@houserentaldanang.com">hello@houserentaldanang.com</a> để xác nhận thông tin hiện tại.',
			'ko' => '게시 이후 예약 가능 여부와 가격이 변경되었을 수 있습니다. 현재 정보는 <a href="mailto:hello@houserentaldanang.com">hello@houserentaldanang.com</a>으로 문의해 주세요.',
			'ja' => '掲載後に空室状況や料金が変更されている場合があります。最新情報は <a href="mailto:hello@houserentaldanang.com">hello@houserentaldanang.com</a> までお問い合わせください。',
			'ru' => 'Доступность и стоимость могли измениться после публикации. Уточните актуальную информацию по адресу <a href="mailto:hello@houserentaldanang.com">hello@houserentaldanang.com</a>.',
			'zh' => '房源状态和价格可能在发布后发生变化。请联系 <a href="mailto:hello@houserentaldanang.com">hello@houserentaldanang.com</a> 确认最新信息。',
		),
		'global'   => array(
			'en' => 'Property availability, pricing, addresses, and local information may change over time. Please confirm current details before making decisions.',
			'vi' => 'Tình trạng còn trống, giá, địa chỉ và thông tin địa phương có thể thay đổi theo thời gian. Vui lòng xác nhận thông tin hiện tại trước khi quyết định.',
			'ko' => '매물 예약 가능 여부, 가격, 주소 및 지역 정보는 시간에 따라 변경될 수 있습니다. 결정하기 전에 현재 정보를 확인해 주세요.',
			'ja' => '物件の空室状況、料金、住所、地域情報は変更される場合があります。決定前に最新情報をご確認ください。',
			'ru' => 'Доступность объектов, цены, адреса и местная информация могут меняться. Проверяйте актуальные данные перед принятием решения.',
			'zh' => '房源状态、价格、地址和当地信息可能会发生变化。做出决定前请确认最新信息。',
		),
	);

	return $notices[ $context ][ $language ] ?? $notices[ $context ]['en'];
}

function hrd_prepend_property_freshness_notice( $content ) {
	if (
		! is_singular( 'property' ) ||
		! in_the_loop() ||
		! is_main_query() ||
		get_the_ID() !== get_queried_object_id()
	) {
		return $content;
	}

	$language = hrd_get_current_language();
	$labels   = array(
		'en' => 'Information may have changed',
		'vi' => 'Thông tin có thể đã thay đổi',
		'ko' => '정보가 변경되었을 수 있습니다',
		'ja' => '情報が変更されている場合があります',
		'ru' => 'Информация могла измениться',
		'zh' => '信息可能已发生变化',
	);
	$notice   = sprintf(
		'<aside class="hrd-freshness-notice hrd-freshness-notice--property" aria-label="%s"><strong class="hrd-freshness-notice__title">%s</strong><div class="hrd-freshness-notice__text">%s</div></aside>',
		esc_attr( $labels[ $language ] ?? $labels['en'] ),
		esc_html( $labels[ $language ] ?? $labels['en'] ),
		wp_kses_post( hrd_get_freshness_notice( 'property' ) )
	);

	return $content . $notice;
}
add_filter( 'the_content', 'hrd_prepend_property_freshness_notice', 8 );

function hrd_add_global_freshness_notice( $copyright ) {
	$copyright = str_replace(
		array( '© 2022', '&copy; 2022' ),
		array( '© ' . gmdate( 'Y' ), '&copy; ' . gmdate( 'Y' ) ),
		$copyright
	);
	$notice = sprintf(
		'<span class="hrd-freshness-notice hrd-freshness-notice--global">%s</span>',
		esc_html( wp_strip_all_tags( hrd_get_freshness_notice( 'global' ) ) )
	);

	return $notice . $copyright;
}
add_filter( 'inspiry_copyright_text', 'hrd_add_global_freshness_notice', 20 );

function hrd_allow_notice_span( $allowed_html ) {
	$allowed_html['span']['class'] = array();

	return $allowed_html;
}
add_filter( 'inspiry_allowed_html', 'hrd_allow_notice_span' );

/**
 * Add visible, factual editorial context to guides using only WordPress dates,
 * the assigned author, and existing About/Contact pages.
 */
function hrd_append_editorial_trust_note( $content ) {
	if (
		! is_singular( 'post' ) ||
		! in_the_loop() ||
		! is_main_query() ||
		get_the_ID() !== get_queried_object_id()
	) {
		return $content;
	}

	$language = hrd_get_current_language();
	static $copy = array(
		'en' => array(
			'eyebrow'  => 'Editorial information',
			'title'    => 'How this guide is maintained',
			'date_format' => 'F j, Y',
			'published' => 'Published',
			'updated'  => 'Updated',
			'by'       => 'By',
			'body'     => 'House Rental Danang publishes practical local information for renters and visitors. Prices, opening hours, routes and availability can change, so confirm time-sensitive details with the relevant provider before relying on them.',
			'about'    => 'About our local approach',
			'contact'  => 'Contact House Rental Danang',
		),
		'vi' => array(
			'eyebrow'  => 'Thông tin biên tập',
			'title'    => 'Cách chúng tôi duy trì bài hướng dẫn',
			'date_format' => 'd/m/Y',
			'published' => 'Đăng ngày',
			'updated'  => 'Cập nhật',
			'by'       => 'Tác giả',
			'body'     => 'House Rental Danang chia sẻ thông tin địa phương thực tế cho người thuê nhà và du khách. Giá, giờ mở cửa, lộ trình và tình trạng còn chỗ có thể thay đổi; hãy xác nhận thông tin nhạy cảm về thời gian với đơn vị liên quan trước khi sử dụng.',
			'about'    => 'Về cách hỗ trợ tại địa phương',
			'contact'  => 'Liên hệ House Rental Danang',
		),
		'ko' => array(
			'eyebrow'  => '편집 정보',
			'title'    => '이 가이드를 관리하는 방법',
			'date_format' => 'Y년 n월 j일',
			'published' => '게시',
			'updated'  => '업데이트',
			'by'       => '작성자',
			'body'     => 'House Rental Danang은 임차인과 방문객을 위한 실용적인 현지 정보를 제공합니다. 가격, 영업시간, 이동 경로와 이용 가능 여부는 바뀔 수 있으므로 시점이 중요한 정보는 관련 제공처에 다시 확인하세요.',
			'about'    => '현지 지원 방식 알아보기',
			'contact'  => 'House Rental Danang 문의',
		),
		'ja' => array(
			'eyebrow'  => '編集情報',
			'title'    => 'このガイドの更新方針',
			'date_format' => 'Y年n月j日',
			'published' => '公開日',
			'updated'  => '更新日',
			'by'       => '執筆者',
			'body'     => 'House Rental Danang は、賃貸を探す方や旅行者に役立つ現地情報を掲載しています。料金、営業時間、経路、空き状況は変わるため、時期に左右される情報は利用前に該当事業者へご確認ください。',
			'about'    => '現地サポートについて',
			'contact'  => 'House Rental Danang に問い合わせる',
		),
		'ru' => array(
			'eyebrow'  => 'Редакционная информация',
			'title'    => 'Как мы поддерживаем актуальность гида',
			'date_format' => 'd.m.Y',
			'published' => 'Опубликовано',
			'updated'  => 'Обновлено',
			'by'       => 'Автор',
			'body'     => 'House Rental Danang публикует практическую местную информацию для арендаторов и путешественников. Цены, часы работы, маршруты и доступность могут меняться, поэтому проверяйте сведения, зависящие от времени, у соответствующего поставщика.',
			'about'    => 'О нашем местном подходе',
			'contact'  => 'Связаться с House Rental Danang',
		),
		'zh' => array(
			'eyebrow'  => '编辑信息',
			'title'    => '本指南的维护方式',
			'date_format' => 'Y年n月j日',
			'published' => '发布',
			'updated'  => '更新',
			'by'       => '作者',
			'body'     => 'House Rental Danang 为租客和访客提供实用的本地信息。价格、营业时间、路线和可用情况可能变化；使用时效性信息前，请向相关服务方再次确认。',
			'about'    => '了解我们的本地服务方式',
			'contact'  => '联系 House Rental Danang',
		),
	);
	$labels = $copy[ $language ] ?? $copy['en'];
	$post   = get_post( get_the_ID() );
	if ( ! $post instanceof WP_Post ) {
		return $content;
	}

	$meta = array(
		sprintf(
			'%s <time datetime="%s">%s</time>',
			esc_html( $labels['published'] ),
			esc_attr( get_the_date( DATE_W3C, $post ) ),
			esc_html( get_the_date( $labels['date_format'], $post ) )
		),
	);
	if ( get_post_modified_time( 'U', true, $post ) > get_post_time( 'U', true, $post ) + MINUTE_IN_SECONDS ) {
		$meta[] = sprintf(
			'%s <time datetime="%s">%s</time>',
			esc_html( $labels['updated'] ),
			esc_attr( get_the_modified_date( DATE_W3C, $post ) ),
			esc_html( get_the_modified_date( $labels['date_format'], $post ) )
		);
	}
	$author = get_the_author_meta( 'display_name', (int) $post->post_author );
	if ( $author ) {
		$meta[] = esc_html( $labels['by'] . ' ' . $author );
	}

	$about_id   = function_exists( 'pll_get_post' ) ? pll_get_post( 8953, $language ) : 8953;
	$contact    = get_page_by_path( 'contact-us' );
	$contact_id = $contact instanceof WP_Post ? $contact->ID : 0;
	if ( $contact_id && function_exists( 'pll_get_post' ) ) {
		$contact_id = pll_get_post( $contact_id, $language ) ?: $contact_id;
	}
	$about_url   = $about_id ? get_permalink( $about_id ) : home_url( '/why-us/' );
	$contact_url = $contact_id ? get_permalink( $contact_id ) : home_url( '/contact-us/' );
	$note_id     = 'hrd-editorial-note-' . $post->ID;
	$note        = sprintf(
		'<aside class="hrd-editorial-note" aria-labelledby="%1$s"><span class="hrd-editorial-note__eyebrow">%2$s</span><h2 id="%1$s" class="hrd-editorial-note__title">%3$s</h2><p class="hrd-editorial-note__meta">%4$s</p><p class="hrd-editorial-note__body">%5$s</p><p class="hrd-editorial-note__links"><a href="%6$s">%7$s</a><a href="%8$s">%9$s</a></p></aside>',
		esc_attr( $note_id ),
		esc_html( $labels['eyebrow'] ),
		esc_html( $labels['title'] ),
		implode( '<span aria-hidden="true"> &middot; </span>', $meta ),
		esc_html( $labels['body'] ),
		esc_url( $about_url ),
		esc_html( $labels['about'] ),
		esc_url( $contact_url ),
		esc_html( $labels['contact'] )
	);

	return $content . $note;
}
add_filter( 'the_content', 'hrd_append_editorial_trust_note', 20 );

/**
 * Keep the footer's core listing links aligned with the active language.
 */
function hrd_footer_listing_links_shortcode() {
	$language = hrd_get_current_language();
	$content  = array(
		'en' => array(
			'title' => 'Danang Rentals',
			'links' => array(
				'Houses for Rent'     => home_url( '/houses/' ),
				'Apartments for Rent' => home_url( '/apartments/' ),
				'Villas for Rent'     => home_url( '/villas/' ),
			),
		),
		'vi' => array(
			'title' => 'Bất động sản cho thuê',
			'links' => array(
				'Nhà cho thuê'      => home_url( '/vi/nha-cho-thue/' ),
				'Căn hộ cho thuê'  => home_url( '/vi/can-ho-cho-thue/' ),
				'Biệt thự cho thuê' => home_url( '/vi/biet-thu-cho-thue/' ),
			),
		),
		'ko' => array(
			'title' => '다낭 임대 매물',
			'links' => array(
				'임대 주택'   => home_url( '/ko/rental-houses/' ),
				'임대 아파트' => home_url( '/ko/rental-apartments/' ),
				'임대 빌라'   => home_url( '/ko/rental-villas/' ),
			),
		),
		'ja' => array(
			'Homepage' => 'ホーム', 'Houses' => '一戸建て', 'Apartments' => 'アパート', 'Villas' => 'ヴィラ',
			'FAQs' => 'よくある質問', 'About Us' => '私たちについて', 'Contact' => 'お問い合わせ',
			'Danang Guide' => 'ダナンガイド', 'Other Useful Info' => 'その他のお役立ち情報',
		),
		'ru' => array(
			'Homepage' => 'Главная', 'Houses' => 'Дома', 'Apartments' => 'Квартиры', 'Villas' => 'Виллы',
			'FAQs' => 'Вопросы', 'About Us' => 'О нас', 'Contact' => 'Контакты',
			'Danang Guide' => 'Гид по Данангу', 'Other Useful Info' => 'Полезная информация',
		),
		'zh' => array(
			'Homepage' => '首页', 'Houses' => '房屋', 'Apartments' => '公寓', 'Villas' => '别墅',
			'FAQs' => '常见问题', 'About Us' => '关于我们', 'Contact' => '联系我们',
			'Danang Guide' => '岘港指南', 'Other Useful Info' => '其他实用信息',
		),
	);

	$section = $content[ $language ] ?? $content['en'];
	$output  = '<div class="hrd-footer-listings"><h3 class="title">' . esc_html( $section['title'] ) . '</h3><ul>';

	foreach ( $section['links'] as $label => $url ) {
		$output .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}

	return $output . '</ul></div>';
}
add_shortcode( 'hrd_footer_listing_links', 'hrd_footer_listing_links_shortcode' );

function hrd_footer_intro_shortcode() {
	$language = hrd_get_current_language();
	$content  = array(
		'en' => array(
			'lead' => 'Find your place in Da Nang.',
			'body' => 'Browse houses, apartments, and villas, then ask our local team to confirm the latest availability and rental details.',
			'cta'  => 'Email our local team',
		),
		'vi' => array(
			'lead' => 'Tìm nơi phù hợp với bạn tại Đà Nẵng.',
			'body' => 'Tham khảo nhà, căn hộ và biệt thự, sau đó liên hệ đội ngũ địa phương để xác nhận tình trạng cùng điều khoản thuê hiện tại.',
			'cta'  => 'Email đội ngũ địa phương',
		),
		'ko' => array(
			'lead' => '다낭에서 나에게 맞는 공간을 찾아보세요.',
			'body' => '주택, 아파트와 빌라를 비교한 뒤 현지 팀에 현재 공실 여부와 임대 조건을 확인하세요.',
			'cta'  => '현지 팀에 이메일 보내기',
		),
		'ja' => array(
			'lead' => 'ダナンで理想の住まいを見つけましょう。',
			'body' => '一戸建て、アパート、ヴィラを比較し、空室状況や最新の賃貸条件を現地チームにご確認ください。',
			'cta'  => '現地チームにメールする',
		),
		'ru' => array(
			'lead' => 'Найдите свой дом в Дананге.',
			'body' => 'Сравните дома, квартиры и виллы, а затем уточните наличие и актуальные условия аренды у нашей местной команды.',
			'cta'  => 'Написать местной команде',
		),
		'zh' => array(
			'lead' => '在岘港找到适合您的住所。',
			'body' => '浏览房屋、公寓和别墅，然后联系本地团队确认最新房源状态和租赁条件。',
			'cta'  => '发送邮件给本地团队',
		),
	);
	$section = $content[ $language ] ?? $content['en'];

	return sprintf(
		'<div class="hrd-footer-intro"><p class="hrd-footer-intro__lead">%s</p><p>%s</p><a class="hrd-footer-email" href="mailto:hello@houserentaldanang.com"><span>%s</span><strong>hello@houserentaldanang.com</strong></a></div>',
		esc_html( $section['lead'] ),
		esc_html( $section['body'] ),
		esc_html( $section['cta'] )
	);
}
add_shortcode( 'hrd_footer_intro', 'hrd_footer_intro_shortcode' );

function hrd_footer_guide_links_shortcode() {
	$language = hrd_get_current_language();
	$content  = array(
		'en' => array(
			'title' => 'Explore Da Nang',
			'links' => array(
				'Da Nang Guide'       => home_url( '/category/danang-guide/' ),
				'Best Places to Visit' => home_url( '/best-places-to-visit-in-da-nang/' ),
				'Food and Restaurants' => home_url( '/best-places-to-eat-in-danang/' ),
				'Da Nang to Hoi An'    => home_url( '/travel-from-da-nang-to-hoi-an/' ),
				'Coworking Spaces'      => home_url( '/coworking-spaces-in-da-nang/' ),
			),
		),
		'vi' => array(
			'title' => 'Khám phá Đà Nẵng',
			'links' => array(
				'Cẩm nang Đà Nẵng'      => home_url( '/vi/' ),
				'Địa điểm nên ghé thăm' => home_url( '/best-places-to-visit-in-da-nang/' ),
				'Ẩm thực và nhà hàng'   => home_url( '/best-places-to-eat-in-danang/' ),
				'Từ Đà Nẵng đến Hội An' => home_url( '/travel-from-da-nang-to-hoi-an/' ),
				'Không gian làm việc'   => home_url( '/coworking-spaces-in-da-nang/' ),
			),
		),
		'ko' => array(
			'title' => '다낭 둘러보기',
			'links' => array(
				'다낭 가이드'       => home_url( '/ko/' ),
				'다낭 추천 명소'    => home_url( '/best-places-to-visit-in-da-nang/' ),
				'음식과 레스토랑'   => home_url( '/best-places-to-eat-in-danang/' ),
				'다낭에서 호이안'   => home_url( '/travel-from-da-nang-to-hoi-an/' ),
				'코워킹 스페이스'   => home_url( '/coworking-spaces-in-da-nang/' ),
			),
		),
	);
	$section = $content[ $language ] ?? $content['en'];
	$output  = '<div class="hrd-footer-guides"><h3 class="title">' . esc_html( $section['title'] ) . '</h3><ul>';

	foreach ( $section['links'] as $label => $url ) {
		$output .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}

	return $output . '</ul></div>';
}
add_shortcode( 'hrd_footer_guide_links', 'hrd_footer_guide_links_shortcode' );
