<?php 
/**
* N-Media Mailchimp Front Class
*/
class NM_MC_Front
{
	
	function __construct(){
		add_action( 'wp_enqueue_scripts', array($this, 'nm_mailchimp_front_scripts'));
		add_action( 'admin_enqueue_scripts', array($this, 'nm_mailchimp_admin_scripts'));
		add_action('admin_menu', array($this, 'nm_mailchimp_front_settings'));
		add_action('wp_ajax_nm_mc_front_save_settings', array($this, 'nm_mc_front_save_settings'));
		add_action('wp_ajax_nm_front_camp', array($this, 'nm_front_camp'));
		add_action('wp_head', array($this, 'insert_loader'));
	}

	function insert_loader(){
		global $post;
		echo '<div id="ajax-loader"></div><span class="nmid" data-nmid="'.$post->ID.'" ></span>';
	}

	function nm_mailchimp_admin_scripts($slug){
		if($slug == 'mailchimp-campaign_page_nm_mc_front_settings') {
			wp_enqueue_style( 'nm-mc-front-loader', get_template_directory_uri().'/nm-mailchimp/ajax-loader.css');
			wp_enqueue_script( 'nm-mc-admin', get_template_directory_uri().'/nm-mailchimp/admin.js', array( 'jquery' ));
		}
	}

	function nm_mailchimp_front_scripts() {
		wp_enqueue_style( 'nm-mc-front-loader', get_template_directory_uri().'/nm-mailchimp/ajax-loader.css');
		wp_enqueue_script( 'nm-mc-front', get_template_directory_uri().'/nm-mailchimp/script.js', array( 'jquery' ));
		wp_localize_script( 'nm-mc-front', 'nmOptions', array('ajaxurl' => admin_url( 'admin-ajax.php' )) );
	}

	function nm_mailchimp_front_settings() {
		add_submenu_page( 'nm_mailchimp', 'Frontend Mailchimp Settings', 'Front Campaigns', 'manage_options', 'nm_mc_front_settings', array($this, 'nm_render_settings_page') );
	}


	function nm_render_settings_page() {
		$saved_options = get_option( 'nm_mc_front_save_settings' );
		global $nm_mailchimp;
		$mc_list = $nm_mailchimp->mc-> lists -> getList();
		// API 0d1a02a59d31be4aef245003b42f7075-us1
		?>
			<div class="wrap">
				<div id="ajax-loader"></div>
				<h2>Front Auto Campaigns Settings</h2>

				<table class="widefat wp-list-table widefat fixed">
					<tr>
						<th>Select List</th>
						<td>
							<select id="nm_camp_list" class="widefat">
								<?php foreach ($mc_list['data'] as $camp) {
									echo '<option value="'.$camp['id'].'" '.selected( $saved_options['list_id'], $camp['id']).'>'.$camp['name'].'</option>';
								} ?>
							</select>
						</td>
						<td>
							<p class="description">Select the list to send this campaign (required)</p>
						</td>
					</tr>
					<tr>
						<th>Generate Text</th>
						<td><label><input type="checkbox" id="generate_text" <?php checked( $saved_options['generate_text'], 'true'); ?>>Generate Text</label></td>
						<td>
							<p class="description">Whether of not to auto-generate your Text content from the HTML content. Note that this will be ignored if the Text part of the content passed is not empty, defaults to false</p>
						</td>
					</tr>
					<tr>
						<th>Auto Tweet</th>
						<td><label><input type="checkbox" id="auto_tweet" <?php checked( $saved_options['auto_tweet'], 'true'); ?>>Auto Tweet</label></td>
						<td>
							<p class="description">
								If set, this campaign will be auto-tweeted when it is sent - defaults to false. Note that if a Twitter account isn't linked, this will be silently ignored
							</p>
						</td>
					</tr>
					<tr>
						<th>Auto Facebook Post</th>
						<td><input type="text" class="widefat" id="auto_post" value="<?php echo $saved_options['auto_post']; ?>"></td>
						<td>
							<p class="description">
								Facebook pages IDs each fb page id separated by comma
							</p>
						</td>
					</tr>
				</table>
				<br>
				<button class="button button-primary nm-save-settings">Save Settings</button>
			</div>
		<?php
	}

