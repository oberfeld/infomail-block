<?php

/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

/* Attributes: startDate, endDate (Both strings in the format `2026-04-01T20:03:46`) */

$start_raw = isset($attributes['startDate']) ? (string) $attributes['startDate'] : '';
$end_raw   = isset($attributes['endDate']) ? (string) $attributes['endDate'] : '';

// Expecting values like: 2026-04-01T20:03:46 (or compatible ISO date strings).
$start_ts = strtotime($start_raw);
$end_ts   = strtotime($end_raw);

if (! $start_ts || ! $end_ts) {
	return '<p>Infomail-Block: Bitte Start- und Enddatum setzen.</p>';
}

// Normalize to full-day bounds in site timezone context.
$start_mysql = gmdate('Y-m-d 00:00:00', $start_ts + (int) (get_option('gmt_offset') * HOUR_IN_SECONDS));
$end_mysql   = gmdate('Y-m-d 23:59:59', $end_ts + (int) (get_option('gmt_offset') * HOUR_IN_SECONDS));

$query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'orderby'             => 'date',
		'order'               => 'ASC',
		'ignore_sticky_posts' => true,
		'date_query'          => array(
			array(
				'column'    => 'post_date',
				'after'     => $start_mysql,
				'before'    => $end_mysql,
				'inclusive' => true,
			),
		),
	)
);

if (! $query->have_posts()) {
	return '<p>Infomail-Block: Keine Beiträge im gewählten Zeitraum gefunden.</p>';
}

$html = '<div class="infomail-post-list">';

while ($query->have_posts()) {
	$query->the_post();

	$post_id    = get_the_ID();
	$title      = get_the_title($post_id);
	$permalink  = get_permalink($post_id);
	$post       = get_post($post_id);
	$raw_content = $post ? $post->post_content : '';
	$categories = get_the_category($post_id);

	$extended    = get_extended($raw_content);
	$main_content = isset($extended['main']) ? $extended['main'] : '';
	$has_more     = ! empty($extended['extended']);

	$html .= '<article class="infomail-post">';
	$html .= '<h3 class="infomail-post-title">' . esc_html($title) . '</h3>';

	$known_categories = array_filter($categories, function($category) {
		return $category->term_id != 1; // Exclude "Uncategorized" category which usually has ID 1.
	});

	if(!empty($known_categories)) {
		$category_names = array_map(function($cat) {
			return esc_html($cat->name);
		}, $known_categories);
		$html .= '<p class="infomail-post-categories">' . implode(', ', $category_names) . '</p>';
	}
	
	if ('' !== trim($main_content)) {
		$html .= '<div class="infomail-post-content">' . apply_filters('the_content', $main_content) . '</div>';
	}

	if ($has_more) {
		$escaped_url = esc_url($permalink);
		$html       .= '<p class="infomail-post-more">Weiterlesen: <a href="' . $escaped_url . '">' . $escaped_url . '</a></p>';
	}

	$html .= '</article>';
}

$html .= '</div>';

wp_reset_postdata();

echo $html;
