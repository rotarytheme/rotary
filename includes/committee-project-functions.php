<?php



/**
 * CONSTANTS
 */

// ---------------- Project Types ---------------- //
define('MEETING', 0);
define('SOCIALEVENT', 1);
define('WORKPROJECT', 2);
define('GRANT', 3);
define('FUNDRAISER', 4);
define('CAMPAIGN', 5);

$ProjectType[MEETING] 		= __( 'Meeting' );
$ProjectType[SOCIALEVENT] 	= __( 'Social Event' );
$ProjectType[WORKPROJECT] 	= __( 'Community / Work Project' );
$ProjectType[GRANT]			= __( 'Grant / International Project' );
$ProjectType[FUNDRAISER] 	= __( 'Fundraiser Event' );
$ProjectType[CAMPAIGN] 		= __( 'Fundraiser Campaign' );

// ---------------- Registration Types ---------------- //
define('REGISTER', 0);
define('SIGNUP', 1);
define('VOLUNTEER', 2);
define('SUPPORT', 3);
define('ADVOCATE', 4);
define('PURCHASE', 5);
define('DONATE', 6);

$RegistrationVerb[REGISTER] = __( 'Register' );
$RegistrationVerb[SIGNUP] 	= __( 'Signup' );
$RegistrationVerb[VOLUNTEER] = __( 'Volunteer' );
$RegistrationVerb[SUPPORT]	= __( 'Support' );
$RegistrationVerb[ADVOCATE] = __( 'Advocate' );
$RegistrationVerb[PURCHASE] = __( 'Purchase' );
$RegistrationVerb[DONATE] 	= __( 'Donate' );

$RegistrationNoun[REGISTER] = __( 'Registrations' );
$RegistrationNoun[SIGNUP] 	= __( 'Signups' );
$RegistrationNoun[VOLUNTEER] = __( 'Volunteers' );
$RegistrationNoun[SUPPORT]	= __( 'Supporter' );
$RegistrationNoun[ADVOCATE] = __( 'Advocates' );
$RegistrationNoun[PURCHASE] = __( 'Buyers' );
$RegistrationNoun[DONATE] 	= __( 'Donors' );

$RegistrationCTA[REGISTER] = __( 'Register Now' );
$RegistrationCTA[SIGNUP] 	= __( 'Sign Up Now' );
$RegistrationCTA[VOLUNTEER] = __( 'Volunteer Now' );
$RegistrationCTA[SUPPORT]	= __( 'Become a Supporter' );
$RegistrationCTA[ADVOCATE] = __( 'Become an Advocate' );
$RegistrationCTA[PURCHASE] = __( 'Buy Now' );
$RegistrationCTA[DONATE] 	= __( 'Donate to this Cause' );



/**
 * LIST OF COMMENT PROPERTIES
comment_ID			(integer) The comment ID
comment_post_ID		(integer) The ID of the post/page that this comment responds to
comment_author		(string) The comment author's name
comment_author_email	(string) The comment author's email
comment_author_url	(string) The comment author's webpage
comment_author_IP	(string) The comment author's IP
comment_date		(string) The datetime of the comment (YYYY-MM-DD HH:MM:SS)
comment_date_gmt	(string) The GMT datetime of the comment (YYYY-MM-DD HH:MM:SS)
comment_content		(string) The comment's content
comment_karma		(integer) The comment's karma
comment_approved	(string) The comment approval level (0, 1 or "spam")
comment_agent		(string) The commenter's user agent (browser, operating system, etc.)
comment_type		(string) The comment's type if meaningfull (pingback|trackback), empty for normal comments
comment_parent		(string) The parent comment's ID for nested comments (0 for top level)
user_id				(integer) The comment author's ID if s/he is registered (0 otherwise)
*/


/**
 * rotary_project_and_committee_announcement_dropdown function.
 *
 * @access public
 * @return Select HTML
 */
