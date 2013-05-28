<?php
/**
 * The template for displaying 404 (not found) pages.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

	<h1 class="pagetitle"><span><?php _e( 'Not Found', 'Rotary' ); ?></span></h1>
    <div id="content" role="main"> 
      <div class="inner">
		<p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', 'Rotary' ); ?></p>
      </div>
    </div>

<?php get_footer(); ?>