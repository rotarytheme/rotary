<?php
/**
 * The loop that displays a single post.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
	$args = array(
		'p'         => $_REQUEST['postid'],
		'post_type' => array('rotary_speakers'),
	);
	$query = new WP_Query( $args );

?>

<!--  As per http://blog.mailchimp.com/turn-any-web-page-into-html-email-part-2/ ? -->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/nm-mailchimp/email.css" />
<?php include( 'dynamic_styles.php' );?>

<table id="container-table">
<tr>
<td>

<?php if ( $query->have_posts() ) while ( $query->have_posts() ) : $query->the_post();
	// setup_postdata($query->the_post());
	//$speaker is defined in nm_front_camp();
 	$speaker_title = trim( get_field( 'speaker_title' ));
 	$speaker_company = trim( get_field( 'speaker_company' ));
 	
 	//efined in the calling function nm_front_camp();
 	//$date = DateTime::createFromFormat('Ymd', get_field( 'speaker_date' )); 

	 if ( get_field('speaker_program_notes' )) :
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_scribe' );
	 	$scribe = $terms[0]->name;
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_editor' );
	 	$editor = $terms[0]->name;
	 endif;
	 	$terms = wp_get_post_terms( get_the_id(), 'rotary_program_introducer_cat' );
	 	$introducer = $terms[0]->name;
 
	 	
	 $has_speaker_thumbnail = has_post_thumbnail() ;
	 $speaker_thumbnail = get_the_post_thumbnail(null, 'medium');
	 $speaker_bio = get_field( 'speaker_bio' );
 	
?>

    <table id="branding">
    	<tr>
    		<td>
		      <?php  $clubname = get_theme_mod( 'rotary_club_name', '' );  ?>
		      <?php  $rotaryClubBefore = get_theme_mod( 'rotary_club_first', false); ?>
	            <h1>
	            	<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
	            <?php rotary_club_header($clubname, $rotaryClubBefore);?>
					</a>
	            </h1>
    		</td>
    	</tr>
    </table>

	<table id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<tr id="speakerheader">
			<td id="speakertitle" class="goldborder-bottom firstcol">
				<h2> <?php echo $speaker; ?> </h2>
				<p id="speaker-info-container">
					<?php if( count( $speaker_title) ) ?> <span id="speaker-title" class="speaker-info"><?php echo( $speaker_title );?></span>
 					<?php if( count( $speaker_company) ) ?> <span id="speaker-company" class="speaker-info"><?php echo( $speaker_company );?></span>
				</p>
			</td>
			<td class="cutaway_image secondcol">&nbsp;</td>
			<td id="speakerdate" class="greyborder-right greyborder-top thirdcol"><span id="weekday"><?php echo $date->format('l');?></span> <?php echo $date->format('M dS, Y');?></span></td>
		</tr>
		<tr >
			<td id="blogheader" colspan="3" class="greyborder-left greyborder-right">
				<?php the_title('<h1>', '</h1>'); ?>
			</td>
		</tr>
		<tr >
			<td  colspan="2" class="greyborder-left greyborder-bottom">
				<table>
					<tr>
						<td id="blogcontent" >
							<hr>
							<p id="program-roles">
							<?php if( !empty( $scribe )) {?><span id="scribe"><span class="speaker-term-label"><?php echo _e( 'Scribe', 'Rotary' ); ?>:</span> <?php echo $scribe; ?></span><?php }?>
							<?php if( !empty( $editor )) {?><span id="editor"><span class="speaker-term-label"><?php echo _e( 'Editor', 'Rotary' ); ?>:</span> <?php echo $editor; ?></span><?php }?>
							<?php if( !empty( $introducer )) {?><span id="introducer"><span class="speaker-term-label"><?php echo _e( 'Introduced by', 'Rotary' ); ?>:</span> <?php echo $introducer; ?></span><?php }?>
							</p>
							<?php
								//program notes are filled in after a speakers visit. If the speaker has not yet been to the club, we show the upcoming content
								$programNotes = trim( get_field( 'speaker_program_notes' ));
								if ( empty( $programNotes) ) the_field( 'speaker_program_content' );
									else echo $programNotes;
							?>
						</td>
					</tr>
					<?php if( $date < $today || 1 ==1 ) :?>
					<tr>
						<td>
							<h2 class="blogcontent"><?php echo _e( 'Club Announcements', 'rotary' );?></h2>
							<table>
								<tr>
									<td class="speaker-announcements-container">
									 	<?php 
									 	$context = 'email';
										if ( is_array( $announcements )  ) : 
											$count = count( $announcements );
											if($count > 0 ) :
												foreach( $announcements as $announcement ) : 
													$extra_classes = ''; 
													$announcementsDisplayed++;
													if( $announcement ) :
														include ( get_template_directory() . '/loop-single-announcement.php');
													endif;
												 endforeach; //end comment loop 
											 endif;
										 endif; //end is_array check
										 
										if ( 0 == $announcementsDisplayed && !$speakerdate ) :
											?>	<p><?php echo __( 'There are no active announcements'); ?></p>
										<?php  endif; ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<?php endif;?>
				</table>
			</td>
			<td id="speakerinfo" rowspan="2" class="greyborder-right greyborder-bottom">
				<ul>	
					<li id="speaker-sidebar-thumbnail-container">
						<?php if ( $has_speaker_thumbnail ) {	echo $speaker_thumbnail; } ?>
					</li>
					<li id="speaker-side-container">
						<h3 class="speakerbio"><?php _e( 'About the Speaker', 'Rotary' ); ?></h3>
						<span id="speaker-bio">
							<?php echo $speaker_bio; ?>
						</span>
					</li>
				</ul>
			</td>
		</tr>
	</table>


<?php endwhile; // end of the loop.?>
</td>
</tr>
</table>
	
	
				