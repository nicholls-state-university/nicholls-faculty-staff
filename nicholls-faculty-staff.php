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
 * Include CMB2 framework https://github.com/WebDevStudios/CMB2
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */

if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

add_action('init', 'nicholls_fs_init');
/**
* Initialize and register custom post types
*/
function nicholls_fs_init() {  

    // Global config variagle
    global $nicholls_fs_core;
    
    // Setup custom post type
    $args = array(  
        'label' => __( 'Nicholls ' ) . $nicholls_fs_core->default_title,
        'labels' => array(
                'name' => $nicholls_fs_core->default_title,
                'singular_name' => $nicholls_fs_core->default_title,
                'add_new' => __( 'Add New ' ) . $nicholls_fs_core->default_title,
                'add_new_item' => __( 'Add New ' ) . $nicholls_fs_core->default_title,
                'edit_item' => __( 'Edit ' ) . $nicholls_fs_core->default_title,
                'new_item' => __( 'Add New ' ) . $nicholls_fs_core->default_title,
                'view_item' => __( 'View ' ) . $nicholls_fs_core->default_title,
                'search_items' => __( 'Search ' ) . $nicholls_fs_core->default_title,
                'not_found' => __( 'Nothing found' ),
                'not_found_in_trash' => __( 'Nothing found in trash' )
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
			'slug' => $nicholls_fs_core->default_url,
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
				'name'              => _x('Departments or Areas', 'prefix_portfolio', 'text_domain'),
				'singular_name'     => _x('Department or Area', 'prefix_portfolio', 'text_domain'),
				'menu_name'         => __('Departments or Areas', 'text_domain'),
				'all_items'         => __('All Departments or Areas', 'text_domain'),
				'edit_item'         => __('Edit Department or Area', 'text_domain'),
				'view_item'         => __('View Department or Area', 'text_domain'),
				'update_item'       => __('Update Department or Area', 'text_domain'),
				'add_new_item'      => __('Add New Department or Area', 'text_domain'),
				'new_item_name'     => __('New Department or Area Name', 'text_domain'),
				'search_items'      => __('Search Departments or Areas', 'text_domain'),
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

	if ( is_user_logged_in() ) 
		add_action( 'wp_ajax_nicholls-fs-form-email', 'nicholls_fs_ajax_simple_contact_form' );
	else
		add_action( 'wp_ajax_nopriv_nicholls-fs-form-email', 'nicholls_fs_ajax_simple_contact_form' );
    
}

add_action( 'wp_enqueue_scripts', 'nicholls_fs_js_enqueue' );
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

add_action( 'phpmailer_init', 'nicholls_fs_configure_smtp', 999 );
/**
* Configure SMTP & Advanced PHPMail options
*/
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
 * CMB2 Tabbed Theme Options
 *
 * @author    Arushad Ahmed <@dash8x, contact@arushad.org>
 * @link      http://arushad.org/how-to-create-a-tabbed-options-page-for-your-wordpress-theme-using-cmb
 * @version   0.1.0
 */
class nicholls_fs_core {

    /**
     * Default Option key
     * @var string
     */
    public $key = 'nicholls_fs';

    /**
     * Options prefix for all saved options
     * @var string
     */    
    // Start with an underscore to hide fields from custom fields list
	public $prefix = '_nicholls_fs_';
	
    /**
     * Default public variables.
     * @var string
     */
    public $general_options = array();
	public $default_url = 'faculty-staff';
	public $default_area_url = 'faculty-staff-departments';
	public $default_title = 'Faculty &amp; Staff';

    /**
     * Constructor
     * @since 0.1.0
     */
    public function __construct() {
        // Get our core settings
        $this->general_options = get_option( $this->prefix . 'general_options' );
        
        // Make sure text - url is properly sanitized.
        $this->default_url = sanitize_title( $this->general_options['default_url'] );
        if ( empty( $this->default_url ) ) $this->default_url = 'faculty-staff';

        // Make sure text - url is properly sanitized.
        $this->default_area_url = sanitize_title( $this->general_options['default_area_url'] );
        if ( empty( $this->default_area_url ) ) $this->default_area_url = 'faculty-staff-departments';   
        
        // Set title default if not found.
        $this->default_title = $this->general_options['default_title'];
        if ( empty( $this->default_title ) ) $this->default_title = 'Faculty &amp; Staff';  
        
    }

}

$nicholls_fs_core = new nicholls_fs_core();
global $nicholls_fs_core;

/**
 * CMB2 Tabbed Theme Options
 *
 * @author    Arushad Ahmed <@dash8x, contact@arushad.org>
 * @link      http://arushad.org/how-to-create-a-tabbed-options-page-for-your-wordpress-theme-using-cmb
 * @version   0.1.0
 */
class CMB2_Tab_Admin {

    /**
     * Default Option key
     * @var string
     */
    private $key = 'nicholls_fs';

    /**
     * Array of metaboxes/fields
     * @var array
     */
    protected $option_metabox = array();

    /**
     * Options Page title
     * @var string
     */
    protected $title = '';

    /**
     * Options prefix for all saved options
     * @var string
     */    
    // Start with an underscore to hide fields from custom fields list
	protected $prefix = '_nicholls_fs_';

    /**
     * Options Tab Pages
     * @var array
     */
    protected $options_pages = array();

    /**
     * Constructor
     * @since 0.1.0
     */
    public function __construct() {
        global $nicholls_fs_core;
        // Set our title
        $this->title = $nicholls_fs_core->default_title . __( ' Options', 'nicholls_fs' );
    }

    /**
     * Initiate our hooks
     * @since 0.1.0
     */
    public function hooks() {
        add_action( 'admin_init', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'add_options_page' ) ); //create tab pages
    }

    /**
     * Register our setting tabs to WP
     * @since  0.1.0
     */
    public function init() {
    	$option_tabs = self::option_fields();
        foreach ( $option_tabs as $index => $option_tab ) {
        	register_setting( $option_tab['id'], $option_tab['id'] );
        }
    }

    /**
     * Add menu options page
     * @since 0.1.0
     */
    public function add_options_page() {        
        $option_tabs = self::option_fields();
                
        foreach ($option_tabs as $index => $option_tab) {
        	if ( $index == 0) {
        		$this->options_pages[] = add_submenu_page( 'edit.php?post_type=n-faculty-staff', $this->title, $this->title, 'manage_options', $option_tab['id'], array( $this, 'admin_page_display' ) );
        	} else {
        		$this->options_pages[] = add_submenu_page( 'edit.php?post_type=n-faculty-staff', $option_tab['title'], $option_tab['title'], 'manage_options', $option_tab['id'], array( $this, 'admin_page_display' ) );
        	}
        }
    }

    /**
     * Admin page markup. Mostly handled by CMB2
     * @since  0.1.0
     */
    public function admin_page_display() {
    	$option_tabs = self::option_fields(); //get all option tabs
    	$tab_forms = array();     	   	
        ?>
        <div class="wrap cmb_options_page <?php echo $this->key; ?>">        	
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            
            <!-- Options Page Nav Tabs -->           
            <h2 class="nav-tab-wrapper">
            	<?php foreach ($option_tabs as $option_tab) :
            		$tab_slug = $option_tab['id'];
            		$nav_class = 'nav-tab';
            		if ( $tab_slug == $_GET['page'] ) {
            			$nav_class .= ' nav-tab-active'; //add active class to current tab
            			$tab_forms[] = $option_tab; //add current tab to forms to be rendered
            		}
            	?>            	
            	<a class="<?php echo $nav_class; ?>" href="<?php menu_page_url( $tab_slug ); ?>"><?php esc_attr_e($option_tab['title']); ?></a>
            	<?php endforeach; ?>
            </h2>
            <!-- End of Nav Tabs -->

            <?php foreach ($tab_forms as $tab_form) : //render all tab forms (normaly just 1 form) ?>
            <div id="<?php esc_attr_e($tab_form['id']); ?>" class="group">
            	<?php cmb2_metabox_form( $tab_form, $tab_form['id'] ); ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Defines the theme option metabox and field configuration
     * @since  0.1.0
     * @return array
     */
    public function option_fields() {

        global $nicholls_fs_core;
        
        // Only need to initiate the array once per page-load
        if ( ! empty( $this->option_metabox ) ) {
            return $this->option_metabox;
        }        

        $this->option_metabox[] = array(
            'id'         => $nicholls_fs_core->prefix . 'general_options', //id used as tab page slug, must be unique
            'title'      => 'General Options',
            'show_on'    => array( 'key' => 'options-page', 'value' => array( $nicholls_fs_core->prefix . 'general_options' ), ), //value must be same as id
            'show_names' => true,
            'fields'     => array(
				
				array(
					'name' => 'Faculty & Staff Options',
					'desc' => '<p>General options for faculty and staff listings.</p>',
					'type' => 'title',
					'id'   => 'title'
				),
            	array(
					'name' => __('Default Title', 'theme_textdomain'),
					'desc' => __('Front end title directory areas.', 'theme_textdomain'),
					'id' => 'default_title',
					'default' => $nicholls_fs_core->default_title,				
					'type' => 'text',
				),				
            	array(
					'name' => __('Default URL', 'theme_textdomain'),
					'desc' => __('Front end URL slug for directory profiles.', 'theme_textdomain'),
					'id' => 'default_url',
					'default' => 'faculty-staff',				
					'type' => 'text',
				),
            	array(
					'name' => __('Default Area URL', 'theme_textdomain'),
					'desc' => __('Front end URL slug for departments or areas', 'theme_textdomain'),
					'id' => 'default_area_url',
					'default' => 'faculty-staff-departments',				
					'type' => 'text',
				)
			)
        );
        
        $this->option_metabox[] = array(
            'id'         => 'advanced_options',
            'title'      => 'Advanced Settings',
            'show_on'    => array( 'key' => 'options-page', 'value' => array( $nicholls_fs_core->prefix . 'general_options' ), ), //value must be same as id
            'show_names' => true,
            'fields'     => array(

			)
        );
        
        //insert extra tabs here

        return $this->option_metabox;
    }

    /**
     * Returns the option key for a given field id
     * @since  0.1.0
     * @return array
     */
    public function get_option_key($field_id) {
    	$option_tabs = $this->option_fields();
    	foreach ($option_tabs as $option_tab) { //search all tabs
    		foreach ($option_tab['fields'] as $field) { //search all fields
    			if ($field['id'] == $field_id) {
    				return $option_tab['id'];
    			}
    		}
    	}
    	return $this->key; //return default key if field id not found
    }

    /**
     * Public getter method for retrieving protected/private variables
     * @since  0.1.0
     * @param  string  $field Field to retrieve
     * @return mixed          Field value or exception is thrown
     */
    public function __get( $field ) {

        // Allowed fields to retrieve
        if ( in_array( $field, array( 'key', 'fields', 'title', 'options_pages' ), true ) ) {
            return $this->{$field};
        }
        if ( 'option_metabox' === $field ) {
            return $this->option_fields();
        }

        throw new Exception( 'Invalid property: ' . $field );
    }

}

// Activate options area.
$nicholls_fs_admin = new CMB2_Tab_Admin();
$nicholls_fs_admin->hooks();

/** End Tabbed Options Interfaces **/

add_filter('cmb2_meta_box_url', 'nicholls_fs_url_filter');
/**
* Filter to use protocol independent URL for CMB tools
*
*/
function nicholls_fs_url_filter( $c_url ) {
	$c_url = str_replace( 'http://', '//', $c_url );
	return $c_url;
}

/**
* Display Departments or Areas
*/
function nicholls_fs_display_departments() {		

	global $nicholls_fs_core;
	
	echo '<div class="nicholls-fs-departments clear-group">';

	$taxonomy = 'n-faculty-staff-taxonomy';
	$terms = get_terms( $taxonomy, '' );

	if ($terms) {
		echo '<strong>Departments or Areas</strong><br />';
		echo '<ul class="nicholls-fs-department-links">';
		echo '<li class="nicholls-fs-department-link">' . '<a href="' . esc_attr( get_site_url() . '/' . $nicholls_fs_core->default_url ) . '" title="' . __( "View all" ) . '" ' . '>' . __( "View all" ) . '</a></li>';
		foreach($terms as $term) {
			echo '<li class="nicholls-fs-department-link">' . '<a href="' . esc_attr( get_term_link($term, $taxonomy) ) . '" title="' . sprintf( __( "View all posts in %s" ), $term->name ) . '" ' . '>' . $term->name.'</a></li>';
		}
		echo '</ul>';
	}

	echo '</div>';
}
			
			
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

/**
* Custom function to get current URL when *_permalink won't work.
*
*/
function nicholls_fs_get_url() {
	  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
	  $url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
	  $url .= $_SERVER["REQUEST_URI"];
	  return $url;
}


add_filter( 'image_resize_dimensions', 'nicholls_fs_filter_resize_dimensions', 10, 6 );
/**
* Filter resizing of thumbnails
*
*/
function nicholls_fs_filter_resize_dimensions( $payload, $orig_w, $orig_h, $dest_w, $dest_h, $crop ){
 
	// Change this to a conditional that decides whether you want to override the defaults for this image or not.
	if( false )
		return $payload;
		
	$t_type = false;
	if ( $dest_h == 360 && $dest_w == 240 ) $t_type = 'nicholls-fs-medium';
	if ( $dest_h == 180 && $dest_w == 120 ) $t_type = 'nicholls-fs-thumb';
	
	// If we aren't dealing with a portrait image resize, then bail
	if ( $t_type != false ) {
		return $payload;
	}

	if ( $crop ) {
		// crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
		$aspect_ratio = $orig_w / $orig_h;
		$new_w = min( $dest_w, $orig_w );
		$new_h = min( $dest_h, $orig_h );
 
		if ( !$new_w ) {
			$new_w = intval( $new_h * $aspect_ratio );
		}
 
		if ( !$new_h ) {
			$new_h = intval( $new_w / $aspect_ratio );
		}
 
		$size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );
 
		$crop_w = round( $new_w / $size_ratio );
		$crop_h = round( $new_h / $size_ratio );
 
		//$s_x = 0; // [[ formerly ]] ==> floor( ($orig_w - $crop_w) / 2 );
		// Use former method to center width crop
		$s_x = floor( ($orig_w - $crop_w) / 2 );
		$s_y = 0; // [[ formerly ]] ==> floor( ($orig_h - $crop_h) / 2 );
	} else {
		// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
		$crop_w = $orig_w;
		$crop_h = $orig_h;
 
		//$s_x = 0;
        // Use former method to center width crop
		$s_x = floor( ($orig_w - $crop_w) / 2 );		
		$s_y = 0;
 
		list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
	}
 
	// if the resulting image would be the same size or larger we don't want to resize it
	if ( $new_w >= $orig_w && $new_h >= $orig_h )
		return false;
 
	// the return array matches the parameters to imagecopyresampled()
	// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
	return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
 
}

add_action( 'wp_enqueue_scripts', 'nicholls_fs_resources_load', 11 );
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

add_action('save_post', 'nicholls_fs_save_meta', 1, 2); // save the custom fields
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

add_filter('template_redirect', 'nicholls_fs_template_smart');
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
          $return_template = $fs_template_dir . '/' . $single_template_name;
          nicholls_fs_do_theme_redirect($return_template);
        }

    } else if ( is_archive() && 'n-faculty-staff' == get_post_type() ) {

        $template = locate_template( array( $archive_template_name ), true );
        if(empty($template)) {
          $return_template = $fs_template_dir . '/' . $archive_template_name;
          nicholls_fs_do_theme_redirect($return_template);
        }

    } else if ( is_tax( 'n-faculty-staff-taxonomy' ) ) {

        $template = locate_template( array( $archive_template_name ), true );
        if(empty($template)) {
          $return_template = $fs_template_dir . '/' . $archive_template_name;
          nicholls_fs_do_theme_redirect($return_template);
        }
            
    }
}

/**
* Helper function for template redirections
*/
function nicholls_fs_do_theme_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}


/**
 * Limit, change number of posts in archive pages
 */
add_filter('pre_get_posts', 'nicholls_fs_pre_get_posts');
function nicholls_fs_pre_get_posts($query){
	if ( $query->is_archive && $query->query_vars['post_type'] == 'n-faculty-staff') {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );		
	}
	return $query;
}

add_filter( 'cmb2_init', 'nicholls_fs_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function nicholls_fs_metaboxes() {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_nicholls_fs_';
	
	// Start with a new metabox
	$cmb_demo = new_cmb2_box( array(
		'id'            => $prefix . 'n_metabox_fs',
		'title'         => __( 'Employee Information', 'nicholls_fs' ),
		'object_types'  => array( 'n-faculty-staff', ), // Post type
		'context'       => 'normal',
		'priority'      => 'high',
		'show_names'    => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // true to keep the metabox closed by default
	) );
	
	// Add field to metabox group
	$cmb_demo->add_field( array(
		'name'       => __( 'Employee Title', 'nicholls_fs' ),
		'desc'       => __( 'Use full title as indicated by Human Resources', 'nicholls_fs' ),
		'id'         => $prefix . 'employee_title',
		'type'       => 'text',
		// 'show_on_cb' => 'yourprefix_hide_if_no_cats', // Check updated. function should return a bool value
		// 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
		// 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
		// 'on_front'        => false, // Optionally designate a field to wp-admin only
		// 'repeatable'      => true,
	) );
	
	// Add field to metabox group
	$cmb_demo->add_field( array(
		'name'       => __( 'Employee Departmnet', 'nicholls_fs' ),
		'desc'       => __( 'Use assigned department as indicated by Human Resources', 'nicholls_fs' ),
		'id'         => $prefix . 'employee_dept',
		'type'       => 'text'
	) );		

	// Add field to metabox group
	$cmb_demo->add_field( array(
		'name'       => __( 'Employee Email', 'nicholls_fs' ),
		'desc'       => __( 'Use the full confirmed nicholls.edu email address', 'nicholls_fs' ),
		'id'         => $prefix . 'employee_email',
		'type'       => 'text_email'
	) );

	// Add field to metabox group
	$cmb_demo->add_field( array(
		'name'       => __( 'Employee Phone Number', 'nicholls_fs' ),
		'desc'       => __( 'Please input full phone number with area code xxx.xxx.xxxx', 'nicholls_fs' ),
		'id'         => $prefix . 'phone',
		'type'       => 'text'
	) );

	// Add field to metabox group
	$cmb_demo->add_field( array(
		'name'       => __( 'Employee Office Location', 'nicholls_fs' ),
		'desc'       => __( 'Please upload a recent headshot photo', 'nicholls_fs' ),
		'id'         => $prefix . 'office',
		'type'       => 'text'
	) );

	// Add field to metabox group
	$cmb_demo->add_field( array(
		'name' => __( 'Employee Photo', 'nicholls_fs' ),
		'desc' => __( 'Upload an image or enter a URL.', 'nicholls_fs' ),
		'id'   => $prefix . 'employee_photo',
		'type' => 'file',
	) );

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


add_filter( 'request', 'nicholls_fs_archive_order');
/**
* Reset listings to alphabetical
*/
function nicholls_fs_archive_order( $vars ) {
  if ( !is_admin() && isset( $vars['post_type'] ) && $vars['post_type'] == 'n-faculty-staff' ) {
    $vars['orderby'] = 'menu_order';
    $vars['order'] = 'ASC';
  }

  return $vars;
}