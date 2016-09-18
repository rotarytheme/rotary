<?php
/**
 * The loop that displays a single post.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */
?>

<?php 
if ( have_posts() ) while ( have_posts() ) : the_post();

    $hascontent = '';
    $committeeTitle = get_the_title();
    if ( '' != trim( get_the_content() ) ) :
    	$hascontent = ' hascontent';
    endif;
    ?>
	<div class="committeecontent">
		<div class="committeeheadercontainer<?php echo $hascontent; ?>">
			<h3 class="committeeheader"><?php  _e( 'Description', 'Rotary'); ?></h3>
			<?php if ( !$hascontent ) : ?>
					<p class="addcontent"><?php echo __( 'There is no description yet - add one!', 'Rotary') . (( is_user_logged_in()) ? ' ' . wp_loginout( $_SERVER['REQUEST_URI'], true ) : '' ); ?></p>
			<?php endif;
		edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); 
		?>
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
	) ); 
	
	$hascontent = ''; 
			$link1 =  admin_url() . 'post-new.php?committeeid=' . get_the_id();
			$link2 = get_site_url() . '/posts/?committeeid='.get_the_id();
			if ( $connected->have_posts() ) :
				$hascontent = ' hascontent';
				rotary_show_committee_header_container($hascontent, 'Update', $link1, $link2);
			?>	
				<div class="updates-container clearfix">
			<?php 
				while ( $connected->have_posts() ) : $connected->the_post();
					rotary_output_blogroll();
				endwhile;
			?>
				</div>
			<?php else:
				rotary_show_committee_header_container($hascontent, 'update', $link1, $link2); 
			endif;
	// Reset Post Data
	wp_reset_postdata();
			
 //now get projects 
 	
 	$connected = new WP_Query( array(
		'connected_type'  	=> 'projects_to_committees',
		'connected_items' 	=> get_queried_object_id(),
		'post_type'			=> 'rotary_projects',
		'posts_per_page' 	=> 2,
		'order' 			=> 'DESC',
		'orderby' 			=> 'meta_value',
        'meta_key' 			=> 'rotary_project_date',
		'nopaging'        	=> false,
	) ); 

	$hascontent = ''; 
	$link1 =  admin_url() . 'post-new.php?post_type=rotary_projects&committee=' . get_the_id();
	$link2 = get_post_type_archive_link( 'rotary_projects' ).'?committeeid='.get_the_id();
	if ( $connected->have_posts() ) :
		$hascontent = ' hascontent';
		rotary_show_committee_header_container($hascontent, 'project', $link1, $link2);
		?>
		<div class="connectedprojects clearleft clearfix">				
			<?php show_project_blogroll( $connected, 'no', $committeeTitle ); ?>
		</div>
		
		<?php else: 
			rotary_show_committee_header_container($hascontent, 'project', $link1, $link2); 
		endif;
		// Reset Post Data
		wp_reset_postdata();
		
endwhile; // end of the loop.

