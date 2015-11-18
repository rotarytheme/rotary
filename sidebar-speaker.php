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
			<li id="speaker-sidebar-thumbnail-container">
				<?php if ( has_post_thumbnail() ) {	the_post_thumbnail('medium');} ?>
			</li>
			<li id="speaker-side-container">
				<h3 class="speakerbio"><?php _e( 'About the Speaker', 'Rotary' ); ?></h3>
				<ul>
					<li id="speaker-bio">
						<?php echo the_field( 'speaker_bio' ); ?>
					</li>
				</ul>
			</li>
			<li id="speaker-side-footer">
				<ul>
					<li id="categories">				
						<h3><?php _e( 'Categories', 'Rotary' ); ?></h3>
						<ul>
							<?php $terms = wp_get_post_terms( get_the_id(), 'rotary_speaker_cat' ); ?>
							<?php if ($terms) : ?>
								<?php foreach ($terms as $term) : ?>
									 <?php  $taxonomy = get_taxonomy('rotary_speaker_cat'); ?>					 							
									 <li class="cat-item"><a href="<?php echo trailingslashit(site_url() .'/'. $taxonomy->rewrite['slug'] .'/'.$term->slug); ?>"><?php echo $term->name; ?></a></li>				
								<?php endforeach; ?>
				    		<?php endif; ?>
						</ul>
					</li>
					<li id="photos">
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
					</li>
					<li id="tags" class="clearleft">
						<h3><?php _e( 'Tags', 'Rotary' ); ?></h3>
						<ul class="tagcloud">
							<?php $terms = wp_get_post_terms( get_the_id(), 'rotary_speaker_tag' ); ?>
								<?php if ($terms) : ?>
								<?php foreach ($terms as $term) : ?>
									<li class="cat-item"><a href="<?php echo trailingslashit(site_url() .'/rotary_speaker_tag/'.  $term->slug); ?>"><?php echo $term->name; ?></a></li>				
						<?php endforeach; ?>
				    	<?php endif; ?>
						</ul>
					</li>
				</ul>
			</li>
		</ul><!--end speaker widget area  -->
	</aside>