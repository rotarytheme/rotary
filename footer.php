<?php
/**
 * The template for displaying the footer.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>

	<footer id="footer" role="contentinfo">
		<?php get_sidebar( 'footer' );?>
		
		<section id="colophon">
		<p class="alignleft"><?php echo _e( 'Web Design', 'rotary' ); ?>: <a href="http://www.carolinatorres.com/" target="_blank">Carolina Torres</a> <?php echo _e( 'Web Development', 'rotary' ); ?>: <a href="http://www.koolkatwebdesigns.com/" target="_blank">Merrill M. Mayer</a></p>
		<p class="alignright">&copy; <?php echo date('Y'); ?> <a href="<?php  echo site_url();?>" target="_blank"><?php  echo get_theme_mod( 'rotary_club_name', '' );  ?>
		</a>. <?php echo _e( 'All rights reserved', 'rotary' ); ?>.</p>
		<p id="twentyfive-tag" class="aligncenter"><?php echo _e( 'For assistance in deploying and hosting this website template for your club please contact our technical partners at TwentyFive', 'rotary' ); ?></p>
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