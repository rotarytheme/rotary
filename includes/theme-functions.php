<?php
/**
 * Rotary theme functions and definitions
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
/*remove the admin bar*/

add_filter('show_admin_bar', '__return_false');
add_action( 'wp_print_styles', 'rotary_deregister_styles', 100 );
function rotary_deregister_styles() {
	wp_deregister_style( 'wp-admin' );
}
//add class to post edit link on single speaker page
add_filter('edit_post_link', 'rotary_edit_post_link');

function rotary_edit_post_link($output) {
	if (! is_home() &&  ! is_archive() && ( 'rotary-slides' != get_post_type())) :
		$output = str_replace('class="post-edit-link"', 'class="post-edit-link rotarybutton-largewhite"', $output);

		endif;

	return $output;
}
//show the rotary club header
function rotary_club_header($clubname, $rotaryClubBefore=false) {
	if ($rotaryClubBefore) { ?>
	    <?php if ($clubname) { ?>
				 <span class="clubtype clubbefore">Rotary Club Of</span>
				 <span class="clubname"><?php echo $clubname;?></span>
		<?php }
	}
	else {
		if ($clubname) { ?>
				<span class="clubname namebefore"><?php echo $clubname;?></span>
        <?php }  ?>
			   <span class="clubtype">Rotary Club</span>
     <?php   }

}
//Add the filter to override the standard shortcode
add_filter( 'img_caption_shortcode', 'rotary_img_caption_shortcode', 10, 3 );
function rotary_img_caption_shortcode( $a , $attr, $content = null) {

	extract(shortcode_atts(array(
				'id'    => '',
				'align' => 'alignnone',
				'width' => '',
				'caption' => ''
			), $attr));

	if ( 1 > (int) $width || empty($caption) )
		return $content;



	if ( $id ) $id = 'id="' . esc_attr($id) . '" ';

	return '<div ' . $id . 'class="wp-caption ' . esc_attr($align) . '" style="width: ' . (10 + (int) $width) . 'px"><div class="inner-caption clearfix">'
		. do_shortcode( $content ) . '<p class="wp-caption-text">' . $caption . '</p></div><div class="wp-caption-bottom"></div></div>';
}


add_filter('wp_nav_menu_items','rotary_add_search_box', 10, 2);
function rotary_add_search_box($items) {

	ob_start();
	get_search_form();
	$searchform = ob_get_contents();
	ob_end_clean();

	$items .= '<li class="search">' . $searchform . '</li>';

	return $items;
}
add_filter( 'wp_nav_menu_items', 'add_home_link', 10, 2 );
function add_home_link($items, $args) {

	if (is_front_page())
		$class = 'class="current_page_item homepage"';
	else
		$class = 'class="homepage"';

	$homeMenuItem =
		'<li ' . $class . '>' .
		$args->before .
		'<a href="' . home_url( '/' ) . '" title="Home">' .

		$args->link_before . '<span class="screen-reader-text">Home</span>' . $args->link_after .'<img src="'. get_template_directory_uri().'/rotary-sass/images/home-icon.png" alt="home" title="home"/></a>' .

		$args->after .
		'</li>';

	$items = $homeMenuItem . $items;

	return $items;
}

/**
 * overwrite default theme stylesheet uri
 * filter stylesheet_uri
 * @see get_stylesheet_uri()
 */
add_filter('stylesheet_uri','rotary_stylesheet_uri',10,2);
function rotary_stylesheet_uri($stylesheet_uri, $stylesheet_dir_uri){

	return $stylesheet_dir_uri.'/rotary-sass/stylesheets/style.css';
}
/** Tell WordPress to run Rotary_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'rotary_setup' );

if ( ! function_exists( 'rotary_setup' ) ):
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since rotary 1.0
	 */
	function rotary_setup() {
		//support editor style
		add_editor_style();
		// This theme uses post thumbnails
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 130, 130, true);

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Make theme available for translation
		// Translations can be filed in the /languages/ directory
		load_theme_textdomain( 'rotary', TEMPLATEPATH . '/languages' );

		$locale = get_locale();
		$locale_file = TEMPLATEPATH . "/languages/$locale.php";
		if ( is_readable( $locale_file ) )
			require_once( $locale_file );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
				'primary' => __( 'Primary Navigation', 'rotary' ),
			) );
	}