function rotary_project_and_committee_announcement_dropdown() {
?>
	<select id="committeeselect" name="committeeselect">
		<option value=""><?php echo __( 'SELECT A PROJECT OR COMMITTEE TO ADD A NEW ANNOUNCEMENT', 'rotary' );?></option>
		<?php 

		/* PROJECTS */
		$args = array(
				'posts_per_page' => -1,
				'post_type' =>'rotary_projects',
				'orderby' => 'post_date',
				'status' => 'publish',
				'order' => 'DESC',
				'date_query' => array(
						array(
								'after' => '2015-06-01' //FIXME: put a proper cut-off date based on project end dates
						)
				)
		);
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) :
		?><option value="">~~~~~~~~~~ <?php _e( 'Projects' ,'rotary' );?> ~~~~~~~~~~</option><?php
		    while ( $query->have_posts() ) : $query->the_post();
				echo '<option value="' . get_the_id() . '">' . get_the_title() . '</option>';
			endwhile;
		endif;
		wp_reset_postdata();
		
		
		/* COMMITTEES */
	    $args = array(
	    		'posts_per_page' => -1,
	    		'post_type' => 'rotary-committees',
	    		'orderby' => 'title',
				'status' => 'publish',
	    		'order' => 'ASC'
	    );
	    $query = new WP_Query( $args );
		?><option value="">~~~~~~~~~~ <?php _e( 'Committees' ,'rotary' );?> ~~~~~~~~~~</option><?php 
	    if ( $query->have_posts() ) : 
		    while ( $query->have_posts() ) : $query->the_post();
			echo '<option value="' .  get_the_id() . '">' . get_the_title() . '</option>';
			endwhile;
		endif;
		wp_reset_postdata();

		?></select><?php
}


/***************************************************************
 * Set post2post connections when saving posts
 */
 
add_action( 'save_post', 'rotary_save_post_for_committee' );
add_action( 'save_post', 'rotary_save_post_for_project' );
add_action( 'save_post', 'rotary_save_committee_for_project' );


/**
 * rotary_save_post_for_committee function.
 *
 * @access public
 * @param mixed $post_id
 * @return void
 */
function rotary_save_post_for_committee( $post_id ) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if ( isset( $_REQUEST['committeeid'] ) ) {
			//post to post
		p2p_type( 'committees_to_posts' )->connect( $_REQUEST['committeeid'], $post_id, array('date' => current_time('mysql')
			) );
		
	}
}

/**
 * rotary_save_post_for_project function.
 *
 * @access public
 * @param mixed $post_id
 * @return void
 */
function rotary_save_post_for_project ( $post_id ) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if ( isset( $_REQUEST['projectid'] ) ) {
			//post to post
		p2p_type( 'projects_to_posts' )->connect( $_REQUEST['projectid'], $post_id, array('date' => current_time('mysql')
		) );		
	}
	
}

/**
 * rotary_save_post_for_project function.
 *
 * @access public
 * @param mixed $project_id
 * @return void
 */
function rotary_save_committee_for_project ( $project_id ) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if ( isset( $_REQUEST['committee'] ) && 'rotary_projects' ==  $_REQUEST['post_type'] ) {
			p2p_type( 'projects_to_committees' )->connect( $project_id, $_REQUEST['committee'], array('date' => current_time('mysql')
			) );
	}  
}

/*******************************************************************************
 * Single Post helper functions
 */

/**
 * rotary_show_committee_header_container function.
 * 
 * @access public
 * @param mixed $hascontent
 * @param mixed $title
 * @param mixed $link1
 * @param mixed $link2
 * @param mixed $project
 * @return void
 */
function rotary_show_committee_header_container($hascontent, $title, $link1, $link2, $projectclass = '') { ?>
	<div class="committeeheadercontainer<?php echo $hascontent . $projectclass; ?>">
		<h3 class="committeeheader"><?php echo $title . 's'; ?></h3>
			<?php if ( !$hascontent ) : ?>
			   <?php if ( is_user_logged_in()) : ?>
						<p class="addcontent">No <?php echo $title . 's'; ?> at the moment, add one!</p>
					<?php else : ?>
						<p class="addcontent">No <?php echo $title . 's'; ?> at the moment, <?php wp_loginout( $_SERVER['REQUEST_URI'], true ); ?>!</p>
					<?php endif; ?>
			<?php endif; ?>
			<?php if ( current_user_can( 'edit_page' ) ) : ?>   
				<a class="newpost rotarybutton-largewhite" href="<?php echo $link1; ?>" target="_blank">New <?php echo $title; ?></a>
			<?php endif; ?>	 
			<?php if ( $hascontent ) : ?>
				<a class="newpost rotarybutton-largewhite second" href="<?php echo $link2; ?>">More <?php echo $title . 's'; ?></a>	
			<?php endif; ?>							  		
	</div>
<?php }

