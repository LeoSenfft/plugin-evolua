<?php

/**
 * Reescrita da URL do CPT "comite".
 *
 * Estrutura desejada:
 *   /comite/{caminho-do-termo}/{caminho-do-post}
 *   ex.: /comite/rh/recrutamento/teste           (termo aninhado + post de topo)
 *   ex.: /comite/rh/recrutamento/teste/subteste  (post filho "subteste" de "teste")
 *
 * O CPT "comite" e a taxonomia "categoria_do_comite" são registrados pelo CPT UI
 * (no banco), então toda a lógica de rewrite vive aqui, via filtros do WordPress.
 */

// Bloquear acesso direto
if (! defined('ABSPATH')) exit;

if (! defined('EVOLUA_COMITE_CPT')) {
	define('EVOLUA_COMITE_CPT', 'comite');
}

if (! defined('EVOLUA_COMITE_TAX')) {
	define('EVOLUA_COMITE_TAX', 'categoria_do_comite');
}

/**
 * Geração do link: monta a URL com o caminho completo do termo (primeiro termo
 * atribuído) seguido do caminho hierárquico do post. Para rascunho/preview, mantém
 * o link padrão do WordPress para não quebrar a pré-visualização.
 */
add_filter('post_type_link', function ($permalink, $post) {
	if ($post->post_type !== EVOLUA_COMITE_CPT) {
		return $permalink;
	}

	if (! in_array($post->post_status, ['publish', 'private', 'future'], true)) {
		return $permalink;
	}

	// Caminho hierárquico completo do post (pai/filho).
	$post_uri = get_page_uri($post);

	// Caminho completo do PRIMEIRO termo (ancestrais + termo).
	$term_path = '';
	$terms = get_the_terms($post, EVOLUA_COMITE_TAX);

	if ($terms && ! is_wp_error($terms)) {
		$term  = reset($terms);
		$slugs = [];

		foreach (array_reverse(get_ancestors($term->term_id, EVOLUA_COMITE_TAX)) as $anc_id) {
			$anc = get_term($anc_id, EVOLUA_COMITE_TAX);

			if ($anc && ! is_wp_error($anc)) {
				$slugs[] = $anc->slug;
			}
		}

		$slugs[]   = $term->slug;
		$term_path = implode('/', $slugs);
	}

	$path = EVOLUA_COMITE_CPT . '/' . ($term_path ? $term_path . '/' : '') . $post_uri;

	return home_url(user_trailingslashit($path));
}, 10, 2);

/**
 * Registra a query var auxiliar usada pelo resolvedor.
 */
add_filter('query_vars', function ($vars) {
	$vars[] = 'comite_full_path';

	return $vars;
});

/**
 * Regras de rewrite. O arquivo e sua paginação vêm antes do catch-all para não serem
 * capturados por ele. O catch-all captura tudo após "comite/" e delega a resolução do
 * ponto de corte (termo x post) para o resolvedor em parse_request.
 */
add_action('init', function () {
	// Arquivo e paginação do arquivo.
	add_rewrite_rule('^comite/page/([0-9]+)/?$', 'index.php?post_type=comite&paged=$matches[1]', 'top');

	// Paginação de post único (<!--nextpage-->).
	add_rewrite_rule('^comite/(.+?)/page/([0-9]+)/?$', 'index.php?comite_full_path=$matches[1]&page=$matches[2]', 'top');

	// Catch-all: tudo após comite/.
	add_rewrite_rule('^comite/(.+?)/?$', 'index.php?comite_full_path=$matches[1]', 'top');
});

/**
 * Valida se uma sequência de slugs corresponde ao caminho completo (ancestrais + folha)
 * de um termo existente na taxonomia. Um caminho vazio é válido (post sem termo).
 */
function evolua_comite_valid_term_path(array $slugs)
{
	if (empty($slugs)) {
		return true;
	}

	$leaf = get_term_by('slug', end($slugs), EVOLUA_COMITE_TAX);

	if (! $leaf || is_wp_error($leaf)) {
		return false;
	}

	$expected = [];

	foreach (array_reverse(get_ancestors($leaf->term_id, EVOLUA_COMITE_TAX)) as $id) {
		$t = get_term($id, EVOLUA_COMITE_TAX);

		if ($t && ! is_wp_error($t)) {
			$expected[] = $t->slug;
		}
	}

	$expected[] = $leaf->slug;

	return $expected === array_values($slugs);
}

/**
 * Resolvedor determinístico: testa cada ponto de corte entre caminho de termo (prefixo)
 * e caminho do post (sufixo). Aceita o corte em que o prefixo valida como caminho de termo
 * (ou é vazio) E o sufixo é um post comite existente.
 */
add_action('parse_request', function ($wp) {
	if (empty($wp->query_vars['comite_full_path'])) {
		return;
	}

	$page     = isset($wp->query_vars['page']) ? $wp->query_vars['page'] : '';
	$segments = explode('/', trim($wp->query_vars['comite_full_path'], '/'));

	$resolved = null;

	// k = nº de segmentos do termo (0..n-1); sufixo = caminho do post (>= 1 segmento).
	// Itera k crescente (caminho de post mais longo primeiro) para evitar casar um sufixo
	// curto por acidente.
	for ($k = 0; $k <= count($segments) - 1; $k++) {
		$term_slugs = array_slice($segments, 0, $k);
		$post_path  = implode('/', array_slice($segments, $k));

		if ($post_path === '' || ! evolua_comite_valid_term_path($term_slugs)) {
			continue;
		}

		$post = get_page_by_path($post_path, OBJECT, EVOLUA_COMITE_CPT);

		if ($post) {
			$resolved = $post;
			break;
		}
	}

	if ($resolved) {
		$wp->query_vars = ['post_type' => EVOLUA_COMITE_CPT, 'p' => $resolved->ID];

		if ($page !== '') {
			$wp->query_vars['page'] = $page;
		}
	}

	// Se nada resolver, remove o helper e deixa o WordPress seguir (404 natural).
	unset($wp->query_vars['comite_full_path']);
});

/**
 * Flush das regras uma única vez após o deploy. Alternativa manual: salvar em
 * Configurações → Links Permanentes.
 */
add_action('init', function () {
	if (get_option('evolua_comite_rewrite_ver') !== '1') {
		flush_rewrite_rules();
		update_option('evolua_comite_rewrite_ver', '1');
	}
}, 20);
