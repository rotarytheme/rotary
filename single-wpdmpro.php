<?php
/**
 * The template for displaying all single posts
*
* @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
*
* @package WordPress
* @subpackage Twenty_Seventeen
* @since 1.0
* @version 1.0
*/


get_header(); ?>
<h1 class="pagetitle">
	<span><?php echo  'File Download' ;  ?></span>
</h1>

<div id="page">
	<div id="content" role="main" class="hassidebar">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="sectioncontainer">
            	<div class="downloadheader" id="blog">
                	<div class="downloadcontent">
                    <?php the_title('<h2>', '</h2>'); ?>
							<?php the_content(); ?>
                        </div>
                    </div><!--.sectioncontent-->
                </div> <!--.sectionheader-->
			</div><!--.sectioncontainer-->
		</article>
    </div>
    
	<?php get_sidebar( 'downloads' ); ?>
	
  </div>
  
<?php get_footer(); ?>
