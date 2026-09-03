<?php
/** Shared building registry, labels and listing query for building pages. */

function hrd_building_registry() {
	return array(
		'monarchy' => array(
			'name'            => 'The Monarchy',
			'area'            => 'An Hai',
			'historical_area' => 'Son Tra',
			'nearby'          => 'Han River · Dragon Bridge',
			'layouts'         => 'Studios · 1–3 bedrooms',
			'building_type'   => 'Apartment complex',
			'street_address'  => '535 Tran Hung Dao',
			'developer'       => 'Danang Housing Investment Development JSC. (NDN)',
			'blocks'          => 'Block A and Block B; Block B includes B1, B2 and B3',
			'amenities'       => array( 'Swimming pool', 'Gym', 'Parking', 'Mini mart', 'Restaurants and cafes', 'Kindergarten', 'Park and stroll garden' ),
			'map_embed_url'   => 'https://www.google.com/maps?q=The%20Monarchy%2C%20535%20Tran%20Hung%20Dao%2C%20An%20Hai%2C%20Da%20Nang&output=embed',
			'map_link_url'    => 'https://www.google.com/maps/search/?api=1&query=The%20Monarchy%2C%20535%20Tran%20Hung%20Dao%2C%20An%20Hai%2C%20Da%20Nang',
		),
		'azura' => array(
			'name'           => 'Azura',
			'area'           => 'Son Tra',
			'nearby'         => 'Han River · Tran Hung Dao Street',
			'layouts'        => '1–3 bedrooms · penthouses',
			'building_type'  => 'Apartment building',
			'street_address' => '339 Tran Hung Dao Street',
			'developer'      => 'VinaCapital',
			'amenities'      => array( 'Swimming pool', 'Fitness centre', 'Kids’ club', 'Reception', '24-hour security' ),
			'map_embed_url'  => 'https://www.google.com/maps?q=Azura%2C%20339%20Tran%20Hung%20Dao%20Street%2C%20Son%20Tra%2C%20Da%20Nang&output=embed',
			'map_link_url'   => 'https://www.google.com/maps/search/?api=1&query=Azura%2C%20339%20Tran%20Hung%20Dao%20Street%2C%20Son%20Tra%2C%20Da%20Nang',
		),
		'hiyori-garden-tower' => array(
			'name'           => 'Hiyori Garden Tower',
			'area'           => 'An Hai Ward',
			'nearby'         => 'Vo Van Kiet Street',
			'layouts'        => '2–3 bedrooms',
			'building_type'  => 'Mixed-use apartment building',
			'street_address' => 'Lot 2-A2 Vo Van Kiet Street',
			'developer'      => 'Sun Frontier Da Nang',
			'amenities'      => array( 'Adult swimming pool', 'Children’s swimming pool', 'Fitness room', 'Play area', 'Kindergarten space', 'Green park', 'Community room', 'Basement parking' ),
			'map_embed_url'  => 'https://www.google.com/maps?q=Hiyori%20Garden%20Tower%2C%20Lot%202-A2%20Vo%20Van%20Kiet%20Street%2C%20An%20Hai%20Ward%2C%20Da%20Nang&output=embed',
			'map_link_url'   => 'https://www.google.com/maps/search/?api=1&query=Hiyori%20Garden%20Tower%2C%20Lot%202-A2%20Vo%20Van%20Kiet%20Street%2C%20An%20Hai%20Ward%2C%20Da%20Nang',
		),
		'indochina-riverside-towers' => array( 'name' => 'Indochina Riverside Towers', 'area' => 'Hai Chau', 'nearby' => 'Han River', 'layouts' => '2 bedrooms' ),
		'the-filmore' => array( 'name' => 'The Filmore Da Nang', 'heading_name' => 'The Filmore', 'area' => 'Hai Chau', 'nearby' => 'Han River · Bach Dang Street', 'layouts' => '1–3 bedrooms' ),
		'fpt-plaza' => array( 'name' => 'FPT Plaza', 'area' => 'Ngu Hanh Son', 'nearby' => 'FPT City', 'layouts' => 'Apartment layouts vary' ),
		'ocean-suites' => array( 'name' => 'The Ocean Suites', 'area' => 'Ngu Hanh Son', 'nearby' => 'The Ocean Resort', 'layouts' => '2 bedrooms' ),
		'ocean-villas' => array( 'name' => 'The Ocean Villas', 'area' => 'Ngu Hanh Son', 'nearby' => 'The Ocean Resort', 'layouts' => '4–6 bedrooms' ),
		'one-river-villas' => array( 'name' => 'One River Villas', 'area' => 'Ngu Hanh Son', 'nearby' => 'Co Co River', 'layouts' => '5 bedrooms' ),
		'fusion-resort-villas' => array( 'name' => 'Fusion Resort & Villas', 'area' => 'Ngu Hanh Son', 'nearby' => 'Beachfront resort area', 'layouts' => '2–5 bedrooms' ),
		'koi-resort' => array( 'name' => 'KOI Resort', 'area' => 'Ngu Hanh Son', 'nearby' => 'Coastal resort area', 'layouts' => '4 bedrooms' ),
		'premier-village' => array( 'name' => 'Premier Village', 'area' => 'Ngu Hanh Son', 'nearby' => 'My Khe Beach', 'layouts' => '4 bedrooms' ),
		'sam-towers' => array( 'name' => 'SAM Towers', 'area' => 'Hai Chau', 'nearby' => 'Han River', 'layouts' => '1–3 bedrooms' ),
		'blooming-tower' => array( 'name' => 'Blooming Tower', 'area' => 'Da Nang', 'nearby' => 'Bay and city access', 'layouts' => '2–3 bedrooms' ),
		'wyndham-danang-golden-bay' => array( 'name' => 'Wyndham Danang Golden Bay', 'area' => 'Son Tra', 'nearby' => 'Han River · Bay', 'layouts' => '1–3 bedrooms' ),
		'altara-suites' => array( 'name' => 'Altara Suites', 'area' => 'Son Tra', 'nearby' => 'My Khe Beach', 'layouts' => '1–2 bedrooms' ),
	);
}

