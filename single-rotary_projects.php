<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>
<?php //get the project ID to use in the connected committee loop where the ID reflect the committee and not the project ?>
<?php $projectID = get_the_ID(); ?>
<h1 class="pagetitle"><span><?php the_title();  ?></span></h1>

<div id="page">
	<div class="projectcommitteetitle">
			
	<?php $connected = new WP_Query( array(
			'connected_type'  => 'projects_to_committees',
			'connected_items' => get_the_id(),
			'posts_per_page' => 1, 
			'nopaging'        => false,
		) ); 
		if ( $connected->have_posts() ) : ?>
			   <?php while ( $connected->have_posts() ) : $connected->the_post();?>
				<h2><?php echo _e( 'Project Organized By', 'rotary' )?>: <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php endwhile; ?>
		<?php else : ?>
			<h2><?php echo _e( 'Project', 'rotary' )?></h2>
	<?php endif; ?>
	</div>
		
	<?php wp_reset_postdata();?>
	<?php get_sidebar( 'projects' ); ?>
	
	<div id="content" class="projects" role="main">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail('large'); ?>
		<?php endif; ?>
		<?php comments_template( '/announcements.php' ); ?> 			
		<div class="clear"></div>
		<?php get_template_part( 'loop', 'single-project' ); ?>
    </div><!--#content-->
 </div><!-- #page -->
 
<?php get_footer(); ?>