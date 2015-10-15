<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>
<h1 class="pagetitle">
	<span><?php echo  get_the_title(get_option( 'page_for_posts' ));  ?></span>
</h1>

<div id="page">
	<div id="content" role="main" class="hassidebar">
	<?php echo get_post_type_archive_link( get_post_type() ); ?>
	<?php echo get_post_type(); ?>
		<?php get_template_part( 'loop', 'single' ); ?>
    </div>
    
	<?php get_sidebar(); ?>
	
  </div>
  
<?php get_footer(); ?>