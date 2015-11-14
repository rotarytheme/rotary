<?php

/** 
 * rotary_get_announcements_html
 * replaces rotary_get_committee_announcements function.
 *
 * Paul Osborn created to separate file for each shortcode
 * All the classes have been renamed from comment / committee, to announcements, and reference to home removed
 * to enable the shortcode to live on any page
 * 
*/

/**
 * rotary_get_announcements_shortcode_html function
 * 
 * @access public
 * @param mixed $atts
 * @return void
 */ 

function rotary_get_announcements_shortcode_html( $atts ){
	global $ProjectType;
	
	extract( shortcode_atts(
			array(
					'lookback' 		=> 5,
					'lookforward' 	=> 2, // Give two days for the scribes to act
					'speakerdate'	=> null,
					'context'		=> 'shortcode'
			), $atts, 'announcements' ));
	
	
	// Prepare the query arguments to fetch the appropriate comments depending where this is being called from	
	$args = array(
				'order' => 'DESC',
				'orderby' => array( 'post_type', 'comment_date' ),
				'post_type' => array ('rotary-committees', 'rotary_projects'),
				'status' => 'approve'
			);
	
	if( $speakerdate ) :
		//if we've passed through a speaker date, then make the lookback relative to this date, and not today
		$lookbackdate = new DateTime( $speakerdate );
		$lookforwarddate = new DateTime( $speakerdate );
		$today = new DateTime( $speakerdate );
		if( $lookforward >= 0) :
			$lookforwarddate->add(new DateInterval( 'P' . $lookforward . 'D' ) ) ;
		else:
			$lookforwarddate->sub(new DateInterval( 'P' . abs( $lookforward ) . 'D' ) ) ;
		endif;
		$lookbackdate->sub(new DateInterval( 'P' . $lookback . 'D' ) ) ;
		
		$args['date_query'] = array( 
								array( 
									'column' => 'comment_date', 
									array( 
										//'after' => date_format( $lookbackdate, 'c' ),
										'before' => date_format( $lookforwarddate, 'c' )
									),
								'inclusive' => true	
								)
							);
	else :
		//else, make it relative to today.
		$today = new DateTime;
	endif; 

	// Exclude all announcements that have expired 
	$args['meta_query'] = array(
			array(
					'key' => 'announcement_expiry_date',
					'value' => $today->format( 'Y-m-d'),
					'compare' => '>='
			)
	);
	
	$announcements = get_comments( $args );
	$announcementsDisplayed = 0;
	
	
	// We can't introduce a second comments form a page where there is already another comments form, so don't allow edits on a committee or project page
	// where comments are open.  Also, don't allow edits on the carousel for simplicity
	$allowedits = ( (in_array( get_post_type(), array( 'rotary-committees', 'rotary_projects' )) && comments_open() ) 
					|| 'carousel' == $context )
			? false : true;
		
	ob_start();
			?>
			<div class="<?php echo $context;?>-announcements">
			<?php if( $allowedits ) :?>
				<?php if ( !is_user_logged_in() ) : ?>
						<p><?php echo sprintf( __( 'Please %s to make an announcement' ), wp_loginout( site_url(), false ) ) ;?></p>
					<?php
					else : 
				
						/***************** START MAILCHIMP CAMPAIGN CUSTOMIZATION ****************/
						if( is_user_logged_in() && current_user_can( 'create_mailchimp_campaigns' ) ):
						$serialized = serialize( $announcements );
						$encoded = base64_encode( $serialized );
						
						$hash = md5( $encoded . 'SecretStringHere' );
						?>
							<div id="announcements-mailchimpcampaign">
								<a id="announcements-sendemailtest" class="rotarybutton-largewhite" href="javascript:void" ng-click="saveCampaign()" ><?php echo __( 'Send Test Email', 'Rotary'); ?></a>
								<a id="announcements-sendemailblast" class="rotarybutton-largeblue" href="javascript:void" ng-click="sendCampaign(1)" ><?php echo __( 'Send Email Blast', 'Rotary'); ?></a>
								<input type="hidden" id="announcements-array" value="<?php echo $encoded; ?>" />
								<input type="hidden" id="announcements-hash" value="<?php echo $hash ?>" />
								</div>
								
						<?php endif;
						
						/***************** END MAILCHIMP CAMPAIGN CUSTOMIZATION ****************/
						
						 rotary_project_and_committee_announcement_dropdown();
						 ?><div id="new_announcement_div"></div><?php 
				endif;
			endif; ?>
									
									
				 <div <?php echo (( 'carousel' == $context ) ? 'id="announcements-carousel"' : ''  ); ?> class="announcements-container">
				
				 	<?php 
				 	
				 	rotary_next_program_date();
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
				</div>
			</div>

<?php return ob_get_clean();
}



