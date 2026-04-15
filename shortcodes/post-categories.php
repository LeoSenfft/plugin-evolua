<?php

if (! defined('ABSPATH')) {
	exit;
}

function evolua_post_categories_shortcode($atts = [])
{
	global $post;

	if (! $post instanceof WP_Post) {
		return '';
	}

	$atts = shortcode_atts([
		'taxonomy'   => 'category',
		'separator'  => ', ',
		'linked'     => 'yes',
		'class'      => 'evolua-post-categories',
		'icon_field' => 'icone',
	], $atts, 'post_categories');

	$terms = get_the_terms($post->ID, $atts['taxonomy']);

	if (empty($terms) || is_wp_error($terms)) {
		return '';
	}

	$items = [];

	foreach ($terms as $term) {
		$label = esc_html($term->name);
		$icon_markup = '';
		$term_classes = ['evolua-post-category-item'];

		if (function_exists('get_field')) {
			$icon_value = get_field($atts['icon_field'], $term->taxonomy . '_' . $term->term_id);

			if (! empty($icon_value)) {
				$term_classes[] = 'has-icon';

				if (is_array($icon_value) && ! empty($icon_value['url'])) {
					$icon_markup = evolua_render_term_icon_markup($icon_value['url'], $term->name);
				} elseif (is_string($icon_value) && filter_var($icon_value, FILTER_VALIDATE_URL)) {
					$icon_markup = evolua_render_term_icon_markup($icon_value, $term->name);
				} else {
					$icon_markup = sprintf(
						'<span class="evolua-post-category-icon">%s</span>',
						wp_kses_post($icon_value)
					);
				}
			}
		}

		$label = sprintf(
			'<span class="%s">%s<span class="evolua-post-category-text">%s</span></span>',
			esc_attr(implode(' ', $term_classes)),
			$icon_markup,
			$label
		);

		if ('yes' === strtolower($atts['linked'])) {
			$term_link = get_term_link($term);

			if (! is_wp_error($term_link)) {
				$label = sprintf(
					'<a href="%s">%s</a>',
					esc_url($term_link),
					$label
				);
			}
		}

		$items[] = $label;
	}

	return sprintf(
		'<div class="%s evolua-post-categories-wrapper">%s</div>',
		esc_attr($atts['class']),
		implode(wp_kses_post($atts['separator']), $items)
	);
}
add_shortcode('post_categories', 'evolua_post_categories_shortcode');

function evolua_render_term_icon_markup($icon_url, $term_name = '')
{
	$icon_url = is_string($icon_url) ? trim($icon_url) : '';

	if ($icon_url === '') {
		return '';
	}

	if (strtolower(pathinfo(parse_url($icon_url, PHP_URL_PATH), PATHINFO_EXTENSION)) === 'svg') {
		$svg_markup = evolua_get_inline_svg_markup($icon_url);

		if ($svg_markup !== '') {
			return sprintf(
				'<span class="evolua-post-category-icon evolua-post-category-icon-svg" aria-hidden="true">%s</span>',
				$svg_markup
			);
		}
	}

	return sprintf(
		'<img class="evolua-post-category-icon" src="%s" alt="%s">',
		esc_url($icon_url),
		esc_attr($term_name)
	);
}

function evolua_get_inline_svg_markup($icon_url)
{
	$svg_markup = '';
	$uploads = wp_upload_dir();

	if (
		! empty($uploads['baseurl']) &&
		! empty($uploads['basedir']) &&
		strpos($icon_url, $uploads['baseurl']) === 0
	) {
		$file_path = wp_normalize_path(
			str_replace($uploads['baseurl'], $uploads['basedir'], $icon_url)
		);

		if (file_exists($file_path) && is_readable($file_path)) {
			$svg_markup = file_get_contents($file_path);
		}
	}

	if ($svg_markup === '') {
		$response = wp_remote_get($icon_url, ['timeout' => 10]);

		if (! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$svg_markup = wp_remote_retrieve_body($response);
		}
	}

	if (! is_string($svg_markup) || trim($svg_markup) === '') {
		return '';
	}

	$allowed_svg_tags = [
		'svg' => [
			'class' => true,
			'xmlns' => true,
			'width' => true,
			'height' => true,
			'viewbox' => true,
			'viewBox' => true,
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'role' => true,
			'aria-hidden' => true,
			'focusable' => true,
			'preserveAspectRatio' => true,
		],
		'g' => [
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'transform' => true,
			'clip-path' => true,
			'mask' => true,
			'opacity' => true,
		],
		'path' => [
			'd' => true,
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'transform' => true,
			'opacity' => true,
			'fill-rule' => true,
			'clip-rule' => true,
		],
		'circle' => [
			'cx' => true,
			'cy' => true,
			'r' => true,
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'opacity' => true,
		],
		'rect' => [
			'x' => true,
			'y' => true,
			'rx' => true,
			'ry' => true,
			'width' => true,
			'height' => true,
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'opacity' => true,
			'transform' => true,
		],
		'line' => [
			'x1' => true,
			'x2' => true,
			'y1' => true,
			'y2' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'opacity' => true,
		],
		'polyline' => [
			'points' => true,
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'opacity' => true,
		],
		'polygon' => [
			'points' => true,
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'opacity' => true,
		],
		'defs' => [],
		'clippath' => [
			'id' => true,
		],
		'clipPath' => [
			'id' => true,
		],
		'mask' => [
			'id' => true,
			'x' => true,
			'y' => true,
			'width' => true,
			'height' => true,
			'maskUnits' => true,
			'maskContentUnits' => true,
		],
		'title' => [],
		'desc' => [],
		'use' => [
			'href' => true,
			'xlink:href' => true,
		],
	];

	return wp_kses($svg_markup, $allowed_svg_tags);
}
