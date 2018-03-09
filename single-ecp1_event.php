<?php
get_header();
?>

<div id="et-main-area">
	<div id="main-content">
		<div class="entry-content">
			<div class="et_pb_section  et_pb_section_0 et_section_specialty">
				<div class="et_pb_row et_pb_row_3-4_1-4">
					<div class="et_pb_column et_pb_column_3_4 et_pb_column_0 et_pb_specialty_column">
					
			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'et_pb_post' ); ?>>
					<div class="entry-content">
						<?php the_content();?>
					</div> <!-- .et_post_meta_wrapper -->
					
				</article> <!-- .et_pb_post -->
		<?php endwhile; ?>
					
				</div><!-- et_pb_column_3_4 et_pb_specialty_column-->
				<div class="et_pb_column et_pb_column_1_4 et_pb_column_1 et_pb_column_single">
				<?php 
				if ( is_active_sidebar( 'sidebar-events' ) ) : ?>
					<div class="et_pb_widget_area et_pb_widget_area_right clearfix et_pb_module et_pb_bg_layout_light et_pb_sidebar_0">
						<?php dynamic_sidebar( 'sidebar-events' ); ?>
					</div>
				<?php endif; ?>
				</div> <!-- et_pb_column_single -->
			</div> <!-- et_pb_row_3-4_1-4 -->
		</div> <!-- et_section_specialty -->
	
		<?php wp_reset_postdata();?>
	
		</div> <!-- entry-content -->
	</div> <!-- .main-content -->
</div> <!-- et-main-area -->
				
				
<?php get_footer(); ?>
				
				