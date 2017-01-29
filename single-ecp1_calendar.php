<?php
/**
 * Template Name: Calendar Sidebar
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>
<h1 class="pagetitle">
	<span><?php echo get_the_title();  ?></span>
</h1>

<div id="page">
	<div id="content" role="main" class="hassidebar">
	<?php echo get_post_type_archive_link( get_post_type() ); ?>
		<?php get_template_part( 'loop', 'single-calendar' ); ?>
    </div>
    
	<?php get_sidebar( 'calendar' ); ?>
	
  </div>
  
<?php get_footer(); ?>