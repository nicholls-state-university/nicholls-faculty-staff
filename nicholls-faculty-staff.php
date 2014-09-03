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
        'public' => true, 
        'show_ui' => true,  
        'capability_type' => 'post',  
        'hierarchical' => true,
        'has_archive' => true,
        'rewrite' => array( 'slug' => 'faculty-staff'),  
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes'),
        'register_meta_box_cb' => 'nicholls_fs_add_metaboxes' 
       );  
    register_post_type( 'n-faculty-staff' , $args );
    
    // Setup custom image size  
    add_image_size( 'nicholls-fs-medium', 240, 360 );
    add_image_size( 'nicholls-fs-thumb', 120, 180 );
    
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
				'show_on_cb' => 'cmb_test_text_show_on_cb', // function should return a bool value
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
				'show_on_cb' => 'cmb_test_text_show_on_cb', // function should return a bool value
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