<?php
/**
 * The template for displaying Archive pages.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<?php
	if ( have_posts() )
		the_post();
?>

			<h1 class="pagetitle"><span>Speaker Program</span></h1>
<h2 class="pagesubtitle">Upcoming Programs</h2>
<?php
	rewind_posts(); ?>
<div id="content" role="main" class="fullwidth">
<?php	get_template_part( 'loop', 'archive-speaker' );
?>
</div>

<?php get_footer(); ?>