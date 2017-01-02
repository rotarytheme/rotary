<?php
/**
 * The template for displaying Author Archive pages.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 
get_header(); ?>
 
<?php
    if ( have_posts() )
        the_post();
?>
 
<h1 class="pagetitle"><span><?php echo rotary_get_blog_title();?></span></h1>

<h2 class="pagesubtitle">
	<span class="pagesubtitle-h4"><?php echo __( 'Author Archive', 'Rotary' );?></span><br>
	<span class="pagesubtitle-h1"><?php echo get_the_author(); ?></span>
</h2>

<div id="page" class="blog">
	<div id="content" class="hassidebar">
		<?php
		// If a user has filled out their description, show a bio on their entries.
		if ( get_the_author_meta( 'description' ) ) : ?>
			<section class="authordesc">
			<h4><?php printf( __( 'About %s', 'Rotary' ), get_the_author() ); ?></h4>
				<div class="author-avatar">
					<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'Rotary_author_bio_avatar_size', 100 ) ); ?>
				</div>
				<div class="author-description">
					<?php the_author_meta( 'description' ); ?>
				</div>
		<?php endif; ?></section>
		 
		<?php
		    rewind_posts(); ?>
		<div id="content" role="main"> 
		<?php    get_template_part( 'loop', 'author' );
		?>
		 </div>
	</div><!-- content -->
	<?php get_sidebar(); ?>
</div><!-- page -->
<?php get_footer(); ?>