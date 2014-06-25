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


			<?php
	//secondary loop
	$postCount = 0;
	$clearLeft='';
	$connected = new WP_Query( array(
		'connected_type'  => 'committees_to_posts',
		'connected_items' => $post,
		'posts_per_page' => 2, 
		'nopaging'        => false,
	) ); ?>

			<?php if ( $connected->have_posts() ) : ?>
				<div class="committeeribbon">
					<h3>
						<a href=" <?php echo get_post_type_archive_link( 'rotary-committees' ); ?>?committeeid=<?php the_id(); ?>">Committee News</a>
					</h3>
				</div>
				<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
					<?php $postCount = rotary_output_blogroll($postCount, $clearLeft); ?>
				<?php endwhile;?>
			<?php endif;?>
			<?php // Reset Post Data
wp_reset_postdata();?>





		<div class="committeecontent">
			<?php the_content(); ?>
			<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
		</div>


<?php endwhile; // end of the loop. ?>