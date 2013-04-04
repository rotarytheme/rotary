<?php
/**
 * The template for displaying search forms in Rotary WordPress Them
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>
	<form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label for="s" class="assistive-text"><?php _e( 'Search', 'rotary' ); ?></label>
		<input type="text" class="field" name="s" id="s" placeholder="<?php esc_attr_e( 'Search', 'twentyeleven' ); ?>" />
		<input type="image" class="submit" name="submit" id="searchsubmit" value="<?php esc_attr_e( 'Search', 'rotary' ); ?>" src="<?php echo get_template_directory_uri();?>/rotary-sass/images/search-btn.png" alt="Search" title="Search" />
        <input type="submit" class="submit" name="submit" id="searchsubmit2" value="<?php esc_attr_e( 'Search', 'rotary' ); ?>"  alt="Search" title="Search" />
	</form>
