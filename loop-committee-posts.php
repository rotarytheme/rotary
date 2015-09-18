<?php
/**
 * The loop for the committee connected posts archive
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 

<?php 

if ( isset( $_REQUEST['committeeid'] ) ) :			
	//secondary loop
	$postCount = 0;
	$clearLeft='';
	$connected = new WP_Query( array(
	'connected_type'  => 'committees_to_posts',
	'connected_items' => $_REQUEST['committeeid'],
	'nopaging'        => false,
	) ); 			
	
	if ( $connected->have_posts() ) : $committeePost = get_post( $_REQUEST['committeeid'] ); ?>
		<?php 
		 while ( $connected->have_posts() ) : $connected->the_post();
			rotary_output_blogroll();
		endwhile;
	else : 
		?><p  id="nopostsfound"><?php echo _e( 'No updates', 'Rotary'); ?>.</p><?php 
	endif;
	 // Reset Post Data
	wp_reset_postdata();
else : 
	?><p>This page is only valid for committees</p><?php 
endif; 