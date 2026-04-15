<?php

if (! defined('ABSPATH')) {
	exit;
}

function evolua_post_related_articles_shortcode($atts = [])
{
	global $post;

	if (! $post instanceof WP_Post) {
		return '';
	}

	$atts = shortcode_atts([
		'posts_per_page' => 4,
		'class'          => 'evolua-related-posts',
	], $atts, 'post_related_articles');

	$post_id = (int) $post->ID;
	$categories = get_the_category($post_id);

	if (empty($categories)) {
		return '';
	}

	$primary_category = $categories[0];
	$posts_per_page = max(1, (int) $atts['posts_per_page']);

	$related_query = new WP_Query([
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $posts_per_page,
		'cat'                 => (int) $primary_category->term_id,
		'post__not_in'        => [$post_id],
		'ignore_sticky_posts' => true,
	]);

	if (! $related_query->have_posts()) {
		wp_reset_postdata();

		return '';
	}

	evolua_enqueue_post_related_articles_styles();

	ob_start();
?>
	<section class="<?php echo esc_attr($atts['class']); ?>">
		<div class="evolua-related-posts__grid">
			<?php
			while ($related_query->have_posts()) :
				$related_query->the_post();

				$card_post_id = get_the_ID();
				$categories = get_the_category($card_post_id);
				$primary_category = ! empty($categories) ? $categories[0] : null;
				$category_icon = $primary_category && function_exists('evolua_post_categories_section_get_term_icon')
					? evolua_post_categories_section_get_term_icon($primary_category)
					: '';
				$excerpt = has_excerpt($card_post_id)
					? get_the_excerpt($card_post_id)
					: wp_trim_words(wp_strip_all_tags(get_the_content(null, false, $card_post_id)), 18, '...');

				include EVOLUA_PLUGIN_PATH . 'template-parts/post-card.php';
			endwhile;
			?>
		</div>
	</section>
<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode('post_related_articles', 'evolua_post_related_articles_shortcode');

function evolua_enqueue_post_related_articles_styles()
{
	$card_css_file = EVOLUA_PLUGIN_PATH . 'assets/css/post-categories-section.css';
	$card_css_url = EVOLUA_PLUGIN_URL . 'assets/css/post-categories-section.css';
	$related_css_file = EVOLUA_PLUGIN_PATH . 'assets/css/post-related-articles.css';
	$related_css_url = EVOLUA_PLUGIN_URL . 'assets/css/post-related-articles.css';

	if (! wp_style_is('evolua-post-categories-section', 'registered')) {
		wp_register_style(
			'evolua-post-categories-section',
			$card_css_url,
			[],
			file_exists($card_css_file) ? filemtime($card_css_file) : null
		);
	}

	if (! wp_style_is('evolua-post-related-articles', 'registered')) {
		wp_register_style(
			'evolua-post-related-articles',
			$related_css_url,
			['evolua-post-categories-section'],
			file_exists($related_css_file) ? filemtime($related_css_file) : null
		);
	}

	wp_enqueue_style('evolua-post-categories-section');
	wp_enqueue_style('evolua-post-related-articles');
}

function evolua_enqueue_post_related_articles_styles_in_elementor()
{
	if (! class_exists('\Elementor\Plugin')) {
		return;
	}

	$elementor = \Elementor\Plugin::$instance;
	$is_preview = isset($elementor->preview) && $elementor->preview->is_preview_mode();
	$is_edit_mode = isset($elementor->editor) && $elementor->editor->is_edit_mode();

	if (! $is_preview && ! $is_edit_mode) {
		return;
	}

	evolua_enqueue_post_related_articles_styles();
}
add_action('wp_enqueue_scripts', 'evolua_enqueue_post_related_articles_styles_in_elementor');
add_action('elementor/frontend/after_enqueue_styles', 'evolua_enqueue_post_related_articles_styles_in_elementor');