function hrd_get_building( $key ) {
	$registry = hrd_building_registry();
	return $registry[ sanitize_key( $key ) ] ?? null;
}

function hrd_building_text( $key ) {
	$labels = array(
		'en' => array( 'h1' => '%s Apartments for Rent in Da Nang', 'home' => 'Home', 'guide' => 'Building guide', 'apartments' => 'Apartments', 'area' => 'Area', 'layouts' => 'Typical layouts', 'nearby' => 'Nearby', 'availability' => 'Rental status', 'check' => 'Confirm current options', 'overview' => 'About this building', 'guide_title' => '%s rental guide', 'guide_body' => 'Browse homes matched to this building. Exact availability, rent, utilities, deposit and lease terms must be confirmed before viewing or payment.', 'help' => 'Local rental help', 'help_body' => 'Looking for a particular layout or move-in date around %s? Ask the local team to check suitable options.', 'help_cta' => 'Ask about this building', 'gallery' => 'Gallery', 'photos' => 'Photos of %s', 'details' => 'Amenities and practical details', 'location' => 'Location and nearby places', 'map_open' => 'Open in Google Maps', 'inventory' => 'Apartment listings', 'available' => '%s apartment listings', 'inventory_note' => 'Listings associated with this building are shown below. Availability and final rental terms should be confirmed before arranging a viewing.', 'listing' => 'matching listing', 'listings' => 'matching listings', 'renting' => 'Renting notes', 'faq' => 'Frequently asked questions', 'next_step' => 'Next step', 'next' => 'Want more details about this building?', 'next_body' => 'Tell us your preferred layout, budget and move-in date. We will help confirm which options fit your request.', 'contact' => 'Contact the local team', 'related' => 'Similar buildings and area guides', 'empty' => 'More details about this building will be added soon.' ),
		'vi' => array( 'h1' => 'Căn hộ %s cho thuê tại Đà Nẵng', 'home' => 'Trang chủ', 'guide' => 'Thông tin tòa nhà', 'apartments' => 'Căn hộ', 'area' => 'Khu vực', 'layouts' => 'Loại căn phổ biến', 'nearby' => 'Lân cận', 'availability' => 'Tình trạng thuê', 'check' => 'Xác nhận lựa chọn hiện tại', 'overview' => 'Về tòa nhà', 'guide_title' => 'Hướng dẫn thuê tại %s', 'guide_body' => 'Xem các căn được ghép với tòa nhà này. Hãy xác nhận tình trạng, giá thuê, phí, tiền cọc và thời hạn trước khi xem hoặc thanh toán.', 'help' => 'Hỗ trợ thuê nhà địa phương', 'help_body' => 'Đang tìm loại căn hoặc ngày chuyển vào cụ thể quanh %s? Hãy nhờ đội ngũ địa phương kiểm tra lựa chọn phù hợp.', 'help_cta' => 'Hỏi về tòa nhà này', 'gallery' => 'Thư viện ảnh', 'photos' => 'Ảnh %s', 'details' => 'Tiện ích và thông tin thực tế', 'location' => 'Vị trí và địa điểm lân cận', 'map_open' => 'Mở trong Google Maps', 'inventory' => 'Tin căn hộ', 'available' => 'Tin căn hộ tại %s', 'inventory_note' => 'Các tin được liên kết với tòa nhà hiển thị bên dưới. Hãy xác nhận tình trạng và điều khoản thuê trước khi đặt lịch xem.', 'listing' => 'tin phù hợp', 'listings' => 'tin phù hợp', 'renting' => 'Lưu ý khi thuê', 'faq' => 'Câu hỏi thường gặp', 'next_step' => 'Bước tiếp theo', 'next' => 'Muốn biết thêm về tòa nhà này?', 'next_body' => 'Cho chúng tôi biết loại căn, ngân sách và ngày chuyển vào. Chúng tôi sẽ giúp xác nhận lựa chọn phù hợp với yêu cầu.', 'contact' => 'Liên hệ đội ngũ địa phương', 'related' => 'Tòa nhà và khu vực tương tự', 'empty' => 'Thông tin chi tiết về tòa nhà sẽ được bổ sung.' ),
		'ko' => array( 'h1' => '다낭 %s 임대 아파트', 'home' => '홈', 'guide' => '건물 안내', 'apartments' => '아파트', 'area' => '지역', 'layouts' => '일반적인 구조', 'nearby' => '주변', 'availability' => '임대 현황', 'check' => '현재 매물 확인', 'overview' => '건물 소개', 'guide_title' => '%s 임대 안내', 'guide_body' => '이 건물과 연결된 매물을 확인하세요. 방문하거나 결제하기 전에 실제 공실, 임대료, 공과금, 보증금 및 계약 조건을 확인해야 합니다.', 'help' => '현지 임대 상담', 'help_body' => '%s 인근에서 원하는 구조나 입주일을 찾고 계신가요? 현지 팀에 적합한 매물 확인을 요청하세요.', 'help_cta' => '이 건물 문의하기', 'gallery' => '갤러리', 'photos' => '%s 사진', 'details' => '편의시설 및 실용 정보', 'location' => '위치 및 주변 장소', 'map_open' => 'Google 지도에서 열기', 'inventory' => '아파트 매물', 'available' => '%s 아파트 매물', 'inventory_note' => '이 건물과 연결된 매물이 아래에 표시됩니다. 방문 일정을 잡기 전에 공실 여부와 최종 임대 조건을 확인하세요.', 'listing' => '일치하는 매물', 'listings' => '일치하는 매물', 'renting' => '임대 참고사항', 'faq' => '자주 묻는 질문', 'next_step' => '다음 단계', 'next' => '이 건물에 대해 더 알고 싶으신가요?', 'next_body' => '원하는 구조, 예산 및 입주일을 알려주시면 조건에 맞는 매물을 확인해 드립니다.', 'contact' => '현지 팀에 문의', 'related' => '비슷한 건물 및 지역 안내', 'empty' => '이 건물에 대한 자세한 정보는 곧 추가될 예정입니다.' ),
		'ja' => array( 'h1' => 'ダナンの%s賃貸アパート', 'home' => 'ホーム', 'guide' => '建物ガイド', 'apartments' => 'アパート', 'area' => 'エリア', 'layouts' => '主な間取り', 'nearby' => '周辺', 'availability' => '賃貸状況', 'check' => '現在の物件を確認', 'overview' => '建物について', 'guide_title' => '%s賃貸ガイド', 'guide_body' => 'この建物に紐づく住戸をご覧ください。内見や支払いの前に、空室状況、賃料、光熱費、保証金、契約条件をご確認ください。', 'help' => '現地の賃貸サポート', 'help_body' => '%s周辺で特定の間取りや入居日をお探しですか？現地チームが条件に合う選択肢を確認します。', 'help_cta' => 'この建物について問い合わせる', 'gallery' => 'ギャラリー', 'photos' => '%sの写真', 'details' => '設備と実用情報', 'location' => '立地と周辺スポット', 'map_open' => 'Google マップで開く', 'inventory' => 'アパート物件', 'available' => '%sのアパート物件', 'inventory_note' => 'この建物に関連する物件を以下に掲載しています。内見を予約する前に、空室状況と最終的な賃貸条件をご確認ください。', 'listing' => '該当物件', 'listings' => '該当物件', 'renting' => '賃貸時の注意点', 'faq' => 'よくある質問', 'next_step' => '次のステップ', 'next' => 'この建物について詳しく知りたいですか？', 'next_body' => '希望の間取り、予算、入居日をお知らせください。条件に合う選択肢を確認します。', 'contact' => '現地チームに問い合わせる', 'related' => '似た建物とエリアガイド', 'empty' => 'この建物の詳しい情報は近日追加予定です。' ),
		'ru' => array( 'h1' => 'Аренда квартир в %s, Дананг', 'home' => 'Главная', 'guide' => 'Гид по зданию', 'apartments' => 'Квартиры', 'area' => 'Район', 'layouts' => 'Типовые планировки', 'nearby' => 'Рядом', 'availability' => 'Статус аренды', 'check' => 'Уточнить актуальные варианты', 'overview' => 'О здании', 'guide_title' => 'Гид по аренде в %s', 'guide_body' => 'Посмотрите объявления, связанные с этим зданием. Перед просмотром или оплатой уточните наличие, арендную плату, коммунальные расходы, депозит и условия договора.', 'help' => 'Помощь с арендой на месте', 'help_body' => 'Ищете определённую планировку или дату заезда рядом с %s? Попросите местную команду проверить подходящие варианты.', 'help_cta' => 'Спросить об этом здании', 'gallery' => 'Галерея', 'photos' => 'Фотографии %s', 'details' => 'Удобства и практическая информация', 'location' => 'Расположение и места рядом', 'map_open' => 'Открыть в Google Картах', 'inventory' => 'Объявления о квартирах', 'available' => 'Квартиры в аренду в %s', 'inventory_note' => 'Ниже показаны объявления, связанные с этим зданием. Перед назначением просмотра уточните наличие и окончательные условия аренды.', 'listing' => 'подходящее объявление', 'listings' => 'подходящих объявлений', 'renting' => 'Условия аренды', 'faq' => 'Частые вопросы', 'next_step' => 'Следующий шаг', 'next' => 'Хотите узнать больше об этом здании?', 'next_body' => 'Сообщите желаемую планировку, бюджет и дату заезда. Мы поможем уточнить подходящие варианты.', 'contact' => 'Связаться с местной командой', 'related' => 'Похожие здания и гиды по районам', 'empty' => 'Подробная информация об этом здании будет добавлена позже.' ),
		'zh' => array( 'h1' => '岘港 %s 公寓出租', 'home' => '首页', 'guide' => '楼盘指南', 'apartments' => '公寓', 'area' => '区域', 'layouts' => '常见户型', 'nearby' => '周边', 'availability' => '出租状态', 'check' => '确认当前房源', 'overview' => '楼盘介绍', 'guide_title' => '%s 租房指南', 'guide_body' => '浏览与该楼盘关联的房源。看房或付款前，请确认实际空置情况、租金、水电费、押金和租约条款。', 'help' => '本地租房协助', 'help_body' => '正在寻找 %s 附近的特定户型或入住日期？可请本地团队核实合适的选择。', 'help_cta' => '咨询该楼盘', 'gallery' => '图片', 'photos' => '%s 图片', 'details' => '设施与实用信息', 'location' => '位置与周边地点', 'map_open' => '在 Google 地图中打开', 'inventory' => '公寓房源', 'available' => '%s 公寓房源', 'inventory_note' => '下方显示与该楼盘关联的房源。预约看房前，请确认空置情况和最终租赁条款。', 'listing' => '条匹配房源', 'listings' => '条匹配房源', 'renting' => '租房须知', 'faq' => '常见问题', 'next_step' => '下一步', 'next' => '想进一步了解该楼盘？', 'next_body' => '请告诉我们期望户型、预算和入住日期，我们会协助确认符合条件的选择。', 'contact' => '联系本地团队', 'related' => '类似楼盘与区域指南', 'empty' => '该楼盘的更多信息将稍后补充。' ),
	);
	$language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';
	return $labels[ $language ][ $key ] ?? $labels['en'][ $key ] ?? $key;
}

