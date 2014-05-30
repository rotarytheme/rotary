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
							<?php if ( is_user_logged_in() ) { ?>
								<?php $field = trim(get_field( 'speaker_email' ));
	if (count($field)) { ?>
										<?php echo '<a href="mailto:'.antispambot($field).'">'.antispambot($field).'</a>'; ?>
									<?php } ?>
								<?php } ?>
                   </p>
                   <div class="speakersectioncontent clearfix">
				<div id="content" role="main" class="speaker">

                   <div class="speakercontent">
                   <?php the_title('<h3>', '</h3>'); ?>

                     <hr/>

                       <div class="blogcontent">
                       <?php if ( get_field('speaker_program_notes' )) { ?>
                        <p>
                       	<?php $terms = wp_get_post_terms( get_the_id(), 'rotary_program_scribe' ); ?>
	                    	<?php if ($terms) { ?>
		    	  		    	   		<strong>Scribe:</strong> <?php echo $terms[0]->name; ?>
				  			<?php } ?>

                    	<?php $terms = wp_get_post_terms( get_the_id(), 'rotary_program_editor' ); ?>
	                    	<?php if ($terms) { ?>
		    	  		    	   		<strong>Editor:</strong> <?php echo $terms[0]->name; ?>
				  		   <?php } ?>
                    	<?php }
							else { ?>
	                    	<?php $terms = wp_get_post_terms( get_the_id(), 'rotary_program_introducer_cat' ); ?>
	                    	 <?php if ($terms) { ?>
		    	  		    	   		<p><strong>Introduced By:</strong> <?php echo $terms[0]->name; ?></p>
				  			<?php } ?>
                        </p>	
	                   <?php  } ?>


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
				<?php  if(current_user_can('edit_page')){ ?>
					<a class="post_new_link button" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_speakers">New Speaker</a>
				<?php } ?>

			</footer>
				</div><?php get_sidebar('speaker'); ?></div>
		</article>




<?php endwhile; // end of the loop. ?>