<?php
/**
 * The projects blogroll loop.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */?>
<?php global $wp_query ?>
<?php $query =  $wp_query ?>
<div id="projectblogrollcontainer" class="projectblogrollcontainer clearfix">
<?php if ( have_posts() ) : ?>
	<?php $title=''; ?>
	<?php if ( isset( $_REQUEST['committeeid'] ) ) :	?>
		<?php $committeepost = get_post( $_REQUEST['committeeid'] ); ?>
		<?php $title = $committeepost->post_title; ?>
		<?php //only get projects for the the committee if it is set ?>
		<?php if ( isset( $_REQUEST['committeeid'] ) ) :	?>
			<?php $query = new WP_Query( array(
				'connected_type'  => 'projects_to_committees',
				'connected_items' => $_REQUEST['committeeid'],
				'posts_per_page' => -1, 
			) ); ?>
			
		<?php endif; ?>
		
	<?php endif; ?>
	
	<?php show_project_blogroll($query, 'yes', $title); ?>
	<?php // Reset Post Data
	wp_reset_postdata();?>

<?php endif; ?>
</div>