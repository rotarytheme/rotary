<?php
/**
 * The Template for displaying reveille posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<h1 class="pagetitle"><span>Reveille</span></h1>
    	<?php the_title('<h2>', '</h2>'); ?>
    <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>    
        <p class="singleauthor">by <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ))?>"><?php echo get_the_author(); ?></a></p>
	<div id="content" role="main">

    	<?php the_content(); ?>
	<?php endwhile; ?>    
	</div>
</article>
<?php get_sidebar(); ?>
<?php get_footer(); ?>