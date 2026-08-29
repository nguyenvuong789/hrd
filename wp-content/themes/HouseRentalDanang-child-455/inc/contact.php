<?php
/** Render a useful contact page even when the RealHomes contact email setting is empty. */
function hrd_render_contact_page() {
	if ( ! is_page_template( 'templates/contact.php' ) ) {
		return;
	}
	$language = hrd_get_current_language();
	$copy = array(
		'en' => array( 'eyebrow' => 'Local rental desk / Da Nang', 'title' => "Let's find a home<br><em>that feels right.</em>", 'intro' => 'Share a few details and our local team will help you compare suitable homes, current pricing and neighbourhood context.', 'panel' => 'Tell us what you need', 'name' => 'Name', 'name_ph' => 'Your name', 'email' => 'Email', 'email_ph' => 'you@example.com', 'phone' => 'Phone / WhatsApp', 'optional' => '(optional)', 'message' => 'What are you looking for?', 'message_ph' => 'Area, move-in date, bedrooms, budget...', 'submit' => 'Send enquiry', 'mail' => 'Email us' ),
		'vi' => array( 'eyebrow' => 'Bộ phận cho thuê tại Đà Nẵng', 'title' => 'Tìm một nơi ở<br><em>phù hợp với bạn.</em>', 'intro' => 'Hãy chia sẻ một vài thông tin. Đội ngũ địa phương sẽ giúp bạn so sánh những căn phù hợp, giá hiện tại và đặc điểm từng khu vực.', 'panel' => 'Bạn đang tìm gì?', 'name' => 'Họ tên', 'name_ph' => 'Tên của bạn', 'email' => 'Email', 'email_ph' => 'you@example.com', 'phone' => 'Điện thoại / WhatsApp', 'optional' => '(không bắt buộc)', 'message' => 'Bạn đang cần tìm gì?', 'message_ph' => 'Khu vực, ngày chuyển vào, số phòng ngủ, ngân sách...', 'submit' => 'Gửi yêu cầu', 'mail' => 'Gửi email' ),
		'ko' => array( 'eyebrow' => '다낭 현지 임대 데스크', 'title' => '마음에 맞는 집을<br><em>찾아보세요.</em>', 'intro' => '필요한 조건을 알려 주세요. 현지 팀이 적합한 매물과 현재 가격, 주변 정보를 함께 안내해 드립니다.', 'panel' => '찾고 계신 조건', 'name' => '이름', 'name_ph' => '이름을 입력하세요', 'email' => '이메일', 'email_ph' => 'you@example.com', 'phone' => '전화 / WhatsApp', 'optional' => '(선택)', 'message' => '어떤 집을 찾고 계신가요?', 'message_ph' => '지역, 입주일, 침실 수, 예산...', 'submit' => '문의 보내기', 'mail' => '이메일 보내기' ),
		'ja' => array( 'eyebrow' => 'ダナン現地レンタルデスク', 'title' => 'あなたに合う住まいを<br><em>見つけましょう。</em>', 'intro' => 'ご希望をお聞かせください。現地チームが条件に合う物件、現在の料金、周辺情報をご案内します。', 'panel' => 'ご希望をお聞かせください', 'name' => 'お名前', 'name_ph' => 'お名前', 'email' => 'メール', 'email_ph' => 'you@example.com', 'phone' => '電話 / WhatsApp', 'optional' => '(任意)', 'message' => 'どのような住まいをお探しですか？', 'message_ph' => 'エリア、入居日、寝室数、予算...', 'submit' => '問い合わせを送信', 'mail' => 'メールを送る' ),
		'ru' => array( 'eyebrow' => 'Местный отдел аренды в Дананге', 'title' => 'Найдём жильё,<br><em>которое вам подходит.</em>', 'intro' => 'Расскажите о своих пожеланиях. Наша местная команда поможет сравнить подходящие варианты, актуальные цены и районы.', 'panel' => 'Что вы ищете?', 'name' => 'Имя', 'name_ph' => 'Ваше имя', 'email' => 'Email', 'email_ph' => 'you@example.com', 'phone' => 'Телефон / WhatsApp', 'optional' => '(необязательно)', 'message' => 'Что вы ищете?', 'message_ph' => 'Район, дата въезда, спальни, бюджет...', 'submit' => 'Отправить запрос', 'mail' => 'Написать нам' ),
		'zh' => array( 'eyebrow' => '岘港本地租赁团队', 'title' => '帮你找到<br><em>合适的家。</em>', 'intro' => '告诉我们您的需求。本地团队会协助您比较合适的房源、当前价格和社区信息。', 'panel' => '您正在寻找什么？', 'name' => '姓名', 'name_ph' => '您的姓名', 'email' => '电子邮箱', 'email_ph' => 'you@example.com', 'phone' => '电话 / WhatsApp', 'optional' => '(可选)', 'message' => '您正在寻找什么？', 'message_ph' => '区域、入住日期、卧室数量、预算...', 'submit' => '发送咨询', 'mail' => '发送邮件' ),
	);
	$text = $copy[ $language ] ?? $copy['en'];
	?>
	<section class="hrd-contact-page" aria-labelledby="hrd-contact-title">
		<div class="hrd-contact-page__intro">
			<p class="hrd-contact-page__eyebrow"><span aria-hidden="true"></span> <?php echo esc_html( $text['eyebrow'] ); ?></p>
			<h1 id="hrd-contact-title"><?php echo wp_kses_post( $text['title'] ); ?></h1>
			<p><?php echo esc_html( $text['intro'] ); ?></p>
			<div class="hrd-contact-page__notes"><span>01</span><span>Human advice</span><span>Current availability</span></div>
		</div>
		<div class="hrd-contact-page__panel">
			<div class="hrd-contact-page__panel-head"><strong><?php echo esc_html( $text['panel'] ); ?></strong></div>
			<form class="contact-form hrd-contact-page__form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
				<div class="hrd-contact-page__grid"><p><label for="hrd-contact-name"><?php echo esc_html( $text['name'] ); ?></label><input id="hrd-contact-name" name="name" type="text" placeholder="<?php echo esc_attr( $text['name_ph'] ); ?>" class="required" required></p><p><label for="hrd-contact-email"><?php echo esc_html( $text['email'] ); ?></label><input id="hrd-contact-email" name="email" type="email" placeholder="<?php echo esc_attr( $text['email_ph'] ); ?>" class="email required" required></p></div>
				<p><label for="hrd-contact-phone"><?php echo esc_html( $text['phone'] ); ?> <span><?php echo esc_html( $text['optional'] ); ?></span></label><input id="hrd-contact-phone" name="number" type="text"></p>
				<p><label for="hrd-contact-message"><?php echo esc_html( $text['message'] ); ?></label><textarea id="hrd-contact-message" name="message" rows="5" class="required" placeholder="<?php echo esc_attr( $text['message_ph'] ); ?>" required></textarea></p>
				<p class="hrd-contact-page__submit"><input type="submit" id="submit-button" value="<?php echo esc_attr( $text['submit'] ); ?>" class="rh_btn rh_btn--primary" name="submit"><span id="ajax-loader"></span></p>
				<input type="hidden" name="action" value="send_message"><input type="hidden" name="ere_cf_widget_target_email" value="hello@houserentaldanang.com"><input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'send_message_nonce' ) ); ?>">
				<div id="error-container"></div><div id="message-container"></div>
			</form>
			<div class="hrd-contact-page__links"><a href="mailto:hello@houserentaldanang.com"><?php echo esc_html( $text['mail'] ); ?>: hello@houserentaldanang.com</a></div>
		</div>
	</section>
	<?php
}
add_action( 'inspiry_before_page_contents', 'hrd_render_contact_page', 15 );