endif;

if ( ! function_exists( 'rotary_menu' ) ):
	/**
	 * Set our wp_nav_menu() fallback, rotary_menu().
	 *
	 * @since rotary 1.0
	 */
	function rotary_menu() {
		$excludepage = get_page_by_title ('Home');
		echo '<nav id="mainmenu" class="menu-main-container"><ul id="menu-main" class="menu"><li><a href="'.get_bloginfo('url').'">Home</a></li>';
		wp_list_pages('title_li=&exclude='.$excludepage->ID);
		echo '</ul></nav>';
	}
endif;
//content filter for tags
//add_filter('the_content','rotary_add_tags_to_title');
//function rotary_add_tags_to_title($content) {
// return $content;
//}

//shortcodes
add_action( 'init', 'rotary_register_shortcodes');
function rotary_register_shortcodes(){
	add_shortcode('rotary-reveille-header', 'rotary_reveille_header_function');
	add_shortcode( 'UPCOMING_SPEAKERS', 'rotary_upcoming_programs_function' );
	add_shortcode( 'FEATURED_ITEM', 'rotary_get_featured_post' );
	add_shortcode( 'COMMITTEE_ANNOUNCEMENTS', 'rotary_get_committee_announcements' );
}
function rotary_reveille_header_function($atts, $content = null) {
	extract( shortcode_atts( array(
				'id' => 'inthisissue',
				'class' => 'sectionheader',
			), $atts ) );
	$content = rotary_parse_shortcode_content( $content ); ?>
    <div class="sectioncontainer">
		<div id="<?php echo $id ?>" class="<?php echo $class;?>">
           <div class="sectioncontent">
     		<?php echo $content; ?>
           </div>
    	</div>
    </div>
<?php }
/*
@param string $text String to truncate.
@param integer $length Length of returned string, including ellipsis.
@param string $ending Ending to be appended to the trimmed string.
@param boolean $exact If false, $text will not be cut mid-word
@param boolean $considerHtml If true, HTML tags would be handled correctly
@return string Trimmed string.
*/
function rotary_truncate_text($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
					// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
						// if tag is an opening tag
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = substr($text, 0, $length - strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}

/*gets the featured post*/

function rotary_get_featured_post($atts){
	extract( shortcode_atts( array(
	'header' => 'Latest News',
	'fetch'  => 'next'
			), $atts ) );
			if (post_type_exists( 'rotary_speakers' ) && 'next' == $fetch )  {
				$args = array(
						'posts_per_page' => 1,
						'post_type' => 'rotary_speakers',
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
			}
			// Added by PAO to show the latest, completed Reveille
			elseif (post_type_exists( 'rotary_speakers') && 'last' == $fetch ) {
				$args = array(
						'posts_per_page' => 1,
						'post_type' => 'rotary_speakers',
						'order' => 'DESC',
						'orderby' => 'meta_value',
						'meta_key' => 'speaker_date',
						'meta_query' => array(
								'relation' => 'AND',
								array(
										'key' => 'speaker_date',
										'value' => date('Ymd'),
										'type' => 'DATE',
										'compare' => '<='
								),
								array(
										'key' => 'speaker_program_notes',
										'value' => '',
										'type' => 'CHAR',
										'compare' => '>'
								)
						)
				);
			}
	else {
		$args = array(
			'posts_per_page' => 1,
			'category_name' => 'featured',
		);
	}
	ob_start();
	$query = new WP_Query( $args );
	global $more;
	if ( $query->have_posts() ) : ?>
		<div id="featured">
        <?php  while ( $query->have_posts() ) : $query->the_post(); ?>
         <?php  $more = 0; ?>
		<section class="featuredheader">
        	<h3><?php echo $header ?></h3>
        	<?php if (post_type_exists( 'rotary_speakers')) {
		$speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
        		<p class="featuredspeakername"><span><?php echo $speaker; ?></span></p>
        	<?php
	}
	else { ?>
	        	<p>by <span><?php the_author_meta('user_firstname');?>&nbsp;<?php the_author_meta('user_lastname');?> </span></p>

        	<?php } ?>
        </section>
        <h4><a href="<?php the_permalink()?>"><?php the_title(); ?></a></h4>
        <?php
	if (post_type_exists( 'rotary_speakers')) {
		$content = ( 'next' == $fetch ) ? trim(get_field('speaker_program_content')) : trim(get_field('speaker_program_notes')) ;
	}
	else {
		$content = apply_filters(get_the_content());
	}
	if (strlen($content) > 1024 ) {
		$content = rotary_truncate_text($content, 1024, '', false, true) . '<a href="'.get_permalink().'"> ...continue reading</a>';
	} ?>
        <section class="featuredcontent">
           <?php  if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
		the_post_thumbnail('medium'); ?>
				<div class="hasthumb">
					<?php echo $content; ?>
				</div>
			<?php }
	else {?>
            	<div class="nothumb">
        			<?php echo $content; ?>
            	</div>
           <?php  } ?>
        </section>
		<?php endwhile; ?>
 		</div>
 		<div id="featuredbottom">
		</div>
    <?php endif;
	// Reset Post Data
	wp_reset_postdata();
	return ob_get_clean();
}

function rotary_upcoming_programs_function($atts) {
	extract( shortcode_atts( array(
				'show' => '4',
			), $atts ) );
	$args = array(
		'post_type' => 'rotary_speakers',
		'posts_per_page'  => $show,
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
	$postCount = 0;
	$clearLeft = '';
	ob_start(); ?>
	<div class="home-upcoming-program-ribbon"><h2>Upcoming Speakers</h2></div>
	<div id="home-upcoming-programs" class="home-upcoming-programs clearfix">

	<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
		<?php $postCount++;
	if ($postCount % 2 == 0) {
		$clearLeft='';
	}
	else {
		$clearLeft = 'clearleft';
	}
?>
		 <article id="post-<?php the_ID(); ?>" <?php post_class($clearLeft); ?>>
				<?php $date = DateTime::createFromFormat('Ymd', get_field('speaker_date')); ?>

                <div class="home-upcoming-programs-speaker-date">
                	<span class="dayweek"><?php echo $date->format('l'); ?></span>
                	<span class="day"><?php echo $date->format('d'); ?></span>
                	<span class="month"><?php echo $date->format('F'); ?></span>
                	<?php edit_post_link( __( 'Edit', 'Rotary' ), '', '' ); ?>
                </div>
                <div class="home-upcoming-program-details">
                <?php $speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
                	<h3 class="speakername"><?php echo $speaker?></h3>
                	<p class="speaker-title"><?php the_field( 'speaker_title' ); ?>	</p>
					<p class="speaker-company"><?php the_field('speaker_company'); ?></p>
					<p class="speaker-program-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
                </div><!--.home-upcoming-program-details-->
		 </article>
	<?php endwhile; // End the loop. Whew. ?>
	<?php //now add a new post button ?>
	<?php  if(current_user_can('edit_page')){ ?>
	      <?php $clearLeft = ($clearLeft == 'clearleft' ? '' : ' clearleft');  ?>
	      <div class="newspeaker<?php echo $clearLeft; ?>">
			<a class="post_new_link rotarybutton-largewhite" href="<?php echo admin_url(); ?>post-new.php?post_type=rotary_speakers">New Speaker</a>
	      </div>
	<?php } ?>
	<?php wp_reset_postdata(); ?>
	</div><!--.home-upcoming-programs-->
<?php  return ob_get_clean();
}
function rotary_parse_shortcode_content( $content ) {

	$content = do_shortcode( shortcode_unautop( $content ) );
	$content = preg_replace('#^<\/p>|^<br \/>|<p>$#', '', $content);
	$content = str_replace('<p></p>', '', $content);

	return $content;
}
/**
 * Remove inline styles printed when the gallery shortcode is used.
 *
 * @since rotary HTML5 3.2
 */
add_filter( 'use_default_gallery_style', '__return_false' );

/**
 * @since rotary 1.0
 * @deprecated in rotary HTML5 3.2 for WordPress 3.1
 *
 * @return string The gallery style filter, with the styles themselves removed.
 */
function rotary_remove_gallery_css( $css ) {
	return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
}
// Backwards compatibility with WordPress 3.0.
if ( version_compare( $GLOBALS['wp_version'], '3.1', '<' ) )
	add_filter( 'gallery_style', 'rotary_remove_gallery_css' );

if ( ! function_exists( 'rotary_comment' ) ) :
	/**
	 * Template for comments and pingbacks.
	 *
	 * @since rotary 1.0
	 */
	function rotary_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
		case '' :
?>
	<article <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
			<?php echo get_avatar( $comment, 40 ); ?>
            <div>
			<?php printf( __( '%s says:', 'rotary' ), sprintf( '%s', get_comment_author_link() ) ); ?>
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<?php _e( 'Your comment is awaiting moderation.', 'rotary' ); ?>
			<br />
		<?php endif; ?>

		<p><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
		/* translators: 1: date, 2: time */
		printf( __( '%1$s at %2$s', 'rotary' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'rotary' ), ' ' );
		?><p>
         </div>
         <div class="commenttop"></div>
         <div class="commenttext">
		<?php comment_text(); ?>
        </div>

			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>

	<?php
		break;
	case 'pingback'  :
	case 'trackback' :
?>
	<article <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
		<p><?php _e( 'Pingback:', 'rotary' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'rotary'), ' ' ); ?></p>
	<?php
		break;
		endswitch;
	}