/**
 * rotary_show_project_icons function.
 * 
 * @access public
 * @return void
 */
function rotary_show_project_icons() { 
	//get the users connected to the project ?>
	<?php $users = get_users( array(
		'connected_type' => 'projects_to_users',
		'connected_items' => get_the_id(),
		'connected_direction' => 'from',
	)); ?>
	<div class="projecticons">
		<?php $particpate = ''; ?>
		
		<?php if ( is_user_logged_in() ) : ?>
			<?php $userID = get_current_user_id(); ?>
			<?php $p2p_id = p2p_type( 'projects_to_users' )->get_p2p_id( get_the_id(), wp_get_current_user() ); ?>
			<?php if ( $p2p_id ) : ?>
		 		<?php $particpate = ' going';?>
		 			<div class="hide imgoingtext hovertext<?php echo $particpate; ?>"><?php  _e( 'I\'m going <br />Click to<br />change RSVP', 'Rotary'); ?></div>
		 		<?php else : ?>
		 			<?php $particpate = ' notgoing';?>
		 			<div class="hide imgoingtext hovertext<?php echo $particpate; ?>">><?php _e( 'I\'m not going<br />Click to<br />change RSVP', 'Rotary'); ?></div>
		 	<?php endif; ?>
		 <?php else: ?>
		 	<div class="hide imgoingtext hovertext">I haven't replied</div>
		 <?php endif; ?>
		<span class="imgoing icon<?php echo $particpate; ?>" data-postid="<?php the_ID(); ?>"><?php _e( 'I\'m going', 'Rotary'); ?></span>	
		
		<?php $location = get_field('rotary_project_location'); ?>
		<?php $googleLink = '#'; ?>
		<?php if ($location) : ?>
				<?php $googleLink = 'http://www.google.com/maps?daddr='.$location["address"]; ?>
		<?php endif; ?>
		<div class="hide locationtext hovertext">Map</div>
		<a class="location icon" href="<?php echo $googleLink; ?>" target="_blank">Location</a>
		
		 <div class="hide participanttext hovertext">Attendees</div>
		<span class="participants icon" >Participants</span>
		<span class="count"><?php echo count($users); ?></span>
	</div>
<?php }


/**
 * show_project_blogroll function.
 * 
 * @access public
 * @param mixed $query
 * @param mixed $showthumb
 * @param mixed $committeeTitle
 * @return void
 */
function show_project_blogroll ( $query, $showthumb = 'no', $committeeTitle = null ) {
	global $ProjectType;
	
	$hasCommitteeTitle = ( $committeeTitle ) ? true : false;
	
 	while ( $query->have_posts() ) : $query->the_post();	
 		if ( !$hasCommitteeTitle ) {
		  $committeeTitle = rotary_get_committee_title_from_project( get_the_id() );
 		}
		  $type = get_field( 'project_type' );
		?>
		<div class="connectedprojectscontainer clearfix">	
			<div class="projectheader">
			   <h3><?php echo  $ProjectType[$type]; ?>
			    	<br />
				    <span><?php echo $committeeTitle; ?></span>
			    </h3>
			</div>	
			<div class="projectcontent">			
				<h2 class="projecttitle"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php 
				if ( 1 == get_field( 'participants_table_flag' ) ) :
					echo rotary_show_project_icons(); 
				endif;
				?>
				<div class="rotary_project_date"> 
					<?php echo rotary_show_project_dates();?>
				</div>
			<?php 
				if ( 'yes' == $showthumb ) : 
					if ( has_post_thumbnail() ) : the_post_thumbnail('medium') ; 
					endif; 
				endif; 
				the_excerpt();
			?>
				<p><a href="<?php the_permalink(); ?>">><?php _e( 'Keep Reading', 'Rotary'); ?> </a></p>
				</div>		
		</div>
	<?php endwhile;
}

/**
 * rotary_show_project_dates function.
 *
 * @access public
 * @return HTML for displaying dates
 */
