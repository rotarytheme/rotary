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
<style>

.calendar-sidebar-button {
	width:150px;
	margin-bottom:10px;
}

</style>

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
	                		<a class="rotarybutton-largeblue calendar-sidebar-button" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_speakers" ><?php echo _e( 'New Program', 'Rotary'); ?></a> 
		                 </li>
		                 <?php endif;?>
		            	<?php if( current_user_can( 'publish_posts' )) :?>
					    <li id="new-project-button">            		
	                		<a class="rotarybutton-largeblue calendar-sidebar-button" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_projects" ><?php echo _e( 'New Event/Project', 'Rotary'); ?></a> 
		                 </li>
		                 <li id="new-event-button">            		
	                		<a class="rotarybutton-largeblue calendar-sidebar-button" href="<?php echo admin_url(); ?>post-new.php?post_type=ecp1_event" ><?php echo _e( 'New Meeting', 'Rotary'); ?></a> 
		                 </li>
		                 <?php endif;?>
		                </ul>
		            </li>
			<?php }
			if(current_user_can('manage_options')){ ?>
			      <a class="widgetedit" href="<?php echo admin_url(); ?>widgets.php"><?php echo _e( 'Edit Widgets', 'Rotary'); ?></a>
			  <?php  }
			if ( is_active_sidebar( 'calendar-sidebar' ) ) : 
				dynamic_sidebar( 'calendar-sidebar' ); 
			endif;
			?>
		</ul>
	</aside>