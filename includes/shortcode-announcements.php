<?php

/** 
 * rotary_get_announcements_html
 * replaces rotary_get_committee_announcements function.
 *
 * Paul Osborn created to separate file for each shortcode
 * All the classes have been renamed from comment / committee, to announcements, and reference to home removed
 * to enable the shortcode to live on any page
 * 
 * Redundant Styles:
 *
			.home #content #committeeselect 
			.home #content .comment 
			.home #content .comment div 
			.home #content .committeecomment 
			.home #content .committeecommentdetail 
			.home #content .committee-comment-date 
			.home #content .commentcommittetext 
			.home #content .commentcommittetext h4 
			.home #content .commentcommittetext hr 
			.home #content .commentcommittetext p 

 *
 * NEW Styles:  Here are some starter styles
 * 
 * 
			.shortcode-announcements {
			    width: 50%;
			    margin: 0 auto;
			}
			.announcement {
			    margin: 0;
			    padding: 0;
			    width: 100%;
			}
			.announcement-header {}
			#content h4.announcement-title {
			    clear: left;
			    display: inline;
			    margin: 0;
			    font-size: 22px;
			    padding-bottom: 8px;
			    float: left;
			    text-transform: none;
			    color: #58585A;
			    font-family: "Open Sans Condensed", Arial, Helvetica, sans-serif;
			    font-weight: 700;
			    padding: 0;
			}
			.announcement-date {
			    display: inline;
			    padding-bottom: 8px;
			    font-family: "Open Sans Condensed", Arial, Helvetica, sans-serif;
			    font-size: 19px;
			    color: #58585A;
			    white-space: nowrap;
			    margin: 0px 0 8px;
			    float: right;
			}
			span.day {
			}
			span.month {
			}
			span.year {
			}
			.announcement-body {
			    clear: both;
				padding-bottom: 5px;
			    border-top: 1px solid #B8A3A0;
			    padding-top: 8px;
			}
			.announcement-call-to-action {}
			.announcement-hr {
			    height: 0;
			}

 *
 * @access public
 * @param mixed $atts
 * @return void
 */


function rotary_get_announcements_html( $atts ){
	extract( shortcode_atts(
			array(
					'lookback' 		=> 10,
					'lookforward' 	=> 3,
					'speakerdate'	=> null,
					'context'		=> 'shortcode'
			), $atts, 'announcements' ));
	
	$today = ( $speakerdate ) ? new DateTime( $speakerdate ) : new DateTime();  //if we've passed through a speaker date, then make the lookback relative to this date, and not today
	$lookforwarddate = ( $speakerdate ) ? new DateTime( $speakerdate ) : new DateTime();  //if we've passed through a speaker date, then make the lookback relative to this date, and not today
	$lookbackdate = ( $speakerdate ) ? new DateTime( $speakerdate ) : new DateTime();  //if we've passed through a speaker date, then make the lookback relative to this date, and not today

	if( $lookforward >= 0) :
		$lookforwarddate->add(new DateInterval( 'P' . $lookforward . 'D' ) ) ;
	else:
		$lookforwarddate->sub(new DateInterval( 'P' . abs( $lookforward ) . 'D' ) ) ;
	endif;
	
	$lookbackdate->sub(new DateInterval( 'P' . $lookback . 'D' ) ) ;

		
	$args = array(
			'order' => 'DESC',
			'orderby' => array( 'comment_date', 'post_type',  'title'),
			'post_type' => array ('rotary-committees', 'rotary_projects'),
			'status' => 'approve',
			'date_query' => array( 
					array( 
						'column' => 'comment_date', 
						array( 
							'after' => date_format( $lookbackdate, 'c' ),
							'before' => date_format( $lookforwarddate, 'c' )
						),
					'inclusive' => true	
					)
				)
			);
	$comments = get_comments( $args );
	
	$commentDisplayed = 0;
	
	ob_start();
	?>
	<div class="<?php echo $context;?>-announcements">
		<?php if ( !is_user_logged_in() ) { ?>
				<p><?php echo sprintf( 'Please %s to make an announcement', wp_loginout( site_url(), false ) ) ;?></p>
			<?php }
		else { 
				 rotary_project_and_committee_announcement_dropdown();
		} ?>
		 <div class="announcements-container">
		 	<?php 
			if ( is_array( $comments )  ) : 
			$count = count( $comments );
			if($count > 0 ) :
				foreach( $comments as $comment ) : 
						$extra_classes = ''; 
						$commentDisplayed++; 
						rotary_get_announcement_html( $context, $comment, $extra_classes ); 
				 endforeach; //end comment loop 
			
				 endif;
			 endif; //end is_array check
			 
			if ( 0 == $commentDisplayed ) :
				?>	<p><?php echo printf( 'No new announcements have been made in the last %s days', $lookback );?></p>
			<?php  endif; ?>
		</div>
	</div>

<?php return ob_get_clean();
}