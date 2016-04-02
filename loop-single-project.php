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
    
	<div class="committeecontent project">
		<div class="committeeheadercontainer<?php echo $hascontent; ?>">
			<h3 class="committeeheader"><?php _e( 'Project Description', 'Rotary' ); ?></h3>
			<?php if ( !$hascontent ) : ?>
				<p class="addcontent"><?php echo __( 'There is no description yet - add one!', 'Rotary') . (( !is_user_logged_in()) ? ' ' . wp_loginout( $_SERVER['REQUEST_URI'], true ) : '' ); ?></p>
			<?php endif; ?>
			<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
		</div>
		<div class="project">
			<?php the_content(); ?>
		</div>
	</div>
	<div class="clearleft"></div>

	<?php
	//secondary loop for posts - used for long term projects 
	if ( get_field( 'long_term_project' ) ) : 
		$postCount = 0;
		$clearLeft='';
		$connected = new WP_Query( array(
			'connected_type'  	=> 'projects_to_posts',
			'connected_items' 	=> $post,
			'posts_per_page' 	=> 2, 
			'nopaging'        	=> false,
		) );

		$hascontent = '';
		$link1 =  admin_url() . 'post-new.php?projectid=' . get_the_id();
		$link2 = get_site_url() . '/posts/?projectid='.get_the_id(); 
		if ( $connected->have_posts() ) :
			$hascontent = ' hascontent';
			rotary_show_committee_header_container( $hascontent, 'update', $link1, $link2, ' project');
			?>	
				<div class="updates-container clearfix">
			<?php 
				while ( $connected->have_posts() ) : $connected->the_post();
					rotary_output_blogroll();
				endwhile;
			?>
				</div>
			<?php 
		else:
			rotary_show_committee_header_container( $hascontent, 'update', $link1, $link2, ' project');
		endif;
	endif;
	
	// Reset Post Data
	wp_reset_postdata();

endwhile; // end of the loop.

