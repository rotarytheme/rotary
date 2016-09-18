<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<meta name="viewport" content="width=device-width, initial-scale=1">
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
		echo ' | ' . sprintf( __( 'Page %s', 'rotary' ), max( $paged, $page ) );
 
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
<?php  $bodyClass = 'white';  ?> 
<body <?php body_class($bodyClass); ?>>
 <div id="wrapper">
    <section id="signin">
     <?php if (is_user_logged_in()) { 
	   $currentuser = wp_get_current_user();
	   echo '<p class="loggedin"><span>'.sprintf( __('You are currently logged in as %s', 'rotary'), $currentuser->display_name ) .'</span>'. wp_loginout($_SERVER['REQUEST_URI'], false ) .'</p>';
	 } 
    else {
     $args = array(
		'label_log_in' => __( 'log In', 'rotary' ),
		'label_username' => __( 'username:', 'rotary' ),
        'label_password' => __( 'password:', 'rotary' ),
        'remember' => false); 
    	wp_login_form($args); 
    }   ?> 
    </section>
    <header role="banner">
    	<div id="branding">
	   	<?php if(current_user_can('manage_options')){ ?>
	      	<a class="headeredit" href="<?php echo admin_url(); ?>customize.php"><?php echo _e('Edit Header', 'rotary');?></a>
	  	<?php  }
	      $rotaryLogo = get_theme_mod( 'rotary_club_logo', 0 ); 
	      if ( !$rotaryLogo ) {
		      $clubname = get_theme_mod( 'rotary_club_name', '' ); 
		      $rotaryClubBefore = get_theme_mod( 'rotary_club_first', false);
				?>
	            <h1>
	            <?php
				if ( !is_front_page() ) { ?>
	            	<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
	            <?php }  ?>
	            <?php rotary_club_header($clubname, $rotaryClubBefore );?>
	             <?php if ( !is_front_page() ) { ?>
					</a>
	              <?php }  ?>  
	            </h1>
	      <?php 
	      } else { ?>
            <?php
			if ( !is_front_page() ) { ?>
            	<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
			<?php }  ?>
			<img src="<?php echo $rotaryLogo; ?>"></a> <?php 

		}?>
				<?php $isclub = get_theme_mod( 'rotary_club_district', '1' ); ?>
				<div class="membership-address-container">
			        <section id="membership">
			        <h2><?php echo ( $isclub ) ? __( 'Become a member', 'Rotary' ) : __( 'Join Rotary', 'rotary' ); ?></h2>
			        <?php  $pageID = get_theme_mod( 'rotary_more_info_button', '' );  ?>
					<?php if ($pageID) {?>
			          <a class="rotarybutton-largegold" href="<?php echo get_permalink($pageID);?>"><?php _e('Get More Info', 'Rotary'); ?></a>
			        <?php }?>
			
			        </section>
			        <section id="meetingaddress">
			        <h2><?php echo ( $isclub ) ? __( 'MEETING SITE ADDRESS', 'Rotary' ) : __( 'MAILING ADDRESS', 'Rotary' ); ?></h2>
			        <?php  
			        	$meetingaddress = get_theme_mod( 'rotary_meeting_location', '' );
						$location =  get_option( 'club_location' );
					   $telephone = get_theme_mod( 'rotary_telephone', '');
					   $dayofweek = get_theme_mod( 'rotary_meeting_day', '');
					   $doors_open = get_theme_mod( 'rotary_doors_open', '');
					   $program_starts = get_theme_mod( 'rotary_program_starts', '');
					   $program_ends = get_theme_mod( 'rotary_program_ends', '');
					   if ($meetingaddress) {
							if($location) { ?>
								<p><?php if( $isclub ) { // don't put the map on a mailing address ?>
									<a target="_blank" href="https://www.google.com/maps/place/<?php echo $location['address'];?>/@<?php echo $location['lat'];?>,<?php echo $location['lng'];?>,19z">
								<?php }?>
								<?php echo nl2br($meetingaddress);?></a></p>
							<?php } else { ?>
								<p><?php echo nl2br($meetingaddress);?></p>
						   <?php }
						}
					   if( $telephone ) {  ?>
					  		<p id="telephone"><?php echo sprintf( __( 'Tel: %s', 'Rotary'), $telephone )?></p>
					  <?php }  ?>
					  <?php if( $dayofweek ) {  ?>
					  		<p id="meetingday"><?php echo sprintf( __( 'We meet every %s', 'Rotary'), $dayofweek )?></p>
					  <?php }  ?>
					  <?php if ( $isclub ) {?>
						  <p id="doors_open"><?php echo sprintf( __( 'Doors open: %s', 'Rotary' ), $doors_open ); ?></p>
						  <p id="progrom_start_end"><?php echo sprintf( __( 'Program: %s to %s', 'Rotary'), $program_starts, $program_ends ); ?></p>
					  <?php }?>
			        </section>
			    </div>
    	</div>
        <?php /* Our navigation menu.  If one isn't filled out, wp_nav_menu falls back to the 'Rotary_menu' function which can be found in functions.php.  The menu assiged to the primary position is the one used.  If none is assigned, the menu with the lowest ID is used.  */ ?>
        <?php wp_nav_menu( array( 'container_id'=> 'mainmenu', 'container' => 'nav', 'fallback_cb' => 'Rotary_menu', 'theme_location' => 'primary' ) ); ?>
    </header>
    