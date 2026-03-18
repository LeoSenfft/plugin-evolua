<div class="postCard">
	<a href="<?php the_permalink(); ?>">
		<div class="postCard__thumb">
			<?php the_post_thumbnail(); ?>
		</div>

		<div class="postCard__title">
			<?php the_title(); ?>
		</div>

		<div class="postCard__content">
			<?php the_content(); ?>
		</div>
	</a>
</div>