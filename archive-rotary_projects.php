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

<?php   if ( isset( $_REQUEST['projectid'] ) ) : ?>	
	<h1 class="pagetitle"><span><?php echo rotary_get_blog_title();?></span></h1>
	<h2 class="pagesubtitle"><?php printf( __( 'Project %s', 'Rotary' ), '<br /><span>'. get_the_title($_REQUEST['projectid']) . '</span>' ); ?></h2>
	<div id="content" role="main"> 
		<?php get_template_part( 'loop', 'archive-projects' ); ?>	
	</div>
	<?php get_sidebar('project-archives'); ?>
<?php else: ?>
	<h1 class="pagetitle"><span>Project Blog Roll</span></h1>
	<div id="content" class="fullwidth" role="main"> 
		<?php $args = array( 
				'post_type'		=> 'rotary_projects',
				'meta_key'		=> 'rotary_project_date',
				'orderby'		=> 'meta_value_num',
				'order'			=> 'DESC'
			);?>
		<?php $wp_query = new WP_Query( $args ); ?>
		<?php get_template_part( 'loop', 'blogroll-projects' ); ?>
	</div>
	<?php endif; ?>
<?php get_footer(); ?>
