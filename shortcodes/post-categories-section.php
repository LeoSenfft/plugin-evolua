<?php

if (! defined('ABSPATH')) {
	exit;
}

function evolua_register_post_categories_section_assets()
{
	$css_file = EVOLUA_PLUGIN_PATH . 'assets/css/post-categories-section.css';
	$css_url = EVOLUA_PLUGIN_URL . 'assets/css/post-categories-section.css';
	$css_version = file_exists($css_file) ? (string) filemtime($css_file) : '1.0.0';

	wp_register_style(
		'evolua-post-categories-section',
		$css_url,
		[],
		$css_version
	);
}
add_action('wp_enqueue_scripts', 'evolua_register_post_categories_section_assets');

function evolua_post_categories_section_query_args($category = 0, $page = 1)
{
	$args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'paged'          => max(1, (int) $page),
	];

	$category = (int) $category;

	if ($category > 0) {
		$args['cat'] = $category;
	}

	return $args;
}

function evolua_post_categories_section_get_term_icon($term)
{
	if (! $term instanceof WP_Term || ! function_exists('get_field') || ! function_exists('evolua_render_term_icon_markup')) {
		return '';
	}

	$icon_value = get_field('icone', $term->taxonomy . '_' . $term->term_id);

	if (empty($icon_value)) {
		return '';
	}

	if (is_array($icon_value) && ! empty($icon_value['url'])) {
		return evolua_render_term_icon_markup($icon_value['url'], $term->name);
	}

	if (is_numeric($icon_value)) {
		$icon_url = wp_get_attachment_url((int) $icon_value);

		return $icon_url ? evolua_render_term_icon_markup($icon_url, $term->name) : '';
	}

	if (is_string($icon_value) && filter_var($icon_value, FILTER_VALIDATE_URL)) {
		return evolua_render_term_icon_markup($icon_value, $term->name);
	}

	return sprintf(
		'<span class="evolua-post-category-icon">%s</span>',
		wp_kses_post($icon_value)
	);
}

function evolua_post_categories_section_is_uncategorized($term)
{
	if (! $term instanceof WP_Term) {
		return false;
	}

	$default_category = (int) get_option('default_category');

	if ($default_category > 0 && (int) $term->term_id === $default_category) {
		return true;
	}

	return $term->slug === 'sem-categoria' || $term->slug === 'uncategorized';
}

function evolua_post_categories_section_render_cards(WP_Query $post_query)
{
	ob_start();

	if ($post_query->have_posts()) {
		while ($post_query->have_posts()) {
			$post_query->the_post();

			$post_id = get_the_ID();
			$categories = get_the_category($post_id);
			$primary_category = ! empty($categories) ? $categories[0] : null;
			$category_icon = $primary_category ? evolua_post_categories_section_get_term_icon($primary_category) : '';
			$excerpt = has_excerpt($post_id)
				? get_the_excerpt($post_id)
				: wp_trim_words(wp_strip_all_tags(get_the_content(null, false, $post_id)), 18, '...');

			include EVOLUA_PLUGIN_PATH . 'template-parts/post-card.php';
		}
	}

	wp_reset_postdata();

	return ob_get_clean();
}

function evolua_post_categories_section_ajax()
{
	check_ajax_referer('evolua_post_categories_section', 'nonce');

	$category = isset($_POST['category']) ? absint($_POST['category']) : 0;
	$page = isset($_POST['page']) ? absint($_POST['page']) : 1;

	$post_query = new WP_Query(evolua_post_categories_section_query_args($category, $page));
	$html = evolua_post_categories_section_render_cards($post_query);
	$has_more = $page < (int) $post_query->max_num_pages;

	wp_send_json_success([
		'html'      => $html,
		'next_page' => $page + 1,
		'has_more'  => $has_more,
	]);
}
add_action('wp_ajax_evolua_post_categories_section_load_more', 'evolua_post_categories_section_ajax');
add_action('wp_ajax_nopriv_evolua_post_categories_section_load_more', 'evolua_post_categories_section_ajax');

