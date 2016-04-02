<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>

	<aside id="home-sidebar" role="complementary">
		<ul>

<?php
	/* When we call the dynamic_sidebar() function, it'll spit out
	 * the widgets for that widget area. If it instead returns false,
	 * then the sidebar simply doesn't exist, so we'll hard-code in
	 * some default sidebar stuff just in case.
	 */
	if(current_user_can('manage_options')){ ?>
      <a class="widgetedit" href="<?php echo admin_url(); ?>widgets.php"><?php _e( 'Edit Widgets', 'Rotary'); ?></a>
  <?php  } 
	if ( ! dynamic_sidebar( 'home-widget-area' ) ) : ?>
	

			<li>
				<h3><?php _e( 'Archives', 'Rotary' ); ?></h3>
				<ul>
					<?php wp_get_archives( 'type=monthly' ); ?>
				</ul>
			</li>

			

		<?php endif; // end primary widget area ?>
		</ul>


	
	</aside>