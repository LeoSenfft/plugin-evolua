	<?php
	$terms = get_terms([
		'taxonomy'   => 'categoria_faq',
		'hide_empty' => true,
	]);

	if (!empty($terms) && !is_wp_error($terms)) :

		foreach ($terms as $term) :
	?>
			<section class="faq-categoria">

				<h2 class="faq-categoria-titulo">
					<?php echo esc_html($term->name); ?>
				</h2>
				<?php
				$faq_query = new WP_Query([
					'post_type'      => 'faq',
					'posts_per_page' => -1,
					'tax_query' => [
						[
							'taxonomy' => 'categoria_faq',
							'field'    => 'term_id',
							'terms'    => $term->term_id,
						],
					],
				]);

				if ($faq_query->have_posts()) :
				?>

					<?php
					while ($faq_query->have_posts()) :
						$faq_query->the_post();
					?>
						<div class="elementor-element elementor-element-109c11b2 elementor-widget elementor-widget-n-accordion" data-id="109c11b2" data-element_type="widget" data-settings="{&quot;default_state&quot;:&quot;expanded&quot;,&quot;max_items_expended&quot;:&quot;one&quot;,&quot;n_accordion_animation_duration&quot;:{&quot;unit&quot;:&quot;ms&quot;,&quot;size&quot;:400,&quot;sizes&quot;:[]}}" data-widget_type="nested-accordion.default">
							<div class="elementor-widget-container">
								<div class="e-n-accordion" aria-label="Accordion. Open links with Enter or Space, close with Escape, and navigate with Arrow Keys">
									<details id="e-n-accordion-item-2780" class="e-n-accordion-item" open="">
										<summary class="e-n-accordion-item-title" data-accordion-index="1" tabindex="0" aria-expanded="true" aria-controls="e-n-accordion-item-2780">
											<span class="e-n-accordion-item-title-header">
												<div class="e-n-accordion-item-title-text"> O que é a parceria Bahêa Energia + Evolua? </div>
											</span>
											<span class="e-n-accordion-item-title-icon">
												<span class="e-opened"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-up" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M177 159.7l136 136c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0L160 255.9l-96.4 96.4c-9.4 9.4-24.6 9.4-33.9 0L7 329.7c-9.4-9.4-9.4-24.6 0-33.9l136-136c9.4-9.5 24.6-9.5 34-.1z"></path>
													</svg></span>
												<span class="e-closed"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-down" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"></path>
													</svg></span>
											</span>

										</summary>
										<div role="region" aria-labelledby="e-n-accordion-item-2780" class="elementor-element elementor-element-686df400 e-con-full e-flex e-con e-child" data-id="686df400" data-element_type="container">
											<div role="region" aria-labelledby="e-n-accordion-item-2780" class="elementor-element elementor-element-5dc31873 e-flex e-con-boxed e-con e-child" data-id="5dc31873" data-element_type="container">
												<div class="e-con-inner">
													<div class="elementor-element elementor-element-8c26025 elementor-widget elementor-widget-text-editor" data-id="8c26025" data-element_type="widget" data-widget_type="text-editor.default">
														<div class="elementor-widget-container">
															<p><span style="font-weight: 400;">A parceria entre o Esporte Clube Bahia e a Evolua Energia permite que o sócio garanta desconto na conta de luz por meio da energia limpa por assinatura e ainda receba desconto no plano do Sócio Esquadrão, conforme o valor mensal de sua conta de energia.</span></p>
															<p><span style="font-weight: 400;">É um benefício duplo:</span></p>
															<ul>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Economia na sua conta de energia</span></li>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Economia no seu plano de sócio</span></li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</details>
									<details id="e-n-accordion-item-2781" class="e-n-accordion-item">
										<summary class="e-n-accordion-item-title" data-accordion-index="2" tabindex="-1" aria-expanded="false" aria-controls="e-n-accordion-item-2781">
											<span class="e-n-accordion-item-title-header">
												<div class="e-n-accordion-item-title-text"> Como funciona? </div>
											</span>
											<span class="e-n-accordion-item-title-icon">
												<span class="e-opened"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-up" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M177 159.7l136 136c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0L160 255.9l-96.4 96.4c-9.4 9.4-24.6 9.4-33.9 0L7 329.7c-9.4-9.4-9.4-24.6 0-33.9l136-136c9.4-9.5 24.6-9.5 34-.1z"></path>
													</svg></span>
												<span class="e-closed"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-down" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"></path>
													</svg></span>
											</span>

										</summary>
										<div role="region" aria-labelledby="e-n-accordion-item-2781" class="elementor-element elementor-element-745dbdc8 e-con-full e-flex e-con e-child" data-id="745dbdc8" data-element_type="container">
											<div role="region" aria-labelledby="e-n-accordion-item-2781" class="elementor-element elementor-element-27ee295 e-flex e-con-boxed e-con e-child" data-id="27ee295" data-element_type="container">
												<div class="e-con-inner">
													<div class="elementor-element elementor-element-5a56ef9a elementor-widget elementor-widget-text-editor" data-id="5a56ef9a" data-element_type="widget" data-widget_type="text-editor.default">
														<div class="elementor-widget-container">
															<ol>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Você continua conectado à rede de energia da sua distribuidora (Coelba, Enel, Energisa, etc.).</span></li>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">A Evolua gera créditos de energia que se transformam em desconto na conta de luz.</span></li>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Além dessa economia, você recebe desconto no plano Sócio Esquadrão, de acordo com a sua faixa de consumo.</span></li>
															</ol>
															<p><span style="font-weight: 400;">Tudo isso de forma 100% digital!</span></p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</details>
									<details id="e-n-accordion-item-2782" class="e-n-accordion-item">
										<summary class="e-n-accordion-item-title" data-accordion-index="3" tabindex="-1" aria-expanded="false" aria-controls="e-n-accordion-item-2782">
											<span class="e-n-accordion-item-title-header">
												<div class="e-n-accordion-item-title-text"> É seguro? </div>
											</span>
											<span class="e-n-accordion-item-title-icon">
												<span class="e-opened"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-up" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M177 159.7l136 136c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0L160 255.9l-96.4 96.4c-9.4 9.4-24.6 9.4-33.9 0L7 329.7c-9.4-9.4-9.4-24.6 0-33.9l136-136c9.4-9.5 24.6-9.5 34-.1z"></path>
													</svg></span>
												<span class="e-closed"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-down" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"></path>
													</svg></span>
											</span>

										</summary>
										<div role="region" aria-labelledby="e-n-accordion-item-2782" class="elementor-element elementor-element-6f3fd5ac e-con-full e-flex e-con e-child" data-id="6f3fd5ac" data-element_type="container">
											<div role="region" aria-labelledby="e-n-accordion-item-2782" class="elementor-element elementor-element-481381f1 e-flex e-con-boxed e-con e-child" data-id="481381f1" data-element_type="container">
												<div class="e-con-inner">
													<div class="elementor-element elementor-element-37b17681 elementor-widget elementor-widget-text-editor" data-id="37b17681" data-element_type="widget" data-widget_type="text-editor.default">
														<div class="elementor-widget-container">
															<p><span style="font-weight: 400;">Sim. A Evolua opera dentro das normas da ANEEL e segue o modelo oficial de geração distribuída.</span></p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</details>
									<details id="e-n-accordion-item-2783" class="e-n-accordion-item">
										<summary class="e-n-accordion-item-title" data-accordion-index="4" tabindex="-1" aria-expanded="false" aria-controls="e-n-accordion-item-2783">
											<span class="e-n-accordion-item-title-header">
												<div class="e-n-accordion-item-title-text"> Quem pode aderir? </div>
											</span>
											<span class="e-n-accordion-item-title-icon">
												<span class="e-opened"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-up" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M177 159.7l136 136c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0L160 255.9l-96.4 96.4c-9.4 9.4-24.6 9.4-33.9 0L7 329.7c-9.4-9.4-9.4-24.6 0-33.9l136-136c9.4-9.5 24.6-9.5 34-.1z"></path>
													</svg></span>
												<span class="e-closed"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-down" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"></path>
													</svg></span>
											</span>

										</summary>
										<div role="region" aria-labelledby="e-n-accordion-item-2783" class="elementor-element elementor-element-ebc481d e-con-full e-flex e-con e-child" data-id="ebc481d" data-element_type="container">
											<div role="region" aria-labelledby="e-n-accordion-item-2783" class="elementor-element elementor-element-744f234 e-flex e-con-boxed e-con e-child" data-id="744f234" data-element_type="container">
												<div class="e-con-inner">
													<div class="elementor-element elementor-element-97a51c1 elementor-widget elementor-widget-text-editor" data-id="97a51c1" data-element_type="widget" data-widget_type="text-editor.default">
														<div class="elementor-widget-container">
															<ul>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Sócios adimplentes</span></li>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Pessoas físicas</span></li>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Unidades de baixa tensão residencial</span></li>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Conta de energia no nome do sócio</span></li>
																<li style="font-weight: 400;" aria-level="1"><span style="font-weight: 400;">Contas das principais distribuidoras atendidas pela Evolua</span></li>
															</ul>
															<p><span style="font-weight: 400;">Se ainda não é um sócio, basta se cadastrar como sócio no Esquadrão e depois usar o mesmo CPF para aderir ao Bahêa Energia.</span></p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</details>
									<details id="e-n-accordion-item-2784" class="e-n-accordion-item">
										<summary class="e-n-accordion-item-title" data-accordion-index="5" tabindex="-1" aria-expanded="false" aria-controls="e-n-accordion-item-2784">
											<span class="e-n-accordion-item-title-header">
												<div class="e-n-accordion-item-title-text"> Como funciona a gratuidade? </div>
											</span>
											<span class="e-n-accordion-item-title-icon">
												<span class="e-opened"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-up" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M177 159.7l136 136c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0L160 255.9l-96.4 96.4c-9.4 9.4-24.6 9.4-33.9 0L7 329.7c-9.4-9.4-9.4-24.6 0-33.9l136-136c9.4-9.5 24.6-9.5 34-.1z"></path>
													</svg></span>
												<span class="e-closed"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-down" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"></path>
													</svg></span>
											</span>

										</summary>
										<div role="region" aria-labelledby="e-n-accordion-item-2784" class="elementor-element elementor-element-14a7750e e-con-full e-flex e-con e-child" data-id="14a7750e" data-element_type="container">
											<div role="region" aria-labelledby="e-n-accordion-item-2784" class="elementor-element elementor-element-4c065b60 e-flex e-con-boxed e-con e-child" data-id="4c065b60" data-element_type="container">
												<div class="e-con-inner">
													<div class="elementor-element elementor-element-b2bf2a8 elementor-widget elementor-widget-text-editor" data-id="b2bf2a8" data-element_type="widget" data-widget_type="text-editor.default">
														<div class="elementor-widget-container">
															<p><span style="font-weight: 400;">A gratuidade (ou desconto) funciona de acordo com o valor da sua conta de luz e o seu plano de sócio. Quanto maior for o valor da conta, maior é o benefício que você recebe no plano de sócio-torcedor.</span></p>
															<p><span style="font-weight: 400;">Vamos verificar em qual faixa de consumo a sua conta se encaixa para definir se você terá um desconto parcial no valor do plano ou gratuidade total da mensalidade por um período.</span></p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</details>
									<details id="e-n-accordion-item-2785" class="e-n-accordion-item">
										<summary class="e-n-accordion-item-title" data-accordion-index="6" tabindex="-1" aria-expanded="false" aria-controls="e-n-accordion-item-2785">
											<span class="e-n-accordion-item-title-header">
												<div class="e-n-accordion-item-title-text"> Quanto eu economizo? </div>
											</span>
											<span class="e-n-accordion-item-title-icon">
												<span class="e-opened"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-up" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M177 159.7l136 136c9.4 9.4 9.4 24.6 0 33.9l-22.6 22.6c-9.4 9.4-24.6 9.4-33.9 0L160 255.9l-96.4 96.4c-9.4 9.4-24.6 9.4-33.9 0L7 329.7c-9.4-9.4-9.4-24.6 0-33.9l136-136c9.4-9.5 24.6-9.5 34-.1z"></path>
													</svg></span>
												<span class="e-closed"><svg aria-hidden="true" class="e-font-icon-svg e-fas-angle-down" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg">
														<path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"></path>
													</svg></span>
											</span>

										</summary>
										<div role="region" aria-labelledby="e-n-accordion-item-2785" class="elementor-element elementor-element-526f3203 e-con-full e-flex e-con e-child" data-id="526f3203" data-element_type="container">
											<div role="region" aria-labelledby="e-n-accordion-item-2785" class="elementor-element elementor-element-3fab0653 e-flex e-con-boxed e-con e-child" data-id="3fab0653" data-element_type="container">
												<div class="e-con-inner">
													<div class="elementor-element elementor-element-53adce95 elementor-widget elementor-widget-text-editor" data-id="53adce95" data-element_type="widget" data-widget_type="text-editor.default">
														<div class="elementor-widget-container">
															<p><span style="font-weight: 400;">São duas economias: desconto na conta de luz e no plano de Sócio Esquadrão.</span></p>
															<p><span style="font-weight: 400;">Com a energia limpa por assinatura, você garante 15% de desconto na tarifa de energia da sua conta de luz todos os meses.&nbsp;</span></p>
															<p><span style="font-weight: 400;">E, com o Bahêa, você também tem desconto no plano de Sócio Esquadrão com até um ano de sócio pago pela Evolua — o benefício varia de acordo com o seu consumo.</span></p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</details>
								</div>
							</div>
						</div>
					<?php endwhile; ?>

				<?php
					wp_reset_postdata();
				endif;
				?>

			</section>

	<?php
		endforeach;
	endif;
	?>