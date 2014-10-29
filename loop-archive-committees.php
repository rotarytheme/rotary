<?php
/**
 * The loop for the committee connected posts archive
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 

<?php   if ( isset( $_REQUEST['committeeid'] ) ) :			
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
else : ?>
	<p>This page is only valid from the committee</p>
<?php endif; ?>