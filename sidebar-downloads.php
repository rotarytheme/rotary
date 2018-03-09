<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

if ( ! is_active_sidebar( 'sidebar-downloads' ) ) {
	return;
}
?>

<aside id="secondary" role="complementary">
	<ul>
	<?php dynamic_sidebar( 'sidebar-downloads' ); ?>
	</ul>
</aside><!-- #secondary -->
