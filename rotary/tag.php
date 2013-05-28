<?php
/**
 * The template for displaying Tag Archive pages.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

		<h1 class="pagetitle"><span><?php
			printf( __( 'Tag Archives: %s', 'Rotary' ), '' . single_tag_title( '', false ) . '' );
		?></span></h1>
<div id="content" role="main"> 
<?php
 get_template_part( 'loop', 'tag' );
?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>