function hrd_get_building_properties( $building_key ) {
	return new WP_Query( array( 'post_type' => 'property', 'post_status' => 'publish', 'posts_per_page' => 6, 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'DESC' ), 'meta_query' => array( array( 'key' => 'hrd_building_key', 'value' => sanitize_key( $building_key ), 'compare' => '=' ) ) ) );
}

function hrd_get_building_content( $post_id, $locale = null ) {
	$locale = $locale ?: ( function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en' );
	$content = array();
	foreach ( array( 'en', $locale ) as $current_locale ) {
		$raw = get_post_meta( $post_id, 'hrd_building_content_' . sanitize_key( $current_locale ), true );
		$decoded = is_string( $raw ) ? json_decode( $raw, true ) : array();
		if ( is_array( $decoded ) ) {
			$content = array_merge( $content, $decoded );
		}
	}
	return $content;
}

function hrd_building_content_fields() {
	return array( 'hero_summary', 'overview', 'gallery', 'amenities', 'location', 'renting_notes', 'faq', 'related' );
}

/** Render the stored Q:/A: string as semantic, scannable FAQ items. */
function hrd_render_building_faq( $raw ) {
	$raw = str_replace( array( '\\r\\n', '\\n', '\\r' ), "\n", (string) $raw );
	if ( ! preg_match_all( '/Q:\s*(.*?)\s+A:\s*(.*?)(?=\n\s*\n\s*Q:|$)/s', $raw, $matches, PREG_SET_ORDER ) ) {
		return wpautop( wp_kses_post( $raw ) );
	}

	$html = '<div class="hrd-building__faq-list">';
	foreach ( $matches as $match ) {
		$question = trim( wp_strip_all_tags( $match[1] ) );
		$answer   = trim( wp_kses_post( $match[2] ) );
		if ( '' === $question || '' === $answer ) {
			continue;
		}
		$html .= '<article class="hrd-building__faq-item"><h3>' . esc_html( $question ) . '</h3><p>' . $answer . '</p></article>';
	}
	$html .= '</div>';

	return $html;
}

/** Present stored related links as separate destinations when possible. */
function hrd_render_building_related( $raw ) {
	$raw = wp_kses_post( (string) $raw );
	if ( ! preg_match_all( '/<a\b[^>]*>.*?<\/a>/is', $raw, $matches ) || count( $matches[0] ) < 2 ) {
		return wpautop( $raw );
	}

	$html = '<ul class="hrd-building__related-list">';
	foreach ( $matches[0] as $anchor ) {
		$html .= '<li>' . wp_kses( $anchor, array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) ) . '</li>';
	}
	$html .= '</ul>';

	return $html;
}