endif;
/**
 * Closes comments and pingbacks with </article> instead of </li>.
 *
 * @since rotary 1.0
 */
function rotary_comment_close() {
	echo '</article>';
}

/**
 * Adjusts the comment_form() input types for HTML5.
 *
 * @since rotary 1.0
 */
function rotary_comment_fields($fields) {
	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$fields =  array(
		'author' => '<p><label for="author">' . __( 'Name' ) . '</label> ' . ( $req ? '*' : '' ) .
		'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'email'  => '<p><label for="email">' . __( 'Email' ) . '</label> ' . ( $req ? '*' : '' ) .
		'<input id="email" name="email" type="email" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
		'url'    => '<p><label for="url">' . __( 'Website' ) . '</label>' .
		'<input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
	);
	return $fields;
}
add_filter('comment_form_default_fields','rotary_comment_fields');


/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 *
 * @updated rotary HTML5 3.2
 */
function rotary_remove_recent_comments_style() {
	add_filter( 'show_recent_comments_widget_style', '__return_false' );
}
add_action( 'widgets_init', 'rotary_remove_recent_comments_style' );

if ( ! function_exists( 'rotary_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post—date/time and author.
	 *
	 * @since rotary 1.0
	 */
	function rotary_posted_on() {
		if ('rotary_speakers' == get_post_type() ) {
			$date = DateTime::createFromFormat('Ymd', get_field('speaker_date'));
			printf( __( 'Speaker on <br/>%2$s', 'rotary' ),
				'meta-prep meta-prep-author',
				sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time datetime="%3$s" pubdate>%4$s</time></a>',
					get_permalink(),
					esc_attr( get_the_time() ),
					$date->format('Y-m-d'),
					$date->format('M j, Y')
				)
			);
		}
		else {
			printf( __( 'Posted on <br/>%2$s', 'rotary' ),
				'meta-prep meta-prep-author',
				sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time datetime="%3$s" pubdate>%4$s</time></a>',
					get_permalink(),
					esc_attr( get_the_time() ),
					get_the_date('Y-m-d'),
					get_the_date('M j, Y')
				)
			);
		}
	}
