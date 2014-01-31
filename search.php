<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<?php if ( have_posts() ) : ?>
		<h1 class="pagetitle"><span><?php printf( __( 'Search Results for: %s', 'Rotary' ), '' . get_search_query() . '' ); ?></span></h1>
			<div id="content" role="main"> 
			<?php
				get_template_part( 'loop', 'search' );
			?>
            </div>
<?php else : ?>
		<h1 class="pagetitle"><span><?php _e( 'Nothing Found', 'Rotary' ); ?></span></h1>
        <div id="content" role="main"> 
			<p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'Rotary' ); ?></p>
            </div>
			
<?php endif; ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>