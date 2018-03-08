<?php

class RotaryCustomPostTypes {
	private $rotaryProfiles;
	private $rotaryAuth;

	function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles'));
		$this->rotaryProfiles = new RotaryProfiles();	

		add_action( 'init', array($this, 'register_speaker_post_type'));
		add_filter( 'manage_rotary_speakers_posts_columns' , array($this, 'rotary_speakers_cpt_columns'));
		add_action( 'manage_rotary_speakers_posts_custom_column' , array($this, 'rotary_custom_speaker_column_data'), 10, 2 );
		add_filter( 'manage_edit-rotary_speakers_sortable_columns', array($this, 'rotary_column_register_sortable'));
		add_filter( 'request', array($this, 'rotary_speaker_column_orderby' ));
		add_action( 'init', array($this, 'register_commitee_post_type'));
		add_action( 'init', array($this, 'register_project_post_type'));
		add_action( 'p2p_init', array($this, 'rotary_connection_types' ));
		add_action( 'init', array($this, 'register_script_for_shortcodes') );
		add_action( 'template_redirect', array($this, 'enqueue_scripts_for_shortcodes') );
		//ajax to get members
		add_action( 'wp_ajax_nopriv_rotarymembers', array($this, 'rotary_get_members' ));
		add_action( 'wp_ajax_rotarymembers', array($this, 'rotary_get_members' ));
		add_action( 'wp_ajax_nopriv_rotaryform', array($this, 'rotary_get_form_entries' ));
		add_action( 'wp_ajax_rotaryform', array($this, 'rotary_get_form_entries' ));
		add_action( 'wp_ajax_nopriv_projectmembers', array($this, 'rotary_add_project_members' ));
		add_action( 'wp_ajax_projectmembers', array($this, 'rotary_add_project_members' ));
		add_action( 'wp_ajax_nopriv_deleteprojectmember', array($this, 'rotary_delete_project_member' ));
		add_action( 'wp_ajax_deleteprojectmember', array($this, 'rotary_delete_project_member' ));
		add_action( 'wp_ajax_nopriv_rotarymemberdetails', array($this, 'rotary_get_member_details' ));
		add_action( 'wp_ajax_rotarymemberdetails', array($this, 'rotary_get_member_details' ));	
		//
		// Member Directory 