function evolua_post_categories_section_shortcode()
{
	wp_enqueue_style('evolua-post-categories-section');

	$terms = get_terms([
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	]);

	if (! empty($terms) && ! is_wp_error($terms)) {
		$terms = array_values(array_filter($terms, function ($term) {
			return ! evolua_post_categories_section_is_uncategorized($term);
		}));
	}

	$initial_query = new WP_Query(evolua_post_categories_section_query_args(0, 1));
	$initial_html = evolua_post_categories_section_render_cards($initial_query);
	$has_more = 1 < (int) $initial_query->max_num_pages;
	$wrapper_id = wp_unique_id('post-categories-section-');
	$grid_id = $wrapper_id . '-grid';
	$empty_id = $wrapper_id . '-empty';
	$nonce = wp_create_nonce('evolua_post_categories_section');
	$ajax_url = admin_url('admin-ajax.php');

	ob_start();
?>
	<section id="<?php echo esc_attr($wrapper_id); ?>" class="evolua-posts-section" data-next-page="2" data-category="0">
		<div class="evolua-posts-section__filters" aria-label="Categorias de posts">
			<button class="evolua-posts-section__filter is-active" type="button" data-category="0">
				Todos
			</button>

			<?php if (! empty($terms) && ! is_wp_error($terms)) : ?>
				<?php foreach ($terms as $term) : ?>
					<button class="evolua-posts-section__filter" type="button" data-category="<?php echo esc_attr($term->term_id); ?>">
						<?php echo evolua_post_categories_section_get_term_icon($term); ?>
						<?php echo esc_html($term->name); ?>
					</button>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div id="<?php echo esc_attr($grid_id); ?>" class="evolua-posts-section__grid">
			<?php echo $initial_html; ?>
		</div>

		<p id="<?php echo esc_attr($empty_id); ?>" class="evolua-posts-section__empty" <?php echo $initial_html ? 'hidden' : ''; ?>>
			Nenhum post encontrado.
		</p>

		<div class="evolua-posts-section__actions">
			<button class="evolua-posts-section__load-more" type="button" <?php echo $has_more ? '' : 'hidden'; ?>>
				Ver mais
			</button>
		</div>
	</section>

	<script>
		(function() {
			const wrapper = document.getElementById('<?php echo esc_js($wrapper_id); ?>');
			const grid = document.getElementById('<?php echo esc_js($grid_id); ?>');
			const empty = document.getElementById('<?php echo esc_js($empty_id); ?>');
			const loadMore = wrapper ? wrapper.querySelector('.evolua-posts-section__load-more') : null;
			const filters = wrapper ? wrapper.querySelectorAll('.evolua-posts-section__filter') : [];
			const ajaxUrl = '<?php echo esc_js($ajax_url); ?>';
			const nonce = '<?php echo esc_js($nonce); ?>';

			if (!wrapper || !grid || !empty || !loadMore) return;

			let category = 0;
			let nextPage = 2;
			let loading = false;

			function setLoading(isLoading, mode) {
				loading = isLoading;
				wrapper.classList.toggle('is-loading', isLoading);
				wrapper.classList.toggle('is-replacing', isLoading && mode === 'replace');
				loadMore.disabled = isLoading;
				loadMore.textContent = isLoading ? 'Carregando...' : 'Ver mais';
			}

			function updateEmptyState() {
				empty.hidden = grid.children.length > 0;
			}

			function requestPosts(requestedCategory, page, mode) {
				if (loading) return;

				setLoading(true, mode);

				const body = new URLSearchParams();
				body.append('action', 'evolua_post_categories_section_load_more');
				body.append('nonce', nonce);
				body.append('category', requestedCategory);
				body.append('page', page);

				fetch(ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
						},
						body: body.toString(),
					})
					.then(function(response) {
						return response.json();
					})
					.then(function(response) {
						if (!response || !response.success || !response.data) {
							throw new Error('Invalid response');
						}

						if (mode === 'replace') {
							grid.innerHTML = response.data.html || '';
						} else {
							grid.insertAdjacentHTML('beforeend', response.data.html || '');
						}

						nextPage = response.data.next_page || (page + 1);
						loadMore.hidden = !response.data.has_more;
						updateEmptyState();
					})
					.catch(function() {
						if (mode === 'replace') {
							grid.innerHTML = '';
							updateEmptyState();
						}
					})
					.finally(function() {
						setLoading(false, mode);
					});
			}

			loadMore.addEventListener('click', function() {
				requestPosts(category, nextPage, 'append');
			});

			filters.forEach(function(filter) {
				filter.addEventListener('click', function() {
					const selectedCategory = parseInt(filter.getAttribute('data-category'), 10) || 0;

					if (selectedCategory === category && filter.classList.contains('is-active')) return;

					category = selectedCategory;
					nextPage = 2;

					filters.forEach(function(item) {
						item.classList.remove('is-active');
					});

					filter.classList.add('is-active');
					loadMore.hidden = true;
					requestPosts(category, 1, 'replace');
				});
			});
		})();
	</script>
<?php
	return ob_get_clean();
}
add_shortcode('post_categories_section', 'evolua_post_categories_section_shortcode');
