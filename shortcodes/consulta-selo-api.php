<?php

if (! defined('ABSPATH')) {
	exit;
}


function evolua_enqueue_consulta_selo_style()
{
	$css_file = EVOLUA_PLUGIN_PATH . 'assets/css/consulta-selo-api.css';
	$css_url = EVOLUA_PLUGIN_URL . 'assets/css/consulta-selo-api.css';

	if (! wp_style_is('evolua-consulta-selo-api', 'registered')) {
		wp_register_style(
			'evolua-consulta-selo-api',
			$css_url,
			[],
			file_exists($css_file) ? filemtime($css_file) : null
		);
	}

	wp_enqueue_style('evolua-consulta-selo-api');
}

function evolua_enqueue_consulta_selo_style_in_elementor()
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

	evolua_enqueue_consulta_selo_style();
}
add_action('wp_enqueue_scripts', 'evolua_enqueue_consulta_selo_style_in_elementor');
add_action('elementor/frontend/after_enqueue_styles', 'evolua_enqueue_consulta_selo_style_in_elementor');


function evola_consulta_selo_api_request($consulta)
{
	$cnpj = preg_replace('/\D+/', '', $consulta);

	if ($cnpj === '') {
		return [
			'success' => false,
			'message' => 'Informe um CNPJ valido para consulta.',
		];
	}

	$endpoint = add_query_arg([
		'cnpj' => $cnpj,
		'view' => 'aggregate',
	], 'https://evolua--homolog.sandbox.my.salesforce-sites.com/services/apexrest/Ecology');

	$response = wp_remote_get($endpoint, [
		'timeout' => 15,
		'headers' => [
			'Accept' => 'application/json',
		],
	]);

	if (is_wp_error($response)) {
		return [
			'success' => false,
			'message' => 'Erro ao conectar na API externa: ' . $response->get_error_message(),
		];
	}

	$status_code = wp_remote_retrieve_response_code($response);
	$response_body = wp_remote_retrieve_body($response);
	$response_data = json_decode($response_body, true);

	return [
		'success' => $status_code >= 200 && $status_code < 300,
		'status_code' => $status_code,
		'body' => $response_body,
		'data' => is_array($response_data) ? $response_data : null,
		'message' => $status_code >= 200 && $status_code < 300
			? 'Consulta realizada com sucesso.'
			: 'A API externa retornou um erro.',
	];
}

function evola_consulta_selo_api_ajax()
{
	check_ajax_referer('evola_consulta_selo_api', 'nonce');

	$consulta = isset($_POST['consulta']) ? sanitize_text_field(wp_unslash($_POST['consulta'])) : '';

	if ($consulta === '') {
		wp_send_json_error([
			'message' => 'Informe um valor para consulta.',
		], 400);
	}

	$result = evola_consulta_selo_api_request($consulta);

	if (empty($result['success'])) {
		wp_send_json_error([
			'message' => $result['message'],
			'status_code' => $result['status_code'] ?? null,
			'body' => $result['body'] ?? null,
		]);
	}

	wp_send_json_success([
		'message' => $result['message'],
		'status_code' => $result['status_code'],
		'body' => $result['body'],
		'data' => $result['data'],
	]);
}
add_action('wp_ajax_evola_consulta_selo_api', 'evola_consulta_selo_api_ajax');
add_action('wp_ajax_nopriv_evola_consulta_selo_api', 'evola_consulta_selo_api_ajax');

