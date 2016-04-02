<?php
/**
 * Name:  Sponsors
 * Author: Luke Holschbach
 * Author URI: http://jukes.us
 * License:
 */
 
 
/*******************************************************
**************** LOAD CSS ******************************
*******************************************************/
 
 function sponsors_load_css() {
    wp_enqueue_style( 'sponsors_style',ROTARY_THEME_CSS_URL . 'sponsors_style.css' );
}
add_action( 'wp_enqueue_scripts', 'sponsors_load_css' );
 

/*******************************************************
********** CREATE CUSTOM POST TYPE *********************
*******************************************************/

add_action( 'init', 'create_custom_post_type' );

function create_custom_post_type() {
  register_post_type( 'sponsors',
    array(
      'labels' => array(
        'name'               => _x( 'Sponsors', 'post type general name'),
        'singular_name'      => _x( 'Sponsor', 'post type singular name'),
        'menu_name'          => _x( 'Sponsors', 'admin menu'),
        'name_admin_bar'     => _x( 'Sponsor', 'add new on admin bar'),
        'add_new'            => _x( 'Add New', 'sponsor'),
        'add_new_item'       => __( 'Add New Sponsor'),
        'new_item'           => __( 'New Sponsor'),
        'edit_item'          => __( 'Edit Sponsor'),
        'view_item'          => __( 'View Sponsor'),
        'all_items'          => __( 'All Sponsors'),
        'search_items'       => __( 'Search Sponsors'),
        'parent_item_colon'  => __( 'Parent Sponsors:'),
        'not_found'          => __( 'No Sponsors found.'),
        'not_found_in_trash' => __( 'No Sponsors found in Trash.')
      ),
	  '_builtin' => false,
      'public' => true,
	  'hierarchical' => true,
	  'supports' => array('title', 'editor')
    )
  );
}


/*******************************************************
********** CREATE CUSTOM TAXONOMY **********************
*******************************************************/

add_action( 'init', 'create_sponsor_level_hierarchical_taxonomy', 0 );

function create_sponsor_level_hierarchical_taxonomy() {
  $labels = array(
    'name' => _x( 'Sponsorship Levels', 'taxonomy general name' ),
    'singular_name' => _x( 'Sponsorship Level', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Sponsorship Levels' ),
    'all_items' => __( 'All Sponsorship Levels' ),
    'parent_item' => __( 'Parent Sponsorship Level' ),
    'parent_item_colon' => __( 'Parent Sponsorship Level:' ),
    'edit_item' => __( 'Edit Sponsorship Level' ), 
    'update_item' => __( 'Update Sponsorship Level' ),
    'add_new_item' => __( 'Add New Sponsorship Level' ),
    'new_item_name' => __( 'New Sponsorship Level Name' ),
    'menu_name' => __( 'Sponsorship Levels' ),
  ); 	

// Now register the taxonomy

  register_taxonomy('sponsorship_levels',array('sponsors'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'sponsorship_levels' ),
  ));

}


/*******************************************************
********** IMPORT ACF SETTINGS *************************
*******************************************************/

