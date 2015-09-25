<?php
/**
 * The template for displaying Announcements.
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
			$stub = 'project';
			$button_class = "rotarybutton-largegold";
			if (has_post_thumbnail()) :
				$hasThumbnail = 'hasthumbnail';
			endif;
			break;
		case 'rotary-committees':
			$stub = 'committee';
			$button_class = "rotarybutton-largeblue";
			break;
		default:
			$stub = $currentPostType;
			$button_class = "rotarybutton-largeblue";
}?>	
<?php $commentid = $stub .'-announcement-form'; ?>

<div class="<?php echo $stub?> <?php echo $hasThumbnail; ?>">	

<?php if ( have_comments() ) : ?>
		<div class="<?php echo $stub?>-announcements hascontent">
		<?php if ( is_user_logged_in() ) : ?>
			<a id="newcomment" class="newcomment <?php echo $button_class; ?>" href="#respond">New Announcement</a>
		<?php else : ?>
			<?php  wp_loginout($_SERVER['REQUEST_URI'], true ); ?>
		<?php endif; ?>
			<?php
				//wp_list_comments( array( 'style' => 'div', 'callback' => 'rotary_committee_comment', type => 'comment', 'per_page' => 5, 'reverse_top_level'  => true) );
				// rotary_committee_comment( $currentPostType );  renamed by PAO 2015-09-13
				rotary_get_single_post_announcements_html( $currentPostType, $stub );
			?>
		</div>
	<?php else : // or, if we don't have comments:
	
		if ( ! comments_open() ) :
	?>
		<p><?php _e( 'Announcements are closed.', 'Rotary' ); ?></p>
		<?php else : ?>
		
		<div class="<?php echo $stub; ?>-announcements nocontent">
			<p><?php _e( 'No Announcements at the Moment', 'Rotary' ); ?></p>
			<?php if ( is_user_logged_in() ) : ?>
				<p><?php _e( 'Would you like to make one', 'Rotary' ); ?>?</p>
				<a id="newcomment" class="newcomment <?php echo $button_class; ?>" href="#respond"><?php _e( 'New Announcement', 'rotary' );?></a>
			<?php else : ?>
				<p><?php _e( sprintf( 'Would you like to %s ?', 'Rotary' ),  wp_loginout($_SERVER['REQUEST_URI'], true ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; // end ! comments_open() ?>
	<?php endif; // end have_comments() ?>
<?php // no comment navigation ?>
</div>

<?php $args = array(
		'title_reply' => 'New Announcement',
		'comment_notes_after'  => '',
		'logged_in_as'  => '',
		'label_submit'  => 'Save Announcement',
		'id_form'       => $commentid
		);
    ?> 
    <div id="new-announcement-form">
		<?php comment_form( $args ); ?>
	</div>

