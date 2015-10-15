<?php

/*gets the featured post*/

function rotary_get_featured_shortcode_html( $atts ){
	extract( shortcode_atts( array(
	'header' => 'Latest News',
	'fetch'  => 'next'
			), $atts ) );
			if (post_type_exists( 'rotary_speakers' ) && 'next' == $fetch )  {
				$args = array(
						'posts_per_page' => 1,
						'post_type' => 'rotary_speakers',
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
			}
			// Added by PAO to show the latest, completed Reveille
			elseif (post_type_exists( 'rotary_speakers') && 'last' == $fetch ) {
				$args = array(
						'posts_per_page' => 1,
						'post_type' => 'rotary_speakers',
						'order' => 'DESC',
						'orderby' => 'meta_value',
						'meta_key' => 'speaker_date',
						'meta_query' => array(
								'relation' => 'AND',
								array(
										'key' => 'speaker_date',
										'value' => date('Ymd'),
										'type' => 'DATE',
										'compare' => '<='
								),
								array(
										'key' => 'speaker_program_notes',
										'value' => '',
										'type' => 'CHAR',
										'compare' => '>'
								)
						)
				);
			}
			else {
				$args = array(
						'posts_per_page' => 1,
						'category_name' => 'featured',
				);
			}
			ob_start();
			$query = new WP_Query( $args );
			global $more;
	if ( $query->have_posts() ) : ?>
		<div id="featured">
        <?php  while ( $query->have_posts() ) : $query->the_post(); ?>
         <?php  $more = 0; ?>
		<section class="featuredheader">
        	<h3><?php echo $header ?></h3>
        	<?php if (post_type_exists( 'rotary_speakers')) {
		$speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
        		<p class="featuredspeakername"><span><?php echo $speaker; ?></span></p>
        	<?php
	}
	else { ?>
	        	<p>by <span><?php the_author_meta('user_firstname');?>&nbsp;<?php the_author_meta('user_lastname');?> </span></p>

        	<?php } ?>
        </section>
        <h1><a href="<?php the_permalink()?>"><?php the_title(); ?></a></h1>
        <?php
	if (post_type_exists( 'rotary_speakers')) {
		$content = ( 'next' == $fetch ) ? trim(get_field('speaker_program_content')) : trim(get_field('speaker_program_notes')) ;
	}
	else {
		$content = apply_filters(get_the_content());
	}
	if (strlen( $content ) > 1024 ) {
		$content = rotary_truncate_text( $content, 1024, '', false, true ) . '<a href="'.get_permalink().'"> ...continue reading</a>';
	} ?>
        <section class="featuredcontent">
           <?php  if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
		the_post_thumbnail('medium'); ?>
				<div class="hasthumb">
					<?php echo $content; ?>
				</div>
			<?php }
	else {?>
            	<div class="nothumb">
        			<?php echo $content; ?>
            	</div>
           <?php  } ?>
        </section>
		<?php endwhile; ?>
 		</div>
 		<div id="featuredbottom">
		</div>
    <?php endif;
	// Reset Post Data
	wp_reset_postdata();
	return ob_get_clean();
}
