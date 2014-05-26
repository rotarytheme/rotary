<?php
/**
 *  Template Name: Comments Form
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>
	<?php $args = array(
		'title_reply' => 'reply');
    ?> 
<?php comment_form($args); ?>

<?php get_footer(); ?>
