<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.  The actual display of comments is
 * handled by a callback to Rotary_comment which is
 * located in the functions.php file.
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
<?php $hascontent = ''; ?>
<?php $hasThumbnail = ''; ?>
<?php $commentid = 'committeecommentform'; ?>
<?php if ( 'rotary_projects' == $currentPostType ) :
	$commentid = 'projectcommentform';
	if (has_post_thumbnail()) :
		$hasThumbnail = 'hasthumbnail';
	endif;
endif; ?>	

<article <?php comment_class($hasThumbnail); ?>>	

<?php if ( have_comments() ) : ?>
	<?php $hascontent = ' hascontent'; ?>
	<div class="commentcommittetext<?php echo $hascontent; ?>">
				<?php
					//wp_list_comments( array( 'style' => 'div', 'callback' => 'rotary_committee_comment', type => 'comment', 'per_page' => 5, 'reverse_top_level'  => true) );
					rotary_committee_comment( $currentPostType );
				?>
		

	</div>
	<div class="commentbottom">
	
	
	</div>
<?php else : // or, if we don't have comments:

	if ( ! comments_open() ) :
?>
	<p><?php _e( 'Announcements are closed.', 'Rotary' ); ?></p>
	<?php else : ?>
	
	<div class="commentcommittetext">
		<div class="committeecomment">
			<?php if ( 'rotary-committees' ==  $currentPostType ) : ?>
				<?php $button_class = "rotarybutton-largeblue"; ?>
				<?php else: ?>
					<?php $button_class = "rotarybutton-largegold"; ?>
				<?php endif; ?>
		<p><?php _e( 'No Announcements at the Moment', 'Rotary' ); ?></p>
		<?php if ( is_user_logged_in() ) : ?>
			<p><?php _e( 'Would you like to add one', 'Rotary' ); ?>?</p>
			<a id="newcomment" class="newcomment <?php echo $button_class; ?>" href="#respond">New Announcement</a>
		<?php else : ?>
			<p><?php _e( 'Would you like to ', 'Rotary' ); ?> <?php  wp_loginout($_SERVER['REQUEST_URI'], true ); ?>?</p>
		<?php endif; ?>
		</div>
	</div>
	<div class="commentbottom">

			
	</div>
	
<?php endif; // end ! comments_open() ?>


<?php endif; // end have_comments() ?>

<?php // no comment navigation ?>
</article>
<?php $args = array(
		'title_reply' => 'New Announcement',
		'comment_notes_after'  => '',
		'logged_in_as'  => '',
		'label_submit'  => 'Save Announcement',
		'id_form'       => $commentid
		);
    ?> 
<?php comment_form($args); ?>