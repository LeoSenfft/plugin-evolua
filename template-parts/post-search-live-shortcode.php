<?php

if (! defined('ABSPATH')) exit;

function evolua_post_search_ajax()
{
	$term = isset($_REQUEST['term'])
		? sanitize_text_field(wp_unslash($_REQUEST['term']))
		: '';

	if (strlen($term) < 2) {
		wp_send_json_success([]);
	}

	$post_query = new WP_Query([
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		's'              => $term,
		'no_found_rows'  => true,
	]);

	$items = [];

	if ($post_query->have_posts()) {
		while ($post_query->have_posts()) {
			$post_query->the_post();
			$items[] = [
				'title' => wp_strip_all_tags(get_the_title()),
				'url'   => get_permalink(),
			];
		}
	}

	wp_reset_postdata();

	wp_send_json_success($items);
}
add_action('wp_ajax_evolua_post_search', 'evolua_post_search_ajax');
add_action('wp_ajax_nopriv_evolua_post_search', 'evolua_post_search_ajax');

function evolua_register_post_search_live_assets()
{
	$css_file = EVOLUA_PLUGIN_PATH . 'assets/css/post-search-live.css';
	$css_url = EVOLUA_PLUGIN_URL . 'assets/css/post-search-live.css';
	$css_version = file_exists($css_file) ? (string) filemtime($css_file) : '1.0.0';

	wp_register_style(
		'evolua-post-search-live',
		$css_url,
		[],
		$css_version
	);
}
add_action('wp_enqueue_scripts', 'evolua_register_post_search_live_assets');

function post_search_shortcode()
{
	wp_enqueue_style('evolua-post-search-live');

	$wrapper_id = wp_unique_id('post-search-wrapper-');
	$input_id = $wrapper_id . '-input';
	$results_id = $wrapper_id . '-results';
	$ajax_url = admin_url('admin-ajax.php');

	ob_start();
?>
	<div id="<?php echo esc_attr($wrapper_id); ?>" class="post-search-shortcode">
		<div class="post-search-inputbox">
			<button class="post-search-btn-search" type="button"><img src="<?php echo esc_url(home_url('/wp-content/uploads/2026/02/Vector.webp')); ?>" alt=""></button>

			<input
				id="<?php echo esc_attr($input_id); ?>"
				type="text"
				name="post_search"
				placeholder="O que voc&ecirc; est&aacute; procurando? Pesquise aqui"
				autocomplete="off" />

			<button class="post-search-btn-clear" type="button">
				<svg width="14" height="14" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M15.0244 15.0244L8.01221 8.01221M8.01221 8.01221L1 1M8.01221 8.01221L15.0244 1M8.01221 8.01221L1 15.0244" stroke="#FF6432" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</button>


		</div>

		<div id="<?php echo esc_attr($results_id); ?>" class="post-search-results" hidden></div>
	</div>

	<script>
		(function() {
			const input = document.getElementById('<?php echo esc_js($input_id); ?>');
			const results = document.getElementById('<?php echo esc_js($results_id); ?>');
			const wrapper = document.getElementById('<?php echo esc_js($wrapper_id); ?>');
			const ajaxUrl = '<?php echo esc_js($ajax_url); ?>';
			const clearBtn = wrapper ? wrapper.querySelector('.post-search-btn-clear') : null;
			const searchBtn = wrapper ? wrapper.querySelector('.post-search-btn-search') : null;

			if (!input || !results || !wrapper || !clearBtn || !searchBtn) return;

			let timer;
			let controller;

			function hideResults() {
				results.innerHTML = '';
				results.hidden = true;
			}

			function showLoading() {
				results.innerHTML = '';
				const loading = document.createElement('div');
				loading.className = 'post-search-result-empty';
				loading.textContent = 'Buscando...';
				results.appendChild(loading);
				results.hidden = false;
			}

			function showEmpty() {
				results.innerHTML = '';
				const empty = document.createElement('div');
				empty.className = 'post-search-result-empty';
				empty.textContent = 'Nenhum resultado';
				results.appendChild(empty);
				results.hidden = false;
			}

			function showItems(items) {
				results.innerHTML = '';

				items.forEach(function(item) {
					const link = document.createElement('a');
					link.className = 'post-search-result-item';
					link.href = item.url;
					link.textContent = item.title;
					results.appendChild(link);
				});

				results.hidden = false;
			}

			function searchPosts() {
				const term = input.value.trim();

				if (term.length < 2) {
					hideResults();
					return;
				}

				if (controller) {
					controller.abort();
				}

				controller = new AbortController();
				showLoading();

				const body = new URLSearchParams();
				body.append('action', 'evolua_post_search');
				body.append('term', term);

				fetch(ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
						},
						body: body.toString(),
						signal: controller.signal,
					})
					.then(function(response) {
						return response.json();
					})
					.then(function(response) {
						if (!response || !response.success || !Array.isArray(response.data)) {
							showEmpty();
							return;
						}

						if (!response.data.length) {
							showEmpty();
							return;
						}

						showItems(response.data);
					})
					.catch(function(error) {
						if (error.name === 'AbortError') return;
						showEmpty();
					});
			}

			clearBtn.addEventListener('click', function() {
				input.value = '';
				hideResults();
			});

			input.addEventListener('input', function() {
				clearTimeout(timer);
				timer = setTimeout(searchPosts, 200);
			});

			searchBtn.addEventListener('click', searchPosts);

			document.addEventListener('click', function(e) {
				if (wrapper.contains(e.target)) return;
				hideResults();
			});
		})();
	</script>

<?php
	return ob_get_clean();
}

add_shortcode('post_search', 'post_search_shortcode');
