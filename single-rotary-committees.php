<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<?php get_sidebar('committee'); ?>
<h1 class="pagetitle"><span><?php the_title();  ?></span></h1>
<?php if(current_user_can('edit_page')) { ?>
		<a href="<?php echo admin_url();?>post-new.php?post_type=rotary_projects&committee=<?php the_id(); ?>" class="new-project-link">New Project</a>
<?php } ?>	
	<div id="content" class="committee" role="main">
	<?php comments_template( '/committee-comments.php' ); ?> 
	<div class="committeeblogtop">
				</div>
				
		<?php get_template_part( 'loop', 'single-committee' ); ?>
    </div>

<?php get_footer(); ?>