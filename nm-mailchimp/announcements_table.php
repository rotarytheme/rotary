<?php
/**
 * Displays the current set of announcements in a table format suitable for emailing
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary HTML5 3.2
 */

?>


<!--  As per http://blog.mailchimp.com/turn-any-web-page-into-html-email-part-2/ ? -->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/nm-mailchimp/email.css" />
<?php include( 'dynamic_styles.php' );?>

<table id="container-table">
<tr>
<td>
    <table id="branding">
    	<tr>
    		<td>
		      <?php  $clubname = get_theme_mod( 'rotary_club_name', '' );  ?>
		      <?php  $rotaryClubBefore = get_theme_mod( 'rotary_club_first', false); ?>
	            <h1>
	            	<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
	            <?php rotary_club_header($clubname, $rotaryClubBefore);?>
					</a>
	            </h1>
    		</td>
    	</tr>
    	<tr>
    		<td>
    			<h2><?php echo $heading; ?></h2>
    		</td>
    	</tr>
    </table>

	<table>
		<tr>
			<td class="announcements-container">
			 	<?php 
			 	$context = 'email';
				if ( is_array( $announcements )  ) : 
					$count = count( $announcements );
					if($count > 0 ) :
						foreach( $announcements as $announcement ) : 
							$extra_classes = ''; 
							$announcementsDisplayed++;
							if( $announcement ) :
								include ( get_template_directory() . '/loop-single-announcement.php');
								if( round( $count/2, 0, PHP_ROUND_HALF_UP ) == $announcementsDisplayed ) { echo '</td><td class="announcements-container">'; }
							endif;
						 endforeach; //end comment loop 
					 endif;
				 endif; //end is_array check
				 
				if ( 0 == $announcementsDisplayed && !$speakerdate ) :
					?>	<p><?php echo __( 'There are no active announcements'); ?></p>
				<?php  endif; ?>
			</td>
		</tr>
	</table>

</td>
</tr>
</table>
	
	
				