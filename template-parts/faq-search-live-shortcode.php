<?php

if (! defined('ABSPATH')) exit;

function evolua_register_faq_search_live_assets()
{
	$css_file = EVOLUA_PLUGIN_PATH . 'assets/css/faq-search-live.css';
	$css_url = EVOLUA_PLUGIN_URL . 'assets/css/faq-search-live.css';
	$css_version = file_exists($css_file) ? (string) filemtime($css_file) : '1.0.0';

	wp_register_style(
		'evolua-faq-search-live',
		$css_url,
		[],
		$css_version
	);
}
add_action('wp_enqueue_scripts', 'evolua_register_faq_search_live_assets');

function faq_search_live_shortcode()
{
	$args = [
		'post_type'      => 'faq',
		'posts_per_page' => -1,
	];

	$faq_query = new WP_Query($args);
	$faq_items = [];

	if ($faq_query->have_posts()) {
		while ($faq_query->have_posts()) {
			$faq_query->the_post();
			$faq_items[] = [
				'id'    => get_the_ID(),
				'title' => wp_strip_all_tags(get_the_title()),
				'url'   => get_permalink(),
			];
		}
	}

	wp_reset_postdata();

	wp_enqueue_style('evolua-faq-search-live');

	$wrapper_id = wp_unique_id('faq-search-wrapper-');
	$input_id = $wrapper_id . '-input';
	$results_id = $wrapper_id . '-results';

	ob_start();
?>
	<div id="<?php echo esc_attr($wrapper_id); ?>" class="faq-search-shortcode">
		<div class="faq-search-inputbox">
			<input
				id="<?php echo esc_attr($input_id); ?>"
				type="text"
				name="faq_search"
				placeholder="O que você está procurando? Pesquise aqui"
				autocomplete="off" />

			<button class="faq-search-btn-clear">
				<svg width="14" height="14" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M15.0244 15.0244L8.01221 8.01221M8.01221 8.01221L1 1M8.01221 8.01221L15.0244 1M8.01221 8.01221L1 15.0244" stroke="#FF6432" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</button>

			<button class="faq-search-btn-search"><img src="<?php echo home_url(); ?>/wp-content/uploads/2026/02/Vector.webp" alt=""></button>
		</div>

		<div id="<?php echo esc_attr($results_id); ?>" class="faq-search-results" hidden></div>
	</div>

	<script>
		(function() {
			const data = <?php echo wp_json_encode($faq_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
			const input = document.getElementById('<?php echo esc_js($input_id); ?>');
			const results = document.getElementById('<?php echo esc_js($results_id); ?>');
			const wrapper = document.getElementById('<?php echo esc_js($wrapper_id); ?>');
			const clearBtn = wrapper.querySelector('.faq-search-btn-clear');



			if (!input || !results || !wrapper || !Array.isArray(data)) return;

			let timer;

			function hideResults() {
				results.innerHTML = '';
				results.hidden = true;
			}

			function showEmpty() {
				results.innerHTML = '';
				const empty = document.createElement('div');
				empty.className = 'faq-search-result-empty';
				empty.textContent = 'Nenhum resultado';
				results.appendChild(empty);
				results.hidden = false;
			}

			function showItems(items) {
				results.innerHTML = '';

				items.forEach(function(item) {
					const link = document.createElement('a');
					link.className = 'faq-search-result-item';
					link.href = item.url;
					link.textContent = item.title;
					results.appendChild(link);
				});

				results.hidden = false;
			}

			function onSearchInput() {
				const term = input.value.trim().toLowerCase();

				if (term.length < 2) {
					hideResults();
					return;
				}

				const matched = data
					.filter(function(item) {
						return typeof item.title === 'string' && item.title.toLowerCase().includes(term);
					})
					.slice(0, 8);

				if (!matched.length) {
					showEmpty();
					return;
				}

				showItems(matched);
			}

			clearBtn.addEventListener('click', function() {
				input.value = '';
				hideResults();
			});

			input.addEventListener('input', function() {
				clearTimeout(timer);
				timer = setTimeout(onSearchInput, 200);
			});

			document.addEventListener('click', function(e) {
				if (wrapper.contains(e.target)) return;
				hideResults();
			});
		})();
	</script>

<?php
	return ob_get_clean();
}

add_shortcode('faq_search_live', 'faq_search_live_shortcode');
