<?php
/*
Plugin Name: Nicholls - Faculty & Staff Information
Plugin URI: http://nicholls.edu
Description: Simplified listings for faculty and staff.
Author: Jess Planck
Version: 0.5
Author URI: http://nicholls.edu
*/

/**
 * Initialize the metabox class.
 * https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress
 */
if ( !function_exists( 'cmb_initialize_cmb_meta_boxe' ) ) {
	function cmb_initialize_cmb_meta_boxes() {

		if ( ! class_exists( 'cmb_Meta_Box' ) )
			require_once 'Custom-Metaboxes-and-Fields-for-WordPress/init.php';

	}
}
add_action( 'init', 'cmb_initialize_cmb_meta_boxes', 9999 );


function nicholls_fs_url_filter( $c_url ) {
	$c_url = str_replace( 'http://', '//', $c_url );
	return $c_url;
}
add_filter('cmb_meta_box_url', 'nicholls_fs_url_filter');

/**
* Email contact form
*
*/
function nicholls_fs_email_form() { ?>
	<div id="nicholls-fs-form" class="nicholls-fs-form-">
		<form id="nicholls-fs-form-email" class="white-popup-block mfp-hide">
			<div id="nicholls-fs-form-message-top" class="nicholls-fs-form-message-top-"></div>
			<div id="nicholls-fs-form-name" class="nicholls-fs-form-name-">
				Your Name <br/>
				<input id="nicholls-fs-form-email-name" class="text" type="text" name="nicholls-fs-form-email-name"/>
			</div>
			<div id="nicholls-fs-form-email" class="nicholls-fs-form-email-">
				Your Email <br/>
				<input id="nicholls-fs-form-email-email" class="text" type="text" name="nicholls-fs-form-email-email"/>
			</div>
			<div id="nicholls-fs-form-message" class="nicholls-fs-form-message-">
				Your Message <br/>
				<textarea id="nicholls-fs-form-email-message" class="textarea" name="nicholls-fs-form-email-message"></textarea>
			</div>
			<input name="action" type="hidden" value="nicholls-fs-form-email" />
			<input name="nicholls-fs-form-email-addr" type="hidden" value="" />
			<input name="nicholls-fs-form-url" type="hidden" value="<?php the_permalink(); ?>" />
			<?php wp_nonce_field( 'nicholls_fs_email_form', 'nicholls_fs_email_form_nonce' ); ?>
			<input id="scfs" class="button" type="submit" name="scfs" value="Send Message"/>
			<img class="nicholls-fs-form-email-ajax-image" src="<?php echo plugins_url( 'images/11kguf4.gif', __FILE__ ); ?>" alt="Sending Message">
			<div class="nicholls-fs-form-email-message"><p></p></div>
		<form>
	</div>
<?php } 


function nicholls_fs_get_url() {
	  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
	  $url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
	  $url .= $_SERVER["REQUEST_URI"];
	  return $url;
}

/**
* Email contact form - JavaScript
*
*/
function nicholls_fs_js_enqueue() {

	if ( 'n-faculty-staff' != get_post_type() ) return;
	
	//Enqueue Javascript & jQuery if not already loaded
	wp_enqueue_script('jquery');
	wp_enqueue_script('nicholls-fs-js', plugins_url( 'js/nicholls-fs.js' , __FILE__ ), array('jquery'));
	wp_enqueue_script('magnific-popup-js', plugins_url( 'Magnific-Popup-master/dist/jquery.magnific-popup.min.js' , __FILE__ ), array('jquery'));
	
	// Enqueue CSS
	wp_enqueue_style( 'magnific-popup-css', plugins_url( 'Magnific-Popup-master/dist/magnific-popup.css' , __FILE__ ) );

	$localize = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	);
	wp_localize_script('nicholls-fs-js', 'nicholls_fs_js_obj', $localize);
}
add_action( 'wp_enqueue_scripts', 'nicholls_fs_js_enqueue' );

