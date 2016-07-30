<?php
/**
 * Template Name: Project Category Archive
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 4.24
 */
 ?>
 
<?php get_header(); ?>
 
	<h1 class="pagetitle"><?php single_cat_title (); ?></h1>
	<div id="page" class="nomargin">
		<div id="content" class="fullwidth" role="main"> 
			<?php			
				get_template_part( 'loop', 'blogroll-projects' ); 		
			?>
		</div>
	</div>
	
<?php get_footer(); 