		add_shortcode( 'DIRECTORY', array($this, 'get_rotary_club_members') );
		add_shortcode( 'member_directory', array($this, 'get_rotary_club_members') );
	add_shortcode( 'MEMBER_DIRECTORY', array($this, 'get_rotary_club_members') );
	add_shortcode( 'directory', array($this, 'get_rotary_club_members') );
	}
	//the same will be done for committees
	function activate() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
  		$table_name = $wpdb->prefix . 'rotarycommittees';
  		// $wpdb->query("DROP TABLE IF EXISTS $table_name");
  		$sql = 'CREATE TABLE ' . $table_name .'(
     		id int(11) unsigned NOT NULL auto_increment,
			committeenum int(11),
     		PRIMARY KEY  (id)
  		);';
  		dbDelta($sql); 
  	}
	function deactivate() {
		
	}


	function enqueue_scripts_and_styles() {
		//wp_enqueue_script( 'rotarymembership', plugins_url('/js/rotarymembership.js', __FILE__) );
		//wp_enqueue_media();
		//wp_enqueue_script( 'jquery-ui-datepicker');
		//wp_enqueue_script( 'jquery-ui-dialog');
		//wp_register_style( 'rotary-style', plugins_url('/css/rotarymembership.css', __FILE__),false, 0.1);
		//wp_enqueue_style( 'rotary-style' );
	}
	
	//register the post type for speakers
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
				//'capability_type' => 'speaker_program',
				'capability_type' => 'post',
				'hierarchical' => false,
				/* the next one is important, it tells what's enabled in the post editor */
				'supports' => array( 'title', 'editor', 'author', 'custom-fields', 'revisions', 'thumbnail')
			) /* end of options */
		); /* end of register post type */
	
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
							'new_item_name' => __( 'New Program', 'rotary' ) /* name title for taxonomy */
					),
							'show_ui' => true,
							'query_var' => true,
							'rewrite' => array( 'slug' => 'speaker-category' ),
					)
		);
		// now let's add custom categories for program coordinator
		register_taxonomy( 'rotary_program_introducer_cat',
			array('rotary_speakers'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
				array(
					'hierarchical' => true,     /* if this is true, it acts like categories */
					'labels' => array(
							'name' => __( 'Program Introducer', 'rotary' ), /* name of the custom taxonomy */
							'singular_name' => __( 'Program Introducer', 'rotary' ), /* single taxonomy name */
							'search_items' =>  __( 'Search Program Introducers', 'rotary' ), /* search title for taxomony */
							'all_items' => __( 'All Program Introducer', 'rotary' ), /* all title for taxonomies */
							'parent_item' => __( 'Parent Program Introducers', 'rotary' ), /* parent title for taxonomy */
							'parent_item_colon' => __( 'Parent Program Introducers:', 'rotary' ), /* parent taxonomy title */
							'edit_item' => __( 'Edit Program Introducers', 'rotary' ), /* edit custom taxonomy title */
							'update_item' => __( 'Update Program Introducer', 'rotary' ), /* update title for taxonomy */
							'add_new_item' => __( 'Add New Program Introducer', 'rotary' ), /* add new title for taxonomy */
							'new_item_name' => __( 'New Program Introducer', 'rotary' ) /* name title for taxonomy */
						),
					'show_ui' => true,
					'query_var' => true,
					'rewrite' => array( 'slug' => 'program-coordinator' ),
				)
		);
		// now let's add custom categories for program scribe
		register_taxonomy( 'rotary_program_scribe',
			array('rotary_speakers'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
			array(
				'hierarchical' => true,     /* if this is true, it acts like categories */
				'labels' => array(
						'name' => __( 'Program Scribe', 'rotary' ), /* name of the custom taxonomy */
						'singular_name' => __( 'Program Scribe', 'rotary' ), /* single taxonomy name */
						'search_items' =>  __( 'Search Program Scribes', 'rotary' ), /* search title for taxomony */
						'all_items' => __( 'All Program Scribes', 'rotary' ), /* all title for taxonomies */
						'parent_item' => __( 'Parent Program Scribes', 'rotary' ), /* parent title for taxonomy */
						'parent_item_colon' => __( 'Parent Program Scribes:', 'rotary' ), /* parent taxonomy title */
						'edit_item' => __( 'Edit Program Scribes', 'rotary' ), /* edit custom taxonomy title */
						'update_item' => __( 'Update Program Scribe', 'rotary' ), /* update title for taxonomy */
						'add_new_item' => __( 'Add New Program Scribes', 'rotary' ), /* add new title for taxonomy */
						'new_item_name' => __( 'New Program Scribe', 'rotary' ) /* name title for taxonomy */
					),
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'program-scribe' ),
			)
		);
		// now let's add custom categories for program editor
		register_taxonomy( 'rotary_program_editor',
			array('rotary_speakers'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
			array(
					'hierarchical' => true,     /* if this is true, it acts like categories */
					'labels' => array(
							'name' => __( 'Program Editor', 'rotary' ), /* name of the custom taxonomy */
							'singular_name' => __( 'Program Editor', 'rotary' ), /* single taxonomy name */
							'search_items' =>  __( 'Search Program Editors', 'rotary' ), /* search title for taxomony */
							'all_items' => __( 'All Program Editors', 'rotary' ), /* all title for taxonomies */
							'parent_item' => __( 'Parent Program Editors', 'rotary' ), /* parent title for taxonomy */
							'parent_item_colon' => __( 'Parent Program Editors:', 'rotary' ), /* parent taxonomy title */
							'edit_item' => __( 'Edit Program Editors', 'rotary' ), /* edit custom taxonomy title */
							'update_item' => __( 'Update Program Editor', 'rotary' ), /* update title for taxonomy */
							'add_new_item' => __( 'Add New Program Editors', 'rotary' ), /* add new title for taxonomy */
							'new_item_name' => __( 'New Program Editor', 'rotary' ) /* name title for taxonomy */
						),
					'show_ui' => true,
					'query_var' => true,
					'rewrite' => array( 'slug' => 'program-scribe' ),
				)
		);
	
		// now let's add custom tags (these act like tags)
		register_taxonomy( 'rotary_speaker_tag',
			array('rotary_speakers'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
				array(
						'hierarchical' => false,    /* if this is false, it acts like tags */
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
	
	}
	function rotary_speakers_cpt_columns( $columns ) {
		unset( $columns['date'] );
		unset( $columns['author'] );
		unset( $columns['title'] );
		$new_columns = array(
				'speaker_date' => __('Program Date', 'Rotary'),
				'title' => __('Title', 'Rotary'),
				'speaker_name' => __('Speaker', 'Rotary'),
				'speaker_title' => __('Position/Job Title', 'Rotary'),
				'speaker_company' =>  __('Organization', 'Rotary'),
		);
		$columns = array_merge( $columns, $new_columns );
		 
		return $columns;
	}
	function rotary_custom_speaker_column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'speaker_date' :
				$speakerDate = get_post_meta( $post_id , 'speaker_date' , true );
				echo date('l M dS, Y', strtotime($speakerDate));
				break;
			case 'speaker_name':
				echo sprintf ( '%s %s', get_post_meta( $post_id , 'speaker_first_name' , true ), get_post_meta( $post_id , 'speaker_last_name' , true ) );
				break;
			case 'speaker_company':
				echo get_post_meta( $post_id , 'speaker_company' , true );
				break;
			case 'speaker_title':
				echo get_post_meta( $post_id , 'speaker_title' , true );
				break;
	
		}
		 
	}
	function rotary_column_register_sortable( $columns )
	{
		$columns['speaker_date'] = 'speaker_date';
		$columns['speaker_first_name'] = 'speaker_first_name';
		$columns['speaker_last_name'] = 'speaker_last_name';
		return $columns;
	}
	function rotary_speaker_column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'speaker_date' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
					'meta_key' => 'speaker_date',
					'orderby' => 'meta_value'
			) );
		}
		if ( isset( $vars['orderby'] ) && 'speaker_first_name' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
					'meta_key' => 'speaker_first_name',
					'orderby' => 'meta_value'
			) );
		}
		if ( isset( $vars['orderby'] ) && 'speaker_last_name' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
					'meta_key' => 'speaker_last_name',
					'orderby' => 'meta_value'
			) );
		}
	
		return $vars;
	}
	
	
	
	//register the post type for projects
	function register_project_post_type() {
		// creating (registering) the custom type 
		register_post_type( 'rotary_projects', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		 	// let's now add all the options for this post type
			array('labels' => array(
								'name' 				=> __('Projects', 'rotary'), /* This is the Title of the Group */
								'singular_name' 	=> __('Project', 'rotary'), /* This is the individual type */
								'all_items' 		=> __('All Projects', 'rotary'), /* the all items menu item */
								'add_new' 			=> __('Add New', 'rotary'), /* The add new menu item */
								'add_new_item' 		=> __('Add New Project', 'rotary'), /* Add New Display Title */
								'edit' 				=> __( 'Edit', 'rotary' ), /* Edit Dialog */
								'edit_item' 		=> __('Edit Project', 'rotary'), /* Edit Display Title */
								'new_item' 			=> __('New Project', 'rotary'), /* New Display Title */
								'view_item' 		=> __('View Project', 'rotary'), /* View Display Title */
								'search_items' 		=> __('Search Projects', 'rotary'), /* Search Custom Type Title */ 
								'not_found' 		=> __('Nothing found.', 'rotary'), /* This displays if there are no entries yet */ 
								'not_found_in_trash'=> __('Nothing found in Trash', 'rotary'), /* This displays if there is nothing in the trash */
								'parent_item_colon' => ''
							), /* end of arrays */
				'description' 		=> __( 'This is where the ', 'rotary' ), /* Custom Type Description */
				'public' 			=> true,
				'publicly_queryable' => true,
				'exclude_from_search'=> false,
				'show_ui' 			=> true,
				'query_var' 		=> true,
				'menu_position' 	=> 9, /* this is what order you want it to appear in on the left hand side menu */ 
				'rewrite'			=> array( 'slug' => 'project', 'with_front' => false ), /* you can specify its url slug */
				'capability_type' 	=> 'post',
				'has_archive' 		=> 'project_archive', /* you can rename the slug here */
				'hierarchical' 		=> false,
				/* the next one is important, it tells what's enabled in the post editor */
				'supports' => array( 'title', 'editor', 'author', 'custom-fields', 'revisions', 'thumbnail', 'comments')
		 	) /* end of options */
		); /* end of register post type */
	// now let's add custom categories (these act like categories)
    register_taxonomy( 'rotary_project_cat', 
    	array('rotary_projects'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    	array(
    		'hierarchical' => true,     /* if this is true, it acts like categories */             
    		'labels' => array(
			    			'name' 				=> __( 'Project Categories', 'rotary' ), /* name of the custom taxonomy */
			    			'singular_name' 	=> __( 'Project Category', 'rotary' ), /* single taxonomy name */
			    			'search_items' 		=> __( 'Search Project Categories', 'rotary' ), /* search title for taxomony */
			    			'all_items' 		=> __( 'All Project Categories', 'rotary' ), /* all title for taxonomies */
			    			'parent_item' 		=> __( 'Parent Project Category', 'rotary' ), /* parent title for taxonomy */
			    			'parent_item_colon' => __( 'Parent Project Category:', 'rotary' ), /* parent taxonomy title */
			    			'edit_item' 		=> __( 'Edit Project Category', 'rotary' ), /* edit custom taxonomy title */
			    			'update_item' 		=> __( 'Update Project Category', 'rotary' ), /* update title for taxonomy */
			    			'add_new_item' 		=> __( 'Add New Project Category', 'rotary' ), /* add new title for taxonomy */
			    			'new_item_name' 	=> __( 'New Project', 'rotary' ) /* name title for taxonomy */
			    		),
    		'show_ui' => true,
    		'query_var' => true,
    		'rewrite' => array( 'slug' => 'project-category' ),
    	)
    );   
	// now let's add custom tags (these act like tags)
    register_taxonomy( 'rotary_project_tag', 
    	array('rotary_projects'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    	array('hierarchical' => false,    /* if this is false, it acts like tags */                
    		'labels' => array(
			    			'name' 				=> __( 'Project Tags', 'rotary' ), /* name of the custom taxonomy */
			    			'singular_name' 	=> __( 'Project Tag', 'rotary' ), /* single taxonomy name */
			    			'search_items' 		=> __( 'Search Project Tags', 'rotary' ), /* search title for taxomony */
			    			'all_items' 		=> __( 'All Projects Tags', 'rotary' ), /* all title for taxonomies */
			    			'parent_item' 		=> __( 'Parent Project Tag', 'rotary' ), /* parent title for taxonomy */
			    			'parent_item_colon' => __( 'Parent Project Tag:', 'rotary' ), /* parent taxonomy title */
			    			'edit_item' 		=> __( 'Edit Project Tag', 'rotary' ), /* edit custom taxonomy title */
			    			'update_item' 		=> __( 'Update Project Tag', 'rotary' ), /* update title for taxonomy */
			    			'add_new_item' 		=> __( 'Add New Project Tag', 'rotary' ), /* add new title for taxonomy */
			    			'new_item_name' 	=> __( 'New Project Tag Name', 'rotary' ) /* name title for taxonomy */
			    		),
    		'show_ui' => true,
    		'query_var' => true,
    	)
    ); 


	}
	//register the custom post type for committees
	function register_commitee_post_type() {
		$labels = array(
			'add_new_item' 	=> __( 'Add Committee', 'rotary' ),
			'edit_item' 	=> __( 'Edit Committee', 'rotary' ),
			'new_item' 		=> __( 'New Committees', 'rotary' ),
			'view_item' 	=> __( 'View Committee', 'rotary' ),
			'search_items' 	=> __( 'Search Committees', 'rotary' ),
			'not_found' 	=> __( 'No Committees Found', 'rotary' )
		);   
  
        $args = array(  
            'label' => __('Committees'),  
			'labels' => $labels,
            'singular_label' => __('Committee'),
			'query_var' => true,  
            'public' => true,  
            'show_ui' => true, 
	        'capability_type' => 'post',  
            'hierarchical' => true,  
			'exclude_from_search' => true,
			'rewrite' => array("slug" => "committees"),
            'supports' => array('title', 'comments', 'editor', 'thumbnail'),
            'has_archive' => true, 
           );  
      
        register_post_type( 'rotary-committees' , $args );  
		
	}
	function rotary_connection_types() {
	   // relate users to committees
		p2p_register_connection_type( array(
			'name' => 'committees_to_users',
			'from' => 'rotary-committees',
			'to' => 'user'
		) );
	//relate posts to committees
		p2p_register_connection_type( array(
			'name' => 'committees_to_posts',
			'from' => 'rotary-committees',
			'to' => 'post'
		) );
	// relate users to projects
		p2p_register_connection_type( array(
			'name' => 'projects_to_users',
			'from' => 'rotary_projects',
			'to' => 'user'
		) );
	//relate posts to projects
		p2p_register_connection_type( array(
			'name' => 'projects_to_posts',
			'from' => 'rotary_projects',
			'to' => 'post'
		) );
		
		//relate projects to committees
		p2p_register_connection_type( array(
			'name' => 'projects_to_committees',
			'from' => 'rotary_projects',
			'to' => 'rotary-committees'
		) );
	}
	
	
	/*************************************************
	  shortcodes to display rotary club members
	*************************************************/
	function get_rotary_club_members( $atts ) { 

		global  $RegistrationNoun, $RegistrationCTA;
		extract( shortcode_atts( array(
			'type' => '', 
			'id' => ''
		), $atts ) );
		
	 	if (!is_user_logged_in() && 'rotary_projects' != get_post_type() ) :
			$memberTable = '
					<div class="rotarymembernotloggedin">
						<p>' . __( 'You must be logged in to see member information', 'Rotary' ) . '</p>
						<p>' . wp_loginout( get_permalink(), false ) . '</p>
					</div>';
			
		else:
			include( ROTARY_THEME_INCLUDES_PATH . 'rotarymembership-layout.php' );
			$memberTable = get_membership_layout( $this, $type, $id );
		endif;
		return $memberTable;
	}
	
	 //register the scripts for shortcodes 
	 function register_script_for_shortcodes() {
		 wp_register_script('datatables', ROTARY_THEME_JAVASCRIPT_URL . 'jquery.dataTables.min.js',  array( 'jquery' ) );
		 wp_register_script('datatablesreload', ROTARY_THEME_JAVASCRIPT_URL . 'jquery.datatables.reload.js',  array( 'jquery' ) );
		 wp_register_script('rotarydatatables', ROTARY_THEME_JAVASCRIPT_URL . 'rotary.datatables.js',  array( 'jquery' ) );
		 wp_register_style( 'rotary-datatables', ROTARY_THEME_CSS_URL . 'rotarydatatables.css', false, 0.1);
		 wp_register_style( 'rotary-custom', ROTARY_THEME_CSS_URL . 'custom.css');
		 wp_register_style( 'rotary-fullcalendar', ROTARY_THEME_CSS_URL . 'fullcalendar.css');
	 }
	 

	 
	 //the scripts included here are need for the shortcodes
	 function enqueue_scripts_for_shortcodes() {
		wp_enqueue_style('rotary-custom');
		wp_enqueue_style('rotary-fullcalendar');
		wp_enqueue_style('rotary-datatables');
		wp_enqueue_script(array('datatables','datatablesreload', 'rotarydatatables', 'jquery-ui-dialog'));
		wp_localize_script( 'rotarydatatables', 'rotarydatatables', array('ajaxURL' => admin_url('admin-ajax.php'),'tableNonce' => wp_create_nonce( 'rotary-table-nonce' )) );
	 }
	 
	 
	 // Get the list of form entries
	 function rotary_get_form_entries() {
		 die(json_encode($this->rotaryProfiles->get_form_entries_json( $_GET['form_id'], $_GET['post_id'] )));
	 }
	 
	 //get the list of members
	 function rotary_get_members() {
		 die(json_encode($this->rotaryProfiles->get_users_json( $_GET['nameorder'] )));
		
	 }
	 //get the member details
	 function rotary_get_member_details() {
		 if (!isset( $_GET['memberID'])) {
			 die (json_encode( array( 'memberName' => 'Invalid Member ID')));  
		 }
		 die(json_encode($this->rotaryProfiles->get_users_details_json($_GET['memberID'])));
		
	 }
	 //delete a member from a project
	 function rotary_delete_project_member() {
		 $current_user = wp_get_current_user();
		 $response = array(
		 	'status' => 'error',
		 	'message' => 'Invalid nonce',
		 );
	     //security check
	     $nonce = $_POST['nonce'];
	     if ( ! wp_verify_nonce( $nonce, 'rotary-table-nonce' ) ) {
	     	die( json_encode( $response ) );
	     }	
	     p2p_type( 'projects_to_users' )->disconnect( $_POST['project_id'], $_POST['user_id'], array('date' => current_time('mysql')));
		 $response['status'] = 'success';
		 $response['message'] = $current_user->ID;
		 die( json_encode( $response ) );
	 }
	 //add a new mmber to a project
	 function rotary_add_project_members() {
		 $current_user = wp_get_current_user();
		 $response = array(
		 	'status' => 'error',
		 	'message' => 'Invalid nonce',
		 );
	     //security check
	     $nonce = $_POST['nonce'];
	     if ( ! wp_verify_nonce( $nonce, 'rotary-table-nonce' ) ) {
	     	die( json_encode( $response ) );
	     }	
		 //check if connection exists
		 $p2p_id = p2p_type( 'projects_to_users' )->get_p2p_id( $_POST['project_id'], $_POST['user_id'] );
		 if ( ! $p2p_id ) {
		 	p2p_type( 'projects_to_users' )->connect( $_POST['project_id'], $_POST['user_id'], array('date' => current_time('mysql')));
		 	$response['status'] = 'success';
		 	$response['message'] = $current_user->ID;
		 	die( json_encode( $response ) );
		 }
		 else {
			 $response['message'] = $current_user->ID;
			 $response['status'] = 'not added';
			 die( json_encode( $response ) );
		 }
		 
	 }
	  //get the committees from the post
	function get_committees_for_membertable() {
		$args = array(
			'posts_per_page' => -1,
			'post_type' 	 => 'rotary-committees'
		);
		$query = new WP_Query( $args );
		$options = '
				<option value="all">' . _x( 'Filter by committee', 'Member directory dropdown for committees', 'rotary' ) . '</option>
				<option value="all">' . _x( 'All', 'Member directory dropdown for committees', 'rotary' ) . '</option>';
		while ( $query->have_posts() ) : $query->the_post();
		  $options .= '<option value="'.get_the_ID().'">'.get_the_title().'</option>';
		endwhile;
		wp_reset_postdata();
		return $options;
	 }
	 //used on project page to add a new user to the project
	 function get_users_for_membertable_select() {
	  	$args = array(
			 'orderby' => 'meta_value',
			 'meta_key' => 'last_name'
		);
		$users = get_users($args);
		$options = '<option value="">' . _x( 'Add a participant', 'Project participant dropdown', 'rotary' ) . '...</option>';
		foreach ($users as $user) {
		    
			$usermeta = get_user_meta($user->ID);
			if (!isset($usermeta['membersince'][0]) || '' == trim($usermeta['membersince'][0])) {
				continue;
			}
			$memberName = $usermeta['last_name'][0]. ', ' .$usermeta['first_name'][0];
		
			$options .= '<option value="'.$user->ID.'">'.$memberName.'</option>';
		}	
		
		return $options;
	 }
	 
	 
	 
}//end class
$rotaryCustomPostTypes = new RotaryCustomPostTypes();