if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_sponsors',
		'title' => 'Sponsors',
		'fields' => array (
			array (
				'key' => 'field_56900e27024cb',
				'label' => 'Sponsor Logo',
				'name' => 'sponsor_logo',
				'type' => 'image',
				'save_format' => 'object',
				'preview_size' => 'thumbnail',
				'library' => 'all',
			),
			array (
				'key' => 'field_56900e2f024cc',
				'label' => 'Sponsor URL',
				'name' => 'sponsor_url',
				'type' => 'text',
				'instructions' => 'The link to the sponsor\'s website.',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'sponsors',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_sponsorship-level-settings',
		'title' => 'Sponsorship Level Settings',
		'fields' => array (
			array (
				'key' => 'field_56b24ff1278a9',
				'label' => 'Sponsorship Level Order',
				'name' => 'sponsorship_level_order',
				'type' => 'number',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => 1,
				'max' => '',
				'step' => '',
			),
			array (
				'key' => 'field_56b3a7874e463',
				'label' => 'Sponsor Style',
				'name' => 'sponsor_style',
				'type' => 'select',
				'instructions' => 'Select the layout for this sponsorship level.',
				'choices' => array (
					'full' => 'Full-width sponsor (no text)',
					'full_text' => 'Full-width sponsor (with accompanying text side-by-side)',
					'full_text_bottom' => 'Full-width sponsor (with accompanying text below)',
					'double' => 'Two sponsors per row',
					'triple' => 'Three sponsors per row',
					'quad' => 'Four sponsors per row',
				),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'ef_taxonomy',
					'operator' => '==',
					'value' => 'sponsorship_levels',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}



/*******************************************************
********** CREATE SHORTCODE ****************************
*******************************************************/


function sponsorsShortcode( $atts ) {
	// Check for option to display titles or not
    $a = shortcode_atts( array(
        'show_titles' => false
    ), $atts, sponsorsShortcode );
	
	$show_titles = filter_var( $a['show_titles'], FILTER_VALIDATE_BOOLEAN );

	// Get the levels from the taxonomy term we created
	$levels = get_terms( 'sponsorship_levels' );
	// Create array to store sponsors in sponsorship levels
	$sponsor_order_array = array();
	// Add the sponsorship levels to the array
	foreach( $levels as $level ) : 
		// Set order according to ACF order
		if (function_exists('get_field')) {
			if (get_field('sponsorship_level_order', $level)) {
				$order = get_field('sponsorship_level_order', $level);
				$sponsor_order_array[$order] = $level->name;
			} else { // Otherwise just put them in created order I guess
				$sponsor_order_array[] = $level->name;
			}
		} else { // Otherwise just put them in created order I guess
			$sponsor_order_array[] = $level->name;
		}
	endforeach;
	// Sort the levels numerically
	ksort($sponsor_order_array);
	// Start a new array ordered with level name as key
	$sponsor_array = array();
	foreach($sponsor_order_array as $key => $val) {
		$sponsor_array[$val] = array();
	}
	
	/*
	* RUN SPONSOR LOOP TO STORE IN ARRAY
	*/
	$sponsors_args = array( 
		'post_type' => 'sponsors',
		'posts_per_page' => '-1',
		'orderby' => 'menu_order',
		'order' => 'ASC',
	);
	
	$sponsors_loop = new WP_Query($sponsors_args);
	// Make sure ACF is active
	if (function_exists('get_field')) :
		while ( $sponsors_loop->have_posts() ) : $sponsors_loop->the_post();
			// Get ACF Values
			$meta_sponsor_link = get_field('sponsor_url');
			$sponsor_image = get_field('sponsor_logo');
			// Make sure user added link does not end up local
			if ($meta_sponsor_link != NULL) {
				preg_match('/^http/', $meta_sponsor_link, $link_matches);
				if (empty($link_matches)) {
					$meta_sponsor_link = 'http://' . $meta_sponsor_link;
				}
			}
			// Get SPONSORSHIP LEVEL
			$term = wp_get_post_terms(get_the_ID(), 'sponsorship_levels');
			$level = $term[0]->name;
			// Make sure it's been given a sponsorship level and add to array
			if ($level !== NULL) {
				$sponsor_array[$level][] = array(
					'sponsor_name' => get_the_title(),
					'sponsor_url' => $meta_sponsor_link,
					'sponsor_image' => $sponsor_image['sizes']['medium'],
					'sponsor_content' => get_the_content()
				);
			}
		endwhile;
	endif;
	wp_reset_postdata();
	
	/*
	* PRINT SPONSORS
	*/
	
	foreach($sponsor_array as $k_level => $v_level_sponsors) :
		echo '<div class="sponsor_level">';
		// Display titles (User controlled in shortcode)
		if ($show_titles) {
			echo '<span class="sponsor_level_title">';
			echo '<h4>' . $k_level . '</h4>';
			echo '</span>';
		}
		// Get Layout from ACF
		$layout_class = '';
		if (function_exists('get_field')) {
			$category = get_term_by('name', $k_level, 'sponsorship_levels');
			$layout_class = get_field('sponsor_style', $category);
		}
		
		// Sponsor Block
		foreach ($v_level_sponsors as $key => $val) {
			echo '<div class="sponsor ' . $layout_class . '">';
			// Sponsor Image
			echo '<div class="sponsor_logo">';
			if ($val['sponsor_url'] !== '') {
				echo '<a href="' . $val['sponsor_url'] . '" target="_blank"><img src="' . $val['sponsor_image'] . '" alt="' . $val['sponsor_name'] . '" /></a>';
			} else {
				echo '<img src="' . $val['sponsor_image'] . '" alt="' . $val['sponsor_name'] . '" />';
			}
			echo '</div>'; // Close sponsor image
			// Sponsor Content
			if ($val['sponsor_content'] !== '') {
				echo '<div class="sponsor_content">';
				echo '<h3 class="sponsor_title">' . $val['sponsor_name'] . '</h3>';
				$c = apply_filters('the_content', $val['sponsor_content']);
				echo $c . '</div>';
			}
			echo '</div>'; // Close sponsor
		} // End inner loop
		
		echo '</div>'; // Close sponsor level block
	endforeach;
	
}
add_shortcode( 'sponsors', 'sponsorsShortcode' );






?>