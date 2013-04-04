<?php
/**
 * The main template file.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 
get_header(); ?>
<h1 class="pagetitle"><span><?php echo  get_the_title(get_option( 'page_for_posts' ));  ?></span></h1>
 
<div id="content" role="main"> 

    <?php get_template_part( 'loop', 'index' ); ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>