/**
* Email contact form - Ajax actions
*
*/
function nicholls_fs_ajax_simple_contact_form() {

	if ( isset( $_POST['nicholls_fs_email_form_nonce'] ) && wp_verify_nonce( $_POST['nicholls_fs_email_form_nonce'], 'nicholls_fs_email_form' ) ) {

		$name = sanitize_text_field($_POST['nicholls-fs-form-email-name']);
		$email = sanitize_email($_POST['nicholls-fs-form-email-email']);
		$message = stripslashes( wp_filter_kses($_POST['nicholls-fs-form-email-message']) );
		$form_url = esc_url( $_POST['nicholls-fs-form-url'] );
		$subject = 'Nicholls Web Email - Message from ' . $name; 
		
		$to = sanitize_email($_POST['nicholls-fs-form-email-addr']);

		$headers[] = 'From: ' . $name . ' <' . $email . '>' . "\r\n";
		$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>' . "\r\n";
		$headers[] = 'Content-type: text/html' . "\r\n"; //Enables HTML ContentType. Remove it for Plain Text Messages
		
		$message = '<br/>' . $message . '<br/><br/><hr/>This messange sent using a form found at: ' . $form_url . '<br/>Please contact nichweb@nicholls.edu for support.';
		
		add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
		$check = wp_mail( $to, $subject, $message, $headers );
		
		wp_send_json_error( $check );

	}

	die(); // Important
}
add_action( 'wp_ajax_nicholls-fs-form-email', 'nicholls_fs_ajax_simple_contact_form' );
add_action( 'wp_ajax_nopriv_nicholls-fs-form-email', 'nicholls_fs_ajax_simple_contact_form' );

/**
* Configure SMTP & Advanced PHPMail options
*/
add_action( 'phpmailer_init', 'nicholls_fs_configure_smtp', 999 );
function nicholls_fs_configure_smtp( $phpmailer ){

    $phpmailer->From = 'nichweb@nicholls.edu';
    $phpmailer->FromName='Nicholls Webmanager';
	$phpmailer->Sender = 'nichweb@nicholls.edu';

	/*
	* Expception handling for PHPMailer to catch errors for ajax requests.
	* see: https://gist.github.com/franz-josef-kaiser/5840282
	*/
	/*
	$error = null;
	try {
		$sent = $phpmailer->Send();
		! $sent AND $error = new WP_Error( 'phpmailerError', $sent->ErrorInfo );
	}
	catch ( phpmailerException $e ) {
		$error = new WP_Error( 'phpmailerException', $e->errorMessage() );
	}
	catch ( Exception $e ) {
		$error = new WP_Error( 'defaultException', $e->getMessage() );
	}
 
	if ( is_wp_error( $error ) )
		return printf( "%s: %s", $error->get_error_code(), $error->get_error_message() );
	*/
}

/**
* Load resources when shortcode is detected. Only for CSS and Scripts since we're
* running on wp_enqueue_script.
* @author Ian Dunn <ian@iandunn.name> - original author
*/
function nicholls_fs_resources_load() {
/*	
		// Override styles from theme.
		$j_flex_css_file = get_stylesheet_directory() . '/j-flexslider/flexslider.css';
		if ( file_exists( $j_flex_css_file ) )
			$j_flex_css_file_url = get_stylesheet_directory_uri() . '/j-flexslider/flexslider.css';
		else
			$j_flex_css_file_url = plugins_url( 'flexslider.css', __FILE__ );

		wp_register_style(
			'j-flex-style',
			$j_flex_css_file_url,
			false,
			'1.0'
		);		
		wp_enqueue_style( 'j-flex-style' );
*/
}
add_action( 'wp_enqueue_scripts', 'nicholls_fs_resources_load', 11 );
  
