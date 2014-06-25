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
<article <?php comment_class(); ?>>	


<?php if ( have_comments() ) : ?>
	<div class="commentcommittetext">
				<?php
					//wp_list_comments( array( 'style' => 'div', 'callback' => 'rotary_committee_comment', type => 'comment', 'per_page' => 5, 'reverse_top_level'  => true) );
					rotary_committee_comment();
				?>
	</div>
	<div class="commentbottom">
	<?php if(current_user_can('edit_page')) { ?>
		<a id="newpost" class="newpost" href="<?php echo admin_url(); ?>post-new.php?committeeid=<?php the_id(); ?>" target="_blank">New Updates</a>
	<?php } ?>
	<a id="newcomment" class="newcomment" href="#respond">New Announcement</a>

	</div>
<?php else : // or, if we don't have comments:

	if ( ! comments_open() ) :
?>
	<p><?php _e( 'Announcements are closed.', 'Rotary' ); ?></p>
	<?php else : ?>
	<div class="commentcommittetext">
	<p><?php _e( 'No announcements.', 'Rotary' ); ?></p>
	</div>
	<div class="commentbottom">
	<?php if(current_user_can('edit_page')) { ?>
		<a id="newpost" class="newpost" href="<?php echo admin_url(); ?>post-new.php?committeeid=<?php the_id()?>" target="_blank">New Updates</a>
	<?php } ?>
		<a id="newcomment" class="newcomment" href="#respond">New Announcement</a>
	</div>
	
<?php endif; // end ! comments_open() ?>


<?php endif; // end have_comments() ?>

<?php // no comment navigation ?>
</article>
<?php $args = array(
		'title_reply' => 'New Announcement',
		'comment_notes_after'  => '',
		'logged_in_as'  => '',
		'label_submit'  => 'Save Announcement'
		);
    ?> 
<?php comment_form($args); ?>