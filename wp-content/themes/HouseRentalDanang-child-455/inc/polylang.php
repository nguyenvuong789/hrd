<?php
function hrd_is_localized_homepage() {
	if ( is_front_page() ) {
		return true;
	}
	if ( ! is_page() || ! function_exists( 'pll_get_post' ) ) {
		return false;
	}
	$front_id = (int) get_option( 'page_on_front' );
	$lang     = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	return $front_id && $lang && (int) pll_get_post( $front_id, $lang ) === (int) get_queried_object_id();
}

function hrd_is_property_ui_context() {
	return hrd_is_localized_homepage() || is_singular( 'property' ) || is_post_type_archive( 'property' ) || is_tax( get_object_taxonomies( 'property' ) );
}

/** Localize shared property taxonomy labels without changing stored terms. */
function hrd_localize_property_term_name( $term ) {
	if ( is_admin() || ! $term instanceof WP_Term || 'property-status' !== $term->taxonomy ) {
		return $term;
	}

	$translations = array(
		'for-rent' => array(
			'vi' => 'Cho thuê',
			'ko' => '임대',
			'ja' => '賃貸',
			'ru' => 'В аренду',
			'zh' => '出租',
		),
	);
	$language     = hrd_get_current_language();

	if ( isset( $translations[ $term->slug ][ $language ] ) ) {
		$term->name = $translations[ $term->slug ][ $language ];
	}

	return $term;
}
add_filter( 'get_term', 'hrd_localize_property_term_name' );

/** Localize standard rental price postfixes while preserving stored property meta. */
function hrd_localize_property_price_postfix( $value, $object_id, $meta_key, $single ) {
	if (
		is_admin() ||
		'REAL_HOMES_property_price_postfix' !== $meta_key ||
		'property' !== get_post_type( $object_id )
	) {
		return $value;
	}

	remove_filter( 'get_post_metadata', 'hrd_localize_property_price_postfix', 10 );
	$postfix = get_post_meta( $object_id, $meta_key, true );
	add_filter( 'get_post_metadata', 'hrd_localize_property_price_postfix', 10, 4 );
	if ( ! is_string( $postfix ) || ! in_array( strtolower( trim( $postfix ) ), array( 'month', '/month', 'per month' ), true ) ) {
		return $value;
	}

	$translations = array(
		'vi' => 'tháng',
		'ko' => '월',
		'ja' => '月',
		'ru' => 'мес.',
		'zh' => '月',
	);
	$translated = $translations[ hrd_get_current_language() ] ?? $postfix;
	// Preserve the slash format stored by RealHomes (for example `/month`).
	if ( 0 === strpos( trim( $postfix ), '/' ) && 0 !== strpos( $translated, '/' ) ) {
		$translated = '/ ' . $translated;
	}

	return $single ? $translated : array( $translated );
}
add_filter( 'get_post_metadata', 'hrd_localize_property_price_postfix', 10, 4 );

