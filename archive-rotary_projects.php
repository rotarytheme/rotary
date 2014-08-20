<?php
/**
 * The projects connected posts template.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 
<?php get_header(); ?>
<h1 class="pagetitle"><span>Projects</span></h1>
 
<div id="content" role="main"> 

<?php   if ( isset( $_REQUEST['projectid'] ) ) {			
			//secondary loop
			$postCount = 0;
			$clearLeft='';
			$connected = new WP_Query( array(
			'connected_type'  => 'projects_to_posts',
			'connected_items' => $_REQUEST['projectid'],
			'nopaging'        => false,
			) ); ?>			
			
			<?php if ( $connected->have_posts() ) : ?>
			 <?php   $committeePost = get_post( $_REQUEST['projectid'] ); ?>
				<h2 class="pagesubtitle"><?php echo $committeePost->post_title; ?></h2>
				<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
					<?php $postCount = rotary_output_blogroll($postCount, $clearLeft); ?>
				<?php endwhile;?>
			<?php else : ?>
				<p>No project news.</p>
			<?php endif;?>
			<?php // Reset Post Data
			wp_reset_postdata();
}
else { ?>
	<p>This page is only valid from the project</p>
<?php } ?>
</div>
<?php get_sidebar('project-archives'); ?>
<?php get_footer(); ?>
