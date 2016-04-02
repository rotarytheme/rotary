<?php
/**
 * Template Name: Project Archive
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 
<?php get_header(); ?>

	<h1 class="pagetitle"><?php _e( 'All Projects', ' Rotary'); ?></h1>
	<div id="page" class="nomargin">
		<div id="content" class="fullwidth" role="main"> 
			<?php 
				$args = array( 
						'post_type'		=> 'rotary_projects',
						'meta_key'		=> 'rotary_project_date',
						'orderby'		=> 'meta_value_num',
						'order'			=> 'DESC'
					);
				$wp_query = new WP_Query( $args ); 
				get_template_part( 'loop', 'blogroll-projects' ); 
			?>
		</div>
	</div>
	
<?php get_footer(); 
