<?php
/*
Plugin Name: Evolua Plugin
Description: Captura dados de formulário Elementor e envia para o Pipedrive criando pessoa e negócio
Version: 1.0
Author: Evolua
*/

// Bloquear acesso direto
if (! defined('ABSPATH')) exit;

if (! defined('EVOLUA_PLUGIN_PATH')) {
	define('EVOLUA_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if (! defined('EVOLUA_PLUGIN_URL')) {
	define('EVOLUA_PLUGIN_URL', plugin_dir_url(__FILE__));
}

add_action('wp_footer', function () {
?>
	<script>
		document.addEventListener('click', function(e) {

			// procura o click no botão da pergunta
			const question = e.target.closest('.faq-question');
			if (!question) return;

			const item = question.closest('.faq-item');
			const answer = item.querySelector('.faq-answer');

			// fecha outros itens
			const accordion = item.closest('.faq-accordion');
			accordion.querySelectorAll('.faq-item').forEach(el => {
				if (el !== item) {
					el.classList.remove('active');
					el.querySelector('.faq-answer').style.maxHeight = null;
				}
			});

			// toggle atual
			if (item.classList.contains('active')) {
				item.classList.remove('active');
				answer.style.maxHeight = null;
			} else {
				item.classList.add('active');
				answer.style.maxHeight = answer.scrollHeight + "px";
			}

		});
	</script>
<?php
});

// Hook para capturar envios do Elementor
add_action('elementor_pro/forms/new_record', function ($record, $handler) {
	$form_name = $record->get_form_settings('form_name');

	// Altere esse nome para o nome exato do seu formulário
	if ($form_name === 'Formulário Lead Pipedrive') {
		$form_data = $record->get('fields');

		// Mapeamento dos campos

		//org
		$razao_social = $form_data['razao_social']['value'] ?? '';
		$submercado = $form_data['submercado']['value'] ?? '';
		$cpf_cnpj = $form_data['cpf_cnpj']['value'] ?? '';
		$estado = $form_data['estado']['value'] ?? '';
		$cidade = $form_data['cidade']['value'] ?? '';
		$distribuidora = $form_data['distribuidora']['value'] ?? '';
		$numero_instalacao = $form_data['numero_instalacao']['value'] ?? '';
		$tensao = $form_data['tensao']['value'] ?? '';
		$modalidade_tarifaria = $form_data['modalidade_tarifaria']['value'] ?? '';
		$classificacao = $form_data['classificacao']['value'] ?? '';

		//responsavel
		$nome = $form_data['nome']['value'] ?? '';
		$telefone = $form_data['telefone']['value'] ?? '';
		$email = $form_data['email']['value'] ?? '';
		$cargo = $form_data['cargo']['value'] ?? '';

		// lead
		$mes_ano_fatura = $form_data['mes_ano_fatura']['value'] ?? '';
		$data_envio_fatura = $form_data['data_envio_fatura']['value'] ?? '';
		$canal_de_origem = $form_data['canal_de_origem']['value'] ?? '';
		$origem_key = "";
		$origem_value = '';

		if ($canal_de_origem === "Mercado Livre") {
			$origem_key = "3574fe321a79cbd86231b8daee212fc6d6f3f286";
			$origem_value = $form_data['origem_mercado_livre']['value'] ?? '';
		} else if ($canal_de_origem === "Direto MG" || $canal_de_origem === "Direto Expansão") {
			$origem_key = "12498d07840c0a0b4b91a6f892ed6de9598563e1";
			$origem_value = $form_data['executivo_canal_direto']['value'] ?? '';
		} else {
			$origem_key = "e06ba712bf6d2656351afc5a06eee886b188b1cc";
			$origem_value = $form_data['canal_parceiro']['value'] ?? '';
		}

		// API Pipedrive - Exemplo criando um Lead
		$api_token = '987c93e12330837daefa5880350f517750fee8d6'; // 🔴 Troque pela sua API Key real
		// $api_token = 'a7ec5f7ae88b86a8ade6d7973567dfcb1839f748'; // sandbox
		$endpoint = 'https://api.pipedrive.com/v1/leads?api_token=' . $api_token;
		$endpoint_person = 'https://api.pipedrive.com/v1/persons?api_token=' . $api_token;
		$endpoint_organization = 'https://api.pipedrive.com/v1/organizations?api_token=' . $api_token;

		$body_organization = [
			'name' => $razao_social,
			'd35fbaa2dac5267665aaa8d44cc4a8ace8dd3842' => $razao_social, // razao social
			'2a9ac9415341d805c607ba32d3813a6b4119de4d' => $submercado, // submercado,
			'cdf188d41224a9b465607475b18805436dcac504' => $cpf_cnpj, // CPF CNPJ,
			'f6526a78233adbd425b9c4530f7d9c320b5db435' => $estado, // estado,
			'74081fc50203c744fdb4a36eeaf94b0f9a3aa4aa' => $cidade, // cidade,
			'567d8aba5ec1c86a7a3f81b0cc654504d55ba53a' => $distribuidora, // distribuidora,
			'10813a7ffd289ffb039663700f60c0272f703c63' => $numero_instalacao, // Numero instalação
			'a780f87db3ef3bf3766120a07ed33cb95ee5f8aa' => $tensao, // tensao
			'6597255a18508639109dae83c85570bf8ce69af2' => $modalidade_tarifaria, // modalidade tarifaria
			'52d37272cf4674d4571f78cd1df949b6d41354d1' => $classificacao, // classificação
		];

		$response_organization = wp_remote_post($endpoint_organization, [
			'method' => 'POST',
			'timeout' => 15,
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'body' => json_encode($body_organization),
		]);

		$response_organization_body = json_decode(wp_remote_retrieve_body($response_organization), true);
		$organization_id = $response_organization_body['data']['id'] ?? null;



		$body_person = [
			'name' => $nome,
			'org_id' => $organization_id,
			"d488c0c7d43f04dd37a3a615f1c6233c5b81a5ea" => $email,
			"9092c0f1eb9698b2808ac1f06cb7a327888d9071" => $telefone,
			'job_title' => $cargo,
		];

		$response_person = wp_remote_post($endpoint_person, [
			'method' => 'POST',
			'timeout' => 15,
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'body' => json_encode($body_person),
		]);

		$response_person_body = json_decode(wp_remote_retrieve_body($response_person), true);
		$person_id = $response_person_body['data']['id'] ?? null;

		$body = [
			'title' => 'Novo Lead do Site ' . $canal_de_origem . '-' . (int)$origem_value,
			'person_id' => 10642,
			'organization_id' => 9313,
			"a921663b896f488df386a9f149c69de5dbbd326a" => $data_envio_fatura, // data envio da fatura
			'2e37470462665c3b2f8cea2ed9db258e65f333e8' => $mes_ano_fatura,
			$origem_key => $origem_value
		];

		$response = wp_remote_post($endpoint, [
			'method' => 'POST',
			'timeout' => 15,
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'body' => json_encode($body),
		]);

		// Debug - grava no log do WordPress
		if (is_wp_error($response)) {
			error_log('Erro ao enviar Lead para o Pipedrive: ' . $response->get_error_message());
		} else {
			error_log('Resposta do Pipedrive: ' . print_r($response, true));
		}
	}
}, 10, 2);



function meu_plugin_header_shortcode()
{
	ob_start();
	include plugin_dir_path(__FILE__) . 'template-parts/faq-categories.php';
	return ob_get_clean();
}
add_shortcode('meu_header', 'meu_plugin_header_shortcode');

function meu_plugin_faq_list_shortcode()
{
	ob_start();
	include plugin_dir_path(__FILE__) . 'template-parts/faq-list.php';
	return ob_get_clean();
}
add_shortcode('faq_list', 'meu_plugin_faq_list_shortcode');
require_once EVOLUA_PLUGIN_PATH . 'shortcodes/post-categories.php';
require_once EVOLUA_PLUGIN_PATH . 'shortcodes/post-breadcrumb.php';
require_once EVOLUA_PLUGIN_PATH . 'shortcodes/post-author-meta.php';


function faq_sanfona_shortcode()
{

	$terms = get_terms([
		'taxonomy'   => 'categoria_faq',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC'
	]);

	if (empty($terms) || is_wp_error($terms)) {
		return '';
	}

	ob_start();
?>

	<div class="faq-wrapper">

		<?php foreach ($terms as $term) : ?>
			<div class="faq-categoria-item <?php echo 'categoria-' . $term->slug; ?>" id="<?php echo 'categoria-' . $term->slug; ?>" data-category="<?php echo $term->term_id; ?>">

				<?php

				$args = [
					'post_type'      => 'faq',
					'posts_per_page' => -1,
					'tax_query' => [
						[
							'taxonomy' => 'categoria_faq',
							'field'    => 'term_id',
							'terms'    => $term->term_id,
						],
					],
				];

				$faq_query = new WP_Query($args);

				if ($faq_query->have_posts()) :

					$faqs_par   = [];
					$faqs_impar = [];
					$i = 0;

					while ($faq_query->have_posts()) :
						$faq_query->the_post();

						if ($i % 2 === 0) {
							$faqs_par[] = get_the_ID();
						} else {
							$faqs_impar[] = get_the_ID();
						}

						$i++;
					endwhile;
				?>
					<h2 class="faq-categoria">
						<?php echo esc_html($term->name); ?>
					</h2>

					<div class="faq-cols faq-accordion">

						<!-- COLUNA 1 -->
						<div class="faq-col">

							<?php foreach ($faqs_par as $post_id) :
								$post = get_post($post_id);
								setup_postdata($post); ?>

								<div class="faq-item">

									<div class="faq-question">
										<?php esc_html_e($post->post_title); ?>
									</div>

									<div class="faq-answer">
										<?php the_content(); ?>
									</div>

								</div>

							<?php endforeach; ?>

						</div>

						<!-- COLUNA 2 -->
						<div class="faq-col">

							<?php foreach ($faqs_impar as $post_id) :
								$post = get_post($post_id);
								setup_postdata($post); ?>

								<div class="faq-item">

									<div class="faq-question">
										<?php esc_html_e($post->post_title); ?>
									</div>

									<div class="faq-answer">
										<?php the_content(); ?>
									</div>

								</div>

							<?php endforeach; ?>

						</div>

					</div>

				<?php
					wp_reset_postdata();
				endif;
				?>
			</div>

		<?php endforeach; ?>

	</div>

<?php
	return ob_get_clean();
}

add_shortcode('faq_sanfona', 'faq_sanfona_shortcode');

function faq_search_result_shortcode()
{
	$terms = get_terms([
		'taxonomy'   => 'categoria_faq',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC'
	]);

	if (empty($terms) || is_wp_error($terms)) {
		return '';
	}

	ob_start();

	$search_term = isset($_GET['faq_search'])
		? sanitize_text_field($_GET['faq_search'])
		: '';

	if (empty($search_term)) {
		return;
	}
?>

	<div class="faq-wrapper faq-wrapper-search">
		<h2 class="faq-search-title">Exibindo resultados para <span>"<?php echo esc_html($search_term); ?>"</span></h2>

		<?php foreach ($terms as $term) : ?>
			<div class="faq-categoria-item <?php echo 'categoria-' . $term->slug; ?>" id="<?php echo 'categoria-' . $term->slug; ?>" data-category="<?php echo $term->term_id; ?>">

				<?php


				$args = [
					'post_type'      => 'faq',
					'posts_per_page' => -1,
					'tax_query' => [
						[
							'taxonomy' => 'categoria_faq',
							'field'    => 'term_id',
							'terms'    => $term->term_id,
						],
					],
				];

				if (!empty($search_term)) {
					$args['s'] = $search_term;
				}

				$faq_query = new WP_Query($args);

				if ($faq_query->have_posts()) :

					$faqs_par   = [];
					$faqs_impar = [];
					$i = 0;

					while ($faq_query->have_posts()) :
						$faq_query->the_post();

						if ($i % 2 === 0) {
							$faqs_par[] = get_the_ID();
						} else {
							$faqs_impar[] = get_the_ID();
						}

						$i++;
					endwhile;
				?>
					<h2 class="faq-categoria">
						<?php echo esc_html($term->name); ?>
					</h2>

					<div class="faq-cols faq-accordion">

						<!-- COLUNA 1 -->
						<div class="faq-col">

							<?php foreach ($faqs_par as $post_id) :
								$post = get_post($post_id);
								setup_postdata($post); ?>

								<div class="faq-item">

									<div class="faq-question">
										<?php esc_html_e($post->post_title); ?>
									</div>

									<div class="faq-answer">
										<?php the_content(); ?>
									</div>

								</div>

							<?php endforeach; ?>

						</div>

						<!-- COLUNA 2 -->
						<div class="faq-col">

							<?php foreach ($faqs_impar as $post_id) :
								$post = get_post($post_id);
								setup_postdata($post); ?>

								<div class="faq-item">

									<div class="faq-question">
										<?php esc_html_e($post->post_title); ?>
									</div>

									<div class="faq-answer">
										<?php the_content(); ?>
									</div>

								</div>

							<?php endforeach; ?>

						</div>

					</div>

				<?php
					wp_reset_postdata();
				endif;
				?>
			</div>

		<?php endforeach; ?>

	</div>

<?php
	return ob_get_clean();
}

add_shortcode('faq_search_result', 'faq_search_result_shortcode');
require_once EVOLUA_PLUGIN_PATH . 'template-parts/faq-search-live-shortcode.php';
require_once EVOLUA_PLUGIN_PATH . 'template-parts/post-search-live-shortcode.php';
require_once EVOLUA_PLUGIN_PATH . 'shortcodes/post-categories-section.php';


function my_cptui_add_post_type_to_search($query)
{
	if (is_admin()) {
		return;
	}

	if ($query->is_search() && function_exists('cptui_get_post_type_slugs')) {
		$cptui_post_types = cptui_get_post_type_slugs();
		$query->set(
			'post_type',
			array_merge(
				array('post'), // May also want to add the 'page' post type.
				$cptui_post_types
			)
		);
	}
}
add_filter('pre_get_posts', 'my_cptui_add_post_type_to_search');
