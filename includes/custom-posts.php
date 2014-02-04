<?php
// let's create the function for the custom type
function register_speaker_post_type() { 
	// creating (registering) the custom type 
	register_post_type( 'rotary_speakers', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
	 	// let's now add all the options for this post type
		array('labels' => array(
			'name' => __('Programs', 'rotary'), /* This is the Title of the Group */
			'singular_name' => __('Program', 'rotary'), /* This is the individual type */
			'all_items' => __('All Programs', 'rotary'), /* the all items menu item */
			'add_new' => __('Add New', 'rotary'), /* The add new menu item */
			'add_new_item' => __('Add New Speaker', 'rotary'), /* Add New Display Title */
			'edit' => __( 'Edit', 'rotary' ), /* Edit Dialog */
			'edit_item' => __('Edit Program', 'rotary'), /* Edit Display Title */
			'new_item' => __('New Program', 'rotary'), /* New Display Title */
			'view_item' => __('View Program', 'rotary'), /* View Display Title */
			'search_items' => __('Search Programs', 'rotary'), /* Search Custom Type Title */ 
			'not_found' =>  __('Nothing found.', 'rotary'), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __('Nothing found in Trash', 'rotary'), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'This is where the ', 'rotary' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => get_stylesheet_directory_uri() . '/includes/images/speaker-dashboard-icon.png', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'speaker', 'with_front' => false ), /* you can specify its url slug */
			'has_archive' => 'speaker_archive', /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'author', 'custom-fields', 'revisions', 'thumbnail')
	 	) /* end of options */
	); /* end of register post type */
	
	
} 

	// adding the function to the Wordpress init
	add_action( 'init', 'register_speaker_post_type');
	
	/*
	for more information on taxonomies, go here:
	http://codex.wordpress.org/Function_Reference/register_taxonomy
	*/
	
	// now let's add custom categories (these act like categories)
    register_taxonomy( 'rotary_speaker_cat', 
    	array('rotary_speakers'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    	array('hierarchical' => true,     /* if this is true, it acts like categories */             
    		'labels' => array(
    			'name' => __( 'Program Categories', 'rotary' ), /* name of the custom taxonomy */
    			'singular_name' => __( 'Program Category', 'rotary' ), /* single taxonomy name */
    			'search_items' =>  __( 'Search Program Categories', 'rotary' ), /* search title for taxomony */
    			'all_items' => __( 'All Program Categories', 'rotary' ), /* all title for taxonomies */
    			'parent_item' => __( 'Parent Program Category', 'rotary' ), /* parent title for taxonomy */
    			'parent_item_colon' => __( 'Parent Program Category:', 'rotary' ), /* parent taxonomy title */
    			'edit_item' => __( 'Edit Program Category', 'rotary' ), /* edit custom taxonomy title */
    			'update_item' => __( 'Update Program Category', 'rotary' ), /* update title for taxonomy */
    			'add_new_item' => __( 'Add New Program Category', 'rotary' ), /* add new title for taxonomy */
    			'new_item_name' => __( 'New Custom Program Name', 'rotary' ) /* name title for taxonomy */
    		),
    		'show_ui' => true,
    		'query_var' => true,
    		'rewrite' => array( 'slug' => 'speaker-category' ),
    	)
    );   
    
	// now let's add custom tags (these act like categories)
    register_taxonomy( 'rotary_speaker_tag', 
    	array('rotary_speakers'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    	array('hierarchical' => false,    /* if this is false, it acts like tags */                
    		'labels' => array(
    			'name' => __( 'Program Tags', 'rotary' ), /* name of the custom taxonomy */
    			'singular_name' => __( 'Program Tag', 'rotary' ), /* single taxonomy name */
    			'search_items' =>  __( 'Search Program Tags', 'rotary' ), /* search title for taxomony */
    			'all_items' => __( 'All Program Tags', 'rotary' ), /* all title for taxonomies */
    			'parent_item' => __( 'Parent Program Tag', 'rotary' ), /* parent title for taxonomy */
    			'parent_item_colon' => __( 'Parent Program Tag:', 'rotary' ), /* parent taxonomy title */
    			'edit_item' => __( 'Edit Program Tag', 'rotary' ), /* edit custom taxonomy title */
    			'update_item' => __( 'Update Program Tag', 'rotary' ), /* update title for taxonomy */
    			'add_new_item' => __( 'Add New Program Tag', 'rotary' ), /* add new title for taxonomy */
    			'new_item_name' => __( 'New Program Tag Name', 'rotary' ) /* name title for taxonomy */
    		),
    		'show_ui' => true,
    		'query_var' => true,
    	)
    ); 
    
    add_filter('manage_rotary_speakers_posts_columns' , 'rotary_speakers_cpt_columns'); 
    function rotary_speakers_cpt_columns($columns) {
	    unset($columns['date']);
	    $new_columns = array(
		'speaker_date' => __('Speaker Date', 'rotary'),
		);
    	$columns = array_merge($columns, $new_columns);
	    
	    return $columns;
    }
    add_action( 'manage_rotary_speakers_posts_custom_column' , 'rotary_custom_speaker_column_data', 10, 2 );
    function rotary_custom_speaker_column_data($column, $post_id) {
    	switch ( $column ) {
    		case 'speaker_date' :
            	$speakerDate = get_post_meta( $post_id , 'speaker_date' , true );  
            	echo date('l M dS, Y', strtotime($speakerDate));
				break;

		}
	    
    }
    add_filter('manage_edit-rotary_speakers_sortable_columns', 'rotary_column_register_sortable');
    function rotary_column_register_sortable( $columns )
	{
		$columns['speaker_date'] = 'speaker_date';
		return $columns;
	}
	add_filter( 'request', 'rotary_speaker_column_orderby' );
	function rotary_speaker_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'speaker_date' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'speaker_date',
            'orderby' => 'meta_value'
        ) );
    }
 
    return $vars;
}


    
?>