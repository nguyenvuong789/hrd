<?php
/** Keep the public news heading visible when local front-page meta is incomplete. */
function hrd_home_news_heading_fallback( $value, $object_id, $meta_key, $single ) {
	if ( ! is_front_page() || ! in_array( $meta_key, array( 'inspiry_home_news_sub_title', 'inspiry_home_news_title', 'inspiry_home_news_desc' ), true ) || ! empty( $value ) ) {
		return $value;
	}

	$fallbacks = array(
		'inspiry_home_news_sub_title' => 'Live Like a Local',
		'inspiry_home_news_title'     => 'Da Nang Guides',
		'inspiry_home_news_desc'      => 'Practical guides to neighborhoods, food, travel, and everyday life in Da Nang.',
	);

	return $single ? $fallbacks[ $meta_key ] : array( $fallbacks[ $meta_key ] );
}
add_filter( 'get_post_metadata', 'hrd_home_news_heading_fallback', 10, 4 );

/** Keep the shared partner logos visible on translated homepages. */
function hrd_include_shared_partners_on_translations( $query ) {
	if ( is_admin() || ! $query->is_main_query() && 'partners' !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( 'partners' === $query->get( 'post_type' ) ) {
		$query->set( 'suppress_filters', true );
		$query->set( 'lang', '' );
	}
}
add_action( 'pre_get_posts', 'hrd_include_shared_partners_on_translations' );

function hrd_translate_widget_title( $title ) {
	if ( ! function_exists( 'pll_current_language' ) || 'Danang Travel Guide' !== $title ) {
		return $title;
	}

	$translations = array(
		'vi' => 'Cẩm nang Đà Nẵng',
		'ko' => '다낭 가이드',
	);

	return $translations[ pll_current_language() ] ?? $title;
}
add_filter( 'widget_title', 'hrd_translate_widget_title' );

function hrd_translate_compare_title( $value ) {
	if ( ! function_exists( 'pll_current_language' ) ) {
		return $value;
	}

	$translations = array(
		'vi' => 'So sánh bất động sản',
		'ko' => '매물 비교',
	);

	return $translations[ pll_current_language() ] ?? $value;
}
add_filter( 'option_inspiry_compare_view_title', 'hrd_translate_compare_title' );

function hrd_sync_shared_property_translation( $post_id, $post, $update ) {
	if (
		! $update ||
		wp_is_post_revision( $post_id ) ||
		wp_is_post_autosave( $post_id ) ||
		! function_exists( 'pll_get_post_language' ) ||
		'en' !== pll_get_post_language( $post_id )
	) {
		return;
	}

	$translations = pll_get_post_translations( $post_id );
	remove_action( 'save_post_property', 'hrd_sync_shared_property_translation', 20 );

	foreach ( hrd_get_shared_property_languages() as $language ) {
		$translation_id = $translations[ $language ] ?? 0;
		if ( ! $translation_id || ! get_post_meta( $translation_id, '_hrd_shared_property_translation', true ) ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'                   => $translation_id,
				'post_title'           => $post->post_title,
				'post_content'         => $post->post_content,
				'post_excerpt'         => $post->post_excerpt,
				'post_status'          => $post->post_status,
				'post_name'            => $post->post_name,
				'comment_status'       => $post->comment_status,
				'ping_status'          => $post->ping_status,
				'post_content_filtered' => $post->post_content_filtered,
			)
		);

		foreach ( get_object_taxonomies( 'property' ) as $taxonomy ) {
			if ( in_array( $taxonomy, array( 'language', 'post_translations' ), true ) ) {
				continue;
			}

			$term_ids = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $term_ids ) ) {
				wp_set_object_terms( $translation_id, $term_ids, $taxonomy );
			}
		}

		foreach ( get_post_meta( $post_id ) as $meta_key => $values ) {
			if ( in_array( $meta_key, array( '_edit_lock', '_edit_last' ), true ) ) {
				continue;
			}

			delete_post_meta( $translation_id, $meta_key );
			foreach ( $values as $value ) {
				add_post_meta( $translation_id, $meta_key, maybe_unserialize( $value ) );
			}
		}

		update_post_meta( $translation_id, '_hrd_shared_property_translation', 1 );
	}

	add_action( 'save_post_property', 'hrd_sync_shared_property_translation', 20, 3 );
}
add_action( 'save_post_property', 'hrd_sync_shared_property_translation', 20, 3 );

