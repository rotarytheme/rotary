<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<h1 class="pagetitle"><span><?php the_title();  ?></span></h1>

<div id="page">
	<?php get_sidebar('committee'); ?>
	<div id="content" class="committee" role="main">
	
	<?php comments_template( '/tmpl-committee-announcements.php' ); ?> 
	
	<?php get_template_part( 'loop', 'single-committee' ); ?>
	
    </div>
</div>

<?php get_footer(); ?>