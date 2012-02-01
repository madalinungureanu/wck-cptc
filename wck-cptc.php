<?php
/*
Plugin Name: WCK CPTC
Description: Creates Custom Post types
*/

/* include Custom Fields Creator API */
require_once('wordpress-creation-kit-api/wordpress-creation-kit.php');

/* Create the WCK Page */
$args = array(							
			'page_title' => 'Wordpress Creation Kit',
			'menu_title' => 'WCK',
			'capability' => 'edit_theme_options',
			'menu_slug' => 'wck-page',									
			'page_type' => 'menu_page',
			'position' => 30
		);
new WCK_CPTC_WCK_Page_Creator( $args );

add_action('admin_menu', 'wck_remove_wck_submanu_page', 11);
function wck_remove_wck_submanu_page(){	
	remove_submenu_page( 'wck-page', 'wck-page' );
}

/* Create the CPTC Page */
$args = array(							
			'page_title' => 'WCK Custom Post Type Creator',
			'menu_title' => 'Custom Post Type Creator',
			'capability' => 'edit_theme_options',
			'menu_slug' => 'cptc-page',									
			'page_type' => 'submenu_page',
			'parent_slug' => 'wck-page'			
		);
$cptc_page = new WCK_CPTC_WCK_Page_Creator( $args );


/* Add Scripts */
add_action('admin_enqueue_scripts', 'wck_cptc_print_scripts' );
function wck_cptc_print_scripts($hook){		
	if( 'wck_page_cptc-page' == $hook ){			
		wp_register_style('wck-cptc-css', plugins_url('/css/wck-cptc.css', __FILE__));
		wp_enqueue_style('wck-cptc-css');	
	}	
}

/* create the meta box */
add_action( 'init', 'wck_cptc_create_box', 11 );
function wck_cptc_create_box(){
	
	/* get registered taxonomies */
	$args = array( 
				'public'   => true 
			);
	$output = 'objects';
	$taxonomies = get_taxonomies($args,$output);
	$taxonomie_names = array();

	foreach ($taxonomies  as $taxonomie ) {
		if ( $taxonomie->name != 'nav_menu' && $taxonomie->name != 'post_format')
			$taxonomie_names[] = $taxonomie->name;
	}
	
	/* set up the fields array */
	$cpt_creation_fields = array( 
		array( 'type' => 'text', 'title' => 'Post type', 'description' => '(max. 20 characters, can not contain capital letters, hyphens, or spaces)', 'required' => true ), 
		array( 'type' => 'textarea', 'title' => 'Description', 'description' => 'A short descriptive summary of what the post type is.' ),
		array( 'type' => 'text', 'title' => 'Singular Label', 'required' => true, 'description' => 'ex. Book' ),
		array( 'type' => 'text', 'title' => 'Plural Label', 'required' => true, 'description' => 'ex. Books' ),
		array( 'type' => 'select', 'title' => 'Hierarchical', 'options' => array( 'false', 'true' ), 'default' => 'false', 'description' => 'Whether the post type is hierarchical. Allows Parent to be specified.' ),
		array( 'type' => 'select', 'title' => 'Has Archive', 'options' => array( 'false', 'true' ), 'default' => 'false', 'description' => 'Enables post type archives. Will use string as archive slug. Will generate the proper rewrite rules if rewrite is enabled.' ),
		array( 'type' => 'checkbox', 'title' => 'Supports', 'options' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats' ), 'default' =>'title, editor' ),
		
		
		array( 'type' => 'text', 'title' => 'Add New', 'description' => 'ex. Add New' ),
		array( 'type' => 'text', 'title' => 'Add New Item', 'description' => 'ex. Add New Book'),
		array( 'type' => 'text', 'title' => 'Edit Item', 'description' => 'ex. Edit Book' ),
		array( 'type' => 'text', 'title' => 'New Item', 'description' => 'ex. New Book' ),
		array( 'type' => 'text', 'title' => 'All Items', 'description' => 'ex. All Books' ),
		array( 'type' => 'text', 'title' => 'View Items', 'description' => 'ex. View Books' ),
		array( 'type' => 'text', 'title' => 'Search Items', 'description' => 'ex. Search Books' ),
		array( 'type' => 'text', 'title' => 'Not Found', 'description' => 'ex. No Books Found' ),
		array( 'type' => 'text', 'title' => 'Not Found In Trash', 'description' => 'ex. No Books found in Trash' ),	
		array( 'type' => 'text', 'title' => 'Parent Item Colon', 'description' => 'the parent text. This string isn\'t used on non-hierarchical types. In hierarchical ones the default is Parent Page ' ),	
		array( 'type' => 'text', 'title' => 'Menu Name' ),			
		
		array( 'type' => 'select', 'title' => 'Public', 'options' => array( 'false', 'true' ), 'default' => 'true', 'description' => 'Meta argument used to define default values for publicly_queriable, show_ui, show_in_nav_menus and exclude_from_search' ),
		array( 'type' => 'select', 'title' => 'Show UI', 'options' => array( 'false', 'true' ), 'default' => 'true', 'description' => 'Whether to generate a default UI for managing this post type.' ), 
		array( 'type' => 'text', 'title' => 'Menu Position', 'description' => 'The position in the menu order the post type should appear.' ), 
		array( 'type' => 'text', 'title' => 'Menu Icon', 'description' => 'The url to the icon to be used for this menu.' ),
		array( 'type' => 'text', 'title' => 'Capability Type', 'description' => 'The string to use to build the read, edit, and delete capabilities.', 'default' => 'post' ), 		
		array( 'type' => 'checkbox', 'title' => 'Taxonomies', 'options' => $taxonomie_names ),		
		array( 'type' => 'select', 'title' => 'Rewrite', 'options' => array( 'false', 'true' ), 'default' => 'true', 'description' => 'Rewrite permalinks.' ),
		array( 'type' => 'text', 'title' => 'Rewrite Slug', 'description' => 'Defaults to post type name.' )
	);
	
	/* set up the box arguments */
	$args = array(
		'metabox_id' => 'option_page',
		'metabox_title' => 'Custom Post Type Creation',
		'post_type' => 'cptc-page',
		'meta_name' => 'wck_cptc',
		'meta_array' => $cpt_creation_fields,	
		'context' 	=> 'option',
		'sortable' => false
	);

	/* create the box */
	new WCK_CPTC_Wordpress_Creation_Kit( $args );
}

/* hook to create custom post types */
add_action( 'init', 'wck_cptc_create_cpts' );

function wck_cptc_create_cpts(){
	$cpts = get_option('wck_cptc');
	if( !empty( $cpts ) ){
		foreach( $cpts as $cpt ){
			
			$labels = array(
				'name' => _x( $cpt['plural-label'], 'post type general name'),
				'singular_name' => _x( $cpt['singular-label'], 'post type singular name'),
				'add_new' => _x( $cpt['add-new'] ? $cpt['add-new'] : 'Add New', strtolower( $cpt['singular-label'] ) ),
				'add_new_item' => __( $cpt['add-new-item'] ? $cpt['add-new-item'] : "Add New ".$cpt['singular-label']),
				'edit_item' => __( $cpt['edit-item'] ? $cpt['edit-item'] : "Edit ".$cpt['singular-label'] ) ,
				'new_item' => __( $cpt['new-item'] ? $cpt['new-item'] : "New ".$cpt['singular-label']),
				'all_items' => __( $cpt['all-items'] ? $cpt['all-items'] : "All ".$cpt['plural-label']),
				'view_item' => __( $cpt['view-item'] ? $cpt['view-item'] : "View ".$cpt['singular-label']),
				'search_items' => __( $cpt['search-items'] ? $cpt['search-items'] : "Search ".$cpt['plural-label']),
				'not_found' =>  __( $cpt['not-found'] ? $cpt['not-found'] : "No ". strtolower( $cpt['plural-label'] ) ." found"),
				'not_found_in_trash' => __( $cpt['not-found-in-trash'] ? $cpt['not-found-in-trash'] :  "No ". strtolower( $cpt['plural-label'] ) ." found in Trash"), 
				'parent_item_colon' => __( $cpt['parent-item-colon'] ? $cpt['parent-item-colon'] :  "Parent Page"),
				'menu_name' => $cpt['menu-name'] ? $cpt['menu-name'] : $cpt['plural-label']
			);
			$args = array(
				'labels' => $labels,
				'public' => $cpt['public'],
				'description'	=> $cpt['description'],
				'publicly_queryable' => true,
				'show_ui' => $cpt['show-ui'], 	
				'show_in_menu' => true, 				
				'has_archive' => $cpt['has-archive'],
				'hierarchical' => $cpt['hierarchical'],							
				'menu_position' => $cpt['menu-position'],
				'capability_type' => $cpt['capability-type'],
				'supports' => explode( ', ', $cpt['supports'] )				
			);
			
			if( !empty( $cpt['taxonomies'] ) )
				$args['taxonomies'] = explode( ', ', $cpt['taxonomies'] );
			
			if( !empty( $cpt['menu-icon'] ) )
				$args['menu_icon'] = $cpt['menu-icon'];
				
			if( $cpt['rewrite'] == false )
				$args['rewrite'] = $cpt['rewrite'];
			else{
				if( !empty( $cpt['rewrite-slug'] ) )
					$args['rewrite'] = array('slug' => $cpt['rewrite-slug']);
			}	
			
			
			register_post_type( $cpt['post-type'], $args );
		}
	}
}

/* Flush rewrite rules */
add_action('init', 'cptc_flush_rules', 20);
function cptc_flush_rules(){
	if( isset( $_GET['page'] ) && $_GET['page'] == 'cptc-page' && isset( $_GET['updated'] ) && $_GET['updated'] == 'true' )
		flush_rewrite_rules( false  );
}

/* advanced labels container for add form */
add_action( "wck_before_add_form_wck_cptc_element_7", 'wck_cptc_form_label_wrapper_start' );
function wck_cptc_form_label_wrapper_start(){
	echo '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-label-options-container\').toggle(); if( jQuery(this).text() == \'Show Advanced Label Options\' ) jQuery(this).text(\'Hide Advanced Label Options\');  else if( jQuery(this).text() == \'Hide Advanced Label Options\' ) jQuery(this).text(\'Show Advanced Label Options\');">Show Advanced Label Options</a></li>';
	echo '<li id="cptc-advanced-label-options-container" style="display:none;"><ul>';
}

add_action( "wck_after_add_form_wck_cptc_element_17", 'wck_cptc_form_label_wrapper_end' );
function wck_cptc_form_label_wrapper_end(){
	echo '</ul></li>';	
}

/* advanced options container for add form */
add_action( "wck_before_add_form_wck_cptc_element_18", 'wck_cptc_form_wrapper_start' );
function wck_cptc_form_wrapper_start(){
	echo '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-options-container\').toggle(); if( jQuery(this).text() == \'Show Advanced Options\' ) jQuery(this).text(\'Hide Advanced Options\');  else if( jQuery(this).text() == \'Hide Advanced Options\' ) jQuery(this).text(\'Show Advanced Options\');">Show Advanced Options</a></li>';
	echo '<li id="cptc-advanced-options-container" style="display:none;"><ul>';
}

add_action( "wck_after_add_form_wck_cptc_element_25", 'wck_cptc_form_wrapper_end' );
function wck_cptc_form_wrapper_end(){
	echo '</ul></li>';	
}

/* advanced label options container for update form */
add_filter( "wck_before_update_form_wck_cptc_element_7", 'wck_cptc_update_form_label_wrapper_start', 10, 2 );
function wck_cptc_update_form_label_wrapper_start( $form, $i ){
	$form .=  '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-label-options-update-container-'.$i.'\').toggle(); if( jQuery(this).text() == \'Show Advanced Label Options\' ) jQuery(this).text(\'Hide Advanced Label Options\');  else if( jQuery(this).text() == \'Hide Advanced Label Options\' ) jQuery(this).text(\'Show Advanced Label Options\');">Show Advanced Label Options</a></li>';
	$form .= '<li id="cptc-advanced-label-options-update-container-'.$i.'" style="display:none;"><ul>';
	return $form;
}

add_filter( "wck_after_update_form_wck_cptc_element_17", 'wck_cptc_update_form_label_wrapper_end', 10, 2 );
function wck_cptc_update_form_label_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';
	return $form;
}

/* advanced options container for update form */
add_filter( "wck_before_update_form_wck_cptc_element_18", 'wck_cptc_update_form_wrapper_start', 10, 2 );
function wck_cptc_update_form_wrapper_start( $form, $i ){
	$form .=  '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-options-update-container-'.$i.'\').toggle(); if( jQuery(this).text() == \'Show Advanced Options\' ) jQuery(this).text(\'Hide Advanced Options\');  else if( jQuery(this).text() == \'Hide Advanced Options\' ) jQuery(this).text(\'Show Advanced Options\');">Show Advanced Options</a></li>';
	$form .= '<li id="cptc-advanced-options-update-container-'.$i.'" style="display:none;"><ul>';
	return $form;
}

add_filter( "wck_after_update_form_wck_cptc_element_25", 'wck_cptc_update_form_wrapper_end', 10, 2 );
function wck_cptc_update_form_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';	
	return $form;
}


/* advanced label options container for display */
add_filter( "wck_before_listed_wck_cptc_element_7", 'wck_cptc_display_label_wrapper_start', 10, 2 );
function wck_cptc_display_label_wrapper_start( $form, $i ){
	$form .=  '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-label-options-display-container-'.$i.'\').toggle(); if( jQuery(this).text() == \'Show Advanced Labels\' ) jQuery(this).text(\'Hide Advanced Labels\');  else if( jQuery(this).text() == \'Hide Advanced Labels\' ) jQuery(this).text(\'Show Advanced Labels\');">Show Advanced Labels</a></li>';
	$form .= '<li id="cptc-advanced-label-options-display-container-'.$i.'" style="display:none;"><ul>';
	return $form;
}

add_filter( "wck_after_listed_wck_cptc_element_17", 'wck_cptc_display_label_wrapper_end', 10, 2 );
function wck_cptc_display_label_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';	
	return $form;
}

/* advanced options container for display */
add_filter( "wck_before_listed_wck_cptc_element_18", 'wck_cptc_display_adv_wrapper_start', 10, 2 );
function wck_cptc_display_adv_wrapper_start( $form, $i ){
	$form .=  '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-options-display-container-'.$i.'\').toggle(); if( jQuery(this).text() == \'Show Advanced Options\' ) jQuery(this).text(\'Hide Advanced Options\');  else if( jQuery(this).text() == \'Hide Advanced Options\' ) jQuery(this).text(\'Show Advanced Options\');">Show Advanced Options</a></li>';
	$form .= '<li id="cptc-advanced-options-display-container-'.$i.'" style="display:none;"><ul>';
	return $form;
}

add_filter( "wck_after_listed_wck_cptc_element_25", 'wck_cptc_display_adv_wrapper_end', 10, 2 );
function wck_cptc_display_adv_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';	
	return $form;
}

/* add refresh to page */
add_action("wck_refresh_list_wck_cptc", "wck_cptc_after_refresh_list");
function wck_cptc_after_refresh_list(){
	echo '<script type="text/javascript">window.location="'. get_admin_url() . 'admin.php?page=cptc-page&updated=true' .'";</script>';
}

/* Add side metaboxes */
add_action('add_meta_boxes', 'wck_cptc_add_side_boxes' );
function wck_cptc_add_side_boxes(){
	add_meta_box( 'wck-cptc-side', 'Side Box', 'wck_cptc_side_box_one', 'wck_page_cptc-page', 'side', 'high' );
}
function wck_cptc_side_box_one(){
	?>
		<iframe src="http://www.cozmoslabs.com/iframes/cozmoslabs_plugin_iframe.php?origin=<?php echo get_option('home'); ?>" width="260" id="wck-iframe"></iframe>
		<script type="text/javascript">			
			var onmessage = function(e) {
				if( e.origin == 'http://www.cozmoslabs.com' )
					jQuery('#wck-iframe').height(e.data);			
			}
			if(window.postMessage) {
				if(typeof window.addEventListener != 'undefined') {
					window.addEventListener('message', onmessage, false);
				}
				else if(typeof window.attachEvent != 'undefined') {
					window.attachEvent('onmessage', onmessage);
				}
			}			
		</script>
	<?php
}


/* Contextual Help */
add_action('load-wck_page_cptc-page', 'wck_cptc_help');

function wck_cptc_help () {    
    $screen = get_current_screen();

    /*
     * Check if current screen is wck_page_cptc-page
     * Don't add help tab if it's not
     */
    if ( $screen->id != 'wck_page_cptc-page' )
        return;

    // Add help tabs
    $screen->add_help_tab( array(
        'id'	=> 'wck_cptc_overview',
        'title'	=> __('Overview'),
        'content'	=> '<p>' . __( 'WCK Custom Post Type Creator allows you to easily create custom post types for Wordpress without any programming knowledge.<br />Most of the common options for creating a post type are displayed by default while the advanced options and label are just one click away.' ) . '</p>',
    ) );
	
	$screen->add_help_tab( array(
        'id'	=> 'wck_cptc_labels',
        'title'	=> __('Labels'),
        'content'	=> '<p>' . __( 'For simplicity you are required to introduce only the Singular Label and Plural Label from wchich the rest of the labels will be formed.<br />For a more detailed control of the labels you just have to click the "Show Advanced Label Options" link and all the availabel labels will be displayed' ) . '</p>',
    ) );
	
	$screen->add_help_tab( array(
        'id'	=> 'wck_cptc_advanced',
        'title'	=> __('Advanced Options'),
        'content'	=> '<p>' . __( 'The Advanced Options are set to the most common defaults for custom post types. To display them click the "Show Advanced Options" link.' ) . '</p>',
    ) );
}
?>