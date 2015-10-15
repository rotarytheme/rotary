<?php
/**
 * The loop that displays a single post.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
	$args = array(
		'p'         => 1,
		'post_type' => array('post'),
	);
	$query = new WP_Query( $args );

?>

<?php if ( $query->have_posts() ) while ( $query->have_posts() ) : $query->the_post();
	setup_postdata($query->the_post());

	$speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name');
 	$speaker_title = trim( get_field( 'speaker_title' ));
 	$speaker_company = trim( get_field( 'speaker_company' ));
 	
 	$date = DateTime::createFromFormat('Ymd', get_field( 'speaker_date' ));

	 if ( get_field('speaker_program_notes' )) :
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_scribe' );
	 	$scribe = $terms[0]->name;
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_editor' );
	 	$editor = $terms[0]->name;
	 endif;
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_introducer_cat' );
	 	$introducer = $terms[0]->name;
 
 	
?>

<!--  As per http://blog.mailchimp.com/turn-any-web-page-into-html-email-part-2/ ??? or however you want to do this...    media="email"-->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/mailchimp-campaign/email.css" />

<style>

	
</style>

	<table id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<tr id="speakerheader">
			<td id="speakertitle" class="goldborder-bottom firstcol">
				<h2> <?php echo $speaker; ?> </h2>
				<p>
					<?php if( count( $speaker_title) ) ?> <span id="speaker-title" class="speaker-info"><?php echo( $speaker_title );?></span>
 					<?php if( count( $speaker_company) ) ?> <span id="speaker-company" class="speaker-info"><?php echo( $speaker_company );?></span>
				</p>
			</td>
			<td class="cutaway_image secondcol">&nbsp;</td>
			<td id="speakerdate" class="greyborder-right greyborder-top thirdcol"><span id="weekday"><?php echo $date->format('l');?></span> <?php echo $date->format('M dS, Y');?></span></td>
		</tr>
		<tr >
			<td id="blogheader" colspan="2" class="greyborder-left">
				<?php the_title('<h1>', '</h1>'); ?>
				<hr>
				<p id="program-roles">
				<?php if( !empty( $scribe )) {?><span id="scribe"><span class="speaker-term-label"><?php echo _e( 'Scribe', 'Rotary' ); ?>:</span> <?php echo $scribe; ?></span><?php }?>
				<?php if( !empty( $editor )) {?><span id="editor"><span class="speaker-term-label"><?php echo _e( 'Editor', 'Rotary' ); ?>:</span> <?php echo $editor; ?></span><?php }?>
				<?php if( !empty( $introducer )) {?><span id="introducer"><span class="speaker-term-label"><?php echo _e( 'Introduced by', 'Rotary' ); ?>:</span> <?php echo $introducer; ?></span><?php }?>
				</p>
			</td>
			<td class="greyborder-right">&nbsp;</td>
		</tr>
		<tr >
			<td id="blogcontent" colspan="2" class="greyborder-left greyborder-bottom">
				<?php
					//program notes are filled in after a speakers visit. If the speaker has not yet been to the club, we show the upcoming content
					$programNotes = trim( get_field( 'speaker_program_notes' ));
					if ( empty( $programNotes) ) the_field( 'speaker_program_content' );
						else echo $programNotes;
				?>
			</td>
			<td id="speakerinfo" rowspan="2" class="greyborder-right greyborder-bottom">
				<ul>	
					<li id="speaker-sidebar-thumbnail-container">
						<?php if ( has_post_thumbnail() ) {	the_post_thumbnail('medium');} ?>
					</li>
					<li id="speaker-side-container">
						<h3 class="speakerbio"><?php _e( 'About the Speaker', 'Rotary' ); ?></h3>
						<span id="speaker-bio">
							<?php echo the_field( 'speaker_bio' ); ?>
						</span>
					</li>
				</ul>
			</td>
		</tr>
	</table>


<?php endwhile; // end of the loop. 
	
	
				