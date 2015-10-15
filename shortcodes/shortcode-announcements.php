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
	
	// Prepare the query arguments to fetch the appropriate comments depedning where this is being called from	
	$args = array(
				'order' => 'DESC',
				'orderby' => array( 'post_type', 'comment_date' ),
				'post_type' => array ('rotary-committees', 'rotary_projects'),
				'status' => 'approve'
			);
	
	if( $speakerdate ) :
		//if we've passed through a speaker date, then make the lookback relative to this date, and not today
		$lookbackdate = $lookforwarddate = $today = new DateTime( $speakerdate );
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
										'after' => date_format( $lookbackdate, 'c' ),
										'before' => date_format( $lookforwarddate, 'c' )
									),
								'inclusive' => true	
								)
							);
	else :
		// find announcements that have not expired
		$today = new DateTime;
		$args['meta_query'] = array(
								array(
									'key' => 'announcement_expiry_date',
									'value' => $today->format( 'Y-m-d'),
									'compare' => '>='
								)
							);
	endif; 
	
	$announcements = get_comments( $args );
	$announcementsDisplayed = 0;
	
	ob_start();
	?>
	<div class="<?php echo $context;?>-announcements">
		<?php if ( !is_user_logged_in() ) { ?>
				<p><?php echo sprintf( __( 'Please %s to make an announcement' ), wp_loginout( site_url(), false ) ) ;?></p>
			<?php }
		else { 
				 rotary_project_and_committee_announcement_dropdown();
				 ?><div id="new_announcement_div"></div><?php 
		} ?>
							
							
		 <div class="announcements-container">
		 	<?php 
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