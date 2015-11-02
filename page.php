<?php
/**
 * this is a full page template
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<h1 class="pagetitle"><span><?php the_title();  ?></span></h1>
	<?php get_template_part( 'loop', 'page' ); ?>

<?php get_footer(); ?>