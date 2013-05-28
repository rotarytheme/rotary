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
<?php if ( ! have_posts() ) : ?>
        <h2><?php _e( 'Not Found', 'Rotary' ); ?></h2>
            <p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'Rotary' ); ?></p>
            <?php get_search_form(); ?>
<?php endif; ?>
 
<?php while ( have_posts() ) : the_post(); ?>
 		<?php /* Display navigation to next/previous pages when applicable */ ?>
		<?php if (  $wp_query->max_num_pages > 1 ) : ?>
    		<nav class="prevnext">
        		<div class="nav-previous"><?php next_posts_link( __( '&lt; Older posts', 'Rotary' ) ); ?></div>
        		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts &gt;', 'Rotary' ) ); ?></div>
    		</nav>
		<?php endif; ?>


     
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
         	<div class="inner">
      
			<header>
                <h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'Rotary' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                
            </header>
            <?php the_excerpt(); ?>
    
             <footer class="meta">
 
                <?php if ( count( get_the_category() ) ) : ?>
                        <?php printf( __( 'Posted in %2$s', 'Rotary' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?> &middot;
                <?php endif; ?>
                <?php
                    $tags_list = get_the_tag_list( '', ', ' );
                    if ( $tags_list ):
                ?>
                        <?php printf( __( 'Tagged %2$s', 'Rotary' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?> |
                <?php endif; ?>
                <?php comments_popup_link( __( 'Leave a comment', 'Rotary' ), __( '1 Comment', 'Rotary' ), __( '% Comments', 'Rotary' ), 'commentspopup' ); ?>
                <?php edit_post_link( __( 'Edit', 'Rotary' ), ' &middot; ', '' ); ?>
                 
            </footer>
               	
			</div><!--.inner-->
		</article>
 
            <?php comments_template( '', true ); ?>
 
 
 
<?php endwhile; // End the loop. Whew. ?>