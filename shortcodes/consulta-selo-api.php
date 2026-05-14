<?php

if (! defined('ABSPATH')) {
	exit;
}

function evola_consulta_selo_api_request($consulta)
{
	$endpoint = 'https://api.exemplo.com/consulta-selo';

	$body = [
		'consulta' => $consulta,
	];

	$request_args = [
		'method'  => 'POST',
		'timeout' => 15,
		'headers' => [
			'Content-Type' => 'application/json',
		],
		'body'    => wp_json_encode($body),
	];

	// Inicio da integracao externa. Quando o endpoint real existir, use:
	// $response = wp_remote_post($endpoint, $request_args);

	return [
		'endpoint' => $endpoint,
		'request'  => $request_args,
		'message'  => 'Integracao preparada para conectar na API externa.',
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

	wp_send_json_success([
		'message' => $result['message'],
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
			Consulta
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
							const errorMessage = response && response.data && response.data.message ?
								response.data.message :
								'Nao foi possivel realizar a consulta.';

							throw new Error(errorMessage);
						}

						setMessage(response.data.message || 'Consulta enviada com sucesso.', 'success');
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
