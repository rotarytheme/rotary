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
$query = $wp_query;
?>

<div id="projectblogrollcontainer" class="projectblogrollcontainer clearfix" style="clear:both">
	<?php	
	if ( have_posts() ) :
		show_project_blogroll( $query, 'yes' );
	endif;
	?>
</div>

