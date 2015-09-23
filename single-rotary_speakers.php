<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
get_header(); ?>
<h1 class="pagetitle"><span>Speaker Program</span></h1>
<div id="page">
			<?php 
// include( 'mailchimp-campaign/email-rotary_speakers.php'); testing the email template
	get_template_part( 'loop', 'single-speaker' ); ?>

</div><!--#speakercontainer-->
<?php get_footer(); ?>