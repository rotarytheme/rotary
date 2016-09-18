<?php
/**
 * The template for displaying search forms in Rotary WordPress Them
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>
	<form method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label for="s" class="assistive-text"><?php _e( 'Search', 'rotary' ); ?></label>
		<input type="text" class="field" id="s" name="s" placeholder="<?php esc_attr_e( 'Search', 'rotary' ); ?>" />
		<input type="image" class="submit searchsubmit" name="submit" value="<?php esc_attr_e( 'Search', 'rotary' ); ?>" src="<?php echo get_template_directory_uri();?>/rotary-sass/images/search-btn.png" alt="Search" />
        <input type="submit" class="submit searchsubmit2" name="submit" value="<?php esc_attr_e( 'Search', 'rotary' ); ?>" />
	</form>
