<?php
/** Native RealHomes rental form enhancements for the requirements page. */
function hrd_is_rental_form_page() {
	return is_page( array( 8743, 24276, 24277, 25182, 25183, 25184 ) );
}

function hrd_rental_form_template( $template ) {
	if ( hrd_is_rental_form_page() ) {
		$contact_template = get_theme_file_path( 'templates/contact.php' );
		if ( file_exists( $contact_template ) ) {
			return $contact_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'hrd_rental_form_template', 20 );

function hrd_rental_form_assets() {
	if ( ! hrd_is_rental_form_page() ) {
		return;
	}
	wp_add_inline_style( 'parent-default', '.rh_page__contact{max-width:980px;margin-left:auto;margin-right:auto}.rh_contact__form{width:100%;max-width:980px;margin-left:auto;margin-right:auto}#contact-form{width:100%}#contact-form .contact-form{max-width:880px;margin-left:auto!important;margin-right:auto!important}#contact-form .contact-form label{font-size:16px;line-height:1.45}#contact-form .hrd-form-section-title{display:block;width:100%;margin:8px 0 12px;font-size:17px;font-weight:700;color:#183036}#contact-form .hrd-form-note{font-size:16px;line-height:1.5;color:#536264}#contact-form .contact-form input[type=text],#contact-form .contact-form input[type=email],#contact-form .contact-form textarea{font-size:16px;min-height:48px;line-height:1.45}#contact-form .contact-form textarea{min-height:140px}#contact-form #submit-button{font-size:17px;padding:13px 22px;background:#17343A;border-color:#0E252A;color:#fff;font-weight:700}#contact-form #submit-button:hover{background:#0E252A}.hrd-rental-preferences{margin:0 0 22px;padding:22px;background:#fff;border:1px solid #DCE7E5;border-top:5px solid #17343A;box-shadow:0 12px 30px rgba(14,37,42,.08)}.hrd-rental-preferences h4{margin:0 0 20px;font-size:24px;line-height:1.25}.hrd-rental-preferences p{display:inline-block;width:100%;vertical-align:top;font-size:18px;line-height:1.5}.hrd-rental-preferences label,.hrd-rental-preferences legend{font-size:18px;font-weight:600}.hrd-rental-preferences .hrd-rental-half{width:49%;margin-right:1%}.hrd-rental-preferences .hrd-rental-half+ .hrd-rental-half{margin-right:0}.hrd-rental-preferences fieldset{border:0;padding:0;margin:0 0 18px}.hrd-rental-preferences legend{margin-bottom:8px}.hrd-rental-preferences legend small{font-size:15px;font-weight:400}.hrd-choice-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.hrd-choice{position:relative!important;display:flex!important;align-items:center;justify-content:center;min-height:52px;margin:0!important;padding:10px!important;border:2px solid #cbd3d9;border-radius:4px;background:#fff;cursor:pointer;text-align:center;font-weight:600!important}.hrd-choice input{position:absolute;opacity:0}.hrd-choice:has(input:checked){border-color:#17343A;background:#17343A;box-shadow:inset 0 0 0 1px #17343A}.hrd-choice:has(input:checked) span{color:#fff}.hrd-choice:has(input:focus-visible){outline:3px solid #9A7B4F;outline-offset:2px}.hrd-rental-preferences select,.hrd-rental-preferences input[type=date],.hrd-rental-preferences input[type=text]{box-sizing:border-box;width:100%;min-height:48px;padding:9px 12px;font-size:16px;border:1px solid #9aafb4!important;border-radius:3px;background:#fff!important;color:#183036}.hrd-rental-preferences input[type=text]{margin-top:12px}.hrd-rental-preferences select:focus,.hrd-rental-preferences input:focus{border-color:#9A7B4F!important;box-shadow:0 0 0 2px #EEE5D6}.hrd-rental-preferences input[type=checkbox],.hrd-rental-preferences input[type=radio]{width:19px;height:19px;margin-right:7px;vertical-align:-3px}@media(max-width:600px){.hrd-rental-preferences{padding:18px}.hrd-rental-preferences h4{font-size:21px}.hrd-rental-preferences .hrd-rental-half{width:100%;margin-right:0}.hrd-choice-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}' );
	wp_add_inline_script( 'custom', "jQuery(function($){var f=$('#contact-form .contact-form');if(!f.length)return;var msg=f.find('#message');function compile(){var lines=[];function val(id,label){var v=$('#'+id).val();if(v)lines.push(label+': '+v);}val('hrd-accommodation','Accommodation');val('hrd-bedrooms','Bedrooms');val('hrd-bathrooms','Bathrooms');val('hrd-budget','Budget');val('hrd-move-in','Move-in date');var lease=f.find('input[name=hrd_lease]:checked').val();if(lease)lines.push('Lease length: '+lease);var areas=f.find('input[name=\"hrd_areas[]\"]:checked').map(function(){return this.value;}).get();var other=$('#hrd-area-other').val();if(other)areas.push(other);if(areas.length)lines.push('Preferred areas: '+areas.join(', '));var existing=msg.data('hrd-user-message')||'';var current=msg.val();if(current&&!/^Rental requirements:/m.test(current))existing=current;msg.data('hrd-user-message',existing);msg.val('Rental requirements:\\n'+lines.join('\\n')+(existing?'\\nAdditional request: '+existing:''));}f.on('change keyup','.hrd-rental-preferences select,.hrd-rental-preferences input',compile);f.on('click','#submit-button',compile);});" );
}
add_action( 'wp_enqueue_scripts', 'hrd_rental_form_assets', 30 );

function hrd_rental_requirements_copy( $content ) {
	if ( hrd_is_rental_form_page() && in_the_loop() && is_main_query() ) {
		$language = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
		$copy = array(
			'en' => array( 'Tell us a few details about the home you need in Da Nang. Our local team will check suitable options for your budget, area and move-in date.', 'Takes about 1 minute. No booking or payment is required.' ),
			'vi' => array( 'Hãy cho chúng tôi biết một vài thông tin về căn nhà bạn cần tại Đà Nẵng. Đội ngũ địa phương sẽ tìm các lựa chọn phù hợp với ngân sách, khu vực và ngày chuyển vào của bạn.', 'Chỉ mất khoảng 1 phút. Bạn không cần đặt chỗ hay thanh toán.' ),
			'ko' => array( '다낭에서 찾고 계신 집에 대해 몇 가지 정보를 알려주세요. 현지 팀이 예산, 지역 및 입주일에 맞는 매물을 확인해 드립니다.', '약 1분이면 작성할 수 있습니다. 예약이나 결제는 필요하지 않습니다.' ),
			'ja' => array( 'ダナンでお探しの住まいについて、いくつか教えてください。現地チームがご予算、エリア、入居希望日に合う物件を確認します。', '所要時間は約1分です。予約や支払いは必要ありません。' ),
			'ru' => array( 'Расскажите немного о жилье, которое вы ищете в Дананге. Наша местная команда подберёт варианты по вашему бюджету, району и дате заезда.', 'Заполнение займёт около 1 минуты. Бронирование и оплата не требуются.' ),
			'zh' => array( '请告诉我们您在岘港想找什么样的住房。我们的当地团队会根据您的预算、区域和入住日期查找合适的房源。', '填写大约需要 1 分钟，无需预订或付款。' ),
		);
		$selected = $copy[ $language ] ?? $copy['en'];
		return '<p>' . esc_html( $selected[0] ) . '</p><p class="hrd-form-note">' . esc_html( $selected[1] ) . '</p>';
	}
	return $content;
}
add_filter( 'the_content', 'hrd_rental_requirements_copy', 20 );

/** Translate the added rental controls for the site's Polylang languages. */
function hrd_rental_form_translations() {
	if ( ! hrd_is_rental_form_page() ) {
		return;
	}
	wp_add_inline_script( 'custom', "jQuery(function($){var lang=(document.documentElement.lang||'en').slice(0,2),sets={vi:['Tell us what you are looking for|Cho chúng tôi biết bạn đang tìm căn nhà như thế nào','Your rental needs|Nhu cầu thuê nhà','Your contact details|Thông tin liên hệ','Accommodation type|Loại nhà','Please choose|Vui lòng chọn','Monthly budget|Ngân sách mỗi tháng','Preferred move-in date|Ngày chuyển vào dự kiến','Lease length|Thời hạn thuê','Preferred areas|Khu vực ưu tiên','choose up to 3|chọn tối đa 3','Or type another area|Hoặc nhập khu vực khác','Help me find a home|Giúp tôi tìm nhà','Name|Họ và tên','Email|Email','Phone Number|Số điện thoại','Message|Lời nhắn'],ko:['Tell us what you are looking for|찾고 계신 집을 알려주세요','Your rental needs|임대 조건','Your contact details|연락처 정보','Accommodation type|주택 유형','Please choose|선택해 주세요','Monthly budget|월 예산','Preferred move-in date|희망 입주일','Lease length|임대 기간','Preferred areas|선호 지역','choose up to 3|최대 3개 지역 선택','Or type another area|다른 지역을 입력하세요','Help me find a home|집 찾기 도움받기','Name|이름','Email|이메일','Phone Number|전화번호','Message|메시지'],ja:['Tell us what you are looking for|お探しの住まいを教えてください','Your rental needs|ご希望の条件','Your contact details|連絡先','Accommodation type|物件タイプ','Please choose|選択してください','Monthly budget|月額予算','Preferred move-in date|入居希望日','Lease length|契約期間','Preferred areas|希望エリア','choose up to 3|最大3エリア','Or type another area|別のエリアを入力','Help me find a home|住まい探しを依頼','Name|お名前','Email|メール','Phone Number|電話番号','Message|メッセージ'],ru:['Tell us what you are looking for|Расскажите, какое жильё вы ищете','Your rental needs|Параметры аренды','Your contact details|Контактные данные','Accommodation type|Тип жилья','Please choose|Выберите вариант','Monthly budget|Бюджет в месяц','Preferred move-in date|Желаемая дата заезда','Lease length|Срок аренды','Preferred areas|Предпочтительные районы','choose up to 3|до 3 районов','Or type another area|Или укажите другой район','Help me find a home|Помогите найти жильё','Name|Имя','Email|Email','Phone Number|Телефон','Message|Сообщение'],zh:['Tell us what you are looking for|告诉我们您想找什么样的房子','Your rental needs|租房需求','Your contact details|联系方式','Accommodation type|房屋类型','Please choose|请选择','Monthly budget|每月预算','Preferred move-in date|期望入住日期','Lease length|租期','Preferred areas|偏好区域','choose up to 3|最多选择3个区域','Or type another area|或输入其他区域','Help me find a home|帮我找房','Name|姓名','Email|邮箱','Phone Number|电话号码','Message|留言']}[lang]||[];var map={};$.each(sets,function(_,v){var p=v.split('|');map[p[0]]=p[1];});$('#contact-form label,#contact-form legend,#contact-form h4,#contact-form .hrd-form-section-title').each(function(){var el=$(this),text=el.clone().children().remove().end().text().trim();if(map[text])el.contents().filter(function(){return this.nodeType===3&&$.trim(this.nodeValue);}).first().each(function(){this.nodeValue=this.nodeValue.replace(text,map[text]);});});$('#contact-form option,#contact-form .hrd-choice span').each(function(){var el=$(this),text=el.text().trim();if(map[text])el.text(map[text]);});});" );
}
add_action( 'wp_enqueue_scripts', 'hrd_rental_form_translations', 31 );

function hrd_rental_form_translation_polish() {
	if ( ! hrd_is_rental_form_page() ) {
		return;
	}
	wp_add_inline_script( 'custom', "jQuery(function($){var l=(document.documentElement.lang||'en').slice(0,2),t={vi:{'Help me find a home':'Giúp tôi tìm nhà','Your rental needs':'Nhu cầu thuê nhà','Your contact details':'Thông tin liên hệ','choose up to 3':'chọn tối đa 3','1 month':'1 tháng','3-6 months':'3-6 tháng','6-12 months':'6-12 tháng','1 year+':'Từ 1 năm'},ko:{'Help me find a home':'집 찾기 도움받기','Your rental needs':'임대 조건','Your contact details':'연락처 정보','1 month':'1개월','3-6 months':'3-6개월','6-12 months':'6-12개월','1 year+':'1년 이상'},ja:{'Help me find a home':'住まい探しを依頼','Your rental needs':'ご希望の条件','Your contact details':'連絡先','1 month':'1か月','3-6 months':'3〜6か月','6-12 months':'6〜12か月','1 year+':'1年以上'},ru:{'Help me find a home':'Помогите найти жильё','Your rental needs':'Параметры аренды','Your contact details':'Контактные данные','1 month':'1 месяц','3-6 months':'3–6 месяцев','6-12 months':'6–12 месяцев','1 year+':'1 год и более'},zh:{'Help me find a home':'帮我找房','Your rental needs':'租房需求','Your contact details':'联系方式','1 month':'1个月','3-6 months':'3-6个月','6-12 months':'6-12个月','1 year+':'1年以上'}}[l]||{};var b=$('#contact-form #submit-button');if(b.length&&t[b.val()])b.val(t[b.val()]);$('#contact-form .hrd-form-section-title,#contact-form legend,#contact-form .hrd-choice span').each(function(){var e=$(this),v=e.text();$.each(t,function(a,z){if(v.indexOf(a)>=0)e.text(v.replace(a,z));});});});" );
}
add_action( 'wp_enqueue_scripts', 'hrd_rental_form_translation_polish', 32 );
