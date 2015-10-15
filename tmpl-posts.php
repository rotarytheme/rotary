<?php
/**
 *  Template Name: Posts
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

	<?php get_template_part( 'loop', 'page' ); ?>
	<div class="hassidebar">
		<?php get_sidebar(); ?>
	</div>
	
<?php get_footer(); ?>