/** Translate AJAX feedback emitted by Easy Real Estate on this form. */
function hrd_translate_contact_feedback( $translation, $text, $domain ) {
	if ( is_admin() || ! function_exists( 'pll_current_language' ) || 'easy-real-estate' !== $domain ) {
		return $translation;
	}
	$language = pll_current_language();
	$messages = array(
		'Message Sent Successfully!' => array( 'vi' => 'Đã gửi yêu cầu thành công!', 'ko' => '문의가 성공적으로 전송되었습니다!', 'ja' => 'お問い合わせを送信しました。', 'ru' => 'Запрос успешно отправлен!', 'zh' => '咨询已成功发送！' ),
		'Target Email address is not properly configured!' => array( 'vi' => 'Địa chỉ email nhận thư chưa được cấu hình đúng.', 'ko' => '수신 이메일 주소가 올바르게 설정되지 않았습니다.', 'ja' => '送信先メールアドレスが正しく設定されていません。', 'ru' => 'Адрес получателя настроен неправильно.', 'zh' => '收件邮箱配置不正确。' ),
		'Security verification failed, please refresh the page and try again.' => array( 'vi' => 'Xác minh bảo mật không thành công. Vui lòng tải lại trang và thử lại.', 'ko' => '보안 확인에 실패했습니다. 페이지를 새로 고친 후 다시 시도해 주세요.', 'ja' => 'セキュリティ確認に失敗しました。ページを更新して再度お試しください。', 'ru' => 'Проверка безопасности не пройдена. Обновите страницу и повторите попытку.', 'zh' => '安全验证失败，请刷新页面后重试。' ),
	);
	return $messages[ $text ][ $language ] ?? $translation;
}
add_filter( 'gettext', 'hrd_translate_contact_feedback', 30, 3 );
