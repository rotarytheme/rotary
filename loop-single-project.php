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
	<div class="committeecontent project">
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
	<?php //show participants ?>
	<?php echo do_shortcode('[MEMBER_DIRECTORY type="projects" id="'.get_the_id().'"]'); ?>
	<div class="clearleft"></div>
			<?php
	//secondary loop for posts - used for long term projects ?>
	<?php if ( get_field( 'long_term_project' ) ) : 
		$postCount = 0;
		$clearLeft='';
		$connected = new WP_Query( array(
			'connected_type'  => 'projects_to_posts',
			'connected_items' => $post,
			'posts_per_page' => 2, 
			'nopaging'        => false,
		) ); ?>
		<?php $hascontent = ''; ?>
		<?php $link1 =  admin_url() . 'post-new.php?projectid=' . get_the_id(); ?>
		<?php $link2 = get_post_type_archive_link( 'rotary_projects' ).'?projectid='.get_the_id();  ?>
		<?php if ( $connected->have_posts() ) : ?>
			<?php $hascontent = ' hascontent'; ?>
			<?php rotary_show_committee_header_container($hascontent, 'Update', $link1, $link2, ' project'); ?>
			<?php  while ( $connected->have_posts() ) : $connected->the_post();?>
				<?php $postCount = rotary_output_blogroll($postCount, $clearLeft); ?>
			<?php endwhile;?>
		<?php else: ?>
			<?php rotary_show_committee_header_container($hascontent, 'Update', $link1, $link2, ' project'); ?>	
		<?php endif;?>
	<?php endif; ?>
	<?php // Reset Post Data
wp_reset_postdata();?>
<?php endwhile; // end of the loop. ?>