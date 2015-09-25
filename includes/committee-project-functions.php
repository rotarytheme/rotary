<?php

/**
 * rotary_get_single_post_announcements_html
 * renamed from
 * rotary_committee_comment function.
 * 
 * @access public
 * @param string $postType (default: 'rotary-committees')
 * @return void
 */
/*
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
function rotary_get_announcement_html( $context, $announcement, $extra_classes ) {
	
	$id = $announcement->comment_ID;
	$posted_in = get_the_title( $announcement->comment_post_ID );
	$posted_in_permalink = get_the_permalink( $announcement->comment_post_ID );
	$title = get_comment_meta( $announcement, 'title' ); //TODO: add title metadata field
	$call_to_action = get_comment_meta( $announcement, 'call_to_action' ); //TODO: add call to action metadata field
	$announcement_text = apply_filters ("the_content", $announcement->comment_content);
	$announced_by = '<a href="' . get_author_posts_url( $anouncement->user_id ) . '">' . $announcement->comment_author . '</a>';
	$post_type = get_post_type( $announcement->comment_post_ID );
	$date = new DateTime( $announcement->comment_date );
	
	if ( $context ) $extra_classes[] =  $context . '-announcement';
	//$extra_classes[] = 'shortcode-announcement';
	
	// where did this announcement come from - a project, or a committee??
	switch ( $post_type ) {
		case 'rotary_projects':
			$connected = new WP_Query( array(
					'connected_type'  => 'projects_to_committees',
					'connected_items' => get_the_id(),
					'posts_per_page'  => 1,
					'nopaging'        => false,
				) ); 
				if ( $connected->have_posts() ) : 
					while ( $connected->have_posts() ) : $connected->the_post();
						$posted_in_committee_permalink = get_the_permalink();
						$posted_in_committee = get_the_title();
					endwhile;
				endif;
			wp_reset_postdata();
			break;
		case 'rotary_committee':
			break;
	}
	?>
		<article id="announcement-<?php echo $id; ?>" <?php comment_class( $extra_classes ); ?>>
			
			<?php switch ( $context ) { 
			 case 'shortcode':
			 case 'speaker':  ?>
				<div class="announcement-header">
					<?php if( $title ) :?>
						<h3><?php $title; ?></h3>
						<h4><a href="<?php echo $posted_in_permalink;?>"><?php echo $posted_in; ?></a></h4>
					<?php else:?>
						<h3><a href="<?php echo $posted_in_permalink;?>"><?php echo $posted_in; ?></a></h3>
					<?php endif;?>
					<?php if ( $posted_in_committee) :?>
						<h5 class="organizing-committee"><?php echo _e( 'Project organized by', 'rotary' );?> <a href="<?php echo $posted_in_committee_permalink; ?>"><?php echo $posted_in_committee; ?></a></h5>
					<?php endif;?>
				</div>				
				<p class="announced-by"><?php echo sprintf( 'by %s', $announced_by ); ?></p>
				<?php if( 'shortcode' == $context ) : ?>
				<div class="announcement-date">
					<span class="day"><?php echo $date->format( 'd') ; ?></span>
					<span class="month"><?php  echo $date->format( 'M' ); ?></span>
					<span class="year"><?php echo $date->format( 'Y' ); ?></span>
				</div>
				<?php endif;?>					
				<div class="announcement-body">
					<?php echo $announcement_text; ?>			
				</div>
			<?php 
				break; 
			case 'project': 
			case 'committee':
			default: ?>
				<div class="announcement-date">
					<span class="day"><?php echo $date->format( 'd') ; ?></span>
					<span class="month"><?php  echo $date->format( 'M' ); ?></span>
					<span class="year"><?php echo $date->format( 'Y' ); ?></span>
				</div>
				<div class="announcement-content">
					<div class="announcement-header">
						<h3><?php echo ( $title ) ? $title : _e( 'New Announcement!', 'Rotary' ); ?></h3>
						<p class="announced-by">by <?php echo $announced_by ?></p>
					</div>							
					<div class="announcement-body">
						<?php echo $announcement_text; ?>			
					</div>
				</div>		
			<?php }?>
			
			<div class="announcement-call-to-action"><?php $call_to_action; ?></div>
			<!-- <hr class="announcement-hr" /> -->
		</article>
	<?php 
}


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
				echo '<option value="' . get_permalink() . '?open=open' . '">' . get_the_title() . '</option>';
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
			echo '<option value="' . get_permalink() . '?open=open' . '">' . get_the_title() . '</option>';
			endwhile;
		endif;
		wp_reset_postdata();

		?></select><?php
}



function rotary_get_single_post_announcements_html( $postType =  'rotary-committees', $stub = 'committee' ) {
	$args = array(
		'order' => 'DESC',
		'post_type' =>  $postType,
		'status' => 'approve',
		'type' => 'comment',
		'post_id' => get_the_id(),
		'number' => 10
	); 
	$comments = get_comments( $args );
	if (is_array( $comments )) : 
		foreach( $comments as $comment ) : 
			$firstComment = ( $comment === reset( $comments )) ? true : false;  
	  		$extra_classes = array( 'clearleft', (( !$firstComment ) ? 'hide' : '' )); 
			$count++;
			rotary_get_announcement_html( $stub, $comment, $extra_classes );
			if ( $firstComment && get_comments_number() > 1 ) : ?>
				<p class="morecommentcontainer"><a href="#" class="morecomments" id="morecomments"><?php echo  _e( 'Show More', 'Rotary') . '&nbsp;[+' . intval(intval(get_comments_number()) - 1.0) . ']'; ?></a></p>	
			<?php  
			endif; 
			if ( $comment === end( $comments ) && !$firstComment ) : ?>
				<p class="morecommentcontainer"><a href="#" class="lesscomments hide" id="lesscomments"><?php echo _e( 'Show Less', 'Rotary'); ?></a></p>
			 <?php endif;
		endforeach;
	endif;
 }

 function rotary_save_announcement_title( $comment_id ) {
 	add_comment_meta( $comment_id, 'my_custom_comment_field', $_POST['my_custom_comment_field'] );
 }
 add_action( 'comment_post', 'rotary_save_announcement_title' );


function rotary_save_post_for_committee( $post_id ) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if ( isset( $_REQUEST['committeeid'] ) ) {
			//post to post
		p2p_type( 'committees_to_posts' )->connect( $_REQUEST['committeeid'], $post_id, array('date' => current_time('mysql')
			) );
		
	}
}
function rotary_save_post_for_project ( $post_id ) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if ( isset( $_REQUEST['projectid'] ) ) {
			//post to post
		p2p_type( 'projects_to_posts' )->connect( $_REQUEST['projectid'], $post_id, array('date' => current_time('mysql')
		) );		
	}
	
}
function rotary_save_committee_for_project ( $project_id ) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if ( isset( $_REQUEST['committee'] ) && 'rotary_projects' ==  $_REQUEST['post_type'] ) {
			p2p_type( 'projects_to_committees' )->connect( $project_id, $_REQUEST['committee'], array('date' => current_time('mysql')
			) );
	}  
}


add_action( 'save_post', 'rotary_save_post_for_committee' );
add_action( 'save_post', 'rotary_save_post_for_project' );
add_action( 'save_post', 'rotary_save_committee_for_project' );


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
		 			<div class="hide imgoingtext hovertext<?php echo $particpate; ?>">I'm going<br />Click to<br />change RSVP</div>
		 		<?php else : ?>
		 			<?php $particpate = ' notgoing';?>
		 			<div class="hide imgoingtext hovertext<?php echo $particpate; ?>">I'm not going<br />Click to<br />change RSVP</div>
		 	<?php endif; ?>
		 <?php else: ?>
		 	<div class="hide imgoingtext hovertext">I haven't replied</div>
		 <?php endif; ?>
		<span class="imgoing icon<?php echo $particpate; ?>" data-postid="<?php the_ID(); ?>">Im going</span>	
		
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
function show_project_blogroll($query, $showthumb = 'no', $committeeTitle = '') {
	$hasCommitteeTitle = ( '' == trim( $committeeTitle) ? false : true);
 	while ( $query->have_posts() ) : $query->the_post();?>		
		  <?php if (! $hasCommitteeTitle) : ?>
		  	<?php $committeeTitle = rotary_get_committee_title_from_project( get_the_id() ); ?>
		  <?php endif; ?>
		<div class="connectedprojectscontainer clearfix">	
			<div class="projectheader">
				   <h3>Project Organized by:
				    	<br />
					    <span><?php echo $committeeTitle; ?></span>
				    </h3>
					    
			</div>	
			<div class="projectcontent">			
			<h2 class="projecttitle"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				
			<?php rotary_show_project_icons(); ?>
			<?php $startDate = DateTime::createFromFormat('Ymd', get_field( 'rotary_project_date' ) ); ?>
			<?php $endDate = DateTime::createFromFormat('Ymd', get_field( 'rotary_project_end_date' ) ); ?>
			<?php if (get_field( 'long_term_project' ) ) : ?>
				<?php if ( isset( $startDate )  && isset( $endDate )  ) : ?>
						 <?php if ( $startDate  !=  $endDate  ) : ?>
							 <div class="rotary_project_date">
								 <span class="fulldate"><?php  echo $startDate->format( 'jS F Y' ); ?></span>
								 <?php if ( '' != trim( get_field( 'rotary_project_end_date' ) ) ) : ?>
										<br /><span>To</span><br />
										<span class="fulldate"><?php  echo $endDate->format( 'jS F Y' ); ?></span>	
								  <?php else: ?>
								  		<span> (ongoing)</span>
								  <?php endif; ?>
							 </div>
						<?php endif; ?>
				<?php endif; ?>
			<?php else : ?>
				<div class="rotary_project_date">
					<span class="day"><?php echo $startDate->format( 'l') ; ?></span><br />
					<span class="fulldate"><?php  echo $startDate->format( 'jS F Y' ); ?></span>
				</div>
			<?php endif; ?>	
			<?php if ( 'yes' == $showthumb ) : ?>
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail('medium') ; ?>
				<?php endif; ?>
			<?php endif; ?>
			<?php the_excerpt(); ?>
			<p><a href="<?php the_permalink(); ?>">> Keep Reading</a></p>
			</div>		
						
		</div>
					
	<?php endwhile;
	
}
function rotary_get_committee_title_from_project( $projectID ) {
	//get the committee 
	$committeeTitle = 'Club Committee'; 
	$connected = new WP_Query( array(
		'connected_type'  => 'projects_to_committees',
		'connected_items' => $projectID,
		'posts_per_page' => 1, 
		'nopaging'        => false,
	) ); 
	 if ( $connected->have_posts() ) :
		while ( $connected->have_posts() ) : $connected->the_post();
			$committeeTitle = get_the_title();
		endwhile;
	endif;
	wp_reset_postdata();
	return $committeeTitle;
}
function rotary_order_projects($query)  {
	 if ( ! is_admin() && $query->is_main_query() && 'rotary_projects' == $query->query_vars['post_type'] ) :
	 	 $query->set('meta_key', 'rotary_project_date');
	 	 $query->set('orderby', 'meta_value');
	 endif;
	
}
add_action('pre_get_posts', 'rotary_order_projects');

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

//toggle whether or not a member is participating. Notice that there is no "no priv" ajax as the member
//must be logged in to say that he/she is participating.
function rotary_toggleparticipants() {
	// By default, let's start with an error message
	$response = array(
		'status' => 'error',
		'message' => 'Invalid nonce',
	);
	$current_user = wp_get_current_user();
	$going = 'no';
    // Next, check to see if the nonce is valid
    if( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'rotary-participant-nonce' ) ) :
        // Update our message / status since our request was successfully processed
        $response['status'] = 'success';
        //toggle value
        if ('' == $_GET['participate']) :
        	$going = 'yes';
        	p2p_type( 'projects_to_users' )->connect( $_GET['postid'], $current_user->ID, array('date' => current_time('mysql')));
        else : 
        	p2p_type( 'projects_to_users' )->disconnect( $_GET['postid'], $current_user->ID, array('date' => current_time('mysql')));
        endif;
        $response['message'] = $going;

    endif; 

    // Return our response to the script in JSON format
	header( 'Content: application/json' );
	echo json_encode( $response );
	die;
		

}
add_action( 'wp_ajax_toggleparticipants', 'rotary_toggleparticipants' );
