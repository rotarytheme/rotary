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

<?php   if ( isset( $_REQUEST['projectid'] ) ) { ?>	
	<h1 class="pagetitle"><span><?php echo get_the_title($_REQUEST['projectid']); ?></span></h1>
 
	<div id="content" role="main"> 
	<?php //get the committee ?>
	<?php $connected = new WP_Query( array(
		'connected_type'  => 'projects_to_committees',
		'connected_items' => $_REQUEST['projectid'],
		'posts_per_page' => 1, 
		'nopaging'        => false,
	) ); ?>
	
<?php if ( $connected->have_posts() ) : ?>
	<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
		<h3 class="pagecommitteetitle"><?php the_title(); ?> Committee</h3>
	<?php endwhile; ?>
<?php endif; ?>
<?php wp_reset_postdata();?>	
	<?php		//secondary loop to get connected posts
			$postCount = 0;
			$clearLeft='';
			$connected = new WP_Query( array(
			'connected_type'  => 'projects_to_posts',
			'connected_items' => $_REQUEST['projectid'],
			'nopaging'        => false,
			) ); ?>			
			
			<?php if ( $connected->have_posts() ) : ?>
			 <?php   $committeePost = get_post( $_REQUEST['projectid'] ); ?>
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