function hrd_translate_property_labels( $translation, $text, $domain ) {
	if ( is_admin() || ! function_exists( 'pll_current_language' ) ) {
		return $translation;
	}

	static $labels = array(
		'vi' => array(
			'Previous'           => 'Trước',
			'Next'               => 'Tiếp',
			'Added to favorites' => 'Đã thêm vào mục yêu thích',
			'Recommended'        => 'Được đề xuất',
			'Property Agent'     => 'Nhân viên bất động sản',
			'Compare'            => 'So sánh',
			'Login'              => 'Đăng nhập',
			'Register'           => 'Đăng ký',
			'Reset Password'     => 'Đặt lại mật khẩu',
			'Username'           => 'Tên đăng nhập',
			'Password'           => 'Mật khẩu',
			'Property ID'        => 'Mã bất động sản',
			'None'               => 'Không có',
			'Share'              => 'Chia sẻ',
			'Print'              => 'In',
			'Bedrooms'           => 'Phòng ngủ',
			'Bedroom'            => 'Phòng ngủ',
			'Bathrooms'          => 'Phòng tắm',
			'Bathroom'           => 'Phòng tắm',
			'Garage'             => 'Chỗ đậu xe',
			'Area'               => 'Diện tích',
			'Lot Size'           => 'Diện tích đất',
			'Year Built'         => 'Năm xây dựng',
			'Description'        => 'Mô tả',
			'Features'           => 'Tiện nghi',
			'Similar Properties' => 'Bất động sản tương tự',
			'Email'              => 'Email',
			'Name'               => 'Họ tên',
			'Phone'              => 'Số điện thoại',
			'Message'            => 'Nội dung',
			'Your Message'       => 'Nội dung',
			'Send Message'       => 'Gửi tin nhắn',
			'View My Listings'   => 'Xem danh sách bất động sản',
			'Main Location'      => 'Khu vực',
			'Property Type'      => 'Loại BĐS',
			'Min Beds'           => 'Số phòng ngủ tối thiểu',
			'Search'             => 'Tìm kiếm',
			'Advance Search'     => 'Tìm kiếm nâng cao',
			'View Property'      => 'Xem bất động sản',
			'Featured'           => 'Nổi bật',
			'Partners'           => 'Đối tác',
			'News'               => 'Tin tức',
			'Latest'             => 'Mới nhất',
			'Danang Travel Guide' => 'Cẩm nang Đà Nẵng',
			'Compare Properties' => 'So sánh bất động sản',
		),
		'ko' => array(
			'Previous'           => '이전',
			'Next'               => '다음',
			'Added to favorites' => '즐겨찾기에 추가됨',
			'Recommended'        => '추천',
			'Property Agent'     => '부동산 담당자',
			'Compare'            => '비교',
			'Login'              => '로그인',
			'Register'           => '회원가입',
			'Reset Password'     => '비밀번호 재설정',
			'Username'           => '사용자 이름',
			'Password'           => '비밀번호',
			'Property ID'        => '매물 ID',
			'None'               => '없음',
			'Share'              => '공유',
			'Print'              => '인쇄',
			'Bedrooms'           => '침실',
			'Bedroom'            => '침실',
			'Bathrooms'          => '욕실',
			'Bathroom'           => '욕실',
			'Garage'             => '주차 공간',
			'Area'               => '면적',
			'Lot Size'           => '대지 면적',
			'Year Built'         => '준공 연도',
			'Description'        => '설명',
			'Features'           => '편의 시설',
			'Similar Properties' => '비슷한 매물',
			'Email'              => '이메일',
			'Name'               => '이름',
			'Phone'              => '전화번호',
			'Message'            => '문의 내용',
			'Your Message'       => '문의 내용',
			'Send Message'       => '문의 보내기',
			'View My Listings'   => '매물 목록 보기',
			'Main Location'      => '지역',
			'Property Type'      => '매물 유형',
			'Min Beds'           => '최소 침실 수',
			'Search'             => '검색',
			'Advance Search'     => '상세 검색',
			'View Property'      => '매물 보기',
			'Featured'           => '추천',
			'Partners'           => '파트너',
			'News'               => '소식',
			'Latest'             => '최신',
			'Danang Travel Guide' => '다낭 가이드',
			'Compare Properties' => '매물 비교',
		),
		'ja' => array(
			'Previous' => '前へ', 'Next' => '次へ', 'Added to favorites' => 'お気に入りに追加しました',
			'Recommended' => 'おすすめ', 'Property Agent' => '物件担当者', 'Compare' => '比較',
			'Login' => 'ログイン', 'Register' => '登録', 'Reset Password' => 'パスワードを再設定',
			'Username' => 'ユーザー名', 'Password' => 'パスワード', 'Property ID' => '物件ID',
			'None' => 'なし', 'Share' => '共有', 'Print' => '印刷', 'Bedrooms' => '寝室', 'Bedroom' => '寝室',
			'Bathrooms' => 'バスルーム', 'Bathroom' => 'バスルーム', 'Garage' => '駐車場', 'Area' => '面積',
			'Lot Size' => '土地面積', 'Year Built' => '築年', 'Description' => '説明', 'Features' => '設備',
			'Similar Properties' => '類似物件', 'Email' => 'メール', 'Name' => 'お名前', 'Phone' => '電話番号',
			'Message' => 'メッセージ', 'Your Message' => 'メッセージ', 'Send Message' => '送信',
			'View My Listings' => '物件一覧を見る', 'Main Location' => 'エリア', 'Property Type' => '物件タイプ',
			'Min Beds' => '最低寝室数', 'Search' => '検索', 'Advance Search' => '詳細検索',
			'View Property' => '物件を見る', 'Featured' => 'おすすめ', 'Partners' => 'パートナー',
			'News' => 'ニュース', 'Latest' => '最新', 'Danang Travel Guide' => 'ダナンガイド',
			'Compare Properties' => '物件を比較',
		),
		'ru' => array(
			'Previous' => 'Назад', 'Next' => 'Далее', 'Added to favorites' => 'Добавлено в избранное',
			'Recommended' => 'Рекомендуем', 'Property Agent' => 'Агент по недвижимости', 'Compare' => 'Сравнить',
			'Login' => 'Войти', 'Register' => 'Регистрация', 'Reset Password' => 'Сбросить пароль',
			'Username' => 'Имя пользователя', 'Password' => 'Пароль', 'Property ID' => 'ID объекта',
			'None' => 'Нет', 'Share' => 'Поделиться', 'Print' => 'Печать', 'Bedrooms' => 'Спальни',
			'Bedroom' => 'Спальня', 'Bathrooms' => 'Ванные', 'Bathroom' => 'Ванная', 'Garage' => 'Парковка',
			'Area' => 'Площадь', 'Lot Size' => 'Площадь участка', 'Year Built' => 'Год постройки',
			'Description' => 'Описание', 'Features' => 'Удобства', 'Similar Properties' => 'Похожие объекты',
			'Email' => 'Email', 'Name' => 'Имя', 'Phone' => 'Телефон', 'Message' => 'Сообщение',
			'Your Message' => 'Сообщение', 'Send Message' => 'Отправить', 'View My Listings' => 'Смотреть объекты',
			'Main Location' => 'Район', 'Property Type' => 'Тип объекта', 'Min Beds' => 'Мин. спален',
			'Search' => 'Поиск', 'Advance Search' => 'Расширенный поиск', 'View Property' => 'Смотреть объект',
			'Featured' => 'Рекомендуемые', 'Partners' => 'Партнёры', 'News' => 'Новости', 'Latest' => 'Новые',
			'Danang Travel Guide' => 'Гид по Данангу', 'Compare Properties' => 'Сравнить объекты',
		),
		'zh' => array(
			'Previous' => '上一项', 'Next' => '下一项', 'Added to favorites' => '已加入收藏',
			'Recommended' => '推荐', 'Property Agent' => '房产顾问', 'Compare' => '比较',
			'Login' => '登录', 'Register' => '注册', 'Reset Password' => '重置密码',
			'Username' => '用户名', 'Password' => '密码', 'Property ID' => '房源编号', 'None' => '无',
			'Share' => '分享', 'Print' => '打印', 'Bedrooms' => '卧室', 'Bedroom' => '卧室',
			'Bathrooms' => '浴室', 'Bathroom' => '浴室', 'Garage' => '停车位', 'Area' => '面积',
			'Lot Size' => '土地面积', 'Year Built' => '建造年份', 'Description' => '描述', 'Features' => '设施',
			'Similar Properties' => '类似房源', 'Email' => '电子邮箱', 'Name' => '姓名', 'Phone' => '电话',
			'Message' => '留言', 'Your Message' => '留言', 'Send Message' => '发送',
			'View My Listings' => '查看房源', 'Main Location' => '区域', 'Property Type' => '房源类型',
			'Min Beds' => '最少卧室数', 'Search' => '搜索', 'Advance Search' => '高级搜索',
			'View Property' => '查看房源', 'Featured' => '精选', 'Partners' => '合作伙伴',
			'News' => '资讯', 'Latest' => '最新', 'Danang Travel Guide' => '岘港指南',
			'Compare Properties' => '比较房源',
		),
	);
	$language = pll_current_language();

	return $labels[ $language ][ $text ] ?? $translation;
}
add_filter( 'gettext', 'hrd_translate_property_labels', 20, 3 );

