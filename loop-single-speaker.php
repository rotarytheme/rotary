<?php
/**
 * The loop that displays a single post.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); 

	 if ( get_field('speaker_program_notes' )) :
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_scribe' );
	 	$scribe = $terms[0]->name;
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_editor' );
	 	$editor = $terms[0]->name;
	 endif;
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_introducer_cat' );
	 	$introducer = $terms[0]->name;
 
 	$speaker_title = trim( get_field( 'speaker_title' ));
 	$speaker_company = trim( get_field( 'speaker_company' ));
 	$speaker_email = trim( get_field( 'speaker_email' ));
 	if ( is_user_logged_in() ) $speakertitle = trim( get_field( 'speaker_title' ));
?>

		<nav class="prevnext">

			<div class="nav-previous"><?php previous_post_link( '%link', '' . _x( '&lt;', 'Previous post link', 'Rotary' ) . ' %title' ); ?></div>
			<div class="nav-next"><?php next_post_link( '%link', '%title ' . _x( '&gt;', 'Next post link', 'Rotary' ) . '' ); ?></div>
		</nav>
		
		<div id="content" role="main" class="speaker clearfix">

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div id="speakerheader">
                <?php $speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
                <?php $date = DateTime::createFromFormat('Ymd', get_field( 'speaker_date' )); ?>
				<span id="speakerdate"><span id="weekday"><?php echo $date->format('l');?></span> <?php echo $date->format('M dS, Y');?></span>
				<h2> <?php echo $speaker; ?> </h2>
				<p id="speakertitle">
					<?php if( count( $speaker_title) ) ?> <span id="speaker-title" class="speaker-info"><?php echo( $speaker_title );?></span>
 					<?php if( count( $speaker_company) ) ?> <span id="speaker-title" class="speaker-info"><?php echo( $speaker_company );?></span>
 					<?php if( count( $speaker_email) ) ?> <span id="speaker-title" class="speaker-info"><?php echo '<a href="mailto:'.antispambot( $speaker_email ) . '">' . _e( 'email', 'Rotary') . '</a>'; ?></span>
				</p>
             </div>
			<div id="speakerbody" >
				<div id="blogheader">
                   		<?php the_title('<h1>', '</h1>'); ?>
                       	<p id="program-roles">
						<?php if( !empty( $scribe )) {?><span id="scribe"><span class="speaker-term-label"><?php echo _e( 'Scribe', 'Rotary' ); ?>:</span> <?php echo $scribe; ?></span><?php }?>
						<?php if( !empty( $editor )) {?><span id="editor"><span class="speaker-term-label"><?php echo _e( 'Editor', 'Rotary' ); ?>:</span> <?php echo $editor; ?></span><?php }?>
						<?php if(!empty( $introducer )) {?><span id="introducer"><span class="speaker-term-label"><?php echo _e( 'Introduced by', 'Rotary' ); ?>:</span> <?php echo $introducer; ?></span><?php }?>
						</p>
				</div>
				<div id="speakerinfo" <?php if( empty( $scribe ) && empty( $editor ) && empty( $introducer )) echo 'class="noroles"'; ?>>
					<?php get_sidebar( 'speaker' ); ?>
				</div>
				<div id="blogcontent">
				<?php
					//program notes are filled in after a speakers visit. If the speaker has not yet been to the club, we show the upcoming content
					$programNotes = trim(get_field('speaker_program_notes'));
					if ( empty( $programNotes) ) the_field('speaker_program_content');
						else echo $programNotes;
				?>
				</div>
			</div><!--.speakerbody-->
			<footer id="speakerfooter">
				<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
				<?php  if(current_user_can('edit_page')){ ?>
					<a class="post_new_link rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_speakers">New Speaker</a>
				<?php } ?>
			</footer>
		</article>

<?php endwhile; // end of the loop. ?>