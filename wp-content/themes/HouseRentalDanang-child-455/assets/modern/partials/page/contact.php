<?php
/**
 * Displays contact template related stuff.
 *
 * @package realhomes
 */

get_header();

$header_variation = get_option( 'inspiry_contact_header_variation' );

if ( empty( $header_variation ) || ( 'none' === $header_variation ) ) {
	get_template_part( 'assets/modern/partials/banner/header' );
} else if ( ! empty( $header_variation ) && ( 'banner' === $header_variation ) ) {
	get_template_part( 'assets/modern/partials/banner/image' );
}

if ( inspiry_show_header_search_form() ) {
	get_template_part( 'assets/modern/partials/properties/search/advance' );
}
?>
    <section class="rh_section rh_wrap rh_wrap--padding rh_wrap--topPadding">
		<?php
		// Display any contents after the page banner and before the contents.
		do_action( 'inspiry_before_page_contents' );
		?>
        <div class="rh_page">
			<?php if ( empty( $header_variation ) || ( 'none' === $header_variation ) ) : ?>
                <div class="rh_page__head">
                    <h2 class="rh_page__title"><?php the_title(); ?></h2>
                </div>
			<?php endif; ?>

            <div class="rh_page__contact">
				<?php
				// Retrieve Contact Page Meta
				$page_meta = get_post_custom( get_the_ID() );

				$get_content_position = get_post_meta( get_the_ID(), 'REAL_HOMES_content_area_above_footer', true );

				if ( $get_content_position !== '1' ) {

					if ( have_posts() ) :
						?>
                        <div class="rh_blog rh_blog__single">
							<?php while ( have_posts() ) : ?><?php the_post(); ?>
                                <article id="post-<?php the_ID(); ?>" class="rh_blog__post">
                                    <div class="rh_content entry-content"><?php the_content(); ?></div>
                                </article>
							<?php endwhile; ?>
                        </div>
					<?php
					endif;
				}
				?>
                <div class="rh_contact">
                    <div class="rh_contact__wrap">
						<?php
						/**
						 * Contact Form
						 */
						if ( empty( $page_meta['realhomes_show_contact_form'][0] ) || 'hide' !== $page_meta['realhomes_show_contact_form'][0] ) {
							?>
                            <div class="rh_contact__form">
								<?php
								if ( isset( $page_meta['inspiry_contact_form_shortcode'] ) && ! empty( $page_meta['inspiry_contact_form_shortcode'][0] ) ) {
									// Contact Form Shortcode
									echo do_shortcode( $page_meta['inspiry_contact_form_shortcode'][0] );
								} else {
									// Default Contact Form.
									if ( isset( $page_meta['theme_contact_email'] ) && ! empty( $page_meta['theme_contact_email'][0] ) ) {
										$name_label                 = isset( $page_meta['theme_contact_form_name_label'] ) ? $page_meta['theme_contact_form_name_label'][0] : '';
										$email_label                = isset( $page_meta['theme_contact_form_email_label'] ) ? $page_meta['theme_contact_form_email_label'][0] : '';
										$number_label               = isset( $page_meta['theme_contact_form_number_label'] ) ? $page_meta['theme_contact_form_number_label'][0] : '';
										$message_label              = isset( $page_meta['theme_contact_form_message_label'] ) ? $page_meta['theme_contact_form_message_label'][0] : '';
										$contact_form_name_label    = empty( $name_label ) ? esc_html__( 'Name', RH_TEXT_DOMAIN ) : $name_label;
										$contact_form_email_label   = empty( $email_label ) ? esc_html__( 'Email', RH_TEXT_DOMAIN ) : $email_label;
										$contact_form_number_label  = empty( $number_label ) ? esc_html__( 'Phone Number', RH_TEXT_DOMAIN ) : $number_label;
										$contact_form_message_label = empty( $message_label ) ? esc_html__( 'Message', RH_TEXT_DOMAIN ) : $message_label;
										$user_name                  = $user_email = $user_phone = '';

										if ( is_user_logged_in() ) {
											$current_user = wp_get_current_user();

											// Get the user's display name
											$user_name = ! empty( $current_user->display_name ) ? $current_user->display_name : $current_user->user_login;

											// Get the user's email
											$user_email = $current_user->user_email;

											// Get the user's phone (assuming it's stored as 'mobile_number' in user meta)
										$user_phone = get_user_meta( $current_user->ID, 'mobile_number', true );
									}
									if ( hrd_is_rental_form_page() ) {
										$user_name = $user_email = $user_phone = '';
									}
										?>
                                        <section id="contact-form">
                                            <form class="contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
                                                <?php if ( hrd_is_rental_form_page() ) : ?>
                                                    <div class="hrd-rental-preferences">
                                                        <h4>Tell us what you are looking for</h4><p class="hrd-form-section-title">1. Your rental needs</p>
                                                        <p><label for="hrd-accommodation">Accommodation type <span class="required">*</span></label><select id="hrd-accommodation" name="hrd_accommodation" class="required"><option value="">Please choose</option><option>Apartment</option><option>House</option><option>Villa</option><option>Studio</option></select></p>
                                                        <p class="hrd-rental-half"><label for="hrd-bedrooms">Bedrooms</label><select id="hrd-bedrooms" name="hrd_bedrooms"><option value="">Any</option><option>Studio</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></p><p class="hrd-rental-half"><label for="hrd-bathrooms">Bathrooms</label><select id="hrd-bathrooms" name="hrd_bathrooms"><option value="">Any</option><option>1</option><option>2</option><option>3</option><option>4+</option></select></p>
                                                        <p><label for="hrd-budget">Monthly budget <span class="required">*</span></label><select id="hrd-budget" name="hrd_budget" class="required"><option value="">Please choose</option><option>Under $500</option><option>$500-$800</option><option>$800-$1,200</option><option>$1,200-$2,000</option><option>$2,000+</option><option>Flexible</option></select></p><p><label for="hrd-move-in">Preferred move-in date <span class="required">*</span></label><input type="date" id="hrd-move-in" name="hrd_move_in" class="required"></p>
                                                        <fieldset><legend>Lease length <span class="required">*</span></legend><div class="hrd-choice-grid"><label class="hrd-choice"><input type="radio" name="hrd_lease" value="1 month" required><span>1 month</span></label><label class="hrd-choice"><input type="radio" name="hrd_lease" value="3-6 months"><span>3-6 months</span></label><label class="hrd-choice"><input type="radio" name="hrd_lease" value="6-12 months"><span>6-12 months</span></label><label class="hrd-choice"><input type="radio" name="hrd_lease" value="1 year+"><span>1 year+</span></label></div></fieldset>
                                                        <fieldset><legend>Preferred areas <small>(choose up to 3)</small></legend><div class="hrd-choice-grid hrd-area-grid"><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Son Tra"><span>Son Tra</span></label><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Ngu Hanh Son"><span>Ngu Hanh Son</span></label><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Hai Chau"><span>Hai Chau</span></label><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Cam Le"><span>Cam Le</span></label><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Not sure"><span>Not sure</span></label></div><input type="text" id="hrd-area-other" name="hrd_area_other" placeholder="Or type another area"></fieldset>
                                                    </div>
                                                <?php endif; ?>
                                                <p class="hrd-form-section-title">2. Your contact details</p><p class="rh_contact__input rh_contact__input_text">
                                                    <label for="name"><?php echo esc_html( $contact_form_name_label ); ?></label>
                                                    <input type="text" name="name" id="name" class="required" placeholder="<?php esc_attr_e( 'Your Name', RH_TEXT_DOMAIN ); ?>" value="<?php echo esc_attr( $user_name ); ?>" title="<?php esc_attr_e( '* Please provide your name', RH_TEXT_DOMAIN ); ?>">
                                                </p>

                                                <p class="rh_contact__input rh_contact__input_text">
                                                    <label for="email"><?php echo esc_html( $contact_form_email_label ); ?></label>
                                                    <input type="text" name="email" id="email" class="email required" placeholder="<?php esc_attr_e( 'Your Email', RH_TEXT_DOMAIN ); ?>" value="<?php echo esc_attr( $user_email ); ?>" title="<?php esc_attr_e( '* Please provide a valid email address', RH_TEXT_DOMAIN ); ?>">
                                                </p>

                                                <p class="rh_contact__input rh_contact__input_text">
                                                    <label for="number"><?php echo esc_html( $contact_form_number_label ); ?></label>
                                                    <input type="text" name="number" id="number" placeholder="<?php esc_attr_e( 'Your Phone', RH_TEXT_DOMAIN ); ?>" value="<?php echo esc_attr( $user_phone ); ?>">
                                                </p>

                                                <p class="rh_contact__input rh_contact__input_textarea">
                                                    <label for="message"><?php echo esc_html( $contact_form_message_label ); ?></label>
                                                    <textarea cols="40" rows="6" name="message" id="message" class="required" placeholder="<?php esc_attr_e( 'Your Message', RH_TEXT_DOMAIN ); ?>" title="<?php esc_attr_e( '* Please provide your message', RH_TEXT_DOMAIN ); ?>"></textarea>
                                                </p>

												<?php
												if ( function_exists( 'ere_gdpr_agreement' ) ) {
													ere_gdpr_agreement( array(
														'id'              => 'inspiry-gdpr',
														'container'       => 'p',
														'container_class' => 'rh_inspiry_gdpr',
														'title_class'     => 'gdpr-checkbox-label'
													) );
												}

												if ( class_exists( 'Easy_Real_Estate' ) ) {
													/* Display reCAPTCHA if enabled and configured from customizer settings */
													if ( ere_is_reCAPTCHA_configured() ) {
														$recaptcha_type = get_option( 'inspiry_reCAPTCHA_type', 'v2' );
														?>
                                                        <div class="rh_contact__input rh_contact__input_recaptcha inspiry-recaptcha-wrapper clearfix g-recaptcha-type-<?php echo esc_attr( $recaptcha_type ); ?>">
                                                            <div class="inspiry-google-recaptcha"></div>
                                                        </div>
														<?php
													}

													// Turnstile captcha
													ere_turnstile_widget(  'contact-form-cf-turnstile', array( 'container_classes' => "contact-form-cf-turnstile-wrapper rh_contact__input" ) );
												}
												?>

                                                <p class="rh_contact__input rh_contact__submit">
                                                    <input type="submit" id="submit-button" value="<?php echo esc_attr( hrd_is_rental_form_page() ? 'Help me find a home' : __( 'Send Message', RH_TEXT_DOMAIN ) ); ?>" class="rh_btn rh_btn--primary" name="submit">
                                                    <span id="ajax-loader"><?php inspiry_safe_include_svg( '/images/loader.svg' ); ?></span>
                                                    <input type="hidden" name="action" value="send_message" />
                                                    <input type="hidden" name="the_id" value="<?php echo esc_attr( get_the_ID() ); ?>" />
                                                    <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'send_message_nonce' ) ); ?>" />
                                                </p>
                                                <?php if ( hrd_is_rental_form_page() ) : ?><p class="hrd-form-note">We use these details only to review your rental request and contact you about suitable options.</p><?php endif; ?>

                                                <div id="error-container"></div>
                                                <div id="message-container"></div>
                                            </form>
                                        </section>
										<?php
									}
								}
								?>
                            </div><!-- /.rh_contact__form -->
							<?php
						}

						$show_details = isset( $page_meta['theme_show_details'] ) ? $page_meta['theme_show_details'][0] : '';
						if ( $show_details ) {
							$contact_address       = stripslashes( isset( $page_meta['theme_contact_address'] ) ? $page_meta['theme_contact_address'][0] : '' );
							$contact_cell          = isset( $page_meta['theme_contact_cell'] ) ? $page_meta['theme_contact_cell'][0] : '';
							$contact_phone         = isset( $page_meta['theme_contact_phone'] ) ? $page_meta['theme_contact_phone'][0] : '';
							$contact_fax           = isset( $page_meta['theme_contact_fax'] ) ? $page_meta['theme_contact_fax'][0] : '';
							$contact_display_email = isset( $page_meta['theme_contact_display_email'] ) ? $page_meta['theme_contact_display_email'][0] : '';
							?>
                            <div class="rh_contact__details">

								<?php if ( ! empty( $contact_phone ) ) : ?>
                                    <div class="rh_contact__item">
                                        <div class="icon"><?php inspiry_safe_include_svg( '/images/icons/icon-phone.svg' ); ?></div>
                                        <p class="content">
                                            <span class="label"><?php esc_html_e( 'Phone', RH_TEXT_DOMAIN ); ?></span><a href="tel:<?php echo esc_html( $contact_phone ); ?>"><?php echo esc_html( $contact_phone ); ?>
                                            </a>
                                        </p>
                                    </div>
								<?php endif; ?>

								<?php if ( ! empty( $contact_cell ) ) : ?>
                                    <div class="rh_contact__item">
                                        <div class="icon"><?php inspiry_safe_include_svg( '/images/icons/icon-mobile.svg' ); ?></div>
                                        <p class="content">
                                            <span class="label"><?php esc_html_e( 'Mobile', RH_TEXT_DOMAIN ); ?></span><a href="tel:<?php echo esc_html( $contact_cell ); ?>"><?php
												echo esc_html( $contact_cell );
												?>
                                            </a>
                                        </p>
                                    </div>
								<?php endif; ?>

								<?php if ( ! empty( $contact_fax ) ) : ?>
                                    <div class="rh_contact__item">
                                        <div class="icon"><?php inspiry_safe_include_svg( '/images/icons/icon-fax.svg' ); ?></div>
                                        <p class="content">
                                            <span class="label"><?php esc_html_e( 'Fax', RH_TEXT_DOMAIN ); ?></span><a href="fax:<?php echo esc_html( $contact_fax ); ?>"><?php
												echo esc_html( $contact_fax );
												?>
                                            </a>
                                        </p>
                                    </div>
								<?php endif; ?>

								<?php if ( ! empty( $contact_display_email ) ) : ?>
                                    <div class="rh_contact__item">
                                        <div class="icon"><?php inspiry_safe_include_svg( '/images/icons/icon-mail.svg' ); ?></div>
                                        <p class="content">
											<span class="label"><?php
												esc_html_e( 'Email', RH_TEXT_DOMAIN );
												?></span><a href="mailto:<?php echo esc_attr( antispambot( $contact_display_email ) ); ?>"><?php
												echo esc_html( antispambot( $contact_display_email ) );
												?></a>
                                        </p>
                                    </div>
								<?php endif; ?>

								<?php if ( ! empty( $contact_address ) ) : ?>
                                    <div class="rh_contact__item">
                                        <div class="icon"><?php inspiry_safe_include_svg( '/images/icons/icon-marker.svg' ); ?></div>
                                        <p class="content">
                                            <span class="label"><?php esc_html_e( 'Address', RH_TEXT_DOMAIN ); ?></span><?php echo inspiry_kses( $contact_address ); ?>
                                        </p>
                                    </div>
								<?php endif; ?>

                            </div><!-- /.rh_contact__details -->
							<?php
						}


						/*
						 * Contact Map
						 */
						$show_contact_map = isset( $page_meta['theme_show_contact_map'] ) ? $page_meta['theme_show_contact_map'][0] : '';

						if ( $show_contact_map ) {
							?>
                            <!-- Map Container -->
                            <div class="rh_contact__map">
                                <!-- Works for Both Google Maps and Open Street Maps -->
                                <div id="map_canvas"></div>
                            </div><!-- /.rh_contact__map -->
							<?php
						}
						?>

                    </div><!-- /.rh_contact__wrap -->

                </div><!-- /.rh_contact -->

            </div><!-- /.rh_page__contact -->

        </div><!-- /.rh_page -->
		<?php

		if ( '1' === $get_content_position ) {

			if ( have_posts() ) :
				?>
                <div class="rh_blog rh_blog__single">
					<?php while ( have_posts() ) : ?><?php the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" class="rh_blog__post">
                            <div class="rh_content entry-content"><?php the_content(); ?></div>
                        </article>
					<?php endwhile; ?>
                </div>
			<?php
			endif;
		}
		?>

    </section><!-- /.rh_section rh_wrap rh_wrap--padding -->

<?php
get_footer();
