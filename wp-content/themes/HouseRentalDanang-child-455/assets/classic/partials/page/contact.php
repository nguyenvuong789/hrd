<?php
/**
 * Displays contact page template's stuff.
 *
 * @package realhomes
 */

get_header();

get_template_part( 'assets/classic/partials/banners/default' ); ?>

    <!-- Content -->
    <div class="container contents contact-page">
		<?php
		// Display any contents after the page banner and before the contents.
		do_action( 'inspiry_before_page_contents' );
		?>
        <div class="row">
            <div class="span9 main-wrap">
                <!-- Main Content -->
                <div class="main">
                    <div class="inner-wrapper">
						<?php
						// Retrieve Page Meta
						$page_meta = get_post_custom( get_the_ID() );

						/*
						 * Page contents
						 */
						$get_content_position = get_post_meta( get_the_ID(), 'REAL_HOMES_content_area_above_footer', true );

						if ( $get_content_position !== '1' ) {
							if ( have_posts() ) {
								while ( have_posts() ) {
									the_post();
									$rh_content_is_empty = '';
									if ( ! get_the_content() ) {
										$rh_content_is_empty = 'rh_page_content_is_empty';
									}
									?>
                                    <article id="post-<?php the_ID(); ?>" <?php post_class( $rh_content_is_empty ); ?>>
										<?php the_content(); ?>
                                    </article>
									<?php
								}
							}
						}

						/*
						 * Contact Map
						 */
						$show_contact_map = isset( $page_meta['theme_show_contact_map'][0] ) ? $page_meta['theme_show_contact_map'][0] : '';
						if ( $show_contact_map ) {
							?>
                            <!-- Map Container -->
                            <div class="map-container clearfix">
                                <!-- Works for Both Google Maps and Open Street Maps -->
                                <div id="map_canvas"></div>
                            </div>
							<?php
							// Function that renders Open Street Map - inspiry_render_contact_open_street_map()
							// Function that renders Google Map - inspiry_render_contact_google_map()
						}

						/*
						 * Contact Details
						 */
						$show_details = isset( $page_meta['theme_show_details'][0] ) ? $page_meta['theme_show_details'][0] : false;
						if ( $show_details ) {
							$contact_details_title = isset( $page_meta['theme_contact_details_title'][0] ) ? $page_meta['theme_contact_details_title'][0] : '';
							$contact_address       = stripslashes( isset( $page_meta['theme_contact_address'][0] ) ? $page_meta['theme_contact_address'][0] : '' );
							$contact_cell          = isset( $page_meta['theme_contact_cell'][0] ) ? $page_meta['theme_contact_cell'][0] : '';
							$contact_phone         = isset( $page_meta['theme_contact_phone'][0] ) ? $page_meta['theme_contact_phone'][0] : '';
							$contact_fax           = isset( $page_meta['theme_contact_fax'][0] ) ? $page_meta['theme_contact_fax'][0] : '';
							$contact_display_email = isset( $page_meta['theme_contact_display_email'][0] ) ? $page_meta['theme_contact_display_email'][0] : '';
							?>
                            <section class="contact-details clearfix">
								<?php
								if ( ! empty( $contact_details_title ) ) {
									?><h3><?php echo esc_html( $contact_details_title ); ?></h3><?php
								}
								?>
                                <ul class="contacts-list">
									<?php
									if ( ! empty( $contact_phone ) ) {
										?>
                                        <li class="phone">
											<?php
											inspiry_safe_include_svg( '/images/icon-phone.svg' );
											esc_html_e( 'Phone', RH_TEXT_DOMAIN );
											?>
                                            : <?php echo '<a class="rh_classic_contact_numbers" href="tel://' . esc_attr( $contact_phone ) . '">' . esc_html( $contact_phone ) . '</a>'; ?>
                                        </li>
										<?php
									}


									if ( ! empty( $contact_cell ) ) {
										?>
                                        <li class="mobile">
											<?php
											inspiry_safe_include_svg( '/images/icon-mobile.svg' );
											esc_html_e( 'Mobile', RH_TEXT_DOMAIN );
											?>
                                            : <?php echo '<a class="rh_classic_contact_numbers" href="tel://' . esc_attr( $contact_cell ) . '">' . esc_html( $contact_cell ) . '</a>'; ?>
                                        </li>
										<?php
									}

									if ( ! empty( $contact_fax ) ) {
										?>
                                        <li class="fax">
											<?php
											inspiry_safe_include_svg( '/images/icon-printer.svg' );
											esc_html_e( 'Fax', RH_TEXT_DOMAIN );
											?>
                                            : <a href="fax:<?php echo esc_html( $contact_fax ); ?>"><?php echo esc_html( $contact_fax ); ?></a>
                                        </li>
										<?php
									}

									if ( ! empty( $contact_display_email ) ) {
										?>
                                        <li class="email">
											<?php
											inspiry_safe_include_svg( '/images/icon-mail.svg' );
											esc_html_e( 'Email', RH_TEXT_DOMAIN );
											?>
                                            :
                                            <a href="mailto:<?php echo antispambot( $contact_display_email ); ?>"><?php echo antispambot( $contact_display_email ); ?></a>
                                        </li>
										<?php
									}

									if ( ! empty( $contact_address ) ) {
										?>
                                        <li class="address">
											<?php
											inspiry_safe_include_svg( '/images/icon-map.svg' );
											esc_html_e( 'Address', RH_TEXT_DOMAIN );
											?>
                                            : <?php echo wp_kses( $contact_address, inspiry_allowed_html() ); ?>
                                        </li>
										<?php
									}
									?>
                                </ul>
                            </section>
							<?php
						}

						/**
						 * Contact Form
						 */
						if ( empty( $page_meta['realhomes_show_contact_form'][0] ) || 'hide' !== $page_meta['realhomes_show_contact_form'][0] ) {
							if ( isset( $page_meta['inspiry_contact_form_shortcode'] ) && ! empty( $page_meta['inspiry_contact_form_shortcode'][0] ) ) {
								// Contact Form Shortcode
								echo do_shortcode( $page_meta['inspiry_contact_form_shortcode'][0] );
							} else {
								if ( isset( $page_meta['theme_contact_email'] ) && ! empty( $page_meta['theme_contact_email'][0] ) ) {
									$name_label                 = isset( $page_meta['theme_contact_form_name_label'][0] ) ? $page_meta['theme_contact_form_name_label'][0] : '';
									$email_label                = isset( $page_meta['theme_contact_form_email_label'][0] ) ? $page_meta['theme_contact_form_email_label'][0] : '';
									$number_label               = isset( $page_meta['theme_contact_form_number_label'][0] ) ? $page_meta['theme_contact_form_number_label'][0] : '';
									$message_label              = isset( $page_meta['theme_contact_form_message_label'][0] ) ? $page_meta['theme_contact_form_message_label'][0] : '';
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
										<?php
										if ( isset( $page_meta['theme_contact_form_heading'] ) && ! empty( $page_meta['theme_contact_form_heading'][0] ) ) {
											?>
                                            <h3 class="form-heading"><?php echo esc_html( $page_meta['theme_contact_form_heading'][0] ); ?></h3><?php
										}
                                        ?>
                                        <form class="contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
                                        <?php if ( hrd_is_rental_form_page() ) : ?>
                                            <div class="hrd-rental-preferences">
                                                <h4><?php esc_html_e( 'Tell us what you are looking for', RH_TEXT_DOMAIN ); ?></h4><p class="hrd-form-section-title">1. Your rental needs</p>
                                                <p><label for="hrd-accommodation">Accommodation type <span class="required">*</span></label><select id="hrd-accommodation" name="hrd_accommodation" class="required"><option value="">Please choose</option><option>Apartment</option><option>House</option><option>Villa</option><option>Studio</option></select></p>
                                                <p class="hrd-rental-half"><label for="hrd-bedrooms">Bedrooms</label><select id="hrd-bedrooms" name="hrd_bedrooms"><option value="">Any</option><option>Studio</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></p>
                                                <p class="hrd-rental-half"><label for="hrd-bathrooms">Bathrooms</label><select id="hrd-bathrooms" name="hrd_bathrooms"><option value="">Any</option><option>1</option><option>2</option><option>3</option><option>4+</option></select></p>
                                                <p><label for="hrd-budget">Monthly budget <span class="required">*</span></label><select id="hrd-budget" name="hrd_budget" class="required"><option value="">Please choose</option><option>Under $500</option><option>$500-$800</option><option>$800-$1,200</option><option>$1,200-$2,000</option><option>$2,000+</option><option>Flexible</option></select></p>
                                                <p><label for="hrd-move-in">Preferred move-in date <span class="required">*</span></label><input type="date" id="hrd-move-in" name="hrd_move_in" class="required"></p>
                                                <fieldset><legend>Lease length <span class="required">*</span></legend><div class="hrd-choice-grid"><label class="hrd-choice"><input type="radio" name="hrd_lease" value="1 month" required><span>1 month</span></label><label class="hrd-choice"><input type="radio" name="hrd_lease" value="3-6 months"><span>3-6 months</span></label><label class="hrd-choice"><input type="radio" name="hrd_lease" value="6-12 months"><span>6-12 months</span></label><label class="hrd-choice"><input type="radio" name="hrd_lease" value="1 year+"><span>1 year+</span></label></div></fieldset>
                                                <fieldset><legend>Preferred areas <small>(choose up to 3)</small></legend><div class="hrd-choice-grid hrd-area-grid"><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Son Tra"><span>Son Tra</span></label><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Ngu Hanh Son"><span>Ngu Hanh Son</span></label><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Hai Chau"><span>Hai Chau</span></label><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Cam Le"><span>Cam Le</span></label><label class="hrd-choice"><input type="checkbox" name="hrd_areas[]" value="Not sure"><span>Not sure</span></label></div><input type="text" id="hrd-area-other" name="hrd_area_other" placeholder="Or type another area"></fieldset>
                                            </div>
                                        <?php endif; ?>
                                            <p class="hrd-form-section-title">2. Your contact details</p><p>
                                                <label for="name"><?php echo esc_html( $contact_form_name_label ); ?></label>
                                                <input type="text" name="name" id="name" class="required" value="<?php echo esc_attr( $user_name ); ?>" title="<?php esc_attr_e( '* Please provide your name', RH_TEXT_DOMAIN ); ?>">
                                            </p>
                                            <p>
                                                <label for="email"><?php echo esc_html( $contact_form_email_label ); ?></label>
                                                <input type="text" name="email" id="email" class="email required" value="<?php echo esc_attr( $user_email ); ?>" title="<?php esc_attr_e( '* Please provide a valid email address', RH_TEXT_DOMAIN ); ?>">
                                            </p>
                                            <p>
                                                <label for="number"><?php echo esc_html( $contact_form_number_label ); ?></label>
                                                <input type="text" name="number" id="number" value="<?php echo esc_attr( $user_phone ); ?>">
                                            </p>
                                            <p>
                                                <label for="message"><?php echo esc_html( $contact_form_message_label ); ?></label>
                                                <textarea name="message" id="message" class="required" title="<?php esc_attr_e( '* Please provide your message', RH_TEXT_DOMAIN ); ?>"></textarea>
                                            </p>
											<?php
											if ( function_exists( 'ere_gdpr_agreement' ) ) {
												ere_gdpr_agreement( array(
													'container'       => 'p',
													'container_class' => 'gdpr-agreement',
													'title_class'     => 'gdpr-checkbox-label'
												) );
											}

											if ( function_exists( 'ere_is_reCAPTCHA_configured' ) && ere_is_reCAPTCHA_configured() ) : ?>
                                                <p>
													<?php
													/* Display reCAPTCHA if enabled and configured from customizer settings */
													get_template_part( 'common/google-reCAPTCHA/google-reCAPTCHA' );
													?>
                                                </p>
											<?php endif; ?>
                                            <p>
                                                <input type="submit" id="submit-button" value="<?php echo esc_attr( hrd_is_rental_form_page() ? 'Help me find a home' : __( 'Send Message', RH_TEXT_DOMAIN ) ); ?>" class="real-btn" name="submit">
                                                <img src="<?php echo esc_attr( INSPIRY_DIR_URI ); ?>/images/ajax-loader.gif" id="ajax-loader" alt="Loading...">
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
						}
						?>
                    </div>
					<?php
					if ( '1' === $get_content_position ) {
						if ( have_posts() ) :
							while ( have_posts() ) :
								the_post();
								?>
                                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
									<?php the_content(); ?>
                                </article>
							<?php
							endwhile;
						endif;

					}
					?>

                </div><!-- End Main Content -->
            </div><!-- End span9 -->

			<?php get_sidebar( 'contact' ); ?>

        </div><!-- End contents row -->
    </div><!-- End Content -->

<?php get_footer(); ?>
