<?php

if (! defined('ABSPATH')) {
	exit;
}

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
		<label for="<?php echo esc_attr($form_id); ?>-consulta">
			CNPJ
		</label>

		<input
			id="<?php echo esc_attr($form_id); ?>-consulta"
			name="consulta"
			type="text"
			required
		>

		<button type="submit">
			Consultar
		</button>

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

			function updateCompanyData(data) {
				if (!data) return;

				setElementText('#status-empresa .elementor-icon-box-description', data.status);
				setElementText('#nome-empresa .elementor-heading-title', data.name);
				setElementText('#data-empresa', formatDate(data.helpingSince));
				setElementText('#co2 .elementor-heading-title', data.co2Reduction);
				setElementText('#arvores .elementor-heading-title', data.enviromentImpact);
				setElementText('#kwh .elementor-heading-title', data.cleanEnergy);
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
