<?php
/**
* Template Name: Home Page
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<?php  if (get_theme_mod( 'rotary_slideshow', true )) {
	rotary_get_slideshow(); 
} ?>
<div id="content" role="main"
<?php if (!get_theme_mod( 'rotary_home_sidebar', true )) { ?>
  class="fullwidth"
<?php } ?>
>
<?php if (get_theme_mod( 'rotary_home_featured', true )) { 
    rotary_get_featured_post(); 
	
} ?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
   <section class="homecontent">
      <div class="inner">
	   <?php the_content();?>
       <?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
     </div>
    </section>
<?php endwhile; ?>
</div>
<?php if (get_theme_mod( 'rotary_home_sidebar', true )) { 
	get_sidebar('home'); 
} ?>
<?php get_footer(); ?>