<?php
/**
 * The loop that displays a page.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
    <h1 class="pagetitle"><span><?php the_title(); ?></span></h1>
    <div id="content" role="main" class="fullwidth">
    	<div class="inner">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>	

				<?php the_content(); ?>
						
				<?php wp_link_pages( array( 'before' => '<nav>' . __( 'Pages:', 'Rotary' ), 'after' => '</nav>' ) ); ?>
						
				<footer>
				<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
				</footer>
			</article>
        </div>
     </div>
	

<?php endwhile; ?>