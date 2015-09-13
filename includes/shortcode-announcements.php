<?php

/** 
 * rotary_get_announcements_html
 * replaces rotary_get_committee_announcements function.
 *
 * Paul Osborn created to separate file for each shortcode
 * All the classes have been renamed from comment / committee, to announcements, and reference to home removed
 * to enable the shortcode to live on any page
 * 
 * Redundant Styles:
 * .home #content .committee-comment-date
 * .home #content .comment
 * .home #content .comment div
 * .home #content .commentcommittetext
 *
 *
 * NEW Styles:  Here are some starter styles
 * 
 * 
			.shortcode-announcements {
			    width: 50%;
			    margin: 0 auto;
			}
			.announcement {
			    margin: 0;
			    padding: 0;
			    width: 100%;
			}
			.announcement-header {}
			#content h4.announcement-title {
			    clear: left;
			    display: inline;
			    margin: 0;
			    font-size: 22px;
			    padding-bottom: 8px;
			    float: left;
			    text-transform: none;
			    color: #58585A;
			    font-family: "Open Sans Condensed", Arial, Helvetica, sans-serif;
			    font-weight: 700;
			    padding: 0;
			}
			.announcement-date {
			    display: inline;
			    padding-bottom: 8px;
			    font-family: "Open Sans Condensed", Arial, Helvetica, sans-serif;
			    font-size: 19px;
			    color: #58585A;
			    white-space: nowrap;
			    margin: 0px 0 8px;
			    float: right;
			}
			span.day {
			}
			span.month {
			}
			span.year {
			}
			.announcement-body {
			    clear: both;
				padding-bottom: 5px;
			    border-top: 1px solid #B8A3A0;
			    padding-top: 8px;
			}
			.announcement-call-to-action {}
			.announcement-hr {
			    height: 0;
			}

 *
 * @access public
 * @param mixed $atts
 * @return void
 */


function rotary_get_announcements_html( $atts ){
	extract( shortcode_atts(
			array(
					'lookback' => 10
			), $atts, 'announcements' ));
	
	$args = array(
			'posts_per_page' => -1,
			'post_type' => array ( 'rotary-committees', 'rotary_projects' ),
			'orderby' => 'type',
			'order' => 'ASC'
	);
	$committeeArray = array();
	$commentDisplayed = 0;
	ob_start();
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) : ?>
	<div class="shortcode-announcements">
		 <div class="announcements-container">
		 <?php  while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php  $committeeArray[get_the_title()] = get_permalink() . '?open=open'; ?>
				<?php
				$args = array(
					'order' => 'DESC',
					'orderby' => array('type', 'title'),
					'post_type' => array ('rotary-committees', 'rotary_projects'),
					'status' => 'approve',
					'type' => 'comment',
					'post_id' => get_the_id(),
					'number' => 1
				);
				$comments = get_comments( $args ); ?>
				<?php if ( is_array( $comments )) : ?>
		                <?php $count = count($comments); ?>
		                <?php if ( $count > 0 ) : ?>      
							<?php foreach($comments as $comment) : ?>
								<?php $date = new DateTime($comment->comment_date); ?>
								<?php $today = new DateTime(); ?>
								<?php $interval = $today->diff($date); ?>
								<?php $announcement_text = apply_filters ("the_content", $comment->comment_content); ?>
								<?php //only show comments less than 10 days old, or whatever the lookback is (in days) ?>
								<?php if( abs($interval->days) < $lookback ) : ?>	
									<?php $commentDisplayed++; ?>
									<div class="announcement">
									<div class="announcement-header">
										<h4 class="announcement-title">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h4>
										<div class="announcement-date">
											<span class="day"><?php echo $date->format( 'd') ; ?></span>
											<span class="month"><?php  echo $date->format( 'M' ); ?></span>
											<span class="year"><?php echo $date->format( 'Y' ); ?></span>
										</div>
									</div>									
									<div class="announcement-body">
										<?php echo $announcement_text; ?>			
									</div>
									<div class="announcement-call-to-action"></div>
								<hr class="announcement-hr" />
								</div>
								<?php endif; //end check for comment age over 10 (lookback period) days ?>
							<?php  endforeach; //end comment loop ?>
						<?php  endif; //end check for comment count ?>
					<?php  endif; //end is_array check?>
		<?php endwhile; //end wp_query loop ?>
		<?php if ( 0 == $commentDisplayed ) :?>
			<p>No announcements have been made in the last <?php  echo $atts['lookback']; ?> days old</p>
		<?php  endif; ?>
			<?php if (!is_user_logged_in()) { ?>
			<p>Please <?php echo wp_loginout( site_url(), false ); ?> to make an announcement</p>
			<?php }
	else { ?>
				<select id="committeeselect" name="committeeselect">
					<option value="">-- Select a committee to add a new announcement --</option>
					<?php
		$project_printed = false;
		foreach($committeeArray as $key => $value):
		    if ( !$project_printed && strpos( $value, 'project' ) ) :
		    	$project_printed = true; ?>
		    	<option value="">-- Select a project to add a new announcement --</option>
		    <?php endif;
			echo '<option value="'.$value.'">'.$key.'</option>';
		endforeach;
?>
					</select>
			<?php } ?>

			</div>
		</div>

	<?php endif; ?>

	<?php // Reset Post Data
	wp_reset_postdata();
	return ob_get_clean();

}