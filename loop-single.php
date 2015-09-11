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

		<nav class="prevnext">
			<div class="nav-previous"><?php previous_post_link( '%link', '' . _x( '&lt;', 'Previous post link', 'Rotary' ) . ' %title' ); ?></div>
			<div class="nav-next"><?php next_post_link( '%link', '%title ' . _x( '&gt;', 'Next post link', 'Rotary' ) . '' ); ?></div>
		</nav>
		
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
			<div class="sectioncontainer">
            	<div class="sectionheader" id="blog">
                	<div class="sectioncontent">
                    <?php the_title('<h2>', '</h2>'); ?>
                    <p>Posted By <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ))?>"><?php echo get_the_author(); ?></a></p>
                    <div class="postdate">
                       	<?php Rotary_posted_on(); ?>
                       </div>
                     <hr/>  
 
                       <div class="blogcontent">
                    	
                        	
							<?php the_content(); ?>
                            
                        </div>
                        <?php Rotary_posted_in(); ?>
                        <hr/>
                        <?php comments_template( '', true ); ?>
                    </div><!--.sectioncontent-->
                </div> <!--.sectionheader-->
			</div><!--.sectioncontainer-->
		
	
			
			<footer>
				<?php //Rotary_posted_in(); ?>
				<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
			</footer>
				
		</article>


	

<?php endwhile; // end of the loop ?>