function evola_consulta_selo_api_shortcode()
{
	$form_id = wp_unique_id('evola-consulta-selo-api-');
	$nonce = wp_create_nonce('evola_consulta_selo_api');
	$ajax_url = admin_url('admin-ajax.php');

	ob_start();
?>
	<form id="<?php echo esc_attr($form_id); ?>" class="evola-consulta-selo-api-form">
		<div class="input-box">

			<input
				id="<?php echo esc_attr($form_id); ?>-consulta"
				name="consulta"
				type="text"
				placeholder="Insira aqui o CNPJ"
				class="input-consulta"
				required>

			<button class="btn-submit" type="submit">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M17.9833 19.5L11.1583 12.675C10.6167 13.1083 9.99375 13.4514 9.28958 13.7042C8.58542 13.9569 7.83611 14.0833 7.04167 14.0833C5.07361 14.0833 3.40817 13.4016 2.04533 12.038C0.682501 10.6744 0.000722795 9.009 5.73192e-07 7.04167C-0.000721649 5.07433 0.681056 3.40889 2.04533 2.04533C3.40961 0.681778 5.07506 0 7.04167 0C9.00828 0 10.6741 0.681778 12.0391 2.04533C13.4041 3.40889 14.0855 5.07433 14.0833 7.04167C14.0833 7.83611 13.9569 8.58542 13.7042 9.28958C13.4514 9.99375 13.1083 10.6167 12.675 11.1583L19.5 17.9833L17.9833 19.5ZM7.04167 11.9167C8.39583 11.9167 9.54706 11.4429 10.4953 10.4953C11.4436 9.54778 11.9174 8.39656 11.9167 7.04167C11.9159 5.68678 11.4422 4.53592 10.4953 3.58908C9.5485 2.64225 8.39728 2.16811 7.04167 2.16667C5.68606 2.16522 4.5352 2.63936 3.58908 3.58908C2.64297 4.53881 2.16883 5.68967 2.16667 7.04167C2.1645 8.39367 2.63864 9.54489 3.58908 10.4953C4.53953 11.4458 5.69039 11.9196 7.04167 11.9167Z" fill="currentColor" />
				</svg>

			</button>
		</div>

		<div class="evola-consulta-selo-api-message" role="status" aria-live="polite"></div>
	</form>

	<script>
		(function() {
			const form = document.getElementById('<?php echo esc_js($form_id); ?>');

			if (!form) return;

			const input = form.querySelector('[name="consulta"]');
			const button = form.querySelector('button[type="submit"]');
			const message = form.querySelector('.evola-consulta-selo-api-message');
			const ajaxUrl = '<?php echo esc_js($ajax_url); ?>';
			const nonce = '<?php echo esc_js($nonce); ?>';

			function setMessage(text, type) {
				message.textContent = text;
				message.dataset.type = type || '';
			}

			function setElementText(selector, value) {
				const element = document.querySelector(selector);

				if (!element || value === null || typeof value === 'undefined') return;

				element.textContent = value;
			}

			function formatDate(value) {
				if (!value) return value;

				const normalizedValue = value.replace(' ', 'T');
				const date = new Date(normalizedValue);

				if (Number.isNaN(date.getTime())) return value;

				return date.toLocaleDateString('pt-BR');
			}

			function formatNumber(value) {
				const number = Number(value);

				if (Number.isNaN(number)) return value;

				return number.toLocaleString('pt-BR');
			}

			function updateCompanyData(data) {
				if (!data) return;

				setElementText('#status-empresa .elementor-icon-box-description', data.status);
				setElementText('#nome-empresa .elementor-heading-title', data.name);
				setElementText('#data-empresa', formatDate(data.helpingSince));
				setElementText('#co2 .elementor-heading-title', formatNumber(data.co2Reduction));
				setElementText('#arvores .elementor-heading-title', formatNumber(data.enviromentImpact));
				setElementText('#kwh .elementor-heading-title', formatNumber(data.cleanEnergy));
			}

			form.addEventListener('submit', function(event) {
				event.preventDefault();

				const consulta = input.value.trim();

				if (!consulta) {
					setMessage('Informe um valor para consulta.', 'error');
					input.focus();
					return;
				}

				const body = new URLSearchParams();
				body.append('action', 'evola_consulta_selo_api');
				body.append('nonce', nonce);
				body.append('consulta', consulta);

				button.disabled = true;
				setMessage('Consultando...', 'loading');

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
						if (!response || !response.success) {
							let errorMessage = response && response.data && response.data.message ?
								response.data.message :
								'Nao foi possivel realizar a consulta.';

							if (response && response.data && response.data.status_code) {
								errorMessage += ' Status da API: ' + response.data.status_code + '.';
							}

							if (response && response.data && response.data.body) {
								errorMessage += ' Resposta: ' + response.data.body;
							}

							throw new Error(errorMessage);
						}

						updateCompanyData(response.data.data);
						setMessage(response.data.message || 'Consulta realizada com sucesso.', 'success');
					})
					.catch(function(error) {
						setMessage(error.message || 'Nao foi possivel realizar a consulta.', 'error');
					})
					.finally(function() {
						button.disabled = false;
					});
			});
		})();
	</script>
<?php
	return ob_get_clean();
}
add_shortcode('evola_consulta_selo_api', 'evola_consulta_selo_api_shortcode');
