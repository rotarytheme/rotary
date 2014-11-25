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
    <?php $hascontent = ''; ?>
    <?php $committeeTitle = get_the_title(); ?>
    <?php if ( '' != trim( get_the_content() ) ) : ?>
    	<?php $hascontent = ' hascontent'; ?>
    <?php endif; ?>	
	<div class="committeecontent">
		<div class="committeeheadercontainer<?php echo $hascontent; ?>">
			<h3 class="committeeheader">Description</h3>
			<?php if ( !$hascontent ) : ?>
				<?php if ( is_user_logged_in()) : ?>
					<p class="addcontent">No description at the moment, add one!</p>
				<?php else : ?>
					<p class="addcontent">No description at the moment, <?php wp_loginout( $_SERVER['REQUEST_URI'], true ); ?>!</p>
				<?php endif; ?>
			<?php endif; ?>
			<?php edit_post_link( __( 'Edit <span>></span>', 'Rotary' ), '', '' ); ?>
		</div>
		<div class="committee">
			<?php the_content(); ?>
		</div>
	</div>

			<?php
	//secondary loop to get blog posts attached to the commitee
	$postCount = 0;
	$clearLeft='';
	$committeeID = get_the_id(); 
	$connected = new WP_Query( array(
		'connected_type'  => 'committees_to_posts',
		'connected_items' => $post,
		'posts_per_page' => 2, 
		'nopaging'        => true,
	) ); ?>
			<?php $hascontent = ''; ?>
			<?php $link1 =  admin_url() . 'post-new.php?committeeid=' . get_the_id(); ?>
			<?php $link2 = get_post_type_archive_link( 'rotary-committees' ).'?committeeid='.get_the_id();  ?>
			<?php if ( $connected->have_posts() ) : ?>
				<?php $hascontent = ' hascontent'; ?>
				<?php rotary_show_committee_header_container($hascontent, 'Update', $link1, $link2); ?>
				
				<div class="committeeblogrollcontainer clearfix">
					<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
							<?php $postCount = rotary_output_blogroll($postCount, $clearLeft); ?>
				<?php endwhile;?>
				</div>
				<?php else: ?>
				<?php rotary_show_committee_header_container($hascontent, 'update', $link1, $link2); ?>
			<?php endif;?>
	<?php // Reset Post Data
	wp_reset_postdata();?>
			
 <?php  //now get projects ?>
 	
 	<?php $connected = new WP_Query( array(
		'connected_type'  => 'projects_to_committees',
		'connected_items' => get_queried_object_id(),
		'posts_per_page' => 2, 
		'order' => 'DESC',
		'orderby' => 'meta_value',
        'meta_key' => 'rotary_project_date',
		'nopaging'        => false,
	) ); ?>
	<?php $hascontent = ''; ?>
	<?php $link1 =  admin_url() . 'post-new.php?post_type=rotary_projects&committee=' . get_the_id(); ?>
	<?php $link2 = get_post_type_archive_link( 'rotary_projects' ).'?committeeid='.get_the_id();  ?>
	<?php if ( $connected->have_posts() ) : ?>
			<?php $hascontent = ' hascontent'; ?>
			<?php rotary_show_committee_header_container($hascontent, 'project', $link1, $link2); ?>
			<div class="connectedprojects clearleft clearfix">				
			<?php show_project_blogroll( $connected, 'no', $committeeTitle ); ?>		
			</div>
		
		<?php else: ?>
				<?php rotary_show_committee_header_container($hascontent, 'project', $link1, $link2); ?>
			<?php endif;?>
		<?php // Reset Post Data
		wp_reset_postdata();?>

		


<?php endwhile; // end of the loop. ?>