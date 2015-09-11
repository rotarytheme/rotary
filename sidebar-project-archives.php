<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 * sidebar used on blog and interior pages
 */
?>

	<aside id="secondary" role="complementary">
		<ul>

<?php

if(current_user_can('edit_page')){ 
	$currentPostType = get_post_type();?>
 		<li class="newpost">
            <ul >
                <li>
                	<?php if ('rotary_speakers' == $currentPostType ) {?>
                		<a class="rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_speakers">New Speaker</a>
                	<?php } elseif ('rotary-committees' == $currentPostType && isset($_REQUEST['committeeid'])) { ?>               		
                		<a class="rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php?committeeid=<?php echo $_REQUEST['committeeid']?>" target="_blank">Committee News</a> 
                	<?php } elseif ('rotary_projects' == $currentPostType && isset($_REQUEST['projectid'])) { ?>               		
                		<a class="rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php?projectid=<?php echo $_REQUEST['projectid']?>" target="_blank">New Project Update</a> 	             		
					<?php } else { ?>
						<a class="rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php">New Post</a>     
					<?php } ?>
                 </li>
                </ul>
            </li>

	<?php }

if(current_user_can('manage_options')){ ?>
      <a class="widgetedit" href="<?php echo admin_url(); ?>widgets.php">Edit Widgets</a>
  <?php  }
if ( is_active_sidebar( 'projects-widget-area' ) ) : ?>


				<?php dynamic_sidebar( 'projects-widget-area' ); ?>


<?php endif; ?>
		</ul>
	</aside>