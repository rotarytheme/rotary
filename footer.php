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

<?php
	get_sidebar( 'footer' );
?>
	
    <ul class="secondary">
    <li>
    <h3>Follow Us</h3>
    	<ul>
    		<?php  $social = get_theme_mod( 'rotary_twitter', '' );  
				if ($social) { ?>
					<li><a href="<?php echo $social; ?>" class="social twitter" target="_blank">Follow us on Twitter</a></li>
			<?php	} ?>
            <?php  $social = get_theme_mod( 'rotary_facebook', '' );  
				if ($social) { ?>
					<li><a href="<?php echo $social; ?>" class="social facebook" target="_blank">Friend us on Facebook</a></li>
			<?php	} ?>
            <?php  $social = get_theme_mod( 'rotary_linkedin', '' );  
				if ($social) { ?>
					<li><a href="<?php echo $social; ?>" class="social linkedin" target="_blank">Become a contact</a></li>
			<?php	} ?>
		</ul>
    </li>
    </ul>
<section id="colophon">
<p class="alignleft">Web Design: <a href="http://www.carolinatorres.com/" target="_blank">Carolina Torres</a> Web Development: <a href="http://www.koolkatwebdesigns.com/" target="_blank">Merrill M. Mayer</a></p>
<p class="alignright">&copy; <?php echo date('Y'); ?> <a href="<?php  echo site_url();?>" target="_blank"><?php  echo get_theme_mod( 'rotary_club_name', '' );  ?>
</a>. All rights reserved.</p>
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