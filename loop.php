<?php
/**
 * The loop that displays posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>
<?php 

/* Display navigation to next/previous pages when applicable */
if (  $wp_query->max_num_pages > 1 ) :
?>
    <nav class="prevnext">
        <div class="nav-previous">from loop<?php next_posts_link( __( '&lt; Older posts', 'Rotary' ) ); ?></div>
        <div class="nav-next"><?php previous_posts_link( __( 'Newer posts &gt;', 'Rotary' ) ); ?></div>
    </nav>
<?php 

endif; 

/* If there are no posts to display, such as an empty archive page */
if ( !have_posts() ) : 
?>
	<div class="inner">
		<h2><?php _e( 'Not Found', 'Rotary' ); ?></h2>
		<p><?php _e( 'Apologies, but no results were found for the requested archive.', 'Rotary' ); ?></p>
    </div>   
<?php
else:
	$postCount = 0;
	$clearLeft='';
	while ( have_posts() ) : the_post(); 
		rotary_output_blogroll(); 
		comments_template( '', true ); 
	endwhile; // End the loop. Whew.
endif;