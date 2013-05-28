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

<?php if ( have_comments() ) : ?>
			<?php /* Rotary NOTE: The following h3 id is left intact so that comments can be referenced on the page */ ?>
			<h3 id="comments-title"><?php
			printf( _n( 'One Response to %2$s', '%1$s Responses to %2$s', get_comments_number(), 'Rotary' ),
			number_format_i18n( get_comments_number() ), '' . '<span>' . get_the_title() . '</span> ' );
			?></h3>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
	<nav class="prevnext">
		<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'Rotary' ) ); ?></div>
		<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'Rotary' ) ); ?></div>
	</nav>
<?php endif; // check for comment navigation ?>

				<?php
					wp_list_comments( array( 'style' => 'div', 'callback' => 'Rotary_comment', 'end-callback' => 'Rotary_comment_close' ) );
				?>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
	<nav>
		<?php previous_comments_link( __( '&larr; Older Comments', 'Rotary' ) ); ?>
		<?php next_comments_link( __( 'Newer Comments &rarr;', 'Rotary' ) ); ?>
	</nav>
<?php endif; // check for comment navigation ?>

<?php else : // or, if we don't have comments:

	if ( ! comments_open() ) :
?>
	<p><?php _e( 'Comments are closed.', 'Rotary' ); ?></p>
<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>
<?php $args = array(
		'title_reply' => 'reply');
    ?> 
<?php comment_form($args); ?>