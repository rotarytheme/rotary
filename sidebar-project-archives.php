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

<aside id="secondary" role="complementary">
	<ul>
		<?php
		if(current_user_can('edit_page')){ 
			$currentPostType = get_post_type();?>
		
			<li class="newpost">
				<ul >
					<li>
						<a class="rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_projects"><?php _e( 'New Project', 'Rotary'); ?></a>
					</li>
				</ul>
			</li>
			<?php }
		
		if(current_user_can('manage_options')){ ?>
			<a class="widgetedit" href="<?php echo admin_url(); ?>widgets.php"><?php _e( 'Edit Widgets', 'Rotary'); ?></a>
		<?php }
		if ( is_active_sidebar( 'projects-widget-area' ) ) : 
			dynamic_sidebar( 'projects-widget-area' );
		endif; 
?>
	</ul>
</aside>