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

class RotaryAnnouncements {
	public static $announcement_ob 	= array();
	private $slideshow_ob 			= array();
	private $anniversary_ob 		= array();
	private $args 					= array();
	private $slidesDisplayed 		= 0 ;
	public static $announcementsDisplayed = 0;
	private $anniversariesDisplayed = 0;
	private $today;
	private $speakerdate;
	private $lookback;
	private $lookforward;
	private $context;
	private $ProjectType;
	private $allowedits;
	
	public $atts;
	public $shortcode_html;

	function __construct() {
		wp_enqueue_script( 'rotary' );
		$this->ProjectType = $ProjectType;
		$arguments = func_get_args();
		if(!empty($arguments)) {
            foreach($arguments[0] as $key => $property)
                if(property_exists($this, $key))
                    $this->{$key} = $property;
    	}
	    extract( shortcode_atts(
			    	array(
				    	'lookback' 		=> 5,
				    	'lookforward' 	=> 2, // Give two days for the scribes to act
				    	'speakerdate'	=> null,
				    	'context'		=> 'shortcode'
			    	), $this->atts, 'announcements' ));
	   
	   $this->lookback = $lookback;
	   $this->lookforward = $lookforward;
	   $this->speakerdate = $speakerdate;
	   $this->context = $context;

	   $this->program_date = rotary_next_program_date();
	   
		$this->get_shortcode_html();
	}

	function get_shortcode_html() {
	
		
		// Prepare the query arguments to fetch the appropriate comments depending where this is being called from	
		$this->args['order'] = 'DESC';
		$this->args['orderby'] = array( 'post_type', 'comment_date' );
		$this->args['post_type'] = array ('rotary-committees', 'rotary_projects');
		$this->args['status'] = 'approve';
		


		
		// if this is coming from a speaker program, then find the date range around the speaker date to retrieve the announcements
		//other while the dates will be relative to today
		if( $this->speakerdate ) :
			//if we've passed through a speaker date, then make the lookback relative to this date, and not today
			$this->lookbackdate = new DateTime( $this->speakerdate );
			$this->lookforwarddate = new DateTime( $this->speakerdate );
			$this->today = new DateTime( $this->speakerdate );
			if( $this->lookforward >= 0) :
				$this->lookforwarddate->add(new DateInterval( 'P' . $this->lookforward . 'D' ) ) ;
			else:
				$this->lookforwarddate->sub(new DateInterval( 'P' . abs( $this->lookforward ) . 'D' ) ) ;
			endif;
			$this->lookbackdate->sub(new DateInterval( 'P' . $this->lookback . 'D' ) ) ;
			
			$this->args['date_query'] = array( 
									array( 
										'column' => 'comment_date', 
										array( 
											//'after' => date_format( $lookbackdate, 'c' ),
											'before' => date_format( $this->lookforwarddate, 'c' )
										),
									'inclusive' => true	
									)
								);
		else :
			//else, make it relative to today.
			$this->today = new DateTime;
		endif; //  end $speakerdate
		
		

		
		// We can't introduce a second comments form a page where there is already another comments form, so don't allow edits on a committee or project page
		// where comments are open.  Also, don't allow edits on the carousel for simplicity
		$this->allowedits = ( (in_array( get_post_type(), array( 'rotary-committees', 'rotary_projects' )) && comments_open() ) 
						|| 'slideshow' == $this->context )
				? false : true;
		
		//set $announcement_ob - retrieve all of the announcements in an array of HTML (one for each announcment)
		$this->get_announcements();
		

			
		ob_start();
				?>
				<div class="<?php echo $this->context;?>-announcements">
				<?php if( $this->allowedits ) :?>
					<?php if ( !is_user_logged_in() ) : ?>
							<p><?php echo sprintf( __( 'Please %s to make an announcement' ), wp_loginout( site_url(), false ) ) ;?></p>
						<?php
						else : 
					
							/***************** START MAILCHIMP CAMPAIGN CUSTOMIZATION ****************/
							if( is_user_logged_in() && current_user_can( 'create_mailchimp_campaigns' ) ):

								$serialized = serialize( get_comments( $this->args ));
								$encoded = base64_encode( $serialized );
								
								$hash = md5( $encoded . 'SecretStringHere' );
								?>
									<div id="announcements-mailchimpcampaign">
										<a id="announcements-sendemailtest" class="rotarybutton-largewhite" href="javascript:void" ng-click="saveCampaign()" ><?php echo __( 'Send Test Email', 'Rotary'); ?></a>
										<a id="announcements-sendemailblast" class="rotarybutton-largeblue" href="javascript:void" ng-click="sendCampaign(1)" ><?php echo __( 'Send Email Blast', 'Rotary'); ?></a>
										<input type="hidden" id="announcements-array" value="<?php echo $encoded; ?>" />
										<input type="hidden" id="announcements-hash" value="<?php echo $hash ?>" />
									</div>
									
							<?php endif; // end curent_user_can
							
							/***************** END MAILCHIMP CAMPAIGN CUSTOMIZATION ****************/
							
							 rotary_project_and_committee_announcement_dropdown();
							 ?><div id="new_announcement_div"></div><?php 
					endif; // user logged in
				endif; //end $allowedits?>			
					 <div <?php echo (( 'carousel' == $this->context ||  'slideshow' == $this->context ) ? 'id="announcements-' . $this->context . '"' : ''  ); ?> class="announcements-container">
					<?php 
					 	
			$header = ob_get_clean();
			
						
			//set $slideshow_ob - retrieve all of the slides in an array of HTML (one for each slide)
			if( 'slideshow' == $this->context ) :
				$this->get_slides();
				$this->get_anniversaries();
			endif; // slideshow
			
			
			// output the elements in the order that we want to display them
			$slides_counter = $announcement_counter = $anniversary_counter = 0;
			for( $i = 0 ; $i < max( array( $this->announcementsDisplayed, $this->slidesDisplayed, $this->anniversariesDisplayed )); $i++ ) {

				$slides .= ( $i <= $this->anniversariesDisplayed && $this->anniversary_ob) ? $this->anniversary_ob[$i] : ''; // only display once per loop
	
				$slides .= $this->slideshow_ob ?  $this->slideshow_ob[$slides_counter] : '';
				$slides_counter = ( $i >= $this->slidesDisplayed ) ? 0 : $slides_counter + 1;
				
				$slides .= $this->announcement_ob ? $this->announcement_ob[$announcement_counter] : '';
				$announcement_counter = ( $i >= $this->announcementsDisplayed ) ? 0 : $announcement_counter + 1;
				
			}
			
			// get a footer to close the announcements and the announcement-container divs
			$footer = '</div></div>';
			
			$this->shortcode_html = $header . $slides . $footer;
	}
	
