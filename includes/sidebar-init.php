<?php
/**
 * Rotary widgets
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 /** Register widgetized areas.
 *
 * @since rotary 1.0
 */
function rotary_widgets_init() {
	// Area 1, located at the top of the sidebar on the home page. Contains Rotary text widgets and Rotary Blogroll
	register_sidebar( array(
		'name' => __( 'Home Widget Area', 'rotary' ),
		'id' => 'home-widget-area',
		'description' => __( 'The primary widget area for the home page', 'rotary' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );

	// Area 2, located at the top of the sidebar for all pages (including blog) other than the home page. Contains custom archive widget (plugin) and tags
	register_sidebar( array(
		'name' => __( 'Posts Widget Area', 'rotary' ),
		'id' => 'secondary-widget-area',
		'description' => __( 'The secondary widget area', 'rotary' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );

	// Area 3, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Footer Navigation Widget Area', 'rotary' ),
		'id' => 'first-footer-widget-area',
		'description' => __( 'The footer navigation widget area', 'rotary' ),
		'before_widget' => '<li>',
		'after_widget' => '</li>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );

	// Area 4, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'About Footer Widget Area', 'rotary' ),
		'id' => 'second-footer-widget-area',
		'description' => __( 'The second footer widget area', 'rotary' ),
		'before_widget' => '<li>',
		'after_widget' => '</li>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );

}
/** Register sidebars by running rotary_widgets_init() on the widgets_init hook. */
add_action( 'widgets_init', 'rotary_widgets_init' );
function rotary_install_default_widgets() {
	//add default widgets to the home page side bar
	echo 'installing widgets';
	$sidebar = 'home-widget-area';
	$widget_name = 'text';
	$sidebars_widgets = wp_get_sidebars_widgets();
	if ( empty( $sidebars_widgets[$sidebar] ) ) {
		$sidebar_options = get_option('widget_'.$widget_name);
		$startcount = $count = count($sidebar_options); 
		$sidebar_options[$count] = 
		array(
			'title' => 'Rotary Vision',
			'text' => '',
			'filter' => ''
		);
        $count++;
		$sidebar_options[$count] = 
		array(
			'title' => 'Rotary Mission', 
			'text' => '', 
			'filter' => ''
		);
        $count++;
		$sidebar_options[$count] = 
		array(
			'title' => 'Project Filters', 
			'text' => '', 
			'filter' => ''
		);
        $count++;
		$sidebar_options[$count] = 
		array(
			'title' => '4-Way Test', 
			'text' => '', 
			'filter' => ''
		);
		update_option('widget_'.$widget_name,$sidebar_options);
		$sidebars_widgets[$sidebar] = array();
		for ($i=$startcount; $i <=$count; $i++) {
			$sidebars_widgets[$sidebar][] = $widget_name.'-'.$i;
		}	
		//add the rotary blogroll
		$widget_name = 'rotarylinks';
		$sidebar_options = get_option('widget_'.$widget_name);
		$count = count($sidebar_options); 
		$sidebar_options[$count] = 
		array(
			'images' => 0, 
			'name' => '', 
			'description' => '',
			'rating' => 0,
			'orderby' => 0,
			'limit' => 0
		);
		update_option('widget_'.$widget_name,$sidebar_options);	
		$sidebars_widgets[$sidebar][] = $widget_name.'-'.$count;
		
		wp_set_sidebars_widgets( $sidebars_widgets );
	}
	//add default widgets to the secondary side bar
	$sidebar = 'secondary-widget-area';
	$widget_name = 'rotaryarchivewidget';
	$sidebars_widgets = wp_get_sidebars_widgets();
	if ( empty( $sidebars_widgets[$sidebar] ) ) {
		$sidebar_options = get_option('widget_'.$widget_name);
		$count = count($sidebar_options); 
		$sidebar_options[$count] = 
		array(
			'title' => 'Archives', 
			'showcount' => 0, 
			'linkcounter' => 0, 
			'truncmonth' => 0,
			'jsexpand' => 1,
			'groupbyyear' => 1,
			'limitbycategory' => 0
		);
		$sidebars_widgets[$sidebar] = array();
		update_option('widget_'.$widget_name,$sidebar_options);	
		$sidebars_widgets[$sidebar][] = $widget_name.'-'.$count;
		//add the tag cloud
		$widget_name = 'tag_cloud';
		$sidebar_options = get_option('widget_'.$widget_name);
		$count = count($sidebar_options); 
		$sidebar_options[$count] = 
		array(
			'title' => 'Tags', 
			'taxonomy' => ''
		);
		update_option('widget_'.$widget_name,$sidebar_options);	
		$sidebars_widgets[$sidebar][] = $widget_name.'-'.$count;

		
		wp_set_sidebars_widgets( $sidebars_widgets );
		
	}
	
}
add_action('after_switch_theme', 'rotary_install_default_widgets');