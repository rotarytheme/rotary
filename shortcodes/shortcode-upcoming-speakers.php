<?php

function rotary_get_upcoming_speakers_shortcode_html( $atts ) {
	extract( shortcode_atts( array(
	'show' => '4',
	), $atts ) );
	$args = array(
			'post_type' => 'rotary_speakers',
			'posts_per_page'  => $show,
			'order' => 'ASC',
			'orderby' => 'meta_value',
			'meta_key' => 'speaker_date',
			'meta_query' => array(
					array(
							'key' => 'speaker_date',
							'value' => date('Ymd'),
							'type' => 'DATE',
							'compare' => '>='
					)
			)
	);
	$the_query = new WP_Query( $args );
	$postCount = 0;
	$clearLeft = '';
	ob_start(); ?>
	<div class="upcoming-program-shortcode">
		<div class="home-upcoming-program-ribbon"><h2><?php _e( 'Upcoming Speakers', 'rotary'); ?></h2></div>
		<div id="home-upcoming-programs" class="home-upcoming-programs clearfix">
	
		<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
			<?php $postCount++;
		if ($postCount % 2 == 0) {
			$clearLeft='';
		}
		else {
			$clearLeft = 'clearleft';
		}
	?>
			 <article id="post-<?php the_ID(); ?>" <?php post_class($clearLeft); ?>>
					<?php $date = DateTime::createFromFormat('Ymd', get_field('speaker_date')); ?>
	
	                <div class="home-upcoming-programs-speaker-date">
	                	<span class="dayweek"><?php echo $date->format('l'); ?></span>
	                	<span class="day"><?php echo $date->format('d'); ?></span>
	                	<span class="month"><?php echo $date->format('F'); ?></span>
	                	<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
	                </div>
	                <div class="home-upcoming-program-details">
	                <?php $speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
	                	<h3 class="speakername"><?php echo $speaker?></h3>
	                	<p class="speaker-title"><?php the_field( 'speaker_title' ); ?>	</p>
						<p class="speaker-company"><?php the_field('speaker_company'); ?></p>
						<p class="speaker-program-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
	                </div><!--.home-upcoming-program-details-->
			 </article>
		<?php endwhile; // End the loop. Whew. ?>
		<?php //now add a new post button ?>
		<?php  if( current_user_can('create_speaker_programs') ) : ?>
		      <article>
		      <div class="home-upcoming-programs-speaker-date"></div>
			      <div class="home-upcoming-program-details newspeaker">
					<a class="post_new_link rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_speakers">New Speaker</a>
			      </div>
			    </article>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
		</div><!--.home-upcoming-programs-->
	
	</div><!--.upcoming-programs-shortcode-->
<?php  return ob_get_clean();
}