	/**
	 * rotary_get_announcements
	 * @return string
	 */
	function get_announcements() {
		// Exclude all announcements that have expired
		$this->args['meta_query'] = array(
				array(
						'key' => 'announcement_expiry_date',
						'value' => $this->today->format( 'Y-m-d'),
						'compare' => '>='
				)
		);
		
		$announcements = get_comments( $this->args );
		$context = $this->context;
	
		if ( is_array( $announcements )  ) :
				if( count( $announcements ) ) :
					foreach( $announcements as $announcement ) :
					if( $announcement ) :
						$extra_classes = '';
						$this->announcementsDisplayed++;
						ob_start();
							include ( get_template_directory() . '/loop-single-announcement.php');
						$this->announcement_ob[$this->announcementsDisplayed] = ob_get_clean();
					endif;
				endforeach; //end foreach announcement loop
				endif; //end count 
			endif; //end is_array check
				
			if ( 0 == $this->announcementsDisplayed && !$this->speakerdate ) :
				$this->announcement_ob[0] = '<p>' . __( 'There are no active announcements') . '</p>';
			endif;
	}
	
	/**
	 * rotary_get_slides
	 * @return string
	 */
	function get_slides() {
		//Prepare slideshow
		$args = array(
				'order' => 'ASC',
				'post_type' => 'rotary-slides',
		);
		$query = new WP_Query( $args );
		
		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) : $query->the_post();
				$this->slidesDisplayed++;
				ob_start();
					if ( has_post_thumbnail() ) :
						$img_data = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');
						$img_url =  $img_data[0];?>
						<div class="slideshow-announcement hide">
							<div class="slideimage" style="background: url( <?php echo $img_url;?>) 0 0 no-repeat;"></div>
							<div class="slideinfo"  >
								 <?php the_title( '<h1>','</h1>'); ?>
								 <p><?php echo get_the_excerpt(); ?></p>
							</div>					
						</div>
						<?php 
					else:?>
						<div class="slideshow-announcement">
							<div class="slidecontent" >
								<?php the_content(); ?>
							</div>					
						</div>
					<?php 
					endif;//has thumbnail
				$this->slideshow_ob[$this->slidesDisplayed] = ob_get_clean();
			endwhile; // the_post
		endif;// have posts
		wp_reset_postdata();
	}
	
	function get_anniversaries() {
		$birthdays = array();
		$anniversaries = array();
		$rotaryProfiles = new RotaryProfiles;
		$members = $rotaryProfiles->get_members();
		
		$names = array(); //an array of member's names, indexed by member id
		$birthdays = array(); // an array of days of the months
		
		$month = $this->today->format( 'F' );

		if( $members ) :
			foreach( $members as $member ) :
				$member_id = $member->ID;
				$profile = $rotaryProfiles->get_users_details_json( $member->ID );
				// load the birthdates into an associative array indexed by user ID
				if( date( 'F', $profile['birthday_datetime'] ) == $month ) :
					$day = date( 'j', $profile['birthday_datetime'] );
					$birthdays[ $member_id ] = $day;
					$names[ $member_id ] = $profile['memberName'];
				endif;
				// load the anniversaries into an associative array indexed by user ID
				if( $profile['anniversarydate_datetime'] && date( 'F', $profile['anniversarydate_datetime'] ) == $month  ) :
					$day = date( 'j', $profile['anniversarydate_datetime'] );
					$anniversaries[ $member_id ] = $day;
					$names[ $member_id ] = $profile['memberName'];
					$partners[ $member_id ] = $profile['partnername'];
				endif;
				
			//	$membership[ $member_id ] = $day;
			//	$names[ $member_id ] = $profile['memberName'];
			//	$years[ $member_id ] = 5;
				
			//	var_dump($profile['membersince_datetime']  );
				// load the member_since into an associative array indexed by user ID
				if( $profile['membersince_datetime'] && date( 'F', $profile['membersince_datetime'] ) == $month  ) :
					$names[ $member_id ] = $profile['memberName'];
					$now = new DateTime();
					$now_year = $now->format( 'Y' );
					$join_year = date( 'Y', $profile['membersince_datetime'] );
					$years[ $member_id ] = intval($now_year) - intval($join_year);
				endif;
				
			endforeach;
				
			asort( $birthdays );
			asort( $anniversaries );
			asort( $years );
			$years = array_reverse( $years , true);
			
			//output the birthdays
			if( $birthdays )
				$this->get_anniversary_html( $birthdays, 'Birthdays', $month, $names, null );
			//output the wedding anniveraries
			if( $anniversaries )
				$this->get_anniversary_html( $anniversaries, 'Anniversaries', $month, $names, $partners );
			//output the club anniversaries
			if( $years )
				$this->get_anniversary_html( $years, 'Membership', $month, $names, null );

		endif; // members
	}
	
	function get_anniversary_html( $anniversaries, $type, $month, $names, $additional_info )  {

		switch( $type ) {
			case 'Anniversaries' :
				$title = __('Wedding Anniversaries');
				break;
			case 'Membership':
				$title = __('Club Anniversaries');
				break;
			default:
				$title = __('Birthdays');
		}
		$count = 0;
		$col = 1;
		ob_start();
		?>
	 	<div class="slideshow-announcement slidemargins hide">
	 		<div class="anniversarycontent anniversary-<?php echo $type;?>" >
	 			<h1><? echo sprintf( __( '%s for %s' ), $title, $month) ; ?></h1>
	 			<div class="anniversary_inner_container">
	 			<?php 
				foreach( $anniversaries as $user_id => $day ) :
	 				$count++;
					$name = $names[ $user_id ];
					switch( $type ) {
						case 'Anniversaries' :
							$partner = $additional_info[ $user_id ];
							$membername = ( $partner ) ? sprintf( __( '%s and %s'), $partner, $name) : $name;
							break;
						default:
 							$membername = $name;
 					}
					
					if( $previous_day != $day ) :
						if( $previous_day ) {?> </div></div> <?php ; // close previous membername div and the day container, otherwise this is the first time through, so nothing to close?>
						<?php 
							//make a new anniversary_inner_container every 9 records
							if ($count >= 8 * $col  ) {
								$col++;
								?></div><div class="anniversary_inner_container"><?php }
						} ?>
						<div class="anniversary_day-container">
						<?php if( 'Membership' == $type ) {?>
							<div class="anniversary_years anniversary_day"><?php echo sprintf( __('%s Years'), $day ) ; ?></div>
						<?php } else {?>
							<div class="anniversary_day"><?php echo $day ; ?></div>
						<?php }?>
						<div class="membername">
			<?php endif; //if this day is the same as previous day, then all we do is print out the membername?>
					<p><?php echo $membername; ?></p>
			<?php 
			$previous_day = $day;
			
			endforeach;?>
						</div><!-- membername -->
					</div><!-- anniversary_day-container -->
				</div><!-- anniversary_inner_container -->
			</div><!-- anniversarycontent -->
		</div><!-- slideshow-announcement -->
		<?php 
		$this->anniversary_ob[$this->anniversariesDisplayed] = ob_get_clean();
		$this->anniversariesDisplayed++;
	}
}
