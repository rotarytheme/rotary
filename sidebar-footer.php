<?php
/**
 * The Footer widget areas.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?>

<?php
	/* The footer widget area is triggered if any of the areas
	 * have widgets. So let's check that first.
	 *
	 * If none of the sidebars have widgets, then let's bail early.
	 */

	$facebook = get_theme_mod( 'rotary_facebook', '' );
	$twitter = get_theme_mod( 'rotary_twitter', '' );  
	$linkedin = get_theme_mod( 'rotary_linkedin', '' );  

	if (   ! is_active_sidebar( 'first-footer-widget-area'  )
		&& ! is_active_sidebar( 'second-footer-widget-area' )
		&& ! is_active_sidebar( 'third-footer-widget-area'  )
		&& ! is_active_sidebar( 'fourth-footer-widget-area' )
		&& ! $facebook
		&& ! $twitter
		&& ! $linkedin
	)
		return;
	// If we get this far, we have widgets. Let do this.
?>

<?php if ( is_active_sidebar( 'first-footer-widget-area' ) ) : ?>
		<ul>
			<?php dynamic_sidebar( 'first-footer-widget-area' ); ?>
		</ul>
<?php else: //placeholder if no widget?>  
		<ul>
			<li>&nbsp;</li>
		</ul>           
<?php endif; ?>

<?php if ( is_active_sidebar( 'second-footer-widget-area' ) ) : ?>
		<ul class="secondary wide">
			<?php dynamic_sidebar( 'second-footer-widget-area' ); ?>
		</ul>
        
<?php else: //placeholder if no widget?>  
		<ul class="secondary wide">
			<li>&nbsp;</li>
		</ul>   
<?php endif; ?>

    <ul class="secondary">
	<?php if( $facebook || $twitter|| $linkedin ) : ?>
	    <li>
	    <h3><?php echo _e( 'Follow Us', 'rotary' ); ?></h3>
	            <?php  if( $facebook ) { ?>
						<li><a href="<?php echo $facebook; ?>" class="social facebook" target="_blank"><?php echo _e( 'Like us on Facebook' ,'rotary');?></a></li>
				<?php	} ?>
	    		<?php  if( $twitter ) { ?>
						<li><a href="<?php echo $twitter; ?>" class="social twitter" target="_blank"><?php echo _e( 'Follow us on Twitter' ,'rotary');?></a></li>
				<?php	} ?>
	            <?php  if( $linkedin ) { ?>
						<li><a href="<?php echo $linkedin; ?>" class="social linkedin" target="_blank"><?php echo _e( 'Connect on LinkedIn' ,'rotary');?></a></li>
				<?php	} ?>
	    </li>
	<?php endif;?>

<?php if ( is_active_sidebar( 'third-footer-widget-area' ) ) : ?>
		<li>
			<?php dynamic_sidebar( 'third-footer-widget-area' ); ?>
		</li>
        
<?php else: //placeholder if no widget?>  
			<li>&nbsp;</li>
<?php endif; ?>
	</ul>   
