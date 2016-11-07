<?php

/**
 * Class RotaryBlogRoll
 * @author paulosborn
 *
 */
class RotaryBlogRoll {
	private $category = array();
	private $type;
	private $posts;
	
	public $atts;
	public $shortcode_html;

	function __construct() {
		$arguments = func_get_args();
		if(!empty($arguments)) {
            foreach($arguments[0] as $key => $property)
                if(property_exists($this, $key))
                    $this->{$key} = $property;
    	}
	    extract( shortcode_atts(
			    	array(
				    	'type' 			=>'post,speaker,project',
				    	'category' 		=> '',
				    	'posts'			=> 6,
			    	), $this->atts, 'blogroll' ));
	   
	   $this->category = explode(',', $category );
	   
	   $type = str_replace( 'speaker', 'rotary_speakers', $type );
	   $type = str_replace( 'project', 'rotary_projects', $type );
	   $this->type = explode(',', $type );
	   $this->posts = $posts;
	   $this->get_shortcode_html();
	}


	
	function get_shortcode_html() {
		// Prepare the query arguments to fetch the appropriate comments depending where this is being called from	
		$this->args['order'] = 'DESC';
		$this->args['orderby'] = 'date' ;
		$this->args['category_name'] = implode( ',', $this->category );
		$this->args['post_type'] = $this->type;
		$this->args['status'] = 'approve';
		$this->args['posts_per_page'] = $this->posts;		
		
		$query = new WP_Query( $this->args );
		
		$header = '<div class="blogroll-container">';
		$footer = '</div>';
			
		ob_start();
		
			//include_once( TEMPLATEPATH . 'loop.php' );
			
			$postCount = 0;
			$clearLeft='';
			while ( $query->have_posts() ) : $query->the_post();
				rotary_output_blogroll();
				//comments_template( '', true ); 
			endwhile; // End the loop. Whew.
		
			$posts = ob_get_clean();
			
			// get a footer to close the announcements and the announcement-container divs
			//$footer = '</div></div>';
			
			$this->shortcode_html = $header . $posts . $footer;
			$this->blogroll = true;
		wp_reset_postdata();
	}
}