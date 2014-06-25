<?php
/**
 * The loop that displays posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>
 

 
<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : ?>
    		<nav class="prevnext">
        		<div class="nav-previous"><?php next_posts_link( __( '&lt; Older posts', 'Rotary' ) ); ?></div>
        		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts &gt;', 'Rotary' ) ); ?></div>
    		</nav>
		<?php endif; ?>
<?php if ( ! have_posts() ) : ?>
        <div class="inner">
        	<h2><?php _e( 'Not Found', 'Rotary' ); ?></h2>
            <p><?php _e( 'Apologies, but no results were found for the requested archive.', 'Rotary' ); ?></p>
         </div>   
<?php endif; ?>
<?php $postCount = 0;
$clearLeft='';
?>
<?php while ( have_posts() ) : the_post(); ?>
 		<?php /* Display navigation to next/previous pages when applicable */ ?>
		
        	<?php $postCount = rotary_output_blogroll($postCount, $clearLeft); ?>
            <?php comments_template( '', true ); ?>
 
 
 
<?php endwhile; // End the loop. Whew. ?>