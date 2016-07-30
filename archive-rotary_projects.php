<?php
/**
 * Template Name: Project Archive
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 
<?php get_header(); ?>

<?php
global $ProjectType;

	$title = __( 'All Projects', ' Rotary');
	$committee_id = ( get_query_var( 'committeeid' ) ) ? get_query_var( 'committeeid' ) : 0;
	
	if ( $committee_id ) :
		$committeepost = get_post( $committee_id );
		$title = $committeepost->post_title;
	endif;
	
	$project_type = ( get_query_var( 'projecttype' ) ) ? get_query_var( 'projecttype' ) : 0;
	
	if ( $project_type ) :;
		$title = sprintf( __( 'Type: %s' , 'Rotary'), $ProjectType[$project_type] );
	endif;
	
	?>

	<h1 class="pagetitle"><?php echo $title; ?></h1>
	<div id="page" class="nomargin">
		<div id="content" class="fullwidth" role="main"> 
		<?php /*
				<div><h2>Committee</h2><?php echo rotary_list_of_committees_with_project_archives();?></div>
			*/?>
			<?php if( $project_type) :?>
				<div><label class="dropdown-label"><?php echo __( 'Select a project type', Rotary);?></label><?php echo rotary_list_of_project_types_with_project_archives( $project_type );?></div>
			<?php endif;?>	
			<?php
				
				$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
				
				$args = array( 
						'post_type'		=> 'rotary_projects',
						'meta_key'		=> 'rotary_project_date',
						'post_status' 	=> 'publish',
						'posts_per_page' => 12,
						'paged'         => $paged,
						'orderby'		=> 'meta_value_num',
						'order'			=> 'DESC'
					);
				// see if we are filtering projects by committee
				if( $committee_id ) {
					$args['connected_type'] = 'projects_to_committees';
					$args['connected_items'] = $committee_id;
				}
				if( $project_type ) {
					$args['meta_key'] 	= 'project_type';
					$args['meta_value'] = $project_type;
				}
				
				$wp_query = new WP_Query( $args ); 
				
				if (  $wp_query->max_num_pages > 1 ) :	
					the_posts_pagination( array(
						'mid_size'  => 2,
						'prev_text' => __( 'Newer', 'Rotary' ),
						'next_text' => __( 'Older', 'Rotary' ),
					) );
				endif; 	
				
				get_template_part( 'loop', 'blogroll-projects' ); 
				
			?>
		</div>
	</div>
	
<?php get_footer(); 
