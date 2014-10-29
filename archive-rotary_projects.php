<?php
/**
 * The projects connected posts template.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 ?>
 
<?php get_header(); ?>

<?php   if ( isset( $_REQUEST['projectid'] ) ) : ?>	
	<h1 class="pagetitle"><span><?php echo get_the_title($_REQUEST['projectid']); ?></span></h1>
 
	<div id="content" role="main"> 
		<?php get_template_part( 'loop', 'archive-projects' ); ?>	
	</div>
	<?php get_sidebar('project-archives'); ?>
<?php else: ?>
	<h1 class="pagetitle"><span>Project Blog Roll</span></h1>
	<div id="content" class="fullwidth" role="main"> 
		<?php get_template_part( 'loop', 'blogroll-projects' ); ?>
	</div>
	<?php endif; ?>
<?php get_footer(); ?>