function hrd_translate_property_label_option( $value, $option ) {
	if ( is_admin() || ! hrd_is_property_ui_context() || ! function_exists( 'pll_current_language' ) ) {
		return $value;
	}

	static $labels = array(
		'vi' => array(
			'inspiry_bedrooms_field_label'     => 'Phòng ngủ',
			'inspiry_bathrooms_field_label'    => 'Phòng tắm',
			'inspiry_garages_field_label'      => 'Chỗ đậu xe',
			'inspiry_area_field_label'         => 'Diện tích',
			'inspiry_lot_size_field_label'     => 'Diện tích đất',
			'inspiry_year_built_field_label'   => 'Năm xây dựng',
			'inspiry_description_property_label' => 'Mô tả',
			'theme_property_features_title'    => 'Tiện nghi',
			'theme_property_map_title'         => 'Vị trí trên bản đồ',
			'theme_similar_properties_title'   => 'Bất động sản tương tự',
		),
		'ko' => array(
			'inspiry_bedrooms_field_label'     => '침실',
			'inspiry_bathrooms_field_label'    => '욕실',
			'inspiry_garages_field_label'      => '주차 공간',
			'inspiry_area_field_label'         => '면적',
			'inspiry_lot_size_field_label'     => '대지 면적',
			'inspiry_year_built_field_label'   => '준공 연도',
			'inspiry_description_property_label' => '설명',
			'theme_property_features_title'    => '편의 시설',
			'theme_property_map_title'         => '지도에서 보기',
			'theme_similar_properties_title'   => '비슷한 매물',
		),
		'ja' => array(
			'inspiry_bedrooms_field_label'       => '寝室',
			'inspiry_bathrooms_field_label'      => 'バスルーム',
			'inspiry_garages_field_label'        => '駐車場',
			'inspiry_area_field_label'           => '面積',
			'inspiry_lot_size_field_label'       => '土地面積',
			'inspiry_year_built_field_label'     => '築年',
			'inspiry_description_property_label' => '説明',
			'theme_property_features_title'      => '設備',
			'theme_property_map_title'           => '地図上の位置',
			'theme_similar_properties_title'     => '類似物件',
		),
		'ru' => array(
			'inspiry_bedrooms_field_label'       => 'Спальни',
			'inspiry_bathrooms_field_label'      => 'Ванные',
			'inspiry_garages_field_label'        => 'Парковка',
			'inspiry_area_field_label'           => 'Площадь',
			'inspiry_lot_size_field_label'       => 'Площадь участка',
			'inspiry_year_built_field_label'     => 'Год постройки',
			'inspiry_description_property_label' => 'Описание',
			'theme_property_features_title'      => 'Удобства',
			'theme_property_map_title'           => 'Расположение на карте',
			'theme_similar_properties_title'     => 'Похожие объекты',
		),
		'zh' => array(
			'inspiry_bedrooms_field_label'       => '卧室',
			'inspiry_bathrooms_field_label'      => '浴室',
			'inspiry_garages_field_label'        => '停车位',
			'inspiry_area_field_label'           => '面积',
			'inspiry_lot_size_field_label'       => '土地面积',
			'inspiry_year_built_field_label'     => '建造年份',
			'inspiry_description_property_label' => '描述',
			'theme_property_features_title'      => '设施',
			'theme_property_map_title'           => '地图位置',
			'theme_similar_properties_title'     => '类似房源',
		),
	);
	$language = pll_current_language();

	return $labels[ $language ][ $option ] ?? $value;
}

