<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
acf_form_head();
get_header(); ?>
<h1 class="pagetitle"><span>Speaker Program</span></h1>
<div class="speakercontainer">
	<div class="speakerheader">
		
			<?php get_template_part( 'loop', 'single-speaker' ); ?>
	</div><!--#speakerheader-->
</div><!--#speakercontainer-->
<div class="speakerbottom"></div>
<?php get_footer(); ?>