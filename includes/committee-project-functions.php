<?php

/**
 * rotary_committee_comment function.
 * 
 * @access public
 * @param string $postType (default: 'rotary-committees')
 * @return void
 */
function rotary_committee_comment( $postType =  'rotary-committees') { ?>
	<?php $args = array(
		'order' => 'DESC',
		'post_type' =>  $postType,
		'status' => 'approve',
		'type' => 'comment',
		'post_id' => get_the_id(),
		'number' => 5
	); ?>
	<?php $comments = get_comments($args); ?>

	<?php if (is_array($comments)) : ?>
		<?php foreach($comments as $comment) : ?>
		<?php $firstComment = false; ?>
		<?php if ($comment === reset($comments)) : ?>
			<?php $firstComment = true;  ?>
		<?php  endif; ?>
		<div class="clearleft committeecomment <?php if (!$firstComment) {echo ' hide';} ?>" id="comment-<?php echo $comment->comment_post_ID ?>">
			<div class="committee-comment-date">
			 	<?php $date = new DateTime($comment->comment_date); ?>
				<span class="day"><?php echo $date->format('d'); ?></span>
				<span class="month"><?php  echo $date->format('M'); ?></span>
				<span class="year"><?php echo $date->format('Y'); ?></span>
				</div>
				<p class="committeecommentdetail"><?php echo $comment->comment_content; ?></p>
				<p class="announcedby"><em>Announced by</em> <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ))?>"><?php echo $comment->comment_author;?></a></p>
				<?php if ($firstComment ) : ?>
					<?php if ( is_user_logged_in() ) : ?>
						<a id="newcomment" class="newcomment" href="#respond">New Announcement <span>></span></a>
					<?php else : ?>
						<?php  wp_loginout($_SERVER['REQUEST_URI'], true ); ?>
					<?php endif; ?>
				<?php  endif; ?>
			</div>
				<?php if ($firstComment && get_comments_number() > 1 ) : ?>
						<p class="morecommentcontainer"><a href="#" class="morecomments" id="morecomments">Show More Announcements</a></p>	
				<?php  endif; ?>
				<?php if ($comment === end($comments)) : ?>
					<p class="morecommentcontainer"><a href="#" class="lesscomments hide" id="lesscomments">Show Less Announcements</a></p>
				<?php  endif; ?>
			<?php  endforeach; ?>
		<?php  endif; ?>
<?php }

/**
 * rotary_get_committee_announcements function.
 * 
 * @access public
 * @param mixed $atts
 * @return void
 */
function rotary_get_committee_announcements($atts){
	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'rotary-committees',
		'order' => 'ASC'
	);
	$committeeArray = array();
	$commentDisplayed = 0;
	ob_start();
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) : ?>
	<div class="comment">
		 <div class="commentcommittetext">
		 <?php  while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php  $committeeArray[get_the_title()] = get_permalink() . '?open=open'; ?>
		<?php
		$args = array(
			'order' => 'DESC',
			'orderby' => 'title',
			'post_type' => 'rotary-committees',
			'status' => 'approve',
			'type' => 'comment',
			'post_id' => get_the_id(),
			'number' => 1
		);
		$comments = get_comments( $args ); ?>
		<?php if ( is_array($comments )) : ?>
                <?php $count = count($comments); ?>
                <?php if ( $count > 0 ) : ?>      
					<?php foreach($comments as $comment) : ?>
						<?php $date = new DateTime($comment->comment_date); ?>
						<?php $today = new DateTime(); ?>
						<?php $interval = $today->diff($date); ?>
						<?php //only show comments less than 10 days old ?>
						<?php if( abs($interval->days) < 10) : ?>	
							<?php $commentDisplayed++; ?>	
							<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
							<div class="committee-comment-date">
								<span class="homeday"><?php echo $date->format( 'd') ; ?></span>
								<span class="homemonth"><?php  echo $date->format( 'M' ); ?></span>
								<span class="homeyear"><?php echo $date->format( 'Y' ); ?></span>
							</div>									
							<div class="clearleft committeecomment">
								<p class="committeecommentdetail"><?php echo $comment->comment_content; ?></p>						
						</div>
						<hr />
						<?php endif; //end check for comment age over 10 days ?>
					<?php  endforeach; //end comment loop ?>
				<?php  endif; //end check for comment count ?>
			<?php  endif; //end is_array check?>
		<?php endwhile; //end wp_query loop ?>
		<?php if ( 0 == $commentDisplayed ) :?>
			<p>There are no current committee announcements.</p>
		<?php  endif; ?>
			<?php if (!is_user_logged_in()) { ?>
			<p>Please <?php echo wp_loginout( site_url(), false ); ?> to add a new announcment</p>
			<?php }
	else { ?>
				<select id="committeeselect" name="committeeselect">
					<option value="">-- Select a committee to add a new announcement --</option>
					<?php
		foreach($committeeArray as $key => $value):
			echo '<option value="'.$value.'">'.$key.'</option>';
		endforeach;
?>
					</select>
			<?php } ?>

			</div>
		</div>

	<?php endif; ?>

	<?php // Reset Post Data
	wp_reset_postdata();
	return ob_get_clean();

}
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
				<a class="newpost" href="<?php echo $link1; ?>" target="_blank">New <?php echo $title; ?> <span>></span></a>
			<?php endif; ?>	 
			<?php if ( $hascontent ) : ?>
				<a class="newpost second" href="<?php echo $link2; ?>">More <?php echo $title . 's'; ?> <span>></span></a>	
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
		 			<div class="hide imgoingtext<?php echo $particpate; ?>">I'm going</div>
		 		<?php else : ?>
		 			<?php $particpate = ' notgoing';?>
		 			<div class="hide imgoingtext<?php echo $particpate; ?>">I'm not going</div>
		 	<?php endif; ?>
		 <?php else: ?>
		 	<div class="hide imgoingtext">I haven't replied</div>
		 <?php endif; ?>
		<span class="imgoing icon<?php echo $particpate; ?>" data-postid="<?php the_ID(); ?>">Im going</span>		
		<?php $location = get_field('rotary_project_location'); ?>
		<?php $googleLink = '#'; ?>
		<?php if ($location) : ?>
				<?php $googleLink = 'http://www.google.com/maps?daddr='.$location["address"]; ?>
		<?php endif; ?>
		<a class="location icon" href="<?php echo $googleLink; ?>" target="_blank">Location</a>
		
		 
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
			
			<?php if (! get_field( 'long_term_project' ) ) : ?>
				<?php if ( get_field( 'rotary_project_date' ) ) : ?>
					<?php $date = DateTime::createFromFormat('Ymd', get_field( 'rotary_project_date' ) ); ?>
					<div class="rotary_project_date">
						<span class="day"><?php echo $date->format( 'l') ; ?></span><br />
						<span class="fulldate"><?php  echo $date->format( 'jS F Y' ); ?></span>
					</div>
				<?php endif; ?>
			<?php endif; ?>	
			<?php if ( 'yes' == $showthumb ) : ?>
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail('medium') ; ?>
				<?endif; ?>
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