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

	$speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name');
	$date = DateTime::createFromFormat('Ymd', get_field( 'speaker_date' ));

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
 	if ( is_user_logged_in() )  $speaker_email = trim( get_field( 'speaker_email' ));
?>

		<nav class="prevnext">

			<div class="nav-previous"><?php previous_post_link( '%link', '' . _x( '&lt;', 'Previous post link', 'Rotary' ) . ' %title' ); ?></div>
			<div class="nav-next"><?php next_post_link( '%link', '%title ' . _x( '&gt;', 'Next post link', 'Rotary' ) . '' ); ?></div>
		</nav>
		
		<div id="content" role="main" class="speaker clearfix">

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div id="speakerheader">
				<span id="speakerdate"><span id="weekday"><?php echo $date->format('l');?></span> <?php echo $date->format('M dS, Y');?></span>
				
				
				
				<?php 
				/***************** START MAILCHIMP CAMPAIGN CUSTOMIZATION ****************/
				if( is_user_logged_in() && ( current_user_can( 'create_mailchimp_campaigns' ) || current_user_can( 'manage_options' ))):?>
					<div id="speaker-mailchimpcampaign">
						<a id="speaker-sendemailtest" class="rotarybutton-largewhite" href="javascript:void" ng-click="saveCampaign()" ><?php echo __( 'Send Test Email', 'Rotary'); ?></a>
						<a id="speaker-sendemailblast" class="rotarybutton-largeblue" href="javascript:void" ng-click="sendCampaign(1)" ><?php echo __( 'Send Email Blast', 'Rotary'); ?></a>
					</div>
				<?php endif;
				/***************** END MAILCHIMP CAMPAIGN CUSTOMIZATION ****************/
				?>
				
				
				
				
				<h2> <?php echo $speaker; ?> </h2>
				<p id="speakertitle">
					<?php if( count( $speaker_title) ) ?> <span id="speaker-title" class="speaker-info"><?php echo( $speaker_title );?></span>
 					<?php if( count( $speaker_company) ) ?> <span id="speaker-company" class="speaker-info"><?php echo( $speaker_company );?></span>
 					<?php if( $speaker_email ) {?> <span id="speaker-email" class="speaker-info"><a href="mailto:<?php echo antispambot( $speaker_email ); ?>"><?php echo  _e( 'Email', 'Rotary'); ?></a></span><?php }?>
				</p>
             </div>
			<div id="speakerbody" >
				<div id="blogheader">
                   		<?php the_title('<h1>', '</h1>'); ?>
                       	<p id="program-roles">
						<?php if( !empty( $scribe )) {?><span id="scribe"><span class="speaker-term-label"><?php echo _e( 'Scribe', 'Rotary' ); ?>:</span> <?php echo $scribe; ?></span><?php }?>
						<?php if( !empty( $editor )) {?><span id="editor"><span class="speaker-term-label"><?php echo _e( 'Editor', 'Rotary' ); ?>:</span> <?php echo $editor; ?></span><?php }?>
						<?php if( !empty( $introducer )) {?><span id="introducer"><span class="speaker-term-label"><?php echo _e( 'Introduced by', 'Rotary' ); ?>:</span> <?php echo $introducer; ?></span><?php }?>
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
					<div id="speakerannouncements">
						<h2><?php echo _e( 'Club Announcements', 'rotary' );?></h2>
						<?php echo do_shortcode( '[ANNOUNCEMENTS lookback=4 speakerdate="' . $date->format('c') . '" context="speaker"]');?>
					</div>
				</div>
			</div><!--.speakerbody-->
			<footer id="speakerfooter">
				<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' );?>
				<?php  if(current_user_can('create_speaker_programs') || current_user_can( 'manage_options' )){ ?>
					<a class="post_new_link rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_speakers">New Speaker</a>
				<?php } ?>
			</footer>
		</article>

<?php endwhile; // end of the loop. 
				
				
				
				
/***************** START MAILCHIMP CAMPAIGN CUSTOMIZATION ****************/?>
				
	<!-- some cool stuff from Najeeb -->
	<script type="text/javascript"></script>
	
	
	
				