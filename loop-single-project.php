<?php
/**
 * The loop that displays a single post.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	<div class="committeecontent clearleft">
			<?php the_content(); ?>
			<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
		</div>

			<?php
	//secondary loop
	$postCount = 0;
	$clearLeft='';
	$connected = new WP_Query( array(
		'connected_type'  => 'projects_to_posts',
		'connected_items' => $post,
		'posts_per_page' => 2, 
		'nopaging'        => false,
	) ); ?>

			<?php if ( $connected->have_posts() ) : ?>
					<h2>
						<a href=" <?php echo get_post_type_archive_link( 'rotary_projects' ); ?>?projectid=<?php the_id(); ?>">Project News</a>
					</h2>
				<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
					<?php $postCount = rotary_output_blogroll($postCount, $clearLeft); ?>
				<?php endwhile;?>
			<?php endif;?>
			<?php // Reset Post Data
wp_reset_postdata();?>





		


<?php endwhile; // end of the loop. ?>