<?php
/**
 * The Header for the slideshow.
 *
 * Displays all of the <head> section
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<!--[if IE]>
<meta http-equiv="x-ua-compatible" content="IE=edge" />
<![endif]-->
<title><?php
 
    global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );
 
    ?></title>
  <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/rotary-sass/images/favicon.ico" />    
<link rel="profile" href="http://gmpg.org/xfn/11" />

<!--[if IE]>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_directory' ); ?>/rotary-sass/stylesheets/ie.css" />
<![endif]-->
<!--[if lte IE 8 ]>
  <style type="text/css">
  	#mainmenu ul ul {
  	top:52px;
  }
  </style>
<![endif]-->

<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
 
<?php
    /* We add some JavaScript to pages with the comment form
     * to support sites with threaded comments (when in use).
     */
    if ( is_singular() && get_option( 'thread_comments' ) )
        wp_enqueue_script( 'comment-reply' );
 ?> 


<?php 
    /* Always have wp_head() just before the closing </head>
     * tag of your theme, or you will break many plugins, which
     * generally use this hook to add elements to <head> such
     * as styles, scripts, and meta tags.
     */
    wp_head();
?>
<?php 
    /* show custom styles set in theme customizer
     * 
     */
     $customCSS = get_theme_mod( 'rotary_custom_css', false);
     if ( $customCSS ) { ?>
	     <style type="text/css">
	     	<?php echo $customCSS; ?>
	     </style>
    <?php }
?>
<!--[if lte IE 7]>
<script src="<?php echo get_bloginfo('template_directory').'/includes/js/lte-ie7.js'; ?>" type="text/javascript"></script >
<![endif]-->
</head>
<?php  $bodyClass = 'white slideshow';  ?> 
<body <?php body_class($bodyClass); ?>>
 <div id="wrapper">
    <header role="banner">
    	<div id="branding">
	      <?php  $clubname = get_theme_mod( 'rotary_club_name', '' );  ?>
	      <?php  $rotaryClubBefore = get_theme_mod( 'rotary_club_first', false); ?>
	            <h1>
	            <?php rotary_club_header($clubname, $rotaryClubBefore);?>
	            </h1>
    	</div>
    </header>