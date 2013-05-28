<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>
	<?php get_template_part( 'loop', 'page' ); ?>
<?php //get_sidebar(); ?>
<?php get_footer(); ?>