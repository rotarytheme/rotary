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
						
                	<?php $speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
                	<?php $date = DateTime::createFromFormat('Ymd', get_field('speaker_date')); ?>
					<span class="speakerdate"><span><?php echo $date->format('l');?></span> <?php echo $date->format('M dS, Y');?></span>
                   <h2> <?php echo $speaker; ?> </h2>
                   <p class="speakertitle">
	                   <?php $field = trim(get_field( 'speaker_title' ));
							if (count($field)) { ?>
								<?php echo($field) ?>&nbsp;&nbsp;&nbsp;
							<?php } ?>
							<?php $field = trim(get_field( 'speaker_company' ));
							if (count($field)) { ?>
								<?php echo($field) ?>&nbsp;&nbsp;&nbsp;
							<?php } ?>
							<?php $field = trim(get_field( 'speaker_email' ));
							if (count($field)) { ?>
										<?php echo '<a href="mailto:'.antispambot($field).'">'.antispambot($field).'</a>'; ?>
							<?php } ?>
                   </p>
                   <div class="speakersectioncontent clearfix">
				<div id="content" role="main" class="speaker">

                   <div class="speakercontent">
                   <?php the_title('<h3>', '</h3>'); ?>
                                        
                     <hr/>  
 
                       <div class="blogcontent">
                       <?php $scribe = get_field('scribe'); ?>
                       <?php $editor = get_field('editor'); ?>
                    	<p>Posted by: <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ))?>"><?php echo get_the_author(); ?></a>&nbsp;&nbsp;&nbsp;
                    	<strong>Scribe:</strong> <a href="<?php echo get_author_posts_url( $scribe['ID'])?>"><?php echo $scribe['user_firstname'] .' ' .  $scribe['user_lastname']; ?></a>&nbsp;&nbsp;&nbsp;
                    	<strong>Editor:</strong> <a href="<?php echo get_author_posts_url( $editor['ID'])?>"><?php echo $editor['user_firstname'] .' ' .  $editor['user_lastname']; ?></a>
                    	</p>
                    	<?php
                    		//program notes are filled in after a speakers visit. If the speaker has not yet been to the club, we show the upcoming content
							$programNotes = trim(get_field('speaker_program_notes'));
							if ('' == $programNotes)
							{
								the_field('speaker_program_content');
							}
							else {
								echo $programNotes;
							}
                        	?>
                            
                        </div>
                       
                       </div><!--.speakercontent-->
                    
		
	
			
			<footer id="speakerfooter">
				<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
				
			</footer>
				</div><?php get_sidebar('speaker'); ?></div>		
		</article>


	

<?php endwhile; // end of the loop. ?>