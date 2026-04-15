<?php

if (! defined('ABSPATH')) {
	exit;
}

function evolua_post_breadcrumb_shortcode($atts = [])
{
	global $post;

	if (! $post instanceof WP_Post) {
		return '';
	}

	$atts = shortcode_atts([
		'blog_label' => 'Blog',
		'blog_url'   => get_permalink(get_option('page_for_posts')),
		'taxonomy'   => 'category',
		'separator'  => ' > ',
		'class'      => 'evolua-post-breadcrumb',
	], $atts, 'post_breadcrumb');

	$items = [];
	$blog_url = is_string($atts['blog_url']) ? trim($atts['blog_url']) : '';

	if ($blog_url === '') {
		$blog_url = home_url('/blog/');
	}

	$items[] = sprintf(
		'<a class="evolua-post-breadcrumb-link evolua-post-breadcrumb-blog-link" href="%s">%s</a>',
		esc_url($blog_url),
		esc_html($atts['blog_label'])
	);

	$terms = get_the_terms($post->ID, $atts['taxonomy']);

	if (! empty($terms) && ! is_wp_error($terms)) {
		$term = reset($terms);
		$term_link = get_term_link($term);

		if (! is_wp_error($term_link)) {
			$items[] = sprintf(
				'<a class="evolua-post-breadcrumb-link evolua-post-breadcrumb-category-link" href="%s">%s</a>',
				esc_url($term_link),
				esc_html($term->name)
			);
		} else {
			$items[] = sprintf(
				'<span class="evolua-post-breadcrumb-category-text">%s</span>',
				esc_html($term->name)
			);
		}
	}

	$items[] = sprintf(
		'<span class="evolua-post-breadcrumb-current" aria-current="page">%s</span>',
		esc_html(get_the_title($post))
	);

	$classes = [
		'evolua-post-breadcrumb-blog',
		'evolua-post-breadcrumb-category',
		'evolua-post-breadcrumb-title',
	];

	$breadcrumb_items = [];

	foreach ($items as $index => $item) {
		$item_class = $classes[$index] ?? 'evolua-post-breadcrumb-item';

		$breadcrumb_items[] = sprintf(
			'<span class="evolua-post-breadcrumb-item %s">%s</span>',
			esc_attr($item_class),
			$item
		);
	}

	$separator = sprintf(
		'<span class="evolua-post-breadcrumb-separator">%s</span>',
		esc_html($atts['separator'])
	);

	return sprintf(
		'<nav class="%s" aria-label="%s"><span class="evolua-post-breadcrumb-list">%s</span></nav>',
		esc_attr($atts['class']),
		esc_attr__('Breadcrumb', 'evolua'),
		implode($separator, $breadcrumb_items)
	);
}
add_shortcode('post_breadcrumb', 'evolua_post_breadcrumb_shortcode');