function  rotary_show_project_dates() {
	//get the project start and end dates 
	
//setlocale(LC_ALL, 'nl_NL');

$startDate 	= DateTime::createFromFormat('Ymd', get_field( 'rotary_project_date' ) );
$endDate 	= DateTime::createFromFormat('Ymd', get_field( 'rotary_project_end_date' ) );
	
	if ( get_field( 'long_term_project' ) ) : 
		$longTermClass = ' longterm'; 
			if( $startDate ) $startStamp = $startDate->getTimestamp();
			if( $endDate ) $endStamp = $endDate->getTimestamp();
			if ( $startDate && $endDate ) :
				$interval = $startDate->diff( $endDate, true);
				$show_day = ( 2 > $interval->format( '%m') );
			else:
				$show_day = false;
			endif;
			
			//$startDate->format('jS F Y')
			//strftime( '%e %B %Y', $startDate->getTimestamp() )
			// English: strftime( $startDate->format('jS F Y') : $startDate->format('F Y'));
	?>
			<span class="fulldate"><?php echo (( $show_day) ? strftime( '%e %B %Y', $startStamp ) : strftime( '%B %Y', $startStamp )); ?></span>
			<?php if ( '' != trim( get_field( 'rotary_project_end_date' ) ) ) : ?>
				<br /><span><?php _e( 'To', 'Rotary' ); ?></span><br />
				<span class="fulldate"><?php echo (( $show_day) ? strftime( '%e %B %Y', $endStamp ) : strftime( '%e %B %Y', $endStamp ) ); ?></span>
			<?php else : ?>
				<span><?php _e( '(ongoing)', 'Rotary'); ?></span>
			<?php endif; ?>
	<?php 
	else :
		$longTermClass = '';
		$startTime 	= new DateTime( $startDate->format( 'Y-m-d' ) . ' ' . get_field( 'rotary_project_start_time' ) );
		$endTime 	= new DateTime( $startDate->format( 'Y-m-d' ) . ' ' . get_field( 'rotary_project_end_time' ) );

		if ( $startTime && $endTime  ) :
			$startStamp = $startTime->getTimestamp();
			$endStamp = $endTime->getTimestamp();
			$interval = $startTime->diff($endTime, true);
			$show_time = ( 2 > $interval->format( '%d') );
		else:
			$show_time = false;
		endif;
	?>
			<span class="dayweek"><?php echo strftime( '%A', $startStamp ) ; //$startDate->format('l') ?></span><br>
			<span class="fulldate"><?php echo strftime( '%e %B %Y', $startStamp );// $startDate->format('jS F Y'); ?></span>
			<?php if( $show_time) { ?>
				<br />
				<span class="time"><?php echo sprintf( __( '%s To %s', 'Rotary' ), strftime( '%l:%M %p', $startStamp ) , strftime( '%l:%M %p', $endStamp )) ; //$startTime->format('g:i a'), $endTime->format('g:i a') );?></span>
			<?php }?> 
	<?php 
	endif;
}



/**
 * rotary_get_committee_title_from_project function.
 *
 * @access public
 * @param integer $projectID
 * @return array 
 */
function rotary_get_committee_title_from_project( $project_id, $extra_classes  =null ) {
	//get the committee 
	$committee_title = ''; 
	$projects = new WP_Query( array(
		'connected_type'  => 'projects_to_committees',
		'connected_items' => $project_id,
		'posts_per_page' => 1, 
		'nopaging'        => false,
	) ); 
	 if ( $projects->have_posts() ) :
		while ( $projects->have_posts() ) : $projects->the_post();
			$committee_title = '<a href="' . get_the_permalink() . '" class="organizing-committee-title' . $extra_classes . '">' . get_the_title() . '</a>';
		endwhile;
	endif;
	wp_reset_postdata();
	return apply_filters( 'rotary_get_committee_title_from_project', $committee_title,  $project_id, $extra_classes );
}


/**
 * rotary_order_projects function.
 *
 * @access public
 * @param mixed $query
 * @return HTML 
 */
function rotary_order_projects($query)  {
	 if ( ! is_admin() && $query->is_main_query() && 'rotary_projects' == $query->query_vars['post_type'] ) :
	 	 $query->set('meta_key', 'rotary_project_date');
	 	 $query->set('orderby', 'meta_value');
	 endif;
	
}
add_action('pre_get_posts', 'rotary_order_projects');


