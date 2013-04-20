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
		
        <?php $postCount++; 
		  if ($postCount % 2 == 0) {
			  $clearLeft='';
		  }
		  else {
			  $clearLeft='clearleft';
		  }
			  
		?>

     
        <article id="post-<?php the_ID(); ?>" <?php post_class($clearLeft); ?>>
         	<div class="sectioncontainer">
            	<div class="sectionheader" id="blog">
                	<div class="sectioncontent">
			<header>
                <h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'Rotary' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                <div class="postdate">
                	<span class="alignleft">Posted by <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ))?>"><?php echo get_the_author();?></a></span>
                    <span class="alignright"><?php Rotary_posted_on(); ?></span>
                </div>    
            </header>
 
   
                <?php $thumb = has_post_thumbnail(); ?>
                <?php if ( $thumb) { // check if the post has a Post Thumbnail assigned to it.
				        $attr = array(
							'class'	=> 'alignleft',
							);?>

  						<a href="<?php the_permalink(); ?> "><?php the_post_thumbnail('post-thumbnail', $attr);?></a>
					<?php } ?>
   
                <?php if ( $thumb) { ?> 
               		<section class="excerptcontainer"> 
               <?php } ?>  
              <?php the_excerpt(); ?>

              <?php if ( $thumb) { ?> 
                </section>
              <?php } ?>    
              
  
            <footer class="meta">
            <p>
                <?php edit_post_link( __( 'Edit', 'Rotary' ), '', ' &middot;' ); ?>
                <?php comments_popup_link( __( 'Leave a comment', 'Rotary' ), __( '1 Comment', 'Rotary' ), __( '% Comments', 'Rotary' ), 'commentspopup' ); ?></p>
                <?php if ( count( get_the_category() ) ) : ?>
                        <p><?php printf( __( 'Posted in %2$s', 'Rotary' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?></p>
                <?php endif; ?>
                <?php
                    $tags_list = get_the_tag_list( '', ', ' );
                    if ( $tags_list ):
                ?>
                        <p><?php printf( __( 'Tagged %2$s', 'Rotary' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?></p>
                <?php endif; ?>
                
                
                 
            </footer>
               				</div><!--.sectioncontent-->
                </div> <!--.sectionheader-->
			</div><!--.sectioncontainer-->
		</article>
 
            <?php comments_template( '', true ); ?>
 
 
 
<?php endwhile; // End the loop. Whew. ?>