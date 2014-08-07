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
    $args = array(  
        'label' => __( 'Nicholls Faculty & Staff' ),
        'labels' => array(
                'name' => __( 'Nicholls Faculty & Staff' ),
                'singular_name' => __( 'Nicholls Faculty & Staff' ),
                'add_new' => __( 'Add New Nicholls Faculty & Staff' ),
                'add_new_item' => __( 'Add New Nicholls Faculty & Staff' ),
                'edit_item' => __( 'Edit Nicholls Faculty & Staff' ),
                'new_item' => __( 'Add New Nicholls Faculty & Staff' ),
                'view_item' => __( 'View Nicholls Faculty & Staff' ),
                'search_items' => __( 'Search Nicholls Faculty & Staff' ),
                'not_found' => __( 'No Nicholls faculty & ftaff found' ),
                'not_found_in_trash' => __( 'No Nicholls faculty & staff found in trash' )
		),
        'public' => true,  
        'show_ui' => true,  
        'capability_type' => 'post',  
        'hierarchical' => true,  
        'rewrite' => true,  
        'supports' => array('title', 'editor', 'thumbnail', 'revisions', 'page-attributes'),
        'register_meta_box_cb' => 'nicholls_fs_add_metaboxes' 
       );  
    register_post_type( 'n-faculty-staff' , $args );  
    
}  
add_action('init', 'nicholls_fs_init');

/**
* Create metaboxes used by custom post type registration callback.
*/
function nicholls_fs_add_metaboxes() {
    add_meta_box('nicholls-fs-options', __( 'Nicholls Faculty & Staff Options' ), 'nicholls_fs_options', 'nicholls-fs', 'normal', 'default');
}

/**
* Metabox HTML
*/
function nicholls_fs_options() {
    global $post;
    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="nicholls_emergency_options_noncename" id="nicholls_emergency_options_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    echo '<label for="_nicholls_emergency_link_url"><strong>Nicholls Emergnecy Link URL</strong></label>';

    // Get the location data if its already been entered
    $nicholls_emergency_notice_link_url = get_post_meta( $post->ID, '_nicholls_emergency_link_url', true );
    // Echo out the field
    echo '<input type="text" id="_nicholls_emergency_link_url" name="_nicholls_emergency_link_url" value="' . $nicholls_emergency_notice_link_url  . '" class="widefat" />';
    echo '<p>Input the full URL address to link the emergency notice title. <strong>The full URL should include http://</strong></p>';

}

/**
* Save the Metabox Data
*/
function nicholls_fs_save_meta($post_id, $post) {
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['nicholls_emergency_options_noncename'], plugin_basename( __FILE__ ) ) )
		return $post->ID;

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

/*	
	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	$nicholls_emergency_notice_link_url_meta['_nicholls_emergency_notice_link_url'] = $_POST['_nicholls_emergency_notice_link_url'];

	// Add values of $events_meta as custom fields
	foreach ( $nicholls_emergency_notice_link_url_meta as $key => $value ) { // Cycle through the $events_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		
		$value = implode( ',', (array)$value ); // If $value is an array, make it a CSV (unlikely)
		
		if( get_post_meta( $post->ID, $key, FALSE ) ) { // If the custom field already has a value
			update_post_meta( $post->ID, $key, $value );
		} else { // If the custom field doesn't have a value
			add_post_meta( $post->ID, $key, $value );
		}
		
		if( !$value ) delete_post_meta( $post->ID, $key ); // Delete if blank
	}
*/
}
add_action('save_post', 'nicholls_fs_save_meta', 1, 2); // save the custom fields


/*
* Filter the single_template with our custom function
*/
function nicholls_fs_template( $template ) {

	/* Checks for single template by post type */
	if ( 'n-faculty-staff' == get_post_type( get_queried_object_id() ) ) {
		$fs_template = dirname(__FILE__) . '/nicholls-faculty-staff-template.php';

		if( file_exists( $fs_template ) ) $template = $fs_template;
	}
	
    return $template;
}
add_filter('single_template', 'nicholls_fs_template');


