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

		<div class="committeecontent">
			<?php the_content(); ?>
			<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
		</div>
	

<?php endwhile; // end of the loop. ?>