<?php
/**
 * The loop for the project connected posts archive
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 
<?php   if ( isset( $_REQUEST['projectid'] ) ) : ?>		
	<?php	//secondary loop to get connected posts
		$postCount = 0;
		$clearLeft='';
		$connected = new WP_Query( array(
			'connected_type'  => 'projects_to_posts',
			'connected_items' => $_REQUEST['projectid'],
			'nopaging'        => false,
		) ); ?>
		<?php if ( $connected->have_posts() ) : ?>
			 <?php   $committeePost = get_post( $_REQUEST['committeeid'] ); ?>
				
				<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
					<?php $postCount = rotary_output_blogroll($postCount, $clearLeft); ?>
				<?php endwhile;?>
			<?php else : ?>
				<p id="nopostsfound"><?php _e( 'No updates', 'Rotary'); ?></p>
		<?php endif; ?>
		<?php // Reset Post Data ?>
		<?php wp_reset_postdata(); ?>
		
<?php else : ?>
		<p>This page is only valid for projects</p>	
<?php endif;