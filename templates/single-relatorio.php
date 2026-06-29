<?php
// Bloquear acesso direto
if (! defined('ABSPATH')) exit;

while (have_posts()) :
	the_post();
?>

<?php
	if (post_password_required()) {
		// Post protegido por senha: exibe o formulário nativo do WordPress
		echo get_the_password_form();
	} else {
		$html = function_exists('get_field')
			? get_field('html')
			: get_post_meta(get_the_ID(), 'html', true);

		// HTML confiável vindo do campo ACF — impresso sem escape proposital
		echo $html;
	}
?>

<?php
endwhile;
