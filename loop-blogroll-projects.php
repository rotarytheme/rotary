<?php
/**
 * The projects blogroll loop.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 
<?php 

global $wp_query;
$query =  $wp_query
?>

<div id="projectblogrollcontainer" class="projectblogrollcontainer clearfix">
	<?php
	if ( have_posts() ) :
		$title='';
		if ( isset( $_REQUEST['committeeid'] ) ) :
			$committeepost = get_post( $_REQUEST['committeeid'] );
			$title = $committeepost->post_title;
			//only get projects for the the committee if it is set
			if ( isset( $_REQUEST['committeeid'] ) ) :
				$query = new WP_Query( array(
					'connected_type'  => 'projects_to_committees',
					'connected_items' => $_REQUEST['committeeid'],
					'posts_per_page' => -1, 
				) );
			endif;	
		endif;	
		show_project_blogroll($query, 'yes', $title);
		// Reset Post Data
		wp_reset_postdata();
	endif;
	?>
</div>