/** Replace Zynith's generic, empty-author Page schema on building pages. */
function hrd_disable_generic_building_page_schema() {
	if ( is_page_template( 'page-apartment-building.php' ) && function_exists( 'zynith_seo_output_schema' ) ) {
		remove_action( 'wp_head', 'zynith_seo_output_schema' );
	}
}
add_action( 'wp', 'hrd_disable_generic_building_page_schema', 20 );

/** Output self/x-default hreflang until actual Polylang translations exist. */
function hrd_building_hreflang_fallback() {
	if ( is_admin() || ! is_page_template( 'page-apartment-building.php' ) || ! function_exists( 'pll_get_post_translations' ) ) {
		return;
	}

	$translations = array_filter( pll_get_post_translations( get_queried_object_id() ) );
	if ( count( $translations ) > 1 ) {
		return; // Polylang prints the complete reciprocal set.
	}

	$url      = get_permalink();
	$language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'en';
	echo '<link rel="alternate" href="' . esc_url( $url ) . '" hreflang="' . esc_attr( $language ) . '" />' . "\n";
	echo '<link rel="alternate" href="' . esc_url( $url ) . '" hreflang="x-default" />' . "\n";
}
add_action( 'wp_head', 'hrd_building_hreflang_fallback', 6 );

function hrd_building_content_meta_box( $post ) {
	if ( 'page-apartment-building.php' !== get_page_template_slug( $post->ID ) ) {
		return;
	}
	$locales = function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array( 'en' );
	wp_nonce_field( 'hrd_building_content_save', 'hrd_building_content_nonce' );
	echo '<p>Paste one JSON object per language. Empty fields fall back to English.</p>';
	foreach ( $locales as $locale ) {
		$value = get_post_meta( $post->ID, 'hrd_building_content_' . sanitize_key( $locale ), true );
		echo '<p><label for="hrd-building-content-' . esc_attr( $locale ) . '"><strong>' . esc_html( strtoupper( $locale ) ) . '</strong></label><br><textarea class="widefat" rows="10" id="hrd-building-content-' . esc_attr( $locale ) . '" name="hrd_building_content[' . esc_attr( $locale ) . ']" placeholder="{&quot;hero_summary&quot;:&quot;...&quot;,&quot;overview&quot;:&quot;...&quot;,&quot;amenities&quot;:&quot;...&quot;}">' . esc_textarea( $value ) . '</textarea></p>';
	}
}

