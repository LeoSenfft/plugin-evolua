<?php

$terms = get_terms([
	'taxonomy'   => 'categoria_faq',
	'hide_empty' => true, // só categorias que possuem FAQ
]);

if (!empty($terms) && !is_wp_error($terms)) { ?>
	<div class="faq-categorias-wrapper">
		<?php
		foreach ($terms as $term) {
			$icone = get_field('icone', $term->taxonomy . '_' . $term->term_id);
		?>
			<a class="categoria-faq <?php echo 'categoria-' . $term->slug; ?>" data-category="<?php echo $term->term_id; ?>" href="#perguntas">
				<?php $svg_id = get_field('icone', $term->taxonomy . '_' . $term->term_id);

				if ($svg_id) {

					$file_path = get_attached_file($svg_id);

					if ($file_path && file_exists($file_path)) {

						$svg_content = file_get_contents($file_path);

						echo wp_kses(
							$svg_content,
							[
								'svg' => [
									'xmlns' => true,
									'viewBox' => true,
									'width' => true,
									'height' => true,
									'fill' => true,
									'class' => true,
								],
								'path' => [
									'd' => true,
									'fill' => true,
								],
							]
						);
					}
				} ?>

				<div class="categoria-faq-texts">
					<?php echo esc_html($term->name); ?>

					<span>
						<?php echo esc_html($term->description); ?>
					</span>
				</div>
			</a>
		<?php
		} ?>
	</div>
<?php
}