endif;

if ( ! function_exists( 'rotary_posted_in' ) ) :
	/**
	 * Prints HTML with meta information for the current post (category, tags and permalink).
	 *
	 * @since rotary 1.0
	 */
	function rotary_posted_in() {
		// Retrieves tag list of current post, separated by commas.
		echo '<div class="postedin">';
		$tag_list = get_the_tag_list( '', ', ' );
		if ( $tag_list ) {
			$posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'rotary' );
		} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
			$posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'rotary' );
		} else {
			$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'rotary' );
		}
		// Prints the string, replacing the placeholders.
		printf(
			$posted_in,
			get_the_category_list( ', ' ),
			$tag_list,
			get_permalink(),
			the_title_attribute( 'echo=0' )
		);
		echo '</div>';
	}
endif;
//custom post types for slideshows
add_action('init', 'rotary_slides_register');

function rotary_slides_register() {
	$labels = array(
		'add_new_item' => 'Add Slides Item',
		'edit_item' => 'Edit Slides Item',
		'new_item' => 'New Slides Item',
		'view_item' => 'View Slides Item',
		'search_items' => 'Search Slides'
	);

	$args = array(
		'label' => __('Slides'),
		'labels' => $labels,
		'singular_label' => __('Slides Item'),
		'query_var' => true,
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'rewrite' => array("slug" => "slides"),
		'supports' => array('title','editor', 'thumbnail', 'excerpt')
	);

	register_post_type( 'rotary-slides' , $args );
	// Register custom taxonomy
	$labels_taxo = array(
		'name' => _x('Slides Category', 'post type general name'),
		'all_items' => _x('All Slides', 'all items'),
		'add_new_item' => _x('Add Slides Category', 'adding a new item'),
		'new_item_name' => _x('New Slides Category Name', 'adding a new item'),
	);

}

