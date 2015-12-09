<?php
/**
 * The loop that displays a single post.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
		<article id="calendar-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="blogcontent">
				<?php the_content(); ?>
			</div>
			<footer>
				<?php //Rotary_posted_in(); ?>
				<?php //edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
			</footer>
		</article>

<?php endwhile; // end of the loop ?>