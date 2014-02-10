<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>

	<aside id="speaker-sidebar" role="complementary">
		<ul>
			<li>
				<div class="speaker-sidebar-thumbnail-container">
					<ul>
						<li>
							<?php if ( has_post_thumbnail() ) {
								the_post_thumbnail();
							}
							?>

						</li>
					</ul>
				</div>	
			</li>
			<li>
				<div class="speaker-side-container">
					
										<h3 class="speakerbio"><?php _e( 'About the Speaker', 'Rotary' ); ?></h3>
					<ul>
						<li>
							<?php the_field( 'speaker_bio' ); ?>
						</li>
					</ul>
				</div>	
				<div class="speaker-side-bottom"></div>
			</li>

			

			<li>
				<div>
				<h3><?php _e( 'Categories', 'Rotary' ); ?></h3>
				<ul>
					<?php wp_list_categories( array('taxonomy' => 'rotary_speaker_cat', 'title_li' => '' )); ?> 
				</ul>
				</div>
			</li>
			<li>
				<div>
				<h3><?php _e( 'Photographs', 'Rotary' ); ?></h3>
				<ul>
					<?php if(get_field('speaker_program_images')): ?>
						<li class="speakerthumbs">
						<?php while(has_sub_field('speaker_program_images')): ?>
						<?php $image = wp_get_attachment_image_src( get_sub_field('speaker_program_image'), 'full' ); ?>
						<a class="fancybox" rel="gallery1" href="<?php echo  $image[0]?>" title="">
							<?php $image = wp_get_attachment_image_src( get_sub_field('speaker_program_image'), 'thumbnail' ); ?>
							<img class="alignleft" src="<?php echo  $image[0]?>" alt="" title=""/></a>
						<?php endwhile; ?>
						</li>
					<?php endif; ?>
					
				</ul>
				</div>
			</li>
				<li class="clearleft">
				<div>
				<h3><?php _e( 'Tags', 'Rotary' ); ?></h3>
				<div class="tagcloud">
					<?php wp_tag_cloud( array('taxonomy' => 'rotary_speaker_tag')); ?>
				</div></div>
			</li>
 <?php // end speaker widget area ?>
		</ul>


	
	</aside>