<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 * sidebar used on blog and interior pages
 */
?>

	<aside id="projects-sidebar" role="complementary">
		<ul class="projectcontainer">
			<li>
				<ul id="project-title">
					<li><h2><?php the_title(); ?></h2></li>
				</ul>
				<ul id="project-icons">
					<li><?php rotary_show_project_icons(); ?></li>
				</ul>
				
	<?php //get the project start and end dates ?>
	<?php $startDate = DateTime::createFromFormat('Ymd', get_field( 'rotary_project_date' ) ); ?>
	<?php $endDate = DateTime::createFromFormat('Ymd', get_field( 'rotary_project_end_date' ) ); ?>
	<?php if ( get_field( 'long_term_project' ) ) : ?>
		<?php $longTermClass = ' longterm'; ?>
		<?php if ( isset( $startDate )  && isset( $endDate )  ) : ?>
			 <?php if ( $startDate  !=  $endDate  ) : ?>
			 	<ul id="project-date">
					<li id="project-calendar-image">
						<img class="aligncenter project-calendar" src="<?php echo get_template_directory_uri() ?>/rotary-sass/images/project-calendar.png" alt="project calendar image" />
					</li>
					<li id="project-date-text">
						<div class="project-date">
							<span class="day"><?php echo $startDate->format('jS'); ?></span>
							<span class="month"><?php echo $startDate->format('F'); ?></span>
							<span class="year"><?php echo $startDate->format('Y'); ?></span>
							<?php if ( '' != trim( get_field( 'rotary_project_end_date' ) ) ) : ?>
								<br /><span>To</span><br />
								<span class="day"><?php echo $endDate->format('jS'); ?></span>
								<span class="month"><?php echo $endDate->format('F'); ?></span>
								<span class="year"><?php echo $endDate->format('Y'); ?></span>
							<?php else : ?>
								<span> (ongoing)</span>
							<?endif; ?>
                	     </div>
					</li>
				</ul>
			 <?php endif; ?>
		<?php endif; ?>
	<?php else : ?>
		<?php $longTermClass = ''; ?>
				<ul id="project-date">
					<li id="project-calendar-image">
						<img class="aligncenter project-calendar" src="<?php echo get_template_directory_uri() ?>/rotary-sass/images/project-calendar.png" alt="project calendar image" />
					</li>
					<li id="project-date-text">
						<div class="project-date">
							<p class="dayweek"><?php echo $startDate->format('l'); ?></p>
							<span class="day"><?php echo $startDate->format('jS'); ?></span>
							<span class="month"><?php echo $startDate->format('F'); ?></span>
							<span class="year"><?php echo $startDate->format('Y'); ?></span>
                	     </div>
					</li>
				</ul>
	<?php endif; ?>
	
	<?php $location = get_field('rotary_project_location'); ?>
	<?php if( !empty($location) ) : ?>
				<ul id="project-address">
					<li id="project-addresstext">
						<div  class="location">
							<h3 class="meetingsite">Address</h3>
							<?php $address = preg_replace('/,/', '<br />', $location["address"], 1);  ?>
							<?php echo $address; ?>
							<p class="instructions"><a href="http://maps.google.com/maps?daddr=<?php echo $location['address'] ?>" target="_blank">Larger Map</a></p>
						</div>
					</li>
				</ul>
				<ul id="project-map">
					<li>
						<div  class="acf-map<?php echo $longTermClass; ?>">
							<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>" data-address="<?php echo $location['address']; ?>">
							</div>
						</div>
					</li>
				</ul>
	<?php else: ?>
				<ul id="project-map" class="clearleft">
				</ul>
	<?php endif; ?>
			
			</li>
		</ul><!--  end of projectcontainer -->
		<ul id="project-categories-photos-tags-container">
			<li>
				<h3><?php _e( 'Categories', 'Rotary' ); ?></h3>
				<ul>
					<?php $terms = wp_get_post_terms( get_the_id(), 'rotary_project_cat' ); ?>
						<?php if ($terms) : ?>
						<?php foreach ($terms as $term) : ?>
						 <?php    
							 $taxonomy = get_taxonomy('rotary_project_cat'); ?>					 							
						 <li class="cat-item"><a href="<?php echo trailingslashit(site_url() .'/'. $taxonomy->rewrite['slug'] .'/'.$term->slug); ?>"><?php echo $term->name; ?></a></li>				
				<?php endforeach; ?>
		    <?php endif; ?>
				</ul>
				<h3><?php _e( 'Photographs', 'Rotary' ); ?></h3>
				<ul class="clearfix" >
					<?php if(get_field('rotary_project_picture_gallery')): ?>
						<li class="speakerthumbs">
						<?php while(has_sub_field('rotary_project_picture_gallery')): ?>
						<?php $image = wp_get_attachment_image_src( get_sub_field('rotary_project_picture'), 'full' ); ?>
						<a class="fancybox" rel="gallery1" href="<?php echo  $image[0]?>" title="">
							<?php $image = wp_get_attachment_image_src( get_sub_field('rotary_project_picture'), 'thumbnail' ); ?>
							<img class="alignleft" src="<?php echo  $image[0]?>" alt="" title=""/></a>
						<?php endwhile; ?>
						</li>
					<?php endif; ?>
				</ul>
				<h3><?php _e( 'Tags', 'Rotary' ); ?></h3>
				<ul class="tagcloud">
					<?php $terms = wp_get_post_terms( get_the_id(), 'rotary_project_tag' ); ?>
						<?php if ($terms) : ?>
						<?php foreach ($terms as $term) : ?>
							<li class="cat-item"><a href="<?php echo trailingslashit(site_url() .'/rotary_project_tag/'.  $term->slug); ?>"><?php echo $term->name; ?></a></li>				
				<?php endforeach; ?>
		    <?php endif; ?>
				</ul>
			</li>
		</ul><!--  end of project-categories-photos-tags-container -->
	</aside>