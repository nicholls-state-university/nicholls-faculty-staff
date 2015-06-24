<?php
/**
 * The template for displaying faculty & staff
 */

get_header();

 ?>

		<div id="container" class="container-">
			<div id="content" class="content-" role="main">

			<h2 class="entry-title" id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>

			<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post(); ?>

			<div class="blogpost">

			<div class="nicholls-fs-photo">
				<?php the_post_thumbnail('nicholls-fs-medium'); ?>
			</div>
			<div class="nicholls-fs-info">
				<div class="nicholls-fs-name"><h2><?php the_title(); ?></h2></div>
				<?php nicholls_fs_display_meta_item( '_nicholls_fs_employee_title' ); ?>
				<?php nicholls_fs_display_meta_item( '_nicholls_fs_employee_dept' ); ?>
				<?php nicholls_fs_display_meta_item( '_nicholls_fs_employee_email' ); ?>
				<?php nicholls_fs_display_meta_item( '_nicholls_fs_phone' ); ?>
				<?php nicholls_fs_display_meta_item( '_nicholls_fs_office' ); ?>
			</div>

			<div class="nicholls-fs-bio">
				<?php the_content(); ?>
			</div>
						
			<?php nicholls_fs_email_form(); ?>
			
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



