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
		add_action('the_content', array($this, 'nm_mc_front_insert_id'));
		add_action('wp_ajax_nm_front_camp', array($this, 'nm_front_camp'));
		add_action('wp_head', array($this, 'insert_loader'));
	}

	function insert_loader(){
		echo '<div id="ajax-loader"></div>';
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

	function nm_mc_front_insert_id($content){
		global $post;
		if (is_singular()) {
			return $content.'<span class="nmid" data-nmid="'.$post->ID.'" ></span>';
		} else {
			return $content;
		}
	}

	function nm_front_camp(){

		$saved_options = get_option( 'nm_mc_front_save_settings' );

		global $nm_mailchimp;
		global $current_user;
		get_currentuserinfo();
		
		$post_id = $_REQUEST['postid']

		 = $post_title = get_the_title( $_REQUEST['postid'] );
		
		$type = 'regular';
		$options = array();

		$options['list_id'] =  $saved_options['list_id'];
		$options['subject'] =  $post_title;
		$options['from_email'] =  $current_user->user_email;
		$options['from_name'] =  $current_user->user_firstname.' '.$current_user->user_lastname;
		$options['generate_text'] = ($saved_options['generate_text'] == 'true' ? true : false);
		$options['auto_tweet'] = ($saved_options['auto_tweet'] == 'true' ? true : false);
		$options['auto_fb_post'] =  explode(',', $saved_options['auto_post']);
		

		$html_email_content = '<h1>'.$post_title.'</h1><hr>';
		$html_email_content .= '<table><tr>';
		
		
		
// PAUL OSBORN MODS  - WHAT DO YOU THINK OF SOMETHING LIKE THIS, RAMEEZ??
		$args = array (
					'post_id' => $post_id
				);
		$query = new WP_Query( $args );
		ob_start();
		include_once ( '../mailchimp-campaign/email-rotary_speakers.php' );
		wp_reset_postdata();
		
		$html_body = nm_add_inline_styles ( ob_get_clean() ); // not needed if we are using helper/inlinecss, I think?
		$html_email_content .= $html_body;
		//$html_email_content .= '<td style="width: 70%; vertical-align: top;">'.$_REQUEST['content'].'</td>';
		//$html_email_content .= '<td style="width: 30%; vertical-align: top;">'.$_REQUEST['sidebar'].'</td>';	
// end mode


		
		$html_email_content .= '</tr></table>';

		$content = array(	'html'	=> stripcslashes($html_email_content),
				 			'text'	=> stripcslashes($html_email_content)
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

if ( is_plugin_active( 'nm-mailchimp-campaign/index.php' ) && class_exists('NM_MC_Front') ) {
	$just_init = new NM_MC_Front;
}


// Something like this? 
function nm_add_inline_styles ( $html ) {
	$dom = new DOMDocument;
	$dom->loadHTML( $html );
	
	$h1s = $dom->getElementsByTagName('h1');
	foreach ($h1s as $h1) {
		$dh1_style = $h1->getAttribute('style');
			$h1->setAttribute('style','background-color:red;');
		}
	
	return $dom->saveHTML();
}