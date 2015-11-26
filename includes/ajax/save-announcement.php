<?php
/**
 * Callback to save the edits to an announcement
 * Mostly borrowed from comment_post.php
 * 
 * Handles Comment Post to WordPress and prevents duplicate comment posting.
*
* @package WordPress
*/

if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
	header('Allow: POST');
	header('HTTP/1.1 405 Method Not Allowed');
	header('Content-Type: text/plain');
	exit;
}

/** Sets up the WordPress Environment. */
require( substr(  __DIR__, 0, strpos(  __DIR__, '/wp-content' )) .'/wp-load.php' );

nocache_headers();

$comment_ID = isset( $_REQUEST['comment_ID'] ) ? (int) $_REQUEST['comment_ID'] : 0;
$announcement = get_comment( $comment_ID );

if ( empty( $announcement ) ) {
	/**
	 * Fires when a comment is attempted on a post that does not exist.
	 *
	 * @since 1.5.0
	 *
	 * @param int $comment_post_ID Post ID.
	 */
	do_action( 'comment_id_not_found', $comment_post_ID );
	exit;
}

$comment_post_ID = $announcement->comment_post_ID;
$post = get_post( $comment_post_ID );

// get_post_status() will get the parent status for attachments.
$status = get_post_status( $post );
$status_obj = get_post_status_object( $status );

if ( ! comments_open( $comment_post_ID ) ) {
	/**
	 * Fires when a comment is attempted on a post that has comments closed.
	 *
	 * @since 1.5.0
	 *
	 * @param int $comment_post_ID Post ID.
	 */
	do_action( 'comment_closed', $comment_post_ID );
	wp_die( __( 'Sorry, comments are closed for this item.' ), 403 );
} elseif ( 'trash' == $status ) {
	/**
	 * Fires when a comment is attempted on a trashed post.
	 *
	 * @since 2.9.0
	 *
	 * @param int $comment_post_ID Post ID.
	 */
	do_action( 'comment_on_trash', $comment_post_ID );
	exit;
} elseif ( ! $status_obj->public && ! $status_obj->private ) {
	/**
	 * Fires when a comment is attempted on a post in draft mode.
	 *
	 * @since 1.5.1
	 *
	 * @param int $comment_post_ID Post ID.
	 */
	do_action( 'comment_on_draft', $comment_post_ID );
	exit;
} elseif ( post_password_required( $comment_post_ID ) ) {
	/**
	 * Fires when a comment is attempted on a password-protected post.
	 *
	 * @since 2.9.0
	 *
	 * @param int $comment_post_ID Post ID.
	 */
	do_action( 'comment_on_password_protected', $comment_post_ID );
	exit;
} else {
	/**
	 * Fires before a comment is posted.
	 *
	 * @since 2.8.0
	 *
	 * @param int $comment_post_ID Post ID.
	 */
	do_action( 'pre_comment_on_post', $comment_post_ID );
}


// This action deals with saving all the custom fields that we are passing through.  It calls rotary_save_announcement_meta() in committee-projects-functions.php
do_action( 'comment_edit', $comment_ID );

$comment_content = ( isset($_REQUEST['comment']) ) ? trim($_REQUEST['comment']) : null;

if ( '' == $comment_content ) {
	wp_die( __( '<strong>ERROR</strong>: please type a comment.' ), 200 );
}

$commentarr = compact( 'comment_ID', 'comment_content' );
wp_update_comment( $commentarr );

$approve = wp_set_comment_status( $comment_id, 'approve' );
$comment = get_comment( $comment_id );

$location = empty( $_REQUEST['redirect_to'] ) ? get_comment_link( $comment_id ) : $_REQUEST['redirect_to'] . '#comment-' . $comment_ID;

/**
 * Filter the location URI to send the commenter after posting.
 *
 * @since 2.0.5
 *
 * @param string $location The 'redirect_to' URI sent via $_REQUEST.
 * @param object $comment  Comment object.
 */
$location = apply_filters( 'comment_post_redirect', $location, $comment );

wp_safe_redirect( $location );
exit;
