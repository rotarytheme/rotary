<?php

/**
 * The gravitylist plugin restricts the post-type to WP_Post - so we have to get round that
 * if we have the gravitylist shortcode at the current post
 * we have to load the datatables JS and CSS
 */
function rotary_spgfdt_wp_enqueue_scripts()
{	
	global $post;
	if( has_shortcode( $post->post_content, 'gravitylist') )
	{
		wp_enqueue_script( 'datatable', plugins_url( '/datatables/js/jquery.dataTables.min.js' , __FILE__ ), array( 'jquery' ) );
		wp_enqueue_style( 'datatable', plugins_url( '/datatables/css/jquery.dataTables.min.css' , __FILE__ ) );
		add_action( 'wp_footer', array( &$this, 'spgfdt_wp_footer') );
	}
}
add_action( 'wp_enqueue_scripts', 'rotary_spgfdt_wp_enqueue_scripts' );



/** 
 * get the gravity forms shortcode and add some
 * helpfull JS and CSS
 */
function rotary_spgfle_wp_enqueue_scripts()
{
	global $post;
	if( has_shortcode( $post->post_content, 'gravityform') ) 
	{
		/**
		 * enqueue the JS and CSS for readonly
		 */
		wp_enqueue_script( 'spgf-readonly', plugins_url( '/js/spgf_readonly.js' , __FILE__ ), array( 'jquery' ) );	
		wp_enqueue_style( 'spgf-readonly', plugins_url( '/css/spgf_readonly.css' , __FILE__ ) );	
		
	} 
	if( has_shortcode( $post->post_content, 'gravitylist') ) 
	{
		/**
		 * add the wp_head action
		 */
		add_action( 'wp_head', 'spgfle_wp_head'  );
		
	}
}
add_action( 'wp_enqueue_scripts', 'rotary_spgfle_wp_enqueue_scripts' );


/**
 * add some JS to the header
 */
function spgfle_wp_head()
{

	?>
	<script type="text/javascript">
		function SetHiddenFormSettings(id, mode, source) {
			document.getElementById('gform_entry_id').value=id;
			document.getElementById('gform_entry_mode').value=mode;
			document.getElementById('gform_entry_source').value=source;
			document.forms["gravitylist"].submit();
		}	
	</script>
	<?php
 
}