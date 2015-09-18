<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>

<?php get_header(); ?>

<div id="page">
	<div id="content" class="">
	<?php get_template_part( 'loop', 'page' ); ?>
	</div>

	<?php //get_sidebar(); ?>

	</div>

<?php get_footer(); ?>