/**
 * rotary_loginout_selector function.
 *
 * @access public
 * @param integer $projectID
 * @return HTML for displaying dates
 */
//add class to login button for projects and committees
function rotary_loginout_selector( $login_text ) {
	$currentPostType = get_post_type();
	if ( 'rotary-committees' == $currentPostType || $currentPostType == 'rotary_projects' ) :
		$selector = 'class="newcomment"';
		$login_text = str_replace('<a ', '<a '.$selector, $login_text);
	endif;
	return $login_text;
}

//commented out as we are not currently using it
//add_filter('loginout', 'rotary_loginout_selector');



/**
 * function: rotary_list_of_committees_with_project_archives
 */
function rotary_list_of_committees_with_project_archives() {
	//get a list of all committees that have projects
	
	$args = array(
		'post_type'				=> 'rotary-committees',
		'posts_per_page' 		=> -1,
		'post_status' 			=> 'publish',
	);
	
	$committees = new WP_Query( $args );

	if ( $committees->have_posts() ) :
	?>
		<ul class="committees-with-projects-list">
		<?php 
		while ( $committees->have_posts() ) : $committees->the_post();
			$committeeid = get_the_ID();
			$text = get_the_title();
			$link = get_post_type_archive_link( 'rotary_projects' ).'?committeeid=' . $committeeid;
		//echo  get_the_ID();
			$args = array(
					'connected_type' 	=> 'projects_to_committees',
					'connected_items' 	=> $committeeid,
					'connected_direction' 	=> 'to',
					'post_type' 		=> 'rotary_projects',
					'posts_per_page' 		=> -1,
			);
			$projects = new WP_Query( $args );
			
			if ( $projects->have_posts() ) : $projects->the_post();	
			if ( get_the_ID() ) {	
				?>
					<li><a href="<?php echo $link;?>"><?php echo $text;?>&nbsp;(<?php echo $projects->found_posts?>)</a></li>
				<?php 
				}
			endif;
			wp_reset_postdata();
		endwhile;
			
		?>
		</ul>
		<?php 
	endif;
	wp_reset_postdata();
}


/**
 * function: rotary_list_of_project_types_with_project_archives
 */
function rotary_list_of_project_types_with_project_archives( $default=null) {
	global $ProjectType;
	/*
	$ProjectType[MEETING] 		= __( 'Meeting' );
	$ProjectType[SOCIALEVENT] 	= __( 'Social Event' );
	$ProjectType[WORKPROJECT] 	= __( 'Community / Work Project' );
	$ProjectType[GRANT]			= __( 'Grant / International Project' );
	$ProjectType[FUNDRAISER] 	= __( 'Fundraiser Event' );
	$ProjectType[CAMPAIGN] 		= __( 'Fundraiser Campaign' );
	 */

//var_dump($ProjectType);die;
?>
	
		<select class="project_types-with-projects-list hyperlink">
		<?php 
	
	foreach( $ProjectType as $key => $value ) :
		$committeeid = get_the_ID();
		$link = get_post_type_archive_link( 'rotary_projects'  ).'?projecttype=' . $key;
		$selected = ( $key == $default ) ? 'selected=selected' : '';
		$args = array(
				'post_type'			=> 'rotary_projects',
				'posts_per_page' 	=> -1,
				'post_status' 		=> 'publish',
				'meta_key' 			=> 'project_type',
				'meta_value' 		=> $key
		);
	
		$projects = new WP_Query( $args );
		if ( $projects->have_posts() ) : $projects->the_post();
		?>
				<option value="<?php echo $link;?>" <?php echo $selected;?>><?php echo $value;?>&nbsp;(<?php echo sprintf( __( '%s projects', 'Rotary'), $projects->found_posts ); ?>)</option>
			<?php 
    		endif;
		wp_reset_postdata();
		
	endforeach;
	?></select><?php 
}


/*
 * Adds committeeid to query vars so you can use get_query_vars() and not have to do a $_REQUEST
 */
function add_query_vars_filter( $vars ){
	$vars[] = "committeeid";
	$vars[] = "projecttype";
	return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );


