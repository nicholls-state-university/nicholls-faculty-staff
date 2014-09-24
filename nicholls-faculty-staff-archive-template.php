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

<div class="example gc3">
    <h3>Popup with form</h3>
    <p>Entered data is not lost if you open and close the popup or if you go to another page and then press back browser button.</p>
    <div class="html-code">
      <!-- link that opens popup -->
      <a class="popup-with-form" href="#test-form">Open form</a>

      <!-- form itself -->
      <form id="test-form" class="mfp-hide white-popup-block">
        <h1>Form</h1>
        <fieldset style="border:0;">
          <p>Lightbox has an option to automatically focus on the first input. It's strongly recommended to use <code>inline</code> popup type for lightboxes with form instead of <code>ajax</code> (to keep entered data if the user accidentally refreshed the page).</p>
          <ol>
            <li>
              <label for="name">Name</label>
              <input id="name" name="name" type="text" placeholder="Name" required>
            </li>
            <li>
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="example@domain.com" required>
            </li>
            <li>
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" placeholder="Eg. +447500000000" required>
            </li>
            <li>
              <label for="textarea">Textarea</label><br/>
              <textarea id="textarea">Try to resize me to see how popup CSS-based resizing works.</textarea>
            </li>
          </ol>
        </fieldset>
      </form>
    </div>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        jQuery('.popup-with-form').magnificPopup({
          type: 'inline',
          preloader: false,
          focus: '#name',

          // When elemened is focused, some mobile browsers in some cases zoom in
          // It looks not nice, so we disable it:
          callbacks: {
            beforeOpen: function() {
              if( jQuery(window).width() < 700 ) {
                this.st.focus = false;
              } else {
                this.st.focus = '#name';
              }
            }
          }
        });
      });
    </script>
  </div>
  
  

		<?php do_action( 'fnbx_template_archive_end', 'template_archive' ) ?>
		<!-- END: template_archive -->

<?php get_sidebar() ?>

<?php get_footer() ?>