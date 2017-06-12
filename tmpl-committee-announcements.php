<?php
/**
 * The template for displaying Announcements on the committee and project pages
 *
 * The area of the page that contains both current comments
 * and the comment form.  The actual display of comments is
 * handled by a callback to rotary_get_single_post_announcements_html which is
 * located in the committee-project-functions.php file.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>

<?php if ( post_password_required() ) : ?>
		<p><?php _e( 'This post is password protected. Enter the password to view any comments.', 'Rotary' ); ?></p>
<?php
		return;
	endif;
?>


<?php
	// You can start editing here -- including this comment!
?>
<?php $currentPostType = get_post_type(); ?>
<?php $hasThumbnail = ''; ?>
<?php switch ( $currentPostType ) {
		case 'rotary_projects':
			$context = 'project';
			$button_class = "rotarybutton-largegold";
			if (has_post_thumbnail()) :
				$hasThumbnail = 'hasthumbnail';
			endif;
			break;
		case 'rotary-committees':
			$context = 'committee';
			$button_class = "rotarybutton-largeblue";
			break;
		default:
			$context = $currentPostType;
			$button_class = "rotarybutton-largeblue";
}?>	
<?php $commentid = $context .'-announcement-form'; ?>

<div class="<?php echo $context?> <?php echo $hasThumbnail; ?>">	

<?php if ( have_comments() ) : ?>
		<div class="<?php echo $context?>-announcements hascontent">
		<?php if ( is_user_logged_in() ) : ?>
			<a id="newcomment" class="newcomment <?php echo $button_class; ?>" href="#respond">New Announcement</a>
		<?php else : ?>
			<?php  wp_loginout($_SERVER['REQUEST_URI'], true ); ?>
		<?php endif; ?>
			<?php
				$args = array(
					'order' => 'DESC',
					'post_type' =>  $postType,
					'status' => 'approve',
					'type' => 'comment',
					'post_id' => get_the_id(),
					'number' => 10
				); 
				$announcements = get_comments( $args );
				if (is_array( $comments )) : 
					foreach( $announcements as $announcement ) : 
						$firstAnnouncement = ( $announcement === reset( $announcements )) ? true : false;  
				  		$extra_classes = array( 'clearleft', (( !$firstAnnouncement ) ? 'hide' : '' )); 
						$count++;
						// Display the announcement body.  We need to have set $announcement, $context, and $extra_classes before calling this
							include ( get_template_directory() . '/loop-single-announcement.php'); 
						
						
						if ( $firstAnnouncement && get_comments_number() > 1 ) : ?>
							<p class="morecommentcontainer"><a href="#" class="morecomments" id="morecomments"><?php echo sprintf( __( 'Show More [+%s]', 'Rotary'), intval(intval(get_comments_number()) - 1.0) ); ?></a></p>	
						<?php  
						endif; 
						if ( $announcement === end( $announcements ) && !$firstAnnouncement ) : ?>
							<p class="morecommentcontainer"><a href="#" class="lesscomments hide" id="lesscomments"><?php _e( 'Show Less', 'Rotary'); ?></a></p>
						 <?php endif;
					endforeach;
				endif;
			?>
		</div>
	<?php else : // or, if we don't have comments:
	
		if ( ! comments_open() ) :
	?>
		<p><?php _e( 'Announcements are closed.', 'Rotary' ); ?></p>
		<?php else : ?>
		
		<div class="<?php echo $context; ?>-announcements nocontent">
			<p><?php _e( 'No Announcements at the Moment', 'Rotary' ); ?></p>
			<?php if ( is_user_logged_in() ) : ?>
				<p><?php _e( 'Would you like to make one', 'Rotary' ); ?>?</p>
				<a id="newcomment" class="newcomment <?php echo $button_class; ?>" href="#respond"><?php _e( 'New Announcement', 'Rotary' );?></a>
			<?php else : ?>
				<p><?php echo sprintf( __( 'Would you like to %s ?', 'Rotary' ),  wp_loginout($_SERVER['REQUEST_URI'], true ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; // end ! comments_open() ?>
	<?php endif; // end have_comments() ?>
<?php // no comment navigation ?>
</div>

<?php $args = array(
		'title_reply' => __( 'New Announcement' ),
		'comment_notes_after'  => rotary_comment_notes_after( ),
		'logged_in_as'  => '',
		'label_submit'  => __( 'Save Announcement' ),
		'id_form'       => $commentid,
		);
    ?> 
    <div id="new-announcement-form">
		<?php comment_form( $args ); ?>
	</div>

