<?php

if (! defined('ABSPATH')) {
	exit;
}

function evolua_post_category_icon_shortcode($atts = [])
{
	global $post;

	if (! $post instanceof WP_Post || ! function_exists('get_field')) {
		return '';
	}

	$atts = shortcode_atts([
		'taxonomy'   => 'category',
		'icon_field' => 'icone',
		'class'      => 'evolua-post-category-icon-shortcode',
	], $atts, 'post_category_icon');

	$terms = get_the_terms($post->ID, $atts['taxonomy']);

	if (empty($terms) || is_wp_error($terms)) {
		return '';
	}

	$term = evolua_post_category_icon_get_first_visible_term($terms);

	if (! $term instanceof WP_Term) {
		return '';
	}

	$icon_value = get_field($atts['icon_field'], $term->taxonomy . '_' . $term->term_id);
	$icon_markup = evolua_post_category_icon_render($icon_value, $term->name);

	if ($icon_markup === '') {
		return '';
	}

	return sprintf(
		'<span class="%s" aria-label="%s">%s</span>',
		esc_attr($atts['class']),
		esc_attr($term->name),
		$icon_markup
	);
}
add_shortcode('post_category_icon', 'evolua_post_category_icon_shortcode');

function evolua_post_category_icon_get_first_visible_term($terms)
{
	foreach ($terms as $term) {
		if (! $term instanceof WP_Term) {
			continue;
		}

		if (function_exists('evolua_post_categories_section_is_uncategorized') && evolua_post_categories_section_is_uncategorized($term)) {
			continue;
		}

		if ($term->slug === 'sem-categoria' || $term->slug === 'uncategorized') {
			continue;
		}

		return $term;
	}

	return null;
}

function evolua_post_category_icon_render($icon_value, $term_name = '')
{
	if (empty($icon_value)) {
		return '';
	}

	if (is_array($icon_value) && ! empty($icon_value['url'])) {
		return evolua_post_category_icon_render_url($icon_value['url'], $term_name);
	}

	if (is_numeric($icon_value)) {
		$icon_url = wp_get_attachment_url((int) $icon_value);

		return $icon_url ? evolua_post_category_icon_render_url($icon_url, $term_name) : '';
	}

	if (is_string($icon_value) && filter_var($icon_value, FILTER_VALIDATE_URL)) {
		return evolua_post_category_icon_render_url($icon_value, $term_name);
	}

	if (is_string($icon_value)) {
		return sprintf(
			'<span class="evolua-post-category-icon" aria-hidden="true">%s</span>',
			wp_kses_post($icon_value)
		);
	}

	return '';
}

function evolua_post_category_icon_render_url($icon_url, $term_name = '')
{
	if (function_exists('evolua_render_term_icon_markup')) {
		return evolua_render_term_icon_markup($icon_url, $term_name);
	}

	return sprintf(
		'<img class="evolua-post-category-icon" src="%s" alt="%s">',
		esc_url($icon_url),
		esc_attr($term_name)
	);
}
