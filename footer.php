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
		<p class="alignleft"><?php echo _e( 'Web Design', 'rotary' ); ?>: <a href="http://www.carolinatorres.com/" target="_blank">Carolina Torres</a> <?php echo _e( 'Web Development', 'rotary' ); ?>: <a href="http://www.koolkatwebdesigns.com/" target="_blank">Merrill M. Mayer</a></p>
		<p class="alignright">&copy; <?php echo date('Y'); ?> <a href="<?php  echo site_url();?>" target="_blank"><?php  echo $clubname;  ?></a>.<?php echo _e( 'All rights reserved', 'rotary' ); ?>.</p>		

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