/*gets the slide show*/
function rotary_get_slideshow(){
	$args = array(
		'order' => 'ASC',
		'post_type' => 'rotary-slides',
	);
	$query = new WP_Query( $args );
	$count = 0;

	if ( $query->have_posts() ) : ?>
	<div id="slideshowcontainer">
		<div id="slideshowleft">
        	<div id="slideshowright">
            	<div id="slideshow">
 <?php  while ( $query->have_posts() ) : $query->the_post();
		if (has_post_thumbnail()) {
			echo '<div class="slide';
			if ($count > 0) {
				echo ' hide';
			}
			echo'">';
			$count++;
			echo '<div class="slideinfo">';
			the_title('<h2>', '</h2>');
			echo '<p>'.get_the_excerpt().'</p>';
			$slidelink = get_post_meta(get_the_ID(), 'slidelink', true);
			if ($slidelink) {
				echo '<p><a href="'.$slidelink.'">Keep Reading...</a></p>';
			}
			else {
				echo '<p><a href="'.get_permalink().'">Keep Reading...</a></p>';
			}

			edit_post_link( __( 'Edit', 'Rotary' ), '<p>', '</p>' );
			echo '</div>'; //end slideinfo

			if ($slidelink) {
				echo '<a href="'.$slidelink.'">';
			}
			else {
				echo '<a href="'.get_permalink().'">';
			}
			the_post_thumbnail('slideshow-size');

			echo '</a></div>';  //end the slide
		}

	endwhile; ?>
				</div>	<!--end slideshow-->
            </div>	<!--end slideshowright-->
		</div>	<!--end slideshowleft-->

     	<div id="controls">
     		<a class ="pause" id="playpause" href="#"><span class="play">> Play</span><span class="pause"> > Pause</span></a>
     	<section id="navsection">
     	</section>
        <section id="sharing">
        <a id="shareshare" target="_blank" href="http://sharethis.com/share?title=<?php echo urlencode(get_the_title()) . '&amp;url=' . urlencode(get_permalink());?>">+ Share</a>
        <a id="facebookshare" class="icon-alone" target="_blank" href="https://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink()).'&amp;t='.urlencode(get_the_title()); ?>">
  <span class="screen-reader-text">Share on Facebook</span></a>
        <a id="twittershare" class="icon-alone" target="_blank" href="http://twitter.com/?status=<?php echo urlencode(get_permalink()); ?>">
  <span class="screen-reader-text">Share on Twitter</span></a>
        </section>
		</div>	<!--end controls-->


   </div>	<!--end slideshowcontainer-->

<?php endif;
	// Reset Post Data
	wp_reset_postdata();


}//custom images sizes for slideshow
if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'slideshow-size', 486, 313, true ); //(cropped)

}

add_filter('image_size_names_choose', 'rotary_image_sizes');
function rotary_image_sizes($sizes) {
	$new_sizes = array();

	$added_sizes = get_intermediate_image_sizes();

	// $added_sizes is an indexed array, therefore need to convert it
	// to associative array, using $value for $key and $value
	foreach( $added_sizes as $key => $value) {
		$new_sizes[$value] = $value;
	}

	// This preserves the labels in $sizes, and merges the two arrays
	$new_sizes = array_merge( $new_sizes, $sizes );
	return $new_sizes;
}