/**
 * Keep editorial workflows simple for posts, pages, and property listings.
 */
function hrd_use_classic_editor_for_content_types( $use_block_editor, $post_type ) {
	if ( in_array( $post_type, array( 'post', 'page', 'property' ), true ) ) {
		return false;
	}

	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'hrd_use_classic_editor_for_content_types', 20, 2 );

/** Add the second homepage property feed that existed in the legacy parent. */
function hrd_add_second_home_property_settings( $meta_boxes ) {
	if ( ! class_exists( 'ERE_Data' ) ) {
		return $meta_boxes;
	}

	foreach ( $meta_boxes as &$meta_box ) {
		if ( 'home-page-meta-box' !== ( $meta_box['id'] ?? '' ) ) {
			continue;
		}

		$count_options = array_combine( range( 1, 20 ), range( 1, 20 ) );
		$meta_box['fields'][] = array(
			'name' => esc_html__( 'Property Section 2', RH_TEXT_DOMAIN ),
			'type' => 'heading',
			'tab'  => 'home-properties',
		);
		$meta_box['fields'][] = array(
			'name'    => esc_html__( 'Show Property Section 2?', RH_TEXT_DOMAIN ),
			'id'      => 'theme_show_home_properties_2',
			'type'    => 'radio',
			'std'     => 'true',
			'options' => array( 'true' => esc_html__( 'Show', RH_TEXT_DOMAIN ), 'false' => esc_html__( 'Hide', RH_TEXT_DOMAIN ) ),
			'columns' => 12,
			'tab'     => 'home-properties',
		);

		$second_fields = array(
			array( 'name' => esc_html__( 'Text Over Title 2', RH_TEXT_DOMAIN ), 'id' => 'inspiry_home_properties_sub_title_2', 'type' => 'text', 'std' => esc_html__( 'Latest', RH_TEXT_DOMAIN ), 'columns' => 6 ),
			array( 'name' => esc_html__( 'Section Title 2', RH_TEXT_DOMAIN ), 'id' => 'inspiry_home_properties_title_2', 'type' => 'text', 'std' => esc_html__( 'Apartments for Rent', RH_TEXT_DOMAIN ), 'columns' => 6 ),
			array( 'name' => esc_html__( 'Section Description 2', RH_TEXT_DOMAIN ), 'id' => 'inspiry_home_properties_desc_2', 'type' => 'textarea', 'columns' => 12 ),
			array(
				'name' => esc_html__( 'Select the kind of properties for Section 2', RH_TEXT_DOMAIN ), 'id' => 'theme_home_properties_2', 'type' => 'radio', 'std' => 'based-on-selection', 'columns' => 12,
				'options' => array( 'recent' => esc_html__( 'Recent Properties', RH_TEXT_DOMAIN ), 'featured' => esc_html__( 'Featured Properties', RH_TEXT_DOMAIN ), 'apartments-only' => esc_html__( 'Apartments Only', RH_TEXT_DOMAIN ), 'based-on-selection' => esc_html__( 'Properties Based on Selected Locations, Statuses and Types from Below', RH_TEXT_DOMAIN ) ),
			),
			array( 'name' => esc_html__( 'Select Property Types', RH_TEXT_DOMAIN ), 'id' => 'theme_types_for_homepage_2', 'type' => 'checkbox_list', 'options' => ERE_Data::get_types_slug_name(), 'columns' => 4, 'visible' => array( 'theme_home_properties_2', 'based-on-selection' ) ),
			array( 'name' => esc_html__( 'Select Property Statuses', RH_TEXT_DOMAIN ), 'id' => 'theme_statuses_for_homepage_2', 'type' => 'checkbox_list', 'options' => ERE_Data::get_statuses_slug_name(), 'columns' => 4, 'visible' => array( 'theme_home_properties_2', 'based-on-selection' ) ),
			array( 'name' => esc_html__( 'Select Property Locations', RH_TEXT_DOMAIN ), 'id' => 'theme_cities_for_homepage_2', 'type' => 'checkbox_list', 'options' => ERE_Data::get_locations_slug_name( true ), 'columns' => 4, 'visible' => array( 'theme_home_properties_2', 'based-on-selection' ) ),
			array(
				'name' => esc_html__( 'Sort Properties By', RH_TEXT_DOMAIN ), 'id' => 'theme_sorty_by_2', 'type' => 'radio', 'std' => 'recent', 'columns' => 12,
				'options' => array( 'recent' => esc_html__( 'Time - Recent First', RH_TEXT_DOMAIN ), 'low-to-high' => esc_html__( 'Price - Low to High', RH_TEXT_DOMAIN ), 'high-to-low' => esc_html__( 'Price - High to Low', RH_TEXT_DOMAIN ), 'random' => esc_html__( 'Random', RH_TEXT_DOMAIN ) ),
			),
			array( 'name' => esc_html__( 'Number of Properties', RH_TEXT_DOMAIN ), 'id' => 'theme_properties_on_home_2', 'type' => 'select', 'std' => '4', 'options' => $count_options, 'columns' => 6 ),
			array( 'name' => esc_html__( 'View More URL 2', RH_TEXT_DOMAIN ), 'id' => 'inspiry_home_properties_link_view_more_2', 'type' => 'url', 'columns' => 6 ),
		);

		foreach ( $second_fields as $field ) {
			$field['tab']     = 'home-properties';
			$field['visible'] = $field['visible'] ?? array( 'theme_show_home_properties_2', 'true' );
			$meta_box['fields'][] = $field;
		}

		// Keep the first feed's destination beside its own property-count setting.
		$view_more_field = array(
			'name'    => esc_html__( 'View More URL', RH_TEXT_DOMAIN ),
			'id'      => 'inspiry_home_properties_link_view_more',
			'type'    => 'url',
			'columns' => 6,
			'tab'     => 'home-properties',
			'visible' => array( 'theme_show_home_properties', 'true' ),
		);
		foreach ( $meta_box['fields'] as $field_index => $field ) {
			if ( 'theme_properties_on_home' === ( $field['id'] ?? '' ) ) {
				array_splice( $meta_box['fields'], $field_index + 1, 0, array( $view_more_field ) );
				break;
			}
		}
	}
	unset( $meta_box );

	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'hrd_add_second_home_property_settings', 30 );

/** Build the independently configured query for homepage property section 2. */
function hrd_get_second_home_property_args( $page_id ) {
	$args = array(
		'post_type'      => 'property',
		'posts_per_page' => max( 1, absint( get_post_meta( $page_id, 'theme_properties_on_home_2', true ) ) ?: 4 ),
	);
	$mode = get_post_meta( $page_id, 'theme_home_properties_2', true ) ?: 'based-on-selection';

	if ( 'featured' === $mode ) {
		$args['meta_query'] = array( array( 'key' => 'REAL_HOMES_featured', 'value' => 1, 'compare' => '=', 'type' => 'NUMERIC' ) );
	} elseif ( 'apartments-only' === $mode ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'property-type', 'field' => 'slug', 'terms' => array( 'apartment', 'apartments' ), 'operator' => 'IN' ) );
	} elseif ( 'based-on-selection' === $mode ) {
		$tax_query = array();
		foreach ( array( 'theme_types_for_homepage_2' => 'property-type', 'theme_statuses_for_homepage_2' => 'property-status', 'theme_cities_for_homepage_2' => 'property-city' ) as $meta_key => $taxonomy ) {
			$terms = array_filter( (array) get_post_meta( $page_id, $meta_key ) );
			if ( $terms ) {
				$tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $terms );
			}
		}
		if ( $tax_query ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$args['tax_query'] = $tax_query;
		}
	}

	switch ( get_post_meta( $page_id, 'theme_sorty_by_2', true ) ) {
		case 'low-to-high':
		case 'high-to-low':
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'REAL_HOMES_property_price';
			$args['order']    = 'low-to-high' === get_post_meta( $page_id, 'theme_sorty_by_2', true ) ? 'ASC' : 'DESC';
			break;
		case 'random':
			$args['orderby'] = 'rand';
			break;
	}

	return $args;
}
