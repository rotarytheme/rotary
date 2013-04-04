<?php
/**
 * The template for displaying attachments.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 
get_header(); ?>
 <h1 class="pagetitle"><span>Attachments</span></h1>
<div id="content" role="main" class="fullwidth">
    <?php get_template_part( 'loop', 'attachment' ); ?>
 </div>
<?php get_footer(); ?>