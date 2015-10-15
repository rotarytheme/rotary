<?php
/**
 * The template for displaying Category Archive pages.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<h1 class="pagetitle"><span><?php echo rotary_get_blog_title();?></span></h1>

<div id="page" class="blog">
<h2 class="pagesubtitle">
	<span class="pagesubtitle-h4"><?php echo __( 'Category Archive', 'Rotary' );?></span><br>
	<span class="pagesubtitle-h1"><?php echo single_cat_title( '', false ); ?></span>
</h2>

	<div id="content" role="main" class="hassidebar">
		<?php
			$category_description = category_description();
			if ( ! empty( $category_description ) )
				echo '' . $category_description . '';
		?>
		<?php get_template_part( 'loop', 'category' );
		?>
	</div>
	<div class="hassubtitle">
		<?php get_sidebar(); ?>
	</div>
</div><!-- page -->
<?php get_footer(); ?>