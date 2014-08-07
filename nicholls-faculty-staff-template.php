<?php
/**
 * The template for displaying faculty & staff
 */

get_header();

 ?>

		<div id="container" class="container-">
			<div id="content" class="content-" role="main">
			<h1>The Staff Template</h1>

			<h3 class="entrytitle" id="post-<?php the_ID(); ?>"> <a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?> </a> </h3>

			<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post(); ?>

			<div class="blogpost">

			<?php the_content(); ?>
			</div>

			<?php endwhile; ?>
			<?php else : ?>
			<h6 class="center">Not Found</h6>
			<p class="center">Sorry, but you are looking for something that isn't here.</p>

			<?php endif; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
