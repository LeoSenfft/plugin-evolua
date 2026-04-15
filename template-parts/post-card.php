<?php

if (! defined('ABSPATH')) {
	exit;
}

$post_id = get_the_ID();
$primary_category = isset($primary_category) && $primary_category instanceof WP_Term ? $primary_category : null;
$categories = isset($categories) && is_array($categories) ? $categories : [];
$category_icon = isset($category_icon) ? $category_icon : '';
$excerpt = isset($excerpt) ? $excerpt : get_the_excerpt($post_id);
?>

<article class="evolua-post-card">
	<div class="evolua-post-card__thumb">
		<?php if (has_post_thumbnail($post_id)) : ?>
			<?php echo get_the_post_thumbnail($post_id, 'medium_large', ['class' => 'evolua-post-card__image']); ?>
		<?php else : ?>
			<div class="evolua-post-card__placeholder" aria-hidden="true"></div>
		<?php endif; ?>
	</div>

	<div class="evolua-post-card__body">
		<?php if (! empty($categories)) : ?>
			<div class="evolua-post-card__categories" aria-label="Categorias do post">
				<?php foreach ($categories as $category) : ?>
					<?php
					if (! $category instanceof WP_Term) {
						continue;
					}

					$category_link = get_term_link($category);

					if (is_wp_error($category_link)) {
						continue;
					}

					$current_category_icon = function_exists('evolua_post_categories_section_get_term_icon')
						? evolua_post_categories_section_get_term_icon($category)
						: '';
					?>
					<div class="evolua-post-card__category">
						<?php echo $current_category_icon; ?>
						<span><?php echo esc_html($category->name); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<h3 class="evolua-post-card__title">
			<a href="<?php echo esc_url(get_permalink($post_id)); ?>">
				<?php echo esc_html(get_the_title($post_id)); ?>
			</a>
		</h3>

		<?php if (! empty($excerpt)) : ?>
			<p class="evolua-post-card__excerpt">
				<?php echo esc_html(wp_trim_words(wp_strip_all_tags($excerpt), 18, '...')); ?>
			</p>
		<?php endif; ?>

		<a class="evolua-post-card__read-more" href="<?php echo esc_url(get_permalink($post_id)); ?>">Leia mais</a>
	</div>
</article>