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
	$committeeID = get_the_id(); 
	$connected = new WP_Query( array(
		'connected_type'  => 'committees_to_posts',
		'connected_items' => $post,
		'posts_per_page' => 2, 
		'nopaging'        => true,
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
				<?php // Reset Post Data
					wp_reset_postdata();?>
			<?php endif;?>
			
 <?php  //now get projects ?>
 	
 	<?php $connected = new WP_Query( array(
		'connected_type'  => 'projects_to_committees',
		'connected_items' => get_queried_object_id(),
		'posts_per_page' => 1, 
		'order' => 'DESC',
		'orderby' => 'meta_value',
        'meta_key' => 'rotary_project_date',
		'nopaging'        => false,
	) ); ?>
	
	<?php if ( $connected->have_posts() ) : ?>
			<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
				<div class="connectedprojects clearleft">
				<div class="connectedprojectscontainer clearfix">
					<p class="projectheader">Latest Events/Projects</p>
					<b></b>
					
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="alignleft">
					<?php if ( get_field( 'rotary_project_date' ) ) : ?>
						<?php $date = DateTime::createFromFormat('Ymd', get_field( 'rotary_project_date' ) ); ?>
						<div class="committee-comment-date">
							<span class="day"><?php echo $date->format( 'd') ; ?></span>
							<span class="month"><?php  echo $date->format( 'M' ); ?></span>
							<span class="year"><?php echo $date->format( 'Y' ); ?></span>
						</div>
					<?php endif; ?>	
						<?php the_content(); ?>
						</div>		
						<?php if (has_post_thumbnail()) : ?>
						<div class="alignleft">
							<?php the_post_thumbnail('medium'); ?>
						</div>		
				<?php endif; ?>
				</div>
				</div>	
			<?php endwhile;?>
		<?php // Reset Post Data
			wp_reset_postdata();?>
		<?php endif;?>


		<div class="committeecontent">
			<?php the_content(); ?>
			<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
		</div>


<?php endwhile; // end of the loop. ?>