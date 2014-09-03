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

<?php
$args=array(
  'order'=>'ASC',
  'orderby'=> 'menu_order',
  'post_type' => 'n-faculty-staff',
  'post_status' => 'publish',
  'posts_per_page' => 100
);
query_posts($args);
?>

<?php while ( have_posts() ) : the_post() ?>
<div class="nicholls-fs-employee clear-group">

	<div class="nicholls-fs-photo">
	<a href="<?php echo get_permalink(); ?>"><?php the_post_thumbnail('nicholls-fs-thumb'); ?></a>
	</div>
	<div class="nicholls-fs-info">
		<h2 class="nicholls-fs-name"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="nicholls-fs-title"><?php echo get_post_meta( get_the_ID(), '_nicholls_fs_employee_title', true ); ?></div>
		<div class="nicholls-fs-email"><strong>Email:</strong> <?php echo get_post_meta( get_the_ID(), '_nicholls_fs_employee_email', true ); ?></div>
		<div class="nicholls-fs-phone"><strong>Phone:</strong> <?php echo get_post_meta( get_the_ID(), '_nicholls_fs_phone', true ); ?></div>
		<div class="nicholls-fs-office"><strong>Office Location:</strong> <?php echo get_post_meta( get_the_ID(), '_nicholls_fs_office', true ); ?></div>
	</div>

</div>
<?php endwhile; ?>

		<?php do_action( 'fnbx_template_archive_end', 'template_archive' ) ?>
		<!-- END: template_archive -->

<?php get_sidebar() ?>

<?php get_footer() ?>