	function nm_mc_front_save_settings(){
		if (isset($_REQUEST)) {
			update_option( 'nm_mc_front_save_settings', $_REQUEST );
		}
	}

	function nm_front_camp(){

		$saved_options = get_option( 'nm_mc_front_save_settings' );

		global $nm_mailchimp;
		global $current_user;
		get_currentuserinfo();

		$campaigntype = $_REQUEST['campaigntype'];
		
		$type = 'regular';
		$options = array();

		$options['list_id'] =  $saved_options['list_id'];
		$options['from_email'] =  $current_user->user_email;
		$options['from_name'] =  $current_user->user_firstname.' '.$current_user->user_lastname . ' [' . get_option( 'blogname') . ']';
		$options['generate_text'] = ($saved_options['generate_text'] == 'true' ? true : false);
		

		$encoded = $_REQUEST['announcements'];
		$hash = md5( $encoded . 'SecretStringHere' );
		if( $_REQUEST['hash'] == $hash) { // the data hasn't been messed with
				$announcements = unserialize( base64_decode( $encoded ));
		} else {echo ' submitted hash was: ' . $_REQUEST['hash'] . '. Recalculated hash was: ' . $hash .'<br><br>Actual string was <br>' . $encoded;}
		
		if ($campaigntype == 'speaker') {
			$post_title = get_the_title( $_REQUEST['postid'] );
			$post_title = str_replace( '&#8217;', '\'' , str_replace( '&#8211;', ' - ', str_replace( '&#038;', '&' , str_replace( '&#8217;', '\'' , str_replace( '&#8220;', '' , str_replace( '&#8221;', '' , $post_title))))));
			$date = DateTime::createFromFormat('Ymd', get_field( 'speaker_date', $_REQUEST['postid'] ));
			$speaker = get_field('speaker_first_name',  $_REQUEST['postid'] ).' '.get_field('speaker_last_name',  $_REQUEST['postid'] );

			$options['subject'] =   substr( sprintf( __( 'Program %s : ' ), $date->format('l M jS') ) 
									. ' - "' 
									. substr( $post_title, 0, 50) . ( strlen( $post_title ) > 50 ? '...' : '')
									. '" by ' . substr( $speaker, 0, 20), 0, 99) ;
			$options['auto_tweet'] = ($saved_options['auto_tweet'] == 'true' ? true : false);
			$options['auto_fb_post'] =  explode(',', $saved_options['auto_post']);	
		}
		elseif ($campaigntype == 'announcements' ) {
			$date = rotary_next_program_date(); // there is a default of 2 days grace before the next meeting is used as the week-of
			$heading = sprintf( __( 'Club Announcements for %s' ), $date->format( 'l M jS' ) ); //Club Announcements for Friday Nov 6th
			$options['subject'] =   $heading . ' - ' . get_option( 'blogname' );
			$options['auto_tweet'] = false;
			$options['auto_fb_post'] =  '';
		}

		ob_start();

			include $campaigntype . '_table.php'; //this does the work

		$post_html = ob_get_clean();

		$html_inline_css = $nm_mailchimp -> mc -> helper -> inlineCss( $post_html );

		$content = array(	'html'	=> stripcslashes( $html_inline_css['html'] ),
				 			'text'	=> stripcslashes( $html_inline_css['html'] )
		);

		$resp = $nm_mailchimp -> mc -> campaigns -> create($type, $options, $content);

		if (isset($resp['id'])) {
			if ($_REQUEST['sendtype'] == 'test') {
				$r =  $nm_mailchimp -> mc -> campaigns -> sendTest($resp['id'], array($current_user->user_email));
				if ($r['complete']) {
					echo 'Test Campaign Sent to '.$current_user->user_email;
				} else {
					echo 'Error';
				}
			}
			if ($_REQUEST['sendtype'] == 'send') {
				$r = $nm_mailchimp -> mc -> campaigns -> send($resp['id']);
				if ($r['complete']) {
					echo 'Campaign Sent!';
				} else {
					echo 'Error';
				}
			}
			
		} else {
			echo $resp;
		}

		die(0);

	}
}

if( function_exists ( 'is_plugin_active' ) || 1==1 ) {
	if ( is_plugin_active( 'nm-mailchimp-campaign/index.php' ) && class_exists('NM_MC_Front') ) {
		$just_init = new NM_MC_Front;
	}
}
