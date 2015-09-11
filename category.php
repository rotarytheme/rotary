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
                <h2 class="pagesubtitle"><?php
					printf( __( 'Category Archive %s', 'Rotary' ), '<br /><span>' . single_cat_title( '', false ) . '</span>' );
				?></h2>
				<?php
					$category_description = category_description();
					if ( ! empty( $category_description ) )
						echo '' . $category_description . '';
				?>
                <div id="content" role="main"> 
				<?php get_template_part( 'loop', 'category' );
				?>
				</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>