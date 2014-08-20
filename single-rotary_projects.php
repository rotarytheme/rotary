<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>
<h1 class="pagetitle"><span>Projects</span></h1>
<h2 class="pagesubtitle"><span><?php the_title(); ?></span></h2>
<?php $connected = new WP_Query( array(
		'connected_type'  => 'projects_to_committees',
		'connected_items' => get_the_id(),
		'posts_per_page' => 1, 
		'nopaging'        => false,
	) ); ?>
	
<?php if ( $connected->have_posts() ) : ?>
	<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
		<h3 class="pagecommitteetitle"><?php the_title(); ?> Committee</h3>
	<?php endwhile; ?>
<?php endif; ?>
<?php wp_reset_postdata();?>
<div class="speakercontainer">
	<div class="speakerheader clearfix">

<?php get_sidebar('projects'); ?>
	<div id="content" class="projects" role="main">
	
	<?php comments_template( '/committee-comments.php' ); ?> 			
		<?php get_template_part( 'loop', 'single-project' ); ?>
		<?php echo do_shortcode('[MEMBER_DIRECTORY type="projects" id="'.get_the_id().'"]'); ?>
    </div>
    
</div><!--#speakerheader-->
<div class="speakerbottom"></div>

</div><!--#speakercontainer-->
<?php get_footer(); ?>