function hrd_register_building_content_meta_box() {
	$screen = get_current_screen();
	if ( $screen && 'page' === $screen->post_type ) {
		add_meta_box( 'hrd-building-content', 'Building content by language', 'hrd_building_content_meta_box', 'page', 'normal', 'default' );
	}
}
add_action( 'add_meta_boxes', 'hrd_register_building_content_meta_box' );

function hrd_save_building_content( $post_id ) {
	if ( 'page-apartment-building.php' !== get_page_template_slug( $post_id ) || ! isset( $_POST['hrd_building_content_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hrd_building_content_nonce'] ) ), 'hrd_building_content_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$submitted = isset( $_POST['hrd_building_content'] ) && is_array( $_POST['hrd_building_content'] ) ? wp_unslash( $_POST['hrd_building_content'] ) : array();
	foreach ( $submitted as $locale => $raw ) {
		$locale = sanitize_key( $locale );
		$decoded = json_decode( (string) $raw, true );
		if ( ! is_array( $decoded ) ) {
			continue;
		}
		$clean = array();
		foreach ( hrd_building_content_fields() as $field ) {
			if ( isset( $decoded[ $field ] ) && is_string( $decoded[ $field ] ) ) {
				$clean[ $field ] = wp_kses_post( $decoded[ $field ] );
			}
		}
		update_post_meta( $post_id, 'hrd_building_content_' . $locale, wp_slash( wp_json_encode( $clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );
	}
}
add_action( 'save_post_page', 'hrd_save_building_content' );