$hrd_property_label_options = array(
	'inspiry_bedrooms_field_label',
	'inspiry_bathrooms_field_label',
	'inspiry_garages_field_label',
	'inspiry_area_field_label',
	'inspiry_lot_size_field_label',
	'inspiry_year_built_field_label',
	'inspiry_description_property_label',
	'theme_property_features_title',
	'theme_property_map_title',
	'theme_similar_properties_title',
);
foreach ( $hrd_property_label_options as $hrd_property_label_option ) {
	add_filter( "option_{$hrd_property_label_option}", 'hrd_translate_property_label_option', 20, 2 );
}

function hrd_translate_home_section_metadata( $value, $object_id, $meta_key, $single ) {
	if ( is_admin() || ! hrd_is_localized_homepage() || ! function_exists( 'pll_current_language' ) ) {
		return $value;
	}

	static $headings = array(
		'en' => array(
			'inspiry_SFOI_title'                 => 'Find your place in Da Nang with local help',
			'inspiry_SFOI_description'           => 'Browse houses, apartments and villas across Da Nang. Share your area, budget and move-in date; our local team will confirm availability and lease details before a viewing.',
			'inspiry_home_features_title'         => 'What renters say about working with our local team',
			'inspiry_home_features_sub_title'     => 'Renter experiences',
			'inspiry_home_partners_sub_title'     => 'Local Partners',
			'inspiry_home_partners_title'         => 'Useful services for renters',
			'inspiry_home_partners_desc'          => 'A selection of local partners offering services that may be useful for everyday life in Da Nang. Please confirm current availability and terms directly with each provider.',
			'inspiry_home_cta_contact_title'      => 'Tell us what you are looking for',
			'inspiry_home_cta_contact_desc'       => 'Share your preferred area, monthly budget, move-in date, lease length and must-haves. We will use these details to narrow the search and confirm which options are currently available.',
			'inspiry_cta_contact_btn_one_title'   => 'Send requirements',
			'inspiry_cta_contact_btn_two_title'   => 'Browse listings',
			'inspiry_home_properties_title_2'      => 'Explore Apartments for Rent in Da Nang',
			'inspiry_home_properties_desc_2'       => 'Compare apartments across Da Nang. Please confirm the exact unit, current rent and lease terms before arranging a viewing or making a payment.',
			'inspiry_home_properties_title'        => 'Explore Houses for Rent in Da Nang',
			'inspiry_home_properties_desc'         => 'Browse by location, size, and lifestyle. Confirm current availability with our local team.',
			'inspiry_home_news_sub_title'          => 'Live Like a Local',
			'inspiry_home_news_title'              => 'Da Nang Guides',
			'inspiry_home_news_desc'               => 'Practical guides to neighborhoods, food, travel, and everyday life in Da Nang.',
		),
		'vi' => array(
			'inspiry_SFOI_title'                 => 'Tìm chỗ ở tại Đà Nẵng với sự hỗ trợ của người địa phương',
			'inspiry_SFOI_description'           => 'Xem nhà, căn hộ và biệt thự tại Đà Nẵng. Gửi khu vực, ngân sách và ngày chuyển vào; đội ngũ địa phương sẽ kiểm tra tình trạng và điều khoản trước khi hẹn xem.',
			'inspiry_home_properties_title'      => 'Khám phá nhà cho thuê tại Đà Nẵng',
			'inspiry_home_properties_title_2'    => 'Khám phá căn hộ cho thuê tại Đà Nẵng',
			'inspiry_featured_prop_sub_title'     => 'Nổi bật',
			'theme_featured_prop_title'           => 'Bất động sản',
			'theme_featured_prop_text'            => 'Những bất động sản được chọn lọc.',
			'inspiry_home_features_title'         => 'Khách thuê nói gì về đội ngũ địa phương của chúng tôi',
			'inspiry_home_features_sub_title'     => 'Trải nghiệm của khách thuê',
			'inspiry_home_partners_sub_title'     => 'Đối tác địa phương',
			'inspiry_home_partners_desc'          => 'Một số đối tác địa phương cung cấp các dịch vụ có thể hữu ích trong cuộc sống hằng ngày tại Đà Nẵng. Vui lòng xác nhận tình trạng dịch vụ và điều khoản trực tiếp với từng đơn vị.',
			'inspiry_home_partners_title'         => 'Dịch vụ hữu ích cho người thuê',
			'inspiry_home_cta_contact_title'      => 'Cho chúng tôi biết bạn đang tìm gì',
			'inspiry_home_cta_contact_desc'       => 'Gửi khu vực mong muốn, ngân sách hằng tháng, ngày chuyển vào, thời hạn thuê và các yêu cầu quan trọng. Chúng tôi sẽ dùng những thông tin này để thu hẹp lựa chọn và xác nhận những căn hiện còn trống.',
			'inspiry_cta_contact_btn_one_title'   => 'Gửi nhu cầu',
			'inspiry_cta_contact_btn_two_title'   => 'Xem tin đang có',
			'inspiry_home_news_sub_title'         => 'Sống như người địa phương',
			'inspiry_home_news_title'             => 'Cẩm nang Đà Nẵng',
			'inspiry_home_news_desc'              => 'Thông tin thực tế về khu vực, ẩm thực, du lịch và cuộc sống hằng ngày tại Đà Nẵng.',
			'inspiry_home_properties_desc_2'       => 'So sánh các căn hộ trên toàn Đà Nẵng. Vui lòng xác nhận đúng căn, giá thuê hiện tại và điều khoản trước khi xem nhà hoặc thanh toán.',
			'inspiry_home_properties_desc'         => 'Xem nhà theo khu vực, diện tích và phong cách sống. Hãy xác nhận tình trạng còn trống với đội ngũ địa phương.',
		),
		'ko' => array(
			'inspiry_SFOI_title'                 => '다낭 현지인의 도움으로 나에게 맞는 집을 찾아보세요',
			'inspiry_SFOI_description'           => '다낭의 주택, 아파트와 빌라를 둘러보세요. 지역, 예산과 입주일을 보내주시면 현지 팀이 공실과 임대 조건을 확인해 드립니다.',
			'inspiry_home_properties_title'      => '다낭 임대 주택 둘러보기',
			'inspiry_home_properties_title_2'    => '다낭 임대 아파트 둘러보기',
			'inspiry_featured_prop_sub_title'     => '추천',
			'theme_featured_prop_title'           => '추천 매물',
			'theme_featured_prop_text'            => '엄선한 추천 매물을 확인해 보세요.',
			'inspiry_home_features_title'         => '현지의 도움을 받으면 집 찾기가 더 쉬워집니다',
			'inspiry_home_features_sub_title'     => '실제 임차인 후기',
			'inspiry_home_partners_desc'          => '다낭 생활에 빠르게 적응할 수 있도록 돕는 신뢰할 수 있는 현지 네트워크입니다.',
			'inspiry_home_partners_sub_title'    => '현지 파트너',
			'inspiry_home_partners_title'         => '저희가 신뢰하는 파트너',
			'inspiry_home_cta_contact_title'      => '원하는 집의 조건을 알려주세요',
			'inspiry_home_cta_contact_desc'       => '희망 지역, 예산, 입주일과 필수 조건을 보내주세요. 선택지를 좁히고 현재 정보를 확인해 드립니다.',
			'inspiry_cta_contact_btn_one_title'   => '요청 보내기',
			'inspiry_cta_contact_btn_two_title'   => '매물 보기',
			'inspiry_home_news_sub_title'         => '현지인처럼 즐기기',
			'inspiry_home_news_title'             => '다낭 생활 가이드',
			'inspiry_home_news_desc'              => '다낭의 지역, 음식, 여행과 일상생활에 관한 실용적인 정보를 확인하세요.',
			'inspiry_home_properties_desc_2'       => '다낭 전역의 아파트를 비교해 보세요. 방문이나 결제 전에 정확한 호수, 현재 임대료와 계약 조건을 확인하세요.',
			'inspiry_home_properties_desc'         => '지역, 크기와 생활 방식에 맞춰 주택을 찾아보세요. 현재 공실 여부는 현지 팀에 확인하세요.',
		),
		'ja' => array(
			'inspiry_SFOI_title' => '現地チームとダナンであなたに合う住まいを見つけましょう',
			'inspiry_SFOI_description' => 'ダナン各地の住宅、アパート、ヴィラをご覧ください。エリア、予算、入居日をお知らせいただければ、現地チームが空室と契約条件を確認します。',
			'inspiry_home_features_title' => '現地チームと住まいを探した入居者の声',
			'inspiry_home_features_sub_title' => '入居者の体験',
			'inspiry_home_partners_sub_title' => 'ローカルネットワーク',
			'inspiry_home_partners_title' => '信頼できるパートナーとサービス',
			'inspiry_home_partners_desc' => 'ダナンでの暮らしに役立つ、信頼できる現地のサービスと事業者をご紹介します。利用条件と営業状況は各事業者へご確認ください。',
			'inspiry_home_cta_contact_title' => 'ご希望をお聞かせください',
			'inspiry_home_cta_contact_desc' => '希望エリア、予算、入居日、必須条件をお知らせください。選択肢を絞り、最新情報を確認します。',
			'inspiry_cta_contact_btn_one_title' => '簡単フォーム',
			'inspiry_cta_contact_btn_two_title' => '物件を見る',
			'inspiry_home_properties_title_2' => 'ダナンの賃貸アパートを探す',
			'inspiry_home_properties_desc_2' => 'ダナン各地のアパートを比較できます。内見や支払いの前に、正確な部屋、現在の家賃、契約条件をご確認ください。',
			'inspiry_home_properties_title' => 'ダナンの賃貸住宅を探す',
			'inspiry_home_properties_desc' => 'エリア、広さ、暮らし方から住宅を探せます。空室状況は現地チームにご確認ください。',
			'inspiry_home_news_sub_title' => '現地のように暮らす',
			'inspiry_home_news_title' => 'ダナンガイド',
			'inspiry_home_news_desc' => 'エリア、食、旅行、日々の暮らしに役立つダナン情報をご紹介します。',
		),
		'ru' => array(
			'inspiry_SFOI_title' => 'Найдите подходящее жильё в Дананге с помощью местной команды',
			'inspiry_SFOI_description' => 'Смотрите дома, квартиры и виллы в Дананге. Укажите район, бюджет и дату въезда, чтобы местная команда проверила наличие и условия аренды.',
			'inspiry_home_features_title' => 'Что говорят арендаторы о нашей местной команде',
			'inspiry_home_features_sub_title' => 'Опыт арендаторов',
			'inspiry_home_partners_sub_title' => 'Местная сеть',
			'inspiry_home_partners_title' => 'Проверенные партнёры и услуги',
			'inspiry_home_partners_desc' => 'Полезные местные сервисы и компании, которым мы доверяем. Уточняйте актуальные условия и доступность напрямую у каждого поставщика.',
			'inspiry_home_cta_contact_title' => 'Расскажите, что вы ищете',
			'inspiry_home_cta_contact_desc' => 'Укажите район, бюджет, дату заезда и важные требования. Мы сузим выбор и проверим актуальные детали.',
			'inspiry_cta_contact_btn_one_title' => 'Быстрая форма',
			'inspiry_cta_contact_btn_two_title' => 'Смотреть объекты',
			'inspiry_home_properties_title_2' => 'Квартиры в аренду в Дананге',
			'inspiry_home_properties_desc_2' => 'Сравните квартиры по всему Данангу. Перед просмотром или оплатой уточните точный объект, актуальную цену и условия аренды.',
			'inspiry_home_properties_title' => 'Дома в аренду в Дананге',
			'inspiry_home_properties_desc' => 'Выбирайте по району, площади и образу жизни. Актуальное наличие уточняйте у нашей местной команды.',
			'inspiry_home_news_sub_title' => 'Живите как местные',
			'inspiry_home_news_title' => 'Гид по Данангу',
			'inspiry_home_news_desc' => 'Практические материалы о районах, еде, путешествиях и повседневной жизни в Дананге.',
		),
		'zh' => array(
			'inspiry_SFOI_title' => '在本地团队帮助下，找到您在岘港的理想住所',
			'inspiry_SFOI_description' => '浏览岘港各地的房屋、公寓和别墅。告诉我们区域、预算和入住日期，本地团队会确认房源状态和租赁条款。',
			'inspiry_home_features_title' => '租客如何评价我们的本地团队',
			'inspiry_home_features_sub_title' => '租客体验',
			'inspiry_home_partners_sub_title' => '本地网络',
			'inspiry_home_partners_title' => '我们信任的商家与服务',
			'inspiry_home_partners_desc' => '为新来者适应岘港生活提供帮助的可靠本地网络。服务时间和条款请直接向各商家确认。',
			'inspiry_home_cta_contact_title' => '告诉我们您的需求',
			'inspiry_home_cta_contact_desc' => '告诉我们偏好的区域、预算、入住日期和必要条件。我们会帮您缩小选择范围并确认最新信息。',
			'inspiry_cta_contact_btn_one_title' => '快速表单',
			'inspiry_cta_contact_btn_two_title' => '浏览房源',
			'inspiry_home_properties_title_2' => '探索岘港出租公寓',
			'inspiry_home_properties_desc_2' => '比较岘港各地的公寓。安排看房或付款前，请确认具体房源、当前租金和租赁条款。',
			'inspiry_home_properties_title' => '探索岘港出租房屋',
			'inspiry_home_properties_desc' => '按区域、面积和生活方式浏览房源。请与本地团队确认当前空置情况。',
			'inspiry_home_news_sub_title' => '像本地人一样生活',
			'inspiry_home_news_title' => '岘港指南',
			'inspiry_home_news_desc' => '提供有关区域、美食、旅行和岘港日常生活的实用指南。',
		),
	);
	$language = pll_current_language();

	return $headings[ $language ][ $meta_key ] ?? $value;
}
add_filter( 'get_post_metadata', 'hrd_translate_home_section_metadata', 20, 4 );

function hrd_translate_home_view_more( $translation, $text, $domain ) {
	if ( is_admin() || 'View more' !== $text ) {
		return $translation;
	}
	$labels = array( 'vi' => 'Xem thêm', 'ko' => '더 보기', 'ja' => 'もっと見る', 'ru' => 'Смотреть ещё', 'zh' => '查看更多' );
	$language = hrd_get_current_language();
	return $labels[ $language ] ?? $translation;
}
add_filter( 'gettext', 'hrd_translate_home_view_more', 25, 3 );
