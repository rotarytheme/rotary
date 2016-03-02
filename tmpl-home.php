<?php
/**
* Template Name: Home Page
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<?php  if (get_theme_mod( 'rotary_slideshow', true )) {
	rotary_get_slideshow(); 
} ?>
	<div id="page">
		<div id="content" role="main" class="<?php  if ( get_theme_mod( 'rotary_home_sidebar', true )){ echo 'hassidebar-wide';} else{ echo 'fullwidth';} ?>">
		
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
		   <section class="homecontent">
		      <div class="inner">
			   	<?php the_content();?>
		       </div>
		    </section>
		<?php endwhile; ?>
		</div>
		<?php if ( get_theme_mod( 'rotary_home_sidebar', true )) { 
			get_sidebar('home'); 
		} ?>
    <footer>
		<?php if( current_user_can( 'edit_home_page' ) || current_user_can( 'manage_options' ) ) { edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); } ?>
	</footer>
		
	</div>
<?php get_footer();