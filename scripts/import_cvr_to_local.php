<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$wp_root = '/Users/vincent/Local Sites/hrd/app/public';
require $wp_root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$handle = fopen($root . '/output/rewrite-master-enriched.csv', 'r');
$headers = fgetcsv($handle);
$rows = [];
while (($values = fgetcsv($handle)) !== false) {
    if (count($values) === count($headers)) {
        $rows[] = array_combine($headers, $values);
    }
}
fclose($handle);

function hrd_import_term_id(string $taxonomy, string $slug): int {
    $terms = get_terms(['taxonomy' => $taxonomy, 'slug' => $slug, 'hide_empty' => false]);
    if (is_wp_error($terms) || !$terms) {
        return 0;
    }
    foreach ($terms as $term) {
        if (function_exists('pll_get_term_language') && 'en' === pll_get_term_language($term->term_id)) {
            return (int) $term->term_id;
        }
    }
    return (int) $terms[0]->term_id;
}

function hrd_import_image(string $source, int $post_id, string $title, int $index) {
    $contents = file_get_contents($source);
    if (false === $contents) {
        return new WP_Error('read_image', 'Cannot read ' . $source);
    }
    $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    $slug = sanitize_title($title);
    $name = sprintf('%s-%02d.%s', $slug, $index, $ext);
    $upload = wp_upload_bits($name, null, $contents);
    if (!empty($upload['error'])) {
        return new WP_Error('upload_image', $upload['error']);
    }
    $mime = wp_check_filetype($upload['file']);
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => $mime['type'],
        'post_title' => sprintf('%s - photo %d', $title, $index),
        'post_content' => '',
        'post_status' => 'inherit',
    ], $upload['file'], $post_id, true);
    if (is_wp_error($attachment_id)) {
        return $attachment_id;
    }
    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
    update_post_meta($attachment_id, '_wp_attachment_image_alt', sprintf('%s in Da Nang - photo %d', $title, $index));
    return (int) $attachment_id;
}

$report = ['created' => [], 'skipped' => [], 'failed' => []];
foreach ($rows as $position => $row) {
    $source_key = trim($row['source_key']);
    $existing = get_posts([
        'post_type' => 'property', 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => 1,
        'meta_key' => '_hrd_shared_property_source_id', 'meta_value' => $source_key,
    ]);
    if ($existing) {
        $report['skipped'][] = ['source_key' => $source_key, 'post_id' => (int) $existing[0]];
        continue;
    }
    $post_id = wp_insert_post([
        'post_type' => 'property',
        'post_status' => 'draft',
        'post_title' => $row['rewritten_title'],
        'post_name' => $row['permalink_candidate'],
        'post_content' => $row['post_content'],
        'post_excerpt' => $row['post_excerpt'],
        'comment_status' => 'closed',
        'ping_status' => 'closed',
    ], true);
    if (is_wp_error($post_id)) {
        $report['failed'][] = ['source_key' => $source_key, 'error' => $post_id->get_error_message()];
        continue;
    }
    if (function_exists('pll_set_post_language')) {
        pll_set_post_language($post_id, 'en');
    }
    $taxonomy_map = [
        'property-type' => array_filter([$row['property_type_slug']]),
        'property-status' => ['for-rent'],
        'property-city' => array_filter(explode(';', $row['property_city_term_slugs'])),
        'property-feature' => array_filter(explode(';', $row['property_features'])),
    ];
    foreach ($taxonomy_map as $taxonomy => $slugs) {
        $ids = array_values(array_filter(array_map(fn($slug) => hrd_import_term_id($taxonomy, $slug), $slugs)));
        wp_set_object_terms($post_id, $ids, $taxonomy, false);
    }
    $meta = [
        'REAL_HOMES_property_price' => $row['public_price'],
        'REAL_HOMES_property_price_postfix' => '/month',
        'REAL_HOMES_property_bedrooms' => $row['bedrooms'],
        'REAL_HOMES_property_bathrooms' => $row['bathrooms'],
        'REAL_HOMES_property_size' => $row['size'],
        'REAL_HOMES_property_size_postfix' => 'sqm',
        'REAL_HOMES_property_id' => 'HRD-' . substr($source_key, 0, 8),
        'REAL_HOMES_property_map' => '0',
        'REAL_HOMES_featured' => '0',
        '_hrd_shared_property_source_id' => $source_key,
        '_hrd_shared_property_language' => 'en',
        '_hrd_shared_property_translation' => '1',
        '_hrd_source_url_internal' => $row['source_url_internal'],
        '_hrd_source_price_raw' => $row['source_price'] . ' ' . $row['price_currency'],
        '_hrd_import_batch' => 'cvr-47-20260830',
        '_hrd_price_basis' => 'total_monthly',
        '_hrd_estimated_total_price_amount' => $row['public_price'],
    ];
    if (!empty($row['building_id'])) {
        $meta['hrd_building_key'] = $row['building_id'];
    }
    foreach ($meta as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }
    $files = glob($root . '/' . $row['image_dir'] . '/*.{webp,jpg,jpeg,png}', GLOB_BRACE) ?: [];
    natsort($files);
    $attachments = [];
    foreach (array_values($files) as $index => $file) {
        $attachment_id = hrd_import_image($file, $post_id, $row['rewritten_title'], $index + 1);
        if (!is_wp_error($attachment_id)) {
            $attachments[] = $attachment_id;
            add_post_meta($post_id, 'REAL_HOMES_property_images', $attachment_id);
        }
    }
    if (!$attachments) {
        $report['failed'][] = ['source_key' => $source_key, 'post_id' => $post_id, 'error' => 'No images imported; left as draft'];
        continue;
    }
    set_post_thumbnail($post_id, $attachments[0]);
    wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
    $report['created'][] = ['source_key' => $source_key, 'post_id' => $post_id, 'images' => count($attachments)];
    echo sprintf("[%d/%d] Created property %d with %d images\n", $position + 1, count($rows), $post_id, count($attachments));
}

file_put_contents($root . '/output/cvr-import-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo sprintf("DONE created=%d skipped=%d failed=%d\n", count($report['created']), count($report['skipped']), count($report['failed']));
