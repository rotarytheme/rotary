<?php
/**
* Template Name: Announcement Projector
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 4.20
 */

get_header( 'slideshow' ); ?>

	<div id="page">
		<div id="content" role="main" class="fullwidth slideshow">
			<?php echo do_shortcode( '[ANNOUNCEMENTS context="slideshow"]' ); ?>
		</div>
	</div>
	
<?php get_footer( 'slideshow' );