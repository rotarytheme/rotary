<?php
/**
 * The template for displaying the footer.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

$clubname = rotary_club_name();

?>

	<footer id="footer" role="contentinfo">
		<?php get_sidebar( 'footer' );?>
		
		<section id="colophon">
			<p class="alignleft"><?php  _e( 'Web Design', 'Rotary' ); ?>: <a href="http://www.carolinatorres.com/" target="_blank">Carolina Torres</a> <?php _e( 'Web Development', 'Rotary' ); ?>: <a href="http://www.koolkatwebdesigns.com/" target="_blank">Merrill M. Mayer</a></p>
			<p class="alignright">&copy; <?php echo date('Y'); ?> <a href="<?php  echo site_url();?>" target="_blank"><?php  echo $clubname;  ?></a>.&nbsp;&nbsp;<?php _e( 'All rights reserved', 'Rotary' ); ?>.</p>		
		</section>		
	</footer>

</div><!--end wrapper-->
<?php
	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */

	wp_footer();
?>
</body>
</html>