/**
* Initialize and register custom post types
*/
function nicholls_fs_init() {  

    // Setup custom post type
    $args = array(  
        'label' => __( 'Nicholls Faculty & Staff' ),
        'labels' => array(
                'name' => __( 'Faculty & Staff' ),
                'singular_name' => __( 'Faculty & Staff' ),
                'add_new' => __( 'Add New Faculty & Staff' ),
                'add_new_item' => __( 'Add New Faculty & Staff' ),
                'edit_item' => __( 'Edit Faculty & Staff' ),
                'new_item' => __( 'Add New Faculty & Staff' ),
                'view_item' => __( 'View Faculty & Staff' ),
                'search_items' => __( 'Search Faculty & Staff' ),
                'not_found' => __( 'No faculty & ftaff found' ),
                'not_found_in_trash' => __( 'No faculty & staff found in trash' )
		),
		'taxonomies'    => array(
			'n-faculty-staff-taxonomy',
		),		
        'public' => true, 
        'show_ui' => true,  
        'capability_type' => 'post',  
        'hierarchical' => false,
        'has_archive' => true,
        'rewrite' => array( 
			'slug' => 'faculty-staff',
			'with_front' => false
		),  
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes'),
        'register_meta_box_cb' => 'nicholls_fs_add_metaboxes'
       );  
    register_post_type( 'n-faculty-staff' , $args );

	register_taxonomy(
		'n-faculty-staff-taxonomy',
		array(
			'n-faculty-staff',
		),
		array(
			'labels'            => array(
				'name'              => _x('Departments', 'prefix_portfolio', 'text_domain'),
				'singular_name'     => _x('Department', 'prefix_portfolio', 'text_domain'),
				'menu_name'         => __('Departments', 'text_domain'),
				'all_items'         => __('All Departments', 'text_domain'),
				'edit_item'         => __('Edit Department', 'text_domain'),
				'view_item'         => __('View Department', 'text_domain'),
				'update_item'       => __('Update Department', 'text_domain'),
				'add_new_item'      => __('Add New Department', 'text_domain'),
				'new_item_name'     => __('New Department Name', 'text_domain'),
				'search_items'      => __('Search Departments', 'text_domain'),
			),
			'show_admin_column' => true,
			'hierarchical'      => true,
			'rewrite'           => array( 
				'slug' => 'faculty-staff-departments',
				'with_front' => false
			),
		)
	);
    
    // Setup custom image size  
    add_image_size( 'nicholls-fs-medium', 240, 360 );
    add_image_size( 'nicholls-fs-thumb', 120, 180 );
    
	// Needs moved to activation after testing
	flush_rewrite_rules( false );
    
}  
add_action('init', 'nicholls_fs_init');


/**
* Save the Metabox Data
*/
function nicholls_fs_save_meta($post_id, $post) {

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;
	
	// Featured Images
	if ( isset( $_POST['_nicholls_fs_employee_photo_id'] ) && !empty( $_POST['_nicholls_fs_employee_photo_id'] ) ) 
		add_post_meta( $post->ID, '_thumbnail_id', $_POST['_nicholls_fs_employee_photo_id'] ); 
	if ( isset( $_POST['_nicholls_fs_employee_photo_id'] ) && empty( $_POST['_nicholls_fs_employee_photo_id'] ) ) 
		delete_post_meta( $post->ID, '_thumbnail_id' ); 
}
add_action('save_post', 'nicholls_fs_save_meta', 1, 2); // save the custom fields


/*
* Filter the single entry and archive templates with our custom function
*/
function nicholls_fs_template_smart(){

    global $post;

	$fs_template_dir = dirname(__FILE__);
    $single_template_name = 'nicholls-faculty-staff-template.php';
    $archive_template_name = 'nicholls-faculty-staff-archive-template.php';

    if ( is_single() && 'n-faculty-staff' == get_post_type() ) {

        $template = locate_template( array( $single_template_name), true );
        if( empty( $template ) ) {
          include( $fs_template_dir . '/' . $single_template_name);
          exit();
        }

    } else if ( is_archive() && 'n-faculty-staff' == get_post_type() ) {

        $template = locate_template( array( $archive_template_name ), true );
        if(empty($template)) {
          include( $fs_template_dir . '/' . $archive_template_name);
          exit();
        }

    } else if ( is_tax( 'n-faculty-staff-taxonomy' ) ) {

        $template = locate_template( array( $archive_template_name ), true );
        if(empty($template)) {
          include( $fs_template_dir . '/' . $archive_template_name);
          exit();
        }
            
    }

}
add_filter('template_redirect', 'nicholls_fs_template_smart');



