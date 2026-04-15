<?php

if (! defined('ABSPATH')) {
	exit;
}

function evolua_post_author_meta_shortcode($atts = [])
{
	global $post;

	if (! $post instanceof WP_Post) {
		return '';
	}

	$atts = shortcode_atts([
		'class'         => 'evolua-post-author-meta',
		'role_meta_key' => 'cargo',
		'default_role'  => 'Cargo',
		'avatar_size'   => 160,
		'prefix'        => 'Por:',
	], $atts, 'post_author_meta');

	$author_id = (int) get_post_field('post_author', $post->ID);

	if ($author_id <= 0) {
		return '';
	}

	evolua_enqueue_post_author_meta_style();

	$author_name = get_the_author_meta('display_name', $author_id);
	$author_role = evolua_get_post_author_role($author_id, $atts['role_meta_key'], $atts['default_role']);
	$avatar_size = max(48, (int) $atts['avatar_size']);
	$avatar_url = get_avatar_url($author_id, ['size' => $avatar_size]);

	if (! $author_name) {
		$author_name = get_the_author_meta('user_nicename', $author_id);
	}

	ob_start();
?>
	<div class="<?php echo esc_attr($atts['class']); ?>">
		<div class="evolua-post-author-meta-card">
			<div class="evolua-post-author-meta-avatar-wrap">
				<img
					class="evolua-post-author-meta-avatar"
					src="<?php echo esc_url($avatar_url); ?>"
					alt="<?php echo esc_attr($author_name); ?>"
					width="<?php echo esc_attr($avatar_size); ?>"
					height="<?php echo esc_attr($avatar_size); ?>"
				>
			</div>

			<div class="evolua-post-author-meta-content">
				<div class="evolua-post-author-meta-name">
					<span class="evolua-post-author-meta-prefix"><?php echo esc_html($atts['prefix']); ?></span>
					<span class="evolua-post-author-meta-author"><?php echo esc_html($author_name); ?></span>
				</div>

				<div class="evolua-post-author-meta-role">
					<?php echo esc_html($author_role); ?>
				</div>
			</div>
		</div>
	</div>
<?php
	return ob_get_clean();
}
add_shortcode('post_author_meta', 'evolua_post_author_meta_shortcode');

function evolua_enqueue_post_author_meta_style()
{
	$css_file = EVOLUA_PLUGIN_PATH . 'assets/css/post-author-meta.css';
	$css_url = EVOLUA_PLUGIN_URL . 'assets/css/post-author-meta.css';

	if (! wp_style_is('evolua-post-author-meta', 'registered')) {
		wp_register_style(
			'evolua-post-author-meta',
			$css_url,
			[],
			file_exists($css_file) ? filemtime($css_file) : null
		);
	}

	wp_enqueue_style('evolua-post-author-meta');
}

function evolua_enqueue_post_author_meta_style_in_elementor()
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

	evolua_enqueue_post_author_meta_style();
}
add_action('wp_enqueue_scripts', 'evolua_enqueue_post_author_meta_style_in_elementor');
add_action('elementor/frontend/after_enqueue_styles', 'evolua_enqueue_post_author_meta_style_in_elementor');

function evolua_get_post_author_role($author_id, $meta_key, $default_role)
{
	$meta_key = is_string($meta_key) ? trim($meta_key) : '';
	$role = '';

	if ($meta_key !== '') {
		$role = get_the_author_meta($meta_key, $author_id);

		if (! $role && function_exists('get_field')) {
			$role = get_field($meta_key, 'user_' . $author_id);
		}
	}

	if (! $role) {
		$role = $default_role;
	}

	return is_string($role) ? $role : '';
}
