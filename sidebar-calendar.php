<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 * sidebar used on the calendar
 */
?>

	<aside id="secondary" role="complementary">
		<ul>
			<?php 
			if(current_user_can('edit_page')){ 
				$currentPostType = get_post_type();
				?>
		 		<li class="newpost">
		            <ul >
		            	<?php if( current_user_can( 'create_speaker_programs' )) :?>
					    <li id="new-speaker-button">            		
	                		<a class="rotarybutton-largeblue calendar-sidebar-button" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_speakers" ><?php  _e( 'New Program', 'Rotary'); ?></a> 
		                 </li>
		                 <?php endif;?>
		            	<?php if( current_user_can( 'publish_posts' )) :?>
					    <li id="new-project-button">            		
	                		<a class="rotarybutton-largeblue calendar-sidebar-button" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_projects" ><?php _e( 'New Event/Project', 'Rotary'); ?></a> 
		                 </li>
		                 <li id="new-event-button">            		
	                		<a class="rotarybutton-largeblue calendar-sidebar-button" href="<?php echo admin_url(); ?>post-new.php?post_type=ecp1_event" ><?php _e( 'New Meeting', 'Rotary'); ?></a> 
		                 </li>
		                 <?php endif;?>
		                </ul>
		            </li>
			<?php }
			if(current_user_can('manage_options')){ ?>
			      <a class="widgetedit" href="<?php echo admin_url(); ?>widgets.php"><?php _e( 'Edit Widgets', 'Rotary'); ?></a>
			  <?php  }
			if ( is_active_sidebar( 'calendar-sidebar' ) ) : 
				if ( ! dynamic_sidebar( 'calendar-sidebar' )) : ?>
					<li>
						<h3><?php _e( 'Calendar Sidebar', 'Rotary' ); ?></h3>
						<ul>
							<li>
								<?php if(current_user_can('manage_options')){ ?>
									<a  href="<?php echo admin_url(); ?>widgets.php"><?php _e( 'Edit Widgets', 'Rotary'); ?></a>
								<?php } else { ?>
									<p><?php _e( 'Add widgets here'); ?></p>
								<?php }?>
							</li>
						</ul>
					</li>
				<?php endif; // end primary widget area 
			endif;
			?>
		</ul>
	</aside>