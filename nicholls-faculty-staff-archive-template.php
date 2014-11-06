<?php
/**
* Archive Template
*
* Template generic archives.
*
* @package FNBX Theme
* @subpackage Template
*/
?>
<?php get_header() ?>

<h1><?php post_type_archive_title(); ?></h1>
			
			<div class="nicholls-fs-departments">
<?php
$taxonomy = 'n-faculty-staff-taxonomy';
$terms = get_terms( $taxonomy, '' );
if ($terms) {
	echo '<strong>Departments</strong><br />';
	echo '<ul class="nicholls-fs-department-links">';
	foreach($terms as $term) {
		echo '<li class="nicholls-fs-department-link">' . '<a href="' . esc_attr(get_term_link($term, $taxonomy)) . '" title="' . sprintf( __( "View all posts in %s" ), $term->name ) . '" ' . '>' . $term->name.'</a></li>';
	}
	echo '</ul>';
}
  
?>

			</div>


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
		<?php nicholls_fs_display_meta_item( '_nicholls_fs_office' ); ?>
	</div>

</div>

<?php endwhile; ?>

<?php nicholls_fs_email_form(); ?>

		<?php do_action( 'fnbx_template_archive_end', 'template_archive' ) ?>
		<!-- END: template_archive -->

<?php get_sidebar() ?>

<?php get_footer() ?>