<?php
/**
 * The committee connected posts template.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 
<?php get_header(); ?>
<h1 class="pagetitle"><span>Committee News</span></h1>
 
<div id="content" role="main"> 

<?php   if ( isset( $_REQUEST['committeeid'] ) ) {			
			//secondary loop
			$postCount = 0;
			$clearLeft='';
			$connected = new WP_Query( array(
			'connected_type'  => 'committees_to_posts',
			'connected_items' => $_REQUEST['committeeid'],
			'nopaging'        => false,
			) ); ?>			
			
			<?php if ( $connected->have_posts() ) : ?>
			 <?php   $committeePost = get_post( $_REQUEST['committeeid'] ); ?>
				<h2 class="pagesubtitle"><?php echo $committeePost->post_title; ?></h2>
				<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
					<?php $postCount = rotary_output_blogroll($postCount, $clearLeft); ?>
				<?php endwhile;?>
			<?php else : ?>
				<p>No committee news.</p>
			<?php endif;?>
			<?php // Reset Post Data
			wp_reset_postdata();
}
else { ?>
	<p>This page is only valid from the committee</p>
<?php } ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
