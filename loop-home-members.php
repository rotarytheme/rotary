<?php
/**
 * The loop that displays the member's (logged in) home page.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
?>

<?php 
if ( get_theme_mod( 'rotary_member_slideshow', true ) ) {
	rotary_get_slideshow();
}
?>
	<div id="page"
			<?php  if( has_shortcode( $post->post_content, 'blogroll')) { echo 'class="blog"';} ?>>
		<div id="content" role="main" class="
			<?php  if ( get_theme_mod( 'rotary_member_sidebar', true ) ){ echo 'hassidebar-wide';} else{ echo 'fullwidth';} ?>">
		
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
		   <section class="homecontent">
		      <div class="inner">
			   	<?php the_content(); 	?>
		       </div>
		    </section>
		<?php endwhile; ?>
		</div>
		<?php if (  get_theme_mod( 'rotary_member_sidebar', true ) ) { 
			get_sidebar('members'); 
		} ?>
    <footer>
		<?php if( current_user_can( 'edit_home_page' ) || current_user_can( 'manage_options' ) ) { edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); } ?>
	</footer>
		
	</div>