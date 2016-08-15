<?php
/**
* Archive Template
*
* Template generic archives.
*
*/

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
<?php

// This is the main title for the archive pages.

	$tax_title = '';
	$post_type_obj = get_post_type_object( 'n-faculty-staff' );
	if ( !empty( $post_type_obj ) ) 
		$tax_title = $post_type_obj->labels->name;
		
	$cur_title = '';
	if ( is_tax() ) {
		$the_tax = get_query_var( 'term' );
		$the_tax_obj = get_term_by( 'slug', $the_tax, 'n-faculty-staff-taxonomy' );
		$cur_title = ' &raquo ' . $the_tax_obj->name;
	}
	
	echo '<h1>' . $tax_title . $cur_title . '</h1>';

?>
			
<?php nicholls_fs_display_departments() ?>

<?php while ( have_posts()) : the_post();  ?>
<div class="nicholls-fs-employee clear-group">

	<div class="nicholls-fs-photo">
	<a href="<?php echo get_permalink(); ?>"><?php the_post_thumbnail('nicholls-fs-thumb'); ?></a>
	</div>
	<div class="nicholls-fs-info">
		<h2 class="nicholls-fs-name"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php nicholls_fs_display_meta_item( '_nicholls_fs_employee_title' ); ?>
		<?php nicholls_fs_display_meta_item( '_nicholls_fs_employee_dept' ); ?>
		<?php nicholls_fs_display_meta_item( '_nicholls_fs_employee_email' ); ?>
		<?php nicholls_fs_display_meta_item( '_nicholls_fs_phone' ); ?>
		<?php nicholls_fs_display_meta_item( '_nicholls_fs_phone_mobile' ); ?>
		<?php nicholls_fs_display_meta_item( '_nicholls_fs_office' ); ?>
	</div>

</div>

<?php endwhile; ?>

<?php nicholls_fs_email_form(); ?>

		<?php do_action( 'fnbx_template_archive_end', 'template_archive' ) ?>
		<!-- END: template_archive -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
