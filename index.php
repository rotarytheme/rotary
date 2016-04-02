<?php
/**
 * The main template file.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 
get_header(); ?>

<h1 class="pagetitle"><?php echo rotary_get_blog_title();?></h1>
<div id="page">
	
	<?php 
	if ( have_posts() ) : the_post();?>		
		<h2 class="hassubtitle">
		<?php if ( isset( $_REQUEST['projectid'] ) ) : ?>
			<span class="pagesubtitle-h4"><?php  _e( 'Project Updates For', 'Rotary' );?></span><br>
			<span class="pagesubtitle-h1"><a href="<?php echo get_permalink( $_REQUEST['projectid'] ); ?>"><?php echo  get_the_title($_REQUEST['projectid']); ?></a></span>
		<?php elseif ( isset( $_REQUEST['committeeid'] ) ) : ?>
			<span class="pagesubtitle-h4"><?php  _e( 'Committee Updates For', 'Rotary' );?></span><br>
			<span class="pagesubtitle-h1"><a href="<?php echo get_permalink( $_REQUEST['committeeid'] ); ?>"><?php echo  get_the_title($_REQUEST['committeeid']); ?></a></span>
		<?php elseif ( is_day() ) : ?>
				<span class="pagesubtitle-h4"><?php  _e( 'Daily Archive', 'Rotary' );?></span><br>
				<span class="pagesubtitle-h1"><?php echo get_the_date(); ?></span>
		<?php elseif ( is_month() ) : ?>
			<?php if (is_post_type_archive( 'rotary_speakers' )) : ?>
				<span class="pagesubtitle-h4"><?php  _e( 'Monthly Archive', 'Rotary' );?></span><br>
				<span class="pagesubtitle-h1"><?php echo date("F", mktime(0, 0, 0, get_query_var('monthnum'), 10)) . ' ' .get_query_var('year') ?></span>
			<?php else : ?>
				<span class="pagesubtitle-h4"><?php  _e( 'Monthly Archive', 'Rotary' );?></span><br>
				<span class="pagesubtitle-h1"><?php echo get_the_date('F Y'); ?></span>
				<?php endif; ?>		
		<?php elseif ( is_year() ) : ?>
			<?php if ( is_post_type_archive( 'rotary_speakers' )) : ?>			
				<span class="pagesubtitle-h4"><?php  _e( 'Annual Archive', 'Rotary' );?></span><br>
				<span class="pagesubtitle-h1"><?php echo get_query_var('year'); ?></span>
			<?php else : ?>
				<span class="pagesubtitle-h4"><?php  _e( 'Annual Archive', 'Rotary' );?></span><br>
				<span class="pagesubtitle-h1"><?php echo get_the_date('Y'); ?></span>
			<?php endif; ?>				
		<?php elseif ( is_post_type_archive( 'post' )) : ?>
				<span class="pagesubtitle-h4"><?php  _e( 'Annual Archive', 'Rotary' );?></span><br>
				<span class="pagesubtitle-h1"><?php echo get_the_date('Y'); ?></span>
		<?php else: ?>
				<span class="pagesubtitle-h4"><?php  _e( 'Archive', 'Rotary' );?></span><br>
				<span class="pagesubtitle-h1"><?php  _e( 'All Posts and Updates', 'Rotary' ); ?></span>
		<?php endif; ?>
		</h2>
		
	<?php rewind_posts(); ?>
	<div id="content" role="main" class="hassidebar">
	<?php
		if ( isset( $_REQUEST['projectid'] ) ) :
			get_template_part( 'loop', 'project-posts' );
		elseif ( isset( $_REQUEST['committeeid'] ) ) :
			get_template_part( 'loop', 'committee-posts' );
		else:
			get_template_part( 'loop' );
		endif;
	endif;
	?>
	</div><!-- content -->
	<div class="hassubtitle"><!-- so we know to increase the top margin -->
		<?php get_sidebar(); ?>
	</div>
</div><!-- page -->

<?php get_footer();