function rotary_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'rotary_excerpt_length', 999 );
function rotary_auto_excerpt_more( $more ) {
	if (is_archive()) {
		return '<a href="'. get_permalink() . '">' .' [&hellip;]</a>';
	}
}
add_filter( 'excerpt_more', 'rotary_auto_excerpt_more' );
//gets the blog title for the current posts page
function rotary_get_blog_title() {
	$blogPage = "Posts";
	$blogID = get_option( 'page_for_posts');
	if ($blogID) {
		$blogPage = get_the_title($blogID);
	}
	return $blogPage;
}
//custom meta box for slides
add_action( 'add_meta_boxes', 'rotary_add_slide_link_metabox');
function rotary_add_slide_link_metabox() {
	add_meta_box( 'slidelink', __( 'Slide Link' ),  'rotary_show_slide_link_metabox', 'rotary-slides', 'normal', 'high' );
}
add_action( 'save_post', 'rotary_save_slide_link_metabox', 10, 2);
function rotary_save_slide_link_metabox($post_id, $post) {
	if ( !isset( $_POST['rotary_slide_link_nonce'] ) || !wp_verify_nonce( $_POST['rotary_slide_link_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		return $post_id;
	}
	if (!isset($_POST['slidelink'])) {
		return $post_id;
	}
	/* Get the meta key. */
	$meta_key = 'slidelink';       /* Get the meta value of the custom field key. */
	$meta_value = get_post_meta( $post_id, $meta_key, true );
	$new_meta_value = strip_tags($_POST['slidelink']);

	/* If a new meta value was added and there was no previous value, add it. */
	if ( $new_meta_value && '' == $meta_value )
		add_post_meta( $post_id, $meta_key, $new_meta_value, true );

	/* If the new meta value does not match the old value, update it. */
	elseif ( $new_meta_value && $new_meta_value != $meta_value )
		update_post_meta( $post_id, $meta_key, $new_meta_value );
	/* If there is no new meta value but an old value exists, delete it. */
	elseif ( '' == $new_meta_value && $meta_value )
		delete_post_meta( $post_id, $meta_key, $meta_value );

}

function rotary_show_slide_link_metabox($object) {
	wp_nonce_field( basename( __FILE__ ), 'rotary_slide_link_nonce' );?>
		 <h3>Enter full URL to create a link for your slide</h3>
		 <p><label for="slidelink">Slide Link:<br />
	        <input id="slidelink" type="url" size="20" name="slidelink" value="<?php echo esc_attr( get_post_meta( $object->ID, 'slidelink', true ) ); ?>" /></label></p>
<?php }
//table for speaker program
function rotary_output_archive_table($term='') { ?>
	<tr>
				<?php $date = DateTime::createFromFormat('Ymd', get_field('speaker_date')); ?>
				<td><a href="<?php the_permalink();?>">speaker link</a></td>
				<td><?php echo $date->format('M d, Y'); ?></td>
				<?php $speakertitle = get_the_title();
	if (strlen($speakertitle) > 50 ) {
		$speakertitle = substr($speakertitle, 0, 50) . '...';
	} ?>
				<td><?php echo $speakertitle; ?></td>
				<?php $speaker = get_field('speaker_first_name').' '.get_field('speaker_last_name'); ?>
				<td><?php echo $speaker; ?></td>
				<?php $jobtitle = trim(get_field( 'speaker_title' ));
	$company = trim(get_field( 'speaker_company' ));
	if (count($company)) { ?>
					<?php $jobtitle .='<br/>'.$company ?>
				<?php } ?>

				<td><?php echo $jobtitle; ?></td>
				<td><?php echo $term;?></td>
			</tr>

<?php }
add_filter('get_previous_post_join', 'rotary_post_join');
add_filter('get_next_post_join', 'rotary_post_join');
add_filter('get_previous_post_where', 'rotary_prev_post_where');
add_filter('get_next_post_where', 'rotary_next_post_where');
add_filter('get_previous_post_sort', 'rotary_prev_post_sort');
add_filter('get_next_post_sort', 'rotary_next_post_sort');
add_filter('next_post_link', 'rotary_filter_next_post_link');
add_filter('previous_post_link', 'rotary_filter_previous_post_link');


function rotary_post_join($join) {
	global $wpdb;

	if ( 'rotary_speakers' == get_post_type() && is_single()) {
		$join = " INNER JOIN $wpdb->postmeta AS pm ON pm.post_id = p.ID";
	}
	return $join;

}
function rotary_prev_post_where($where) {
	global $wpdb, $post;
	$speakerDate = get_post_meta($post->ID, 'speaker_date', true);

	if ( 'rotary_speakers' == get_post_type() && is_single()) {
		$where = $wpdb->prepare(" WHERE pm.meta_key = %s AND pm.meta_value < '$speakerDate' AND p.post_type = %s AND p.post_status = 'publish'", 'speaker_date', 'rotary_speakers');
	}

	return $where;


}
function rotary_next_post_where($where) {
	global $wpdb, $post;
	$speakerDate = get_post_meta($post->ID, 'speaker_date', true);

	if ( 'rotary_speakers' == get_post_type() && is_single()) {
		$where = $wpdb->prepare(" WHERE pm.meta_key = %s AND pm.meta_value > '$speakerDate' AND p.post_type = %s AND p.post_status = 'publish'", 'speaker_date', 'rotary_speakers');
	}
	return $where;


}

function rotary_prev_post_sort($order) {

	if ( 'rotary_speakers' == get_post_type() && is_single()) {
		$order = " ORDER BY pm.meta_value DESC LIMIT 1";
	}

	return $order;
}
function rotary_next_post_sort($order) {
	if ( 'rotary_speakers' == get_post_type() && is_single()) {
		$order = " ORDER BY pm.meta_value ASC LIMIT 1";
	}
	return $order;
}
function rotary_filter_next_post_link($link) {
	if ( 'rotary_speakers' == get_post_type() && is_single()) {
		$next_post = get_next_post();
		if( is_object( $next_post ) ) {
			$speakerDate = get_post_meta($next_post->ID, 'speaker_date', true);
			$link = preg_replace('/<a(.+?)>.+?<\/a>/i',"<a$1><span>".date('l ', strtotime($speakerDate))."</span>".date('M dS, Y', strtotime($speakerDate))." &gt;</a>",$link);
		}	
	}
	return $link;
}

function rotary_filter_previous_post_link($link) {
	if ( 'rotary_speakers' == get_post_type() && is_single()) {
		$previous_post = get_previous_post();
		if( is_object( $previous_post ) ) {
			$speakerDate = get_post_meta($previous_post->ID, 'speaker_date', true);
			$link = preg_replace('/<a(.+?)>.+?<\/a>/i',"<a$1>&lt; <span>".date('l ', strtotime($speakerDate))."</span>".date('M dS, Y', strtotime($speakerDate))."</a>",$link);
		}	
	}
	return $link;

}
//custom category and tags for speakers
add_filter( 'pre_get_posts', 'rotary_pre_get_cats' );
function rotary_pre_get_cats($query) {
	if ($query->is_main_query()  && !is_admin() && 'rotary_speakers' == get_post_type()) {
		$taxonomy = $query->tax_query->queries[0]['taxonomy'];
		if ( isset($taxonomy) && ('rotary_speaker_cat' == $taxonomy || 'rotary_speaker_tag' == $taxonomy )) {
			$query->set('meta_key', 'speaker_date');
			$query->set( 'orderby', 'meta_value' );
		}
	}
	return $query;
}
//custom date archives
add_filter( 'pre_get_posts', 'rotary_pre_get_archive_posts' );
function rotary_pre_get_archive_posts($query) {
	if ($query->is_main_query() && is_post_type_archive('rotary_speakers') && !is_admin()) {
		//print_r($query->query_vars);
		//echo 'the year is '.$query->query_vars['year'];
		//assume year if month is set
		$speakerYear = $query->query_vars['year'];

		$speakerMonth = $query->query_vars['monthnum'];

		if( $speakerMonth) {
			$eStart = $speakerYear.'-'.$speakerMonth.'-01';
			$eEnd = $speakerYear.'-'.$speakerMonth.'-31';
			$query->set('meta_key', 'speaker_date');
			$meta_query = array(
				array(
					'key' => 'speaker_date',
					'value' => array( $eStart, $eEnd ),
					'compare' => 'BETWEEN',
					'type' => 'DATE'
				)
			);
		}
		//just year is set
		if( $speakerYear && !$speakerMonth) {
			$eStart = $speakerYear.'-01-01';
			$eEnd = $speakerYear.'-12-31';
			$query->set('meta_key', 'speaker_date');
			$meta_query = array(
				array(
					'key' => 'speaker_date',
					'value' => array( $eStart, $eEnd ),
					'compare' => 'BETWEEN',
					'type' => 'DATE'
				)
			);
		}
		$query->set( 'meta_query', $meta_query );
		$query->set( 'orderby', 'meta_value' );
	}
	return $query;
}
add_filter( 'posts_where' , 'rotary_archiveposts_where', 10, 2 );

function rotary_archiveposts_where( $where, $query_obj ) {
	if ($query_obj->is_main_query() && is_post_type_archive('rotary_speakers') && !is_admin()) {
		$newWhere = explode('AND', $where);
		$newWhere = array_slice($newWhere, 3);
		$where = 'AND '.implode('AND', $newWhere);
	}
	return $where;
}

//output the standard blog roll 
//also used for posts connected to committees by post to posts
function rotary_output_blogroll($postCount, $clearLeft) {
 		$postCount++; 
		  if ($postCount % 2 == 0) {
			  $clearLeft='';
		  }
		  else {
			  $clearLeft='clearleft';
		  }
			  
		?>

     
        <article id="post-<?php the_ID(); ?>" <?php post_class($clearLeft); ?>>
         	<div class="sectioncontainer">
            	<div class="sectionheader blogroll" id="blog-<?php the_ID(); ?>" >
                	<div class="sectioncontent">
			<header>
			    <?php $title = get_the_title(); ?>
			    <?php if (strlen($title) > 30 ) {
					$title = substr($title, 0, 30) . '...';
				} ?>
                <h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'Rotary' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php echo $title; ?></a></h2>
                               <div class="postdate">
                	<span class="alignleft">Posted by <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ))?>"><?php echo get_the_author();?></a></span>
                    <span class="alignright"><?php Rotary_posted_on(); ?></span>	
                </div>    
            </header>
 
   
                <?php $thumb = has_post_thumbnail(); ?>
                <?php if ( $thumb) { // check if the post has a Post Thumbnail assigned to it.
				        $attr = array(
							'class'	=> 'alignleft',
							);?>

  						<a href="<?php the_permalink(); ?> "><?php the_post_thumbnail('post-thumbnail', $attr);?></a>
					<?php } ?>
   
                <?php if ( $thumb) { ?> 
               		<section class="excerptcontainer"> 
               <?php } ?> 
             <?php if ( 'rotary_speakers' == get_post_type() ) { 
	             //program notes are filled in after a speakers visit. If the speaker has not yet been to the club, we show the upcoming content
					$programNotes = trim(get_field('speaker_program_notes'));
					if ('' == $programNotes)
					{
						$programNotes = trim(get_field('speaker_program_content'));
					}
					$programNotes = preg_replace('/<img[^>]+./','', $programNotes);
					$programNotes = strip_tags($programNotes);
					if (strlen($programNotes) > 200 ) {
						$programNotes = substr($programNotes, 0, 200) ;
					} 
					?>             
					<p><?php echo $programNotes; ?> <a href="<?php the_permalink();?>">[…]</a></p>					 
             <?php  } 
             else {
	             the_excerpt();
             }
             ?>

              <?php if ( $thumb) { ?> 
                </section>
              <?php } ?>    
              
  
            <footer class="meta">
            <p>
                <?php edit_post_link( __( 'Edit', 'Rotary' ), '', ' ' ); ?>
                <?php comments_popup_link( __( 'Leave a comment', 'Rotary' ), __( '1 Comment', 'Rotary' ), __( '% Comments', 'Rotary' ), 'commentspopup' ); ?></p>
                <?php if ( count( get_the_category() ) ) : ?>
                        <p><?php printf( __( 'Posted in %2$s', 'Rotary' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?></p>
                <?php endif; ?>
                <?php
                    $tags_list = get_the_tag_list( '', ', ' );
                    if ( $tags_list ):
                ?>
                        <p><?php printf( __( 'Tagged %2$s', 'Rotary' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?></p>
                <?php endif; ?>
                
                
                 
            </footer>
               				</div><!--.sectioncontent-->
                </div> <!--.sectionheader-->
			</div><!--.sectioncontainer-->
		</article>
       <?php return $postCount; ?>
	
<?php }