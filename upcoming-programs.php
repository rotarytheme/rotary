<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-blog-header.php'); 
$paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
$posts_per_page = 9;  
$offset = 0 < $paged ? $posts_per_page * ( $paged - 1 ) : 0;


$args = array(  
            'post_type' => 'rotary_speakers',
            'paged' => $paged,
            'offset'     =>  $offset,
            'posts_per_page'  => $posts_per_page,
            'order' => 'ASC',
            'orderby' => 'meta_value',
             'meta_key' => 'speaker_date',
             'meta_query' => array(
              array(
			  	'key' => 'speaker_date',
			  	'value' => date('Ymd'),
			  	'type' => 'DATE',
			 	'compare' => '>='
			 	)	
			 ) 
           );  
$the_query = new WP_Query( $args );

// The Loop
	 /* If there are no posts to display, such as an empty archive page */ 
 ?>

    		<nav class="prevnext">
        		
        		<?php $prevLink = get_previous_posts_link( __( '&lt; Older speakers', 'Rotary' ), $the_query->max_num_pages); ?>
        		<?php //append the page/1 or jquery won't reload ?>
        		<?php if (0 != strlen($prevLink) && false === strpos ( $prevLink, 'page')) {
	        		$prevLink = str_replace('.php/', '.php/page/1/', $prevLink);
        		}?>
        		<div class="nav-previous"><?php echo $prevLink; ?></div>
        		<div class="nav-next"><?php echo get_next_posts_link( __( 'Newer speakers &gt; ', 'Rotary' ), $the_query->max_num_pages ); ?></div>
    		</nav>

<?php if ( ! $the_query->have_posts() ) : ?>
        <div class="inner">
        	<h2><?php _e( 'Not Found', 'Rotary' ); ?></h2>
            <p><?php _e( 'Apologies, but no results were found for the requested archive.', 'Rotary' ); ?></p>
         </div>   
<?php endif; ?>
	 
<?php $postCount = 0;
$clearLeft='';
?>
<div id="content" role="main" class="fullwidth"> 
<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
 		<?php /* Display navigation to next/previous pages when applicable */ ?>
		
        <?php  
   
		  if ($postCount % 3 != 0) {
			  $clearLeft='';
		  }
		  else {
			  $clearLeft='clearleft';
		  }
			$postCount++;  
		?>

     
        <article id="post-<?php the_ID(); ?>" <?php post_class($clearLeft); ?>>
         	<div class="sectioncontainer">
            	<div class="sectionheader" id="blog-<?php the_ID(); ?>" >
                	<div class="sectioncontent">
			<header>
				<?php $date = DateTime::createFromFormat('Ymd', get_field('speaker_date')); ?>

                
                <?php $speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
                <h2 class="speakername"><?php echo $speaker?></h2>
                <h3><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'Rotary' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><span class="speakerday"><?php echo strftime( '%A', $date->getTimestamp() ); //$date->format('l'); ?></span> <?php echo strftime( '%B %e, %G', $date->getTimestamp() ); //$date->format('M d, Y'); ?></a></h3>
            </header>
 
   
                <?php if ( has_post_thumbnail() ) {
						the_post_thumbnail();
					}
               ?>
	                
                
                   
                 
              <?php
                   //program notes are filled in after a speakers visit. 
                   //Since these are upcoming speakers (future speakers), we show the upcoming content
					$programNotes = trim(get_field('speaker_program_content'));			
					$programNotes = preg_replace('/<img[^>]+./','', $programNotes);
					$programNotes = strip_tags($programNotes);
					?>
                

			  <p><?php echo $programNotes; ?></p>
              <p class="continue"><a href="<?php the_permalink();?>">Keep Reading...</a></p>  
              
  
            <footer class="meta">
            <p>
                <?php edit_post_link( __( 'Edit', 'Rotary' )); ?>
            </p>                
                
                 
            </footer>
               				</div><!--.sectioncontent-->
                </div> <!--.sectionheader-->
			</div><!--.sectioncontainer-->
		</article>
 
            
 
 
 
<?php endwhile; // End the loop. Whew. ?>

</div>
<?php /* Restore original Post Data 
 * NB: Because we are using new WP_Query we aren't stomping on the 
 * original $wp_query and it does not need to be reset.
*/
 wp_reset_postdata();

?>