/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function nicholls_fs_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_nicholls_fs_';
	
	$meta_boxes['n_metabox'] = array(
		'id'         => 'n_metabox_fs',
		'title'      => __( 'Employee Information', 'cmb' ),
		'pages'      => array( 'n-faculty-staff', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		// 'cmb_styles' => true, // Enqueue the CMB stylesheet on the frontend
		'fields'     => array(
			array(
				'name'       => __( 'Employee Title', 'cmb' ),
				'desc'       => __( 'field description (optional)', 'cmb' ),
				'id'         => $prefix . 'employee_title',
				'type'       => 'text',
				// 'show_on_cb' => 'cmb_test_text_show_on_cb', // function should return a bool value
				// 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
				// 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
				// 'on_front'        => false, // Optionally designate a field to wp-admin only
				// 'repeatable'      => true,
			),	
			array(
				'name'       => __( 'Employee Departmnet', 'cmb' ),
				'desc'       => __( 'field description (optional)', 'cmb' ),
				'id'         => $prefix . 'employee_dept',
				'type'       => 'text',
				// 'show_on_cb' => 'cmb_test_text_show_on_cb', // function should return a bool value
				// 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
				// 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
				// 'on_front'        => false, // Optionally designate a field to wp-admin only
				// 'repeatable'      => true,
			),			
			array(
				'name' => __( 'Employee Email', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'employee_email',
				'type' => 'text_email',
				// 'repeatable' => true,
			),
			array(
				'name' => __( 'Employee Phone Number', 'cmb' ),
				'desc' => __( 'Please input full number with area code (xxx)xxx-xxxx', 'cmb' ),
				'id'   => $prefix . 'phone',
				'type' => 'text',
				// 'repeatable' => true,
			),
			array(
				'name' => __( 'Employee Office Location', 'cmb' ),
				'desc' => __( 'Please input building and room number if known.', 'cmb' ),
				'id'   => $prefix . 'office',
				'type' => 'text',
				// 'repeatable' => true,
			),						
			array(
				'name' => __( 'Employee Photo', 'cmb' ),
				'desc' => __( 'Please upload a proper photo', 'cmb' ),
				'id'   => $prefix . 'employee_photo',
				'type' => 'file',
			),
		)
	);

	/**
	 * Repeatable Field Groups
	 */
	$meta_boxes['n_field_group'] = array(
		'id'         => 'course_group_fs',
		'title'      => __( 'Faculty Courses', 'cmb' ),
		'pages'      => array( 'n-faculty-staff', ),
		'fields'     => array(
			array(
				'id'          => $prefix . 'courses',
				'type'        => 'group',
				'description' => __( 'Place information about courses taught below', 'cmb' ),
				'options'     => array(
					'group_title'   => __( 'Course {#}', 'cmb' ), // {#} gets replaced by row number
					'add_button'    => __( 'Add Another Course', 'cmb' ),
					'remove_button' => __( 'Remove Course', 'cmb' ),
					'sortable'      => true, // beta
				),
				// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
				'fields'      => array(
					array(
						'name' => 'Course or Class Title',
						'id'   => 'course_title',
						'type' => 'text',
						// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
					),
					array(
						'name' => 'Course Description',
						'description' => 'Write a short description for this course',
						'id'   => 'description',
						'type' => 'textarea_small',
					),
					array(
						'name' => __( 'Course Day', 'cmb' ),
						'desc' => __( 'Input meeting days (typically format as M/W/F,T/Th, or similar)', 'cmb' ),
						'id'   => 'course_meeting_days',
						'time_format' => 'l',
						'type' => 'text',
					),
					array(
						'name' => __( 'Class Time', 'cmb' ),
						'desc' => __( 'Format as XX:XX or use suggested format', 'cmb' ),
						'id'   => 'course_meeting_times',
						'type' => 'text_time',
					),											
				),
			),
		),
	);

	// Add other metaboxes as needed

	return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'nicholls_fs_metaboxes' );




add_filter( 'cmb_meta_boxes', 'cmb_sample_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function cmb_sample_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_cmb_';

	/**
	 * Sample metabox to demonstrate each field type included
	 */
	$meta_boxes['test_metabox'] = array(
		'id'         => 'test_metabox',
		'title'      => __( 'Test Metabox', 'cmb' ),
		'pages'      => array( 'page', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		// 'cmb_styles' => true, // Enqueue the CMB stylesheet on the frontend
		'fields'     => array(
			array(
				'name'       => __( 'Test Text', 'cmb' ),
				'desc'       => __( 'field description (optional)', 'cmb' ),
				'id'         => $prefix . 'test_text',
				'type'       => 'text',
				// 'show_on_cb' => 'cmb_test_text_show_on_cb', // function should return a bool value to show
				// 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
				// 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
				// 'on_front'        => false, // Optionally designate a field to wp-admin only
				// 'repeatable'      => true,
			),
			array(
				'name' => __( 'Test Text Small', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textsmall',
				'type' => 'text_small',
				// 'repeatable' => true,
			),
			array(
				'name' => __( 'Test Text Medium', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textmedium',
				'type' => 'text_medium',
				// 'repeatable' => true,
			),
			array(
				'name' => __( 'Website URL', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'url',
				'type' => 'text_url',
				// 'protocols' => array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet'), // Array of allowed protocols
				// 'repeatable' => true,
			),
			array(
				'name' => __( 'Test Text Email', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'email',
				'type' => 'text_email',
				// 'repeatable' => true,
			),
			array(
				'name' => __( 'Test Time', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_time',
				'type' => 'text_time',
			),
			array(
				'name' => __( 'Time zone', 'cmb' ),
				'desc' => __( 'Time zone', 'cmb' ),
				'id'   => $prefix . 'timezone',
				'type' => 'select_timezone',
			),
			array(
				'name' => __( 'Test Date Picker', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textdate',
				'type' => 'text_date',
			),
			array(
				'name' => __( 'Test Date Picker (UNIX timestamp)', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textdate_timestamp',
				'type' => 'text_date_timestamp',
				// 'timezone_meta_key' => $prefix . 'timezone', // Optionally make this field honor the timezone selected in the select_timezone specified above
			),
			array(
				'name' => __( 'Test Date/Time Picker Combo (UNIX timestamp)', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_datetime_timestamp',
				'type' => 'text_datetime_timestamp',
			),
			// This text_datetime_timestamp_timezone field type
			// is only compatible with PHP versions 5.3 or above.
			// Feel free to uncomment and use if your server meets the requirement
			// array(
			// 	'name' => __( 'Test Date/Time Picker/Time zone Combo (serialized DateTime object)', 'cmb' ),
			// 	'desc' => __( 'field description (optional)', 'cmb' ),
			// 	'id'   => $prefix . 'test_datetime_timestamp_timezone',
			// 	'type' => 'text_datetime_timestamp_timezone',
			// ),
			array(
				'name' => __( 'Test Money', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textmoney',
				'type' => 'text_money',
				// 'before'     => '£', // override '$' symbol if needed
				// 'repeatable' => true,
			),
			array(
				'name'    => __( 'Test Color Picker', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_colorpicker',
				'type'    => 'colorpicker',
				'default' => '#ffffff'
			),
			array(
				'name' => __( 'Test Text Area', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textarea',
				'type' => 'textarea',
			),
			array(
				'name' => __( 'Test Text Area Small', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textareasmall',
				'type' => 'textarea_small',
			),
			array(
				'name' => __( 'Test Text Area for Code', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_textarea_code',
				'type' => 'textarea_code',
			),
			array(
				'name' => __( 'Test Title Weeeee', 'cmb' ),
				'desc' => __( 'This is a title description', 'cmb' ),
				'id'   => $prefix . 'test_title',
				'type' => 'title',
			),
			array(
				'name'    => __( 'Test Select', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_select',
				'type'    => 'select',
				'options' => array(
					'standard' => __( 'Option One', 'cmb' ),
					'custom'   => __( 'Option Two', 'cmb' ),
					'none'     => __( 'Option Three', 'cmb' ),
				),
			),
			array(
				'name'    => __( 'Test Radio inline', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_radio_inline',
				'type'    => 'radio_inline',
				'options' => array(
					'standard' => __( 'Option One', 'cmb' ),
					'custom'   => __( 'Option Two', 'cmb' ),
					'none'     => __( 'Option Three', 'cmb' ),
				),
			),
			array(
				'name'    => __( 'Test Radio', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_radio',
				'type'    => 'radio',
				'options' => array(
					'option1' => __( 'Option One', 'cmb' ),
					'option2' => __( 'Option Two', 'cmb' ),
					'option3' => __( 'Option Three', 'cmb' ),
				),
			),
			array(
				'name'     => __( 'Test Taxonomy Radio', 'cmb' ),
				'desc'     => __( 'field description (optional)', 'cmb' ),
				'id'       => $prefix . 'text_taxonomy_radio',
				'type'     => 'taxonomy_radio',
				'taxonomy' => 'category', // Taxonomy Slug
				// 'inline'  => true, // Toggles display to inline
			),
			array(
				'name'     => __( 'Test Taxonomy Select', 'cmb' ),
				'desc'     => __( 'field description (optional)', 'cmb' ),
				'id'       => $prefix . 'text_taxonomy_select',
				'type'     => 'taxonomy_select',
				'taxonomy' => 'category', // Taxonomy Slug
			),
			array(
				'name'     => __( 'Test Taxonomy Multi Checkbox', 'cmb' ),
				'desc'     => __( 'field description (optional)', 'cmb' ),
				'id'       => $prefix . 'test_multitaxonomy',
				'type'     => 'taxonomy_multicheck',
				'taxonomy' => 'post_tag', // Taxonomy Slug
				// 'inline'  => true, // Toggles display to inline
			),
			array(
				'name' => __( 'Test Checkbox', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'test_checkbox',
				'type' => 'checkbox',
			),
			array(
				'name'    => __( 'Test Multi Checkbox', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_multicheckbox',
				'type'    => 'multicheck',
				'options' => array(
					'check1' => __( 'Check One', 'cmb' ),
					'check2' => __( 'Check Two', 'cmb' ),
					'check3' => __( 'Check Three', 'cmb' ),
				),
				// 'inline'  => true, // Toggles display to inline
			),
			array(
				'name'    => __( 'Test wysiwyg', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'test_wysiwyg',
				'type'    => 'wysiwyg',
				'options' => array( 'textarea_rows' => 5, ),
			),
			array(
				'name' => __( 'Test Image', 'cmb' ),
				'desc' => __( 'Upload an image or enter a URL.', 'cmb' ),
				'id'   => $prefix . 'test_image',
				'type' => 'file',
			),
			array(
				'name'         => __( 'Multiple Files', 'cmb' ),
				'desc'         => __( 'Upload or add multiple images/attachments.', 'cmb' ),
				'id'           => $prefix . 'test_file_list',
				'type'         => 'file_list',
				'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
			),
			array(
				'name' => __( 'oEmbed', 'cmb' ),
				'desc' => __( 'Enter a youtube, twitter, or instagram URL. Supports services listed at <a href="http://codex.wordpress.org/Embeds">http://codex.wordpress.org/Embeds</a>.', 'cmb' ),
				'id'   => $prefix . 'test_embed',
				'type' => 'oembed',
			),
		),
	);

	/**
	 * Metabox to be displayed on a single page ID
	 */
	$meta_boxes['about_page_metabox'] = array(
		'id'         => 'about_page_metabox',
		'title'      => __( 'About Page Metabox', 'cmb' ),
		'pages'      => array( 'page', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'show_on'    => array( 'key' => 'id', 'value' => array( 2, ), ), // Specific post IDs to display this metabox
		'fields'     => array(
			array(
				'name' => __( 'Test Text', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . '_about_test_text',
				'type' => 'text',
			),
		)
	);

	/**
	 * Repeatable Field Groups
	 */
	$meta_boxes['field_group'] = array(
		'id'         => 'field_group',
		'title'      => __( 'Repeating Field Group', 'cmb' ),
		'pages'      => array( 'page', ),
		'fields'     => array(
			array(
				'id'          => $prefix . 'repeat_group',
				'type'        => 'group',
				'description' => __( 'Generates reusable form entries', 'cmb' ),
				'options'     => array(
					'group_title'   => __( 'Entry {#}', 'cmb' ), // {#} gets replaced by row number
					'add_button'    => __( 'Add Another Entry', 'cmb' ),
					'remove_button' => __( 'Remove Entry', 'cmb' ),
					'sortable'      => true, // beta
				),
				// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
				'fields'      => array(
					array(
						'name' => 'Entry Title',
						'id'   => 'title',
						'type' => 'text',
						// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
					),
					array(
						'name' => 'Description',
						'description' => 'Write a short description for this entry',
						'id'   => 'description',
						'type' => 'textarea_small',
					),
					array(
						'name' => 'Entry Image',
						'id'   => 'image',
						'type' => 'file',
					),
					array(
						'name' => 'Image Caption',
						'id'   => 'image_caption',
						'type' => 'text',
					),
				),
			),
		),
	);

	/**
	 * Metabox for the user profile screen
	 */
	$meta_boxes['user_edit'] = array(
		'id'         => 'user_edit',
		'title'      => __( 'User Profile Metabox', 'cmb' ),
		'pages'      => array( 'user' ), // Tells CMB to use user_meta vs post_meta
		'show_names' => true,
		'cmb_styles' => false, // Show cmb bundled styles.. not needed on user profile page
		'fields'     => array(
			array(
				'name'     => __( 'Extra Info', 'cmb' ),
				'desc'     => __( 'field description (optional)', 'cmb' ),
				'id'       => $prefix . 'exta_info',
				'type'     => 'title',
				'on_front' => false,
			),
			array(
				'name'    => __( 'Avatar', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'avatar',
				'type'    => 'file',
				'save_id' => true,
			),
			array(
				'name' => __( 'Facebook URL', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'facebookurl',
				'type' => 'text_url',
			),
			array(
				'name' => __( 'Twitter URL', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'twitterurl',
				'type' => 'text_url',
			),
			array(
				'name' => __( 'Google+ URL', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'googleplusurl',
				'type' => 'text_url',
			),
			array(
				'name' => __( 'Linkedin URL', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'linkedinurl',
				'type' => 'text_url',
			),
			array(
				'name' => __( 'User Field', 'cmb' ),
				'desc' => __( 'field description (optional)', 'cmb' ),
				'id'   => $prefix . 'user_text_field',
				'type' => 'text',
			),
		)
	);

	/**
	 * Metabox for an options page. Will not be added automatically, but needs to be called with
	 * the `cmb_metabox_form` helper function. See wiki for more info.
	 */
	$meta_boxes['options_page'] = array(
		'id'      => 'options_page',
		'title'   => __( 'Theme Options Metabox', 'cmb' ),
		'show_on' => array( 'key' => 'options-page', 'value' => array( $prefix . 'theme_options', ), ),
		'fields'  => array(
			array(
				'name'    => __( 'Site Background Color', 'cmb' ),
				'desc'    => __( 'field description (optional)', 'cmb' ),
				'id'      => $prefix . 'bg_color',
				'type'    => 'colorpicker',
				'default' => '#ffffff'
			),
		)
	);

	// Add other metaboxes as needed

	return $meta_boxes;
}


/**
* Display custom meta based on meta key
*
*/
function nicholls_fs_display_meta_item( $meta_item = '', $return = false ) {

	$meta_items = array(
		'_nicholls_fs_employee_title' => array(
			'name' => 'Title',
			'class' => 'nicholls-fs-title'
		),
		'_nicholls_fs_employee_dept' => array(
			'name' => 'Department',
			'class' => 'nicholls-fs-dept'
		),
		'_nicholls_fs_employee_email' => array(
			'name' => 'Email',
			'class' => 'nicholls-fs-email'
		),
		'_nicholls_fs_phone' => array(
			'name' => 'Phone',
			'class' => 'nicholls-fs-phone'
		),
		'_nicholls_fs_office' => array(
			'name' => 'Office Location',
			'class' => 'nicholls-fs-office'
		)
	);

	$meta_item_data = get_post_meta( get_the_ID(), $meta_item, true );
	
	if ( empty( $meta_item_data ) ) return;
	
	if ( $meta_item == '_nicholls_fs_employee_email' ) {
		
		$e_email = explode( '@', $meta_item_data );

		$display .= '<div class="' . $meta_items[$meta_item]['class'] . '"><strong>' . $meta_items[$meta_item]['name'] . ':</strong> ';
		
		$display .= '<script type="text/javascript">' . "\n";
		$display .= '//<![CDATA[' . "\n";
		$display .= 'var n_u = "' . $e_email[0] . '";' . "\n";
		$display .= 'var n_dd = "' . $e_email[1] . '";' . "\n";
		$display .= 'var n_dot = "' . $employee_name . '";' . "\n";
		$display .= '//]]>' . "\n";
		$display .= '</script>' . "\n";
				
		$display .= '<script type="text/javascript">' . "\n";
		$display .= '//<![CDATA[' . "\n";
		$display .= '<!--' . "\n";
		$display .= ' var u = "";' . "\n";
		$display .= 'var d = "";' . "\n";
		$display .= 'var cmd = "m"+""+"a";' . "\n";
		$display .= 'var to = "t";' . "\n";
			   
		$display .= 'cmd = cmd + ""+""+"i";' . "\n";
		$display .= 'to = to+"o:";' . "\n";
		$display .= 'cmd = cmd +"l"+to;' . "\n";
		$display .= 'loc = cmd+n_u;' . "\n";
		$display .= 'loc = loc + "%40";' . "\n";
		$display .= 'loc = loc + n_dd;' . "\n";
		$display .= 'document.write("<a class=\"nicholls-fs-modal-email\" href=\""+loc+"\">"+n_u+"&#64;"+n_dd+"<\/a>");' . "\n";

		$display .= '//-->' . "\n";
		$display .= '//]]>' . "\n";
		$display .= '</script>' . "\n";
		
		$display .= '</div>';		
		
	} else
		$display = '<div class="' . $meta_items[$meta_item]['class'] . '"><strong>' . $meta_items[$meta_item]['name'] . ':</strong> ' . $meta_item_data . '</div>';
	
	if ( !$return )
		echo $display;
	else
		return $display;

}

function nicholls_fs_archive_order( $vars ) {
  if ( !is_admin() && isset( $vars['post_type'] ) && is_post_type_hierarchical($vars['post_type'] ) ) {
    $vars['orderby'] = 'menu_order';
    $vars['order'] = 'ASC';
  }

  return $vars;
}
add_filter( 'request', 'nicholls_fs_archive_order');