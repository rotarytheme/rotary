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
		
        <?php  
   
		  if ($postCount % 3 != 0) {
			  $clearLeft='';
		  }
		  else {
			  $clearLeft='clearleft';
		  }
			$postCount++;  
		?>

     
        <article id="post-<?php the_ID(); ?>" <?php post_class($clearLeft); ?>>
         	<div class="sectioncontainer">
            	<div class="sectionheader" id="blog-<?php the_ID(); ?>" >
                	<div class="sectioncontent">
			<header>
				<?php $date = DateTime::createFromFormat('Ymd', get_field('speaker_date')); ?>

                <h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'Rotary' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><span class="speakerday"><?php echo $date->format('l'); ?></span> <?php echo $date->format('M d, Y'); ?></a></h2>
                <?php $speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
                <h3 class="speakername"><?php echo $speaker?></h3>
            </header>
 
   
                <?php $thumb = has_post_thumbnail(); ?>
                <?php if ( $thumb) { // check if the post has a Post Thumbnail assigned to it.
				        $attr = array(
							'class'	=> 'alignleft',
							);?>

  						<a href="<?php the_permalink(); ?> "><?php the_post_thumbnail('post-thumbnail', $attr);?></a>
					<?php } ?>
   
                 
              <?php
                   //program notes are filled in after a speakers visit. If the speaker has not yet been to the club, we show the upcoming content
					$programNotes = trim(get_field('speaker_program_notes'));
					if ('' == $programNotes)
					{
						$programNotes = trim(get_field('speaker_program_content'));
					}
					$programNotes = preg_replace('/<img[^>]+./','', $programNotes);
					$programNotes = strip_tags($programNotes);
					if (strlen($programNotes) > 50 ) {
						$programNotes = substr($programNotes, 0, 50) ;
					} 
					?>
                

			  <p><?php echo $programNotes; ?></p>
              <p class="continue"><a href="<?php the_permalink();?>">Keep Reading...</a></p>  
 
              
  
            <footer class="meta">
            <p>
                <?php edit_post_link( __( 'Edit', 'Rotary' )); ?>
                               
                
                 
            </footer>
               				</div><!--.sectioncontent-->
                </div> <!--.sectionheader-->
			</div><!--.sectioncontainer-->
		</article>
 
            
 
 
 
<?php endwhile; // End the loop. Whew. ?>