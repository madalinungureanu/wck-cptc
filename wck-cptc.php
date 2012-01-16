<?php
/*
Plugin Name: WCK CPTC
Description: Creates Custom POst types
*/

/* include Custom Fields Creator API */
require_once('wordpress-creation-kit-api/wordpress-creation-kit.php');

/* Create the WCK Page */
$args = array(							
			'page_title' => 'WCK',
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
			'page_title' => 'CPTC',
			'menu_title' => 'CPTC',
			'capability' => 'edit_theme_options',
			'menu_slug' => 'cptc-page',									
			'page_type' => 'submenu_page',
			'parent_slug' => 'wck-page'			
		);
$cptc_page = new WCK_CPTC_WCK_Page_Creator( $args );



add_action( 'init', 'wck_cptc_create_box', 11 );
function wck_cptc_create_box(){
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

	$cpt_creation_fields = array( 
		array( 'type' => 'text', 'title' => 'Post type', 'description' => '(max. 20 characters, can not contain capital letters, hyphens, or spaces)' ), 
		array( 'type' => 'textarea', 'title' => 'Description', 'description' => 'A short descriptive summary of what the post type is.' ),
		array( 'type' => 'text', 'title' => 'Singular Label' ),
		array( 'type' => 'text', 'title' => 'Plural Label' ),
		array( 'type' => 'select', 'title' => 'Public', 'options' => array( 'false', 'true' ), 'default' => 'true', 'description' => 'Meta argument used to define default values for publicly_queriable, show_ui, show_in_nav_menus and exclude_from_search' ),
		array( 'type' => 'select', 'title' => 'Show UI', 'options' => array( 'false', 'true' ), 'default' => 'true', 'description' => 'Whether to generate a default UI for managing this post type.' ), 
		array( 'type' => 'text', 'title' => 'Menu Position', 'description' => 'The position in the menu order the post type should appear.' ), 
		array( 'type' => 'select', 'title' => 'Hierarchical', 'options' => array( 'false', 'true' ), 'default' => 'false', 'description' => 'Whether the post type is hierarchical. Allows Parent to be specified.' ),
		array( 'type' => 'checkbox', 'title' => 'Supports', 'options' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats' ), 'default' =>'title, editor' ),
		array( 'type' => 'checkbox', 'title' => 'Taxonomies', 'options' => $taxonomie_names ),
		array( 'type' => 'select', 'title' => 'Has Archive', 'options' => array( 'false', 'true' ), 'default' => 'false', 'description' => 'Enables post type archives. Will use string as archive slug. Will generate the proper rewrite rules if rewrite is enabled.' )
	);

	$args = array(
		'metabox_id' => 'option_page',
		'metabox_title' => 'Custom Post Type Creation',
		'post_type' => 'cptc-page',
		'meta_name' => 'wck_cptc',
		'meta_array' => $cpt_creation_fields,	
		'context' 	=> 'option',
		'sortable' => false
	);


	new WCK_CPTC_Wordpress_Creation_Kit( $args );
}

add_action( 'init', 'wck_cptc_create_cpts' );

function wck_cptc_create_cpts(){
	$cpts = get_option('wck_cptc');
	if( !empty( $cpts ) ){
		foreach( $cpts as $cpt ){
			
			$labels = array(
				'name' => _x( $cpt['plural-label'], 'post type general name'),
				'singular_name' => _x( $cpt['singular-label'], 'post type singular name'),
				'add_new' => _x('Add New', strtolower( $cpt['singular-label'] ) ),
				'add_new_item' => __("Add New ".$cpt['singular-label']),
				'edit_item' => __("Edit ".$cpt['singular-label']),
				'new_item' => __("New ".$cpt['singular-label']),
				'all_items' => __("All ".$cpt['plural-label']),
				'view_item' => __("View ".$cpt['singular-label']),
				'search_items' => __("Search ".$cpt['plural-label']),
				'not_found' =>  __("No ". strtolower( $cpt['plural-label'] ) ." found"),
				'not_found_in_trash' => __("No ". strtolower( $cpt['plural-label'] ) ." found in Trash"), 
				'parent_item_colon' => '',
				'menu_name' => $cpt['plural-label']
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
				'supports' => explode( ', ', $cpt['supports'] )				
			);
			
			if( !empty( $cpt['taxonomies'] ) )
				$args['taxonomies'] = explode( ', ', $cpt['taxonomies'] );
			 
			register_post_type( $cpt['post-type'], $args );
		}
	}
}


add_action( "wck_before_add_form_wck_cptc_element_4", 'wck_cptc_form_wrapper_start' );
function wck_cptc_form_wrapper_start(){
	echo '<li id="cptc-advanced-options-container" style="display:none;"><ul>';
}

add_action( "wck_after_add_form_wck_cptc_element_10", 'wck_cptc_form_wrapper_end' );
function wck_cptc_form_wrapper_end(){
	echo '</ul></li>';
	echo '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-options-container\').toggle(); if( jQuery(this).text() == \'Show Advanced Options\' ) jQuery(this).text(\'Hide Advanced Options\');  else if( jQuery(this).text() == \'Hide Advanced Options\' ) jQuery(this).text(\'Show Advanced Options\');">Show Advanced Options</a></li>';
}


add_filter( "wck_before_update_form_wck_cptc_element_4", 'wck_cptc_update_form_wrapper_start', 10, 2 );
function wck_cptc_update_form_wrapper_start( $form, $i ){
	$form .= '<li id="cptc-advanced-options-update-container-'.$i.'" style="display:none;"><ul>';
	return $form;
}

add_filter( "wck_after_update_form_wck_cptc_element_10", 'wck_cptc_update_form_wrapper_end', 10, 2 );
function wck_cptc_update_form_wrapper_end( $form, $i ){
	$form .=  '</ul></li>';
	$form .=  '<li><a href="javascript:void(0)" onclick="jQuery(\'#cptc-advanced-options-update-container-'.$i.'\').toggle(); if( jQuery(this).text() == \'Show Advanced Options\' ) jQuery(this).text(\'Hide Advanced Options\');  else if( jQuery(this).text() == \'Hide Advanced Options\' ) jQuery(this).text(\'Show Advanced Options\');">Show Advanced Options</a></li>';
	
	return $form;
}

add_action("wck_refresh_list_wck_cptc", "wck_cptc_after_refresh_list");
function wck_cptc_after_refresh_list(){
	echo '<script type="text/javascript">window.location.reload();</script>';
}
?>