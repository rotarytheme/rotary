<?php
/**
 * Adds a plugin settings link to Options and renders the options form if required
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Make sure we have loaded the admin settings for the post types
require_once( ECP1_DIR . '/includes/custom-post-admin.php' );

// Load the maps interface so we know which map implementations exist
require_once( ECP1_DIR . '/includes/mapstraction/controller.php' );

// Load the external calendars interface so we know which ones exist
require_once( ECP1_DIR . '/includes/external-calendar-providers.php' );

// Load the repeat expression manager class
require_once( ECP1_DIR . '/includes/repeat-expression.php' );

// Load the plugin settings and helper functions
require_once( ECP1_DIR . '/includes/data/ecp1-settings.php' );

// Add action hooks
add_action( 'admin_init', 'ecp1_settings_register' );
add_action( 'admin_menu', 'ecp1_add_options_page' );

// Init plugin options to white list our options
function ecp1_settings_register() {
	register_setting( ECP1_OPTIONS_GROUP, ECP1_GLOBAL_OPTIONS, 'ecp1_validate_options_page' );
}

// Add menu page
function ecp1_add_options_page() {
	// Add the settings / options menu item
	$page = add_options_page( __( 'Every Calendar +1 Options' ), __( 'EveryCal+1' ), 'manage_options', ECP1_GLOBAL_OPTIONS, 'ecp1_render_options_page' );
	add_action( 'admin_print_styles-' . $page, 'ecp1_enqueue_admin_css' );
	add_action( 'admin_print_scripts-' . $page, 'ecp1_enqueue_admin_js' );
	add_action( 'admin_print_footer_scripts', 'ecp1_settings_js' );
}

// Define a global variable for printing JS from
$_ecp1_settings_footer_script = null;

// Function that prints the above footer script if set
function ecp1_settings_js() {
	global $_ecp1_settings_footer_script;
	if ( null != $_ecp1_settings_footer_script ) {
		printf( '%s<!-- Every Calendar +1 Init -->%s<script type="text/javascript">/* <![CDATA[ */%s%s%s/* ]]> */</script>%s', "\n", "\n", "\n", $_ecp1_settings_footer_script, "\n", "\n" );
	}
}

// Draw the option page
function ecp1_render_options_page() {
	global $_ecp1_settings_footer_script;

?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2><?php _e( 'Every Calendar +1 Options' ); ?></h2>
<?php
	if ( ECP1_DEBUG )
		printf( '<pre>%s</pre>', print_r( _ecp1_get_options(), true ) );
?>
		<form method="post" action="options.php">
			<?php settings_fields( ECP1_OPTIONS_GROUP ); ?>
			<h3><?php _e( 'General settings' ); ?></h3>
			<table class="form-table">
				 <tr>
					<th><?php _e( 'Featured Calendars' ); ?></th>
					<td>
<?php
	// List all of the available calendars with checkboxes
	$calendars = _ecp1_current_user_calendars();
	$display_counter = 0;
	foreach( $calendars as $cal ) {
		if ( 0 == $display_counter )
			printf( '<div class="ecp1_checkbox_row">' );
?>
		<span class="ecp1_checkbox_block">
			<input id="<?php printf( '%s[feature_cals][%s]', ECP1_GLOBAL_OPTIONS, $cal->ID ); ?>" name="<?php printf( '%s[feature_cals][%s]', ECP1_GLOBAL_OPTIONS, $cal->ID ); ?>" type="checkbox" value="1"<?php echo _ecp1_calendar_show_featured( $cal->ID ) ? ' checked="checked"' : ''; ?> />
			<label for="<?php printf( '%s[feature_cals][%s]', ECP1_GLOBAL_OPTIONS, $cal->ID ); ?>"><?php echo $cal->post_title; ?></label>
		</span>
<?php
		$display_counter += 1;
		if ( 3 == $display_counter ) { // i.e. 3 displayed
			printf( '</div>' );
			$display_counter = 0;
		}
	}
	if ( 3 != $display_counter ) {
		printf( '</div>' ); // close the div if not already done
	}
?>
					<p><em><?php _e( 'Which calendars should featured events be displayed on?' ); ?></em></p>
				</tr>
				<tr>
					<th><?php _e( 'Timezones' ); ?></th>
					<td>
						<dl>
							<dt><input id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[tz_change]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[tz_change]" type="checkbox" value="1" <?php checked( '1', _ecp1_get_option( 'tz_change' ) ); ?> /> <label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[tz_change]"><?php _e( 'Allow Timezone Changes' ); ?></label></dt>
							<dd><?php _e( 'Calendars will default to the WordPress Timezone setting.' ); ?></dd>

							<dt>
								<input id="<?php printf( '%s[feature_tz_local]', ECP1_GLOBAL_OPTIONS ); ?>" name="<?php printf( '%s[feature_tz_local]', ECP1_GLOBAL_OPTIONS ); ?>" type="checkbox" value="1" <?php checked( '1', _ecp1_get_option( 'base_featured_local_to_event' ) ); ?> /> <label for="<?php printf( '%s[feature_tz_local]', ECP1_GLOBAL_OPTIONS ); ?>"><?php _e( 'Featured events display event local time' ); ?></label><br/>
								<label for="<?php printf( '%s[feature_tz_note]', ECP1_GLOBAL_OPTIONS ); ?>"><?php _e( 'Note text:' ); ?></label>
								<input id="<?php printf( '%s[feature_tz_note]', ECP1_GLOBAL_OPTIONS ); ?>" name="<?php printf( '%s[feature_tz_note]', ECP1_GLOBAL_OPTIONS ); ?>" type="text" class="ecp1_w75" value="<?php echo _ecp1_get_option( 'base_featured_local_note' ); ?>" />
							</dt>
							<dd><?php _e( 'When featured events are displayed on other calendars the event start/end time can either be displayed as the event local time (i.e. time where the event is happening) or as the calendar local time. The default is event local time! For example: An Event starts are 10am (Australia/Melbourne) and is displayed on a calendar with timezone Europe/London if this setting is enabled: the event will show starting time of 10am with the note text below the calendar; if this setting is disabled: the event will show start time as midnight on the calendar (10am Melbourne is midnight London).' ); ?></dd>
					</dl>
				<tr>
					<th><?php _e( 'CDN' ); ?></th>
					<td><dl>
						<dt><input id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[cdnjs]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[cdnjs]" type="checkbox" value="1" <?php checked( '1', _ecp1_get_option( 'cdnjs' ) ); ?> /> <label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[cdnjs]"><?php _e( 'Use CDNJS for JavaScript?' ); ?></label></dt>
						<dd><?php _e( 'Recommended for improving load times' ); ?></dd>
					</dl></td>
				</tr>
				<tr>
					<th><?php _e( 'Calendar Providers' ); ?></th>
					<td>
<?php
	// For each provider display a checkbox do rows of 3
	$cal_providers = ecp1_calendar_providers();
	$display_counter = 0;
	foreach( $cal_providers as $name=>$details ) {
		if ( 0 == $display_counter )
			printf( '<div class="ecp1_checkbox_row">' );
?>
		<span class="ecp1_checkbox_block">
			<input id="<?php printf( '%s[cal_providers][%s]', ECP1_GLOBAL_OPTIONS, $name ); ?>" name="<?php printf( '%s[cal_providers][%s]', ECP1_GLOBAL_OPTIONS, $name ); ?>" type="checkbox" value="1"<?php echo _ecp1_calendar_provider_enabled( $name ) ? ' checked="checked"' : ''; ?> />
			<label for="<?php printf( '%s[cal_providers][%s]', ECP1_GLOBAL_OPTIONS, $name ); ?>"><?php echo $details['name']; ?></label>
		</span>
<?php
		$display_counter += 1;
		if ( 3 == $display_counter ) { // i.e. 3 displayed
			printf( '</div>' );
			$display_counter = 0;
		}
	}
	if ( 3 != $display_counter ) {
		printf( '</div>' ); // close the div if not already done
	}
?>
						<p><em><?php _e( 'Which external calendar providers do you wish to enable?' ); ?></em></p>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Maps' ); ?></th>
					<td><dl>
						<dt><input id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[use_maps]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[use_maps]" type="checkbox" value="1" <?php checked( '1', _ecp1_get_option( 'use_maps' ) ); ?> /> <label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[use_maps]"><?php _e( 'Enable event location maps' ); ?>:</label></dt>
						<dd><?php _e( 'If enabled individual event posts can set whether a map is displayed or not' ); ?></dd>
						<dt>
							<label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[map_provider]"><?php _e( 'Map service' ); ?></label> <select id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[map_provider]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[map_provider]"><?php printf( ECP1Mapstraction::ToOptionTags() ); ?></select> <label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[map_geocoder]"><?php _e( 'Geocoding service' ); ?></label> <select id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[map_geocoder]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[map_geocoder]"><?php printf( ECP1Mapstraction::ToOptionTags( true ) ); ?></select>
						</dt>
						<dd><?php _e( 'Choose which map provider you would like to use. If you want to lookup locations by address (i.e. geocode the address) you will also need to choose a geocoding service.' ); ?></dd>
					</dl></td>
				</tr>
			</table>

			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>

			<h3><?php _e( 'Repeating Events' ); ?></h3>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Cache' ); ?></th>
					<td><dl>
						<dt>
<?php
	// Cache block size templates
	$times = array( '2592000'=>__( '1 Month' ), '15811200'=>__( '6 Months' ), '31536000'=>__( '1 Year' ), '63072000'=>__( '2 Years' ) );
	printf( '<span class="ecp1_ical_q">%s</span>', __( 'Max cache size:' ) );
	printf( '<select id="%s[repeat_cache_size]" name="%s[repeat_cache_size]"><option value="-1">%s</option>', ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS, __( 'Custom' ) );
	$coffsetcustom = false;
	foreach( $times as $val=>$time ) {
		$coffsetcustom = $coffsetcustom || $val == _ecp1_get_option( 'max_repeat_cache_block' ) ? true : false;
		printf( '<option value="%s"%s>%s</option>', $val, $val == _ecp1_get_option( 'max_repeat_cache_block' ) ? ' selected="selected"' : '', $time);
	}
	printf( '</select> or <input id="%s[repeat_cache_size_custom]" name="%s[repeat_cache_size_custom]" type="text" value="%s" /> %s<br/>',
			ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS,
			! $coffsetcustom ? _ecp1_get_option( 'max_repeat_cache_block' ) : '', __( 'seconds' ) );
	
	// Enable / disable custom scheduler expressions
?>
							<input id="<?php printf( '%s[force_cache]', ECP1_GLOBAL_OPTIONS ); ?>" name="<?php printf( '%s[force_cache]', ECP1_GLOBAL_OPTIONS ); ?>" type="checkbox" value="1"<?php echo '1' == _ecp1_get_option( 'enforce_repeat_cache_size' ) ? ' checked="checked"' : '' ?> /> <label for="<?php printf( '%s[force_cache]', ECP1_GLOBAL_OPTIONS ); ?>"><?php _e( 'Enforce the maximum cache size' ); ?></label>
						</dt>
						<dd><?php _e( 'Note: Only enforce the cache size if you know you will never make a larger request; for example the event list shortcode with an open ended finish date will overrun any cache size (except 30 years) because it computes repeats until January 2038. You have two options: a) set an end date on the shortcode or not to enforce the cache size.' ); ?></dd>
					</dl></td>
				</tr>
				<tr>
					<th><?php _e( 'Repeat patterns' ); ?></th>
					<td>
						<input id="<?php printf( '%s[custom_repeats]', ECP1_GLOBAL_OPTIONS ); ?>" name="<?php printf( '%s[custom_repeats]', ECP1_GLOBAL_OPTIONS ); ?>" type="checkbox" value="1"<?php echo '1' == _ecp1_get_option( 'allow_custom_repeats' ) ? ' checked="checked"' : '' ?> />
						<label for="<?php printf( '%s[custom_repeats]', ECP1_GLOBAL_OPTIONS ); ?>"><?php _e( 'Enable custom repeat expressions?' ); ?></label><br/>
						<strong><?php _e( 'Disable the following repeat templates:' ); ?></strong><br/>
<?php
	foreach( EveryCal_RepeatExpression::$TYPES as $key=>$dtl ) {
		printf( '<input id="%s[disable_expressions][%s]" name="%s[disable_expressions][%s]" type="checkbox" value="1"%s /> ',
				ECP1_GLOBAL_OPTIONS, $key, ECP1_GLOBAL_OPTIONS, $key,
				_ecp1_scheduler_expression_is_disabled( $key ) ? ' checked="checked"' : '');
		printf( '<label for="%s[disable_expressions][%s]">%s</label><br/>', ECP1_GLOBAL_OPTIONS, $key, __( $dtl['desc'] ) );
	}
?>
					</td>
				</tr>
			</table>

			<h3>Calendar Export Settings (iCAL / RSS)</h3>
			<table class="form-table">
				<tr>
					<th><?php _e( 'When to publish' ); ?></th>
					<td>
<?php
	// How far in advance to publish RSS feed items
	printf( '<span class="ecp1_ical_q">%s</span>', __( 'RSS Date:' ) );
	printf( '<select id="%s[rss_prequel]" name="%s[rss_prequel]"><option value="-1">%s</option>', ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS, __( 'Custom' ) );
	$roffsetcustom = false;
	foreach( $times as $val=>$time ) {
		$roffsetcustom = $roffsetcustom || $val == _ecp1_get_option( 'rss_pubdate_prequel_range' ) ? true : false;
		printf( '<option value="%s"%s>%s</option>', $val, $val == _ecp1_get_option( 'rss_pubdate_prequel_range' ) ? ' selected="selected"' : '', $time );
	}
	printf( '</select> or <input id="%s[rss_prequel_custom]" name="%s[rss_prequel_custom]" type="text" value="%s" /> %s<br/><em>%s</em><br/>',
			ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS,
			! $roffsetcustom ? _ecp1_get_option( 'rss_pubdate_prequel_range' ) : '', __( 'seconds' ),
			__( 'How far in advance should events be published' ) );
?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Period to export' ); ?></th>
					<td>
<?php
	// How far back and forward should we export
	$times = array( '86400'=>__( '1 Day' ), '604800'=>__( '1 Week' ), '2592000'=>__( '1 Month' ), '15811200'=>__( '6 Months' ), '31536000'=>__( '1 Year' ) );

	$soffsetcustom = false;
	printf( '<span class="ecp1_ical_q">%s</span>', __( 'How far back?' ) );
	printf( '<select id="%s[ical_start]" name="%s[ical_start]"><option value="-1">%s</option>', ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS, __( 'Custom' ) );
	foreach( $times as $val=>$time ) {
		$soffsetcustom = $soffsetcustom || $val == _ecp1_get_option( 'export_start_offset' ) ? true : false;
		printf( '<option value="%s"%s>%s</option>', $val, $val == _ecp1_get_option( 'export_start_offset' ) ? ' selected="selected"' : '', $time );
	}
	printf( '</select> or <input id="%s[ical_start_custom]" name="%s[ical_start_custom]" type="text" value="%s" /> %s<br/>',
			ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS,
			! $soffsetcustom ? _ecp1_get_option( 'export_start_offset' ) : '', __( 'seconds' ) );

	$eoffsetcustom = false;
	printf( '<span class="ecp1_ical_q">%s</span>', __( 'How far forward?' ) );
	printf( '<select id="%s[ical_end]" name="%s[ical_end]"><option value="-1">%s</option>', ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS, __( 'Custom' ) );
	foreach( $times as $val=>$time ) {
		$eoffsetcustom = $eoffsetcustom || $val == _ecp1_get_option( 'export_end_offset' ) ? true : false;
		printf( '<option value="%s"%s>%s</option>', $val, $val == _ecp1_get_option( 'export_end_offset' ) ? ' selected="selected"' : '', $time );
	}
	printf( '</select> or <input id="%s[ical_end_custom]" name="%s[ical_end_custom]" type="text" value="%s" /> %s<br/>',
			ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS,
			! $eoffsetcustom ? _ecp1_get_option( 'export_end_offset' ) : '', __( 'seconds' ) );
?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'External calendars' ); ?></th>
					<td>
<?php
	// Include options for ical_export_include_external and ical_export_external_cache_life
	printf( '<input id="%s[export_external]" name="%s[export_external]" type="checkbox" value="1"%s /> <label for="%s[export_external]">%s</label><br/>',
			ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS,
			'1' == _ecp1_get_option( 'export_include_external' ) ? ' checked="checked"' : '',
			ECP1_GLOBAL_OPTIONS, __( 'Include external calendars in export feeds (such as iCal and RSS)?' ) );
	printf( '<span class="ecp1_ical_q">%s</span>', __( 'Cache locally for:' ) );
	printf( '<select id="%s[cache_expire]" name="%s[cache_expire]"><option value="-1">%s</option>', ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS, __( 'Custom' ) );
	$eoffsetcustom = false;
	foreach( $times as $val=>$time ) {
		$eoffsetcustom = $eoffsetcustom || $val == _ecp1_get_option( 'export_external_cache_life' ) ? true : false;
		printf( '<option value="%s"%s>%s</option>', $val, $val == _ecp1_get_option( 'export_external_cache_life' ) ? ' selected="selected"' : '', $time );
	}
	printf( '</select> or <input id="%s[cache_expire_custom]" name="%s[cache_expire_custom]" type="text" value="%s" /> %s<br/>',
			ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS,
			! $eoffsetcustom ? _ecp1_get_option( 'export_external_cache_life' ) : '', __( 'seconds' ) );
?>
					</td>
				</tr>
			</table>

			<h3><?php _e( 'Calendar and Event Post Template' ); ?></h3>
			<table class="form-table ecp1subsettings">
				<tr valign="top">
					<th scope="row"><?php _e( 'Export Icon' ); ?></th>
					<td><div>
<?php
	printf( '
		<input id="_export_icon" name="%s[export_icon]" type="hidden" value="%s" />
		<img id="_export_icon_preview" src="%s" alt="%s" />
		<a id="ecp1changeicon" href="#" title="%s">%s</a><br/>',
			ECP1_GLOBAL_OPTIONS, _ecp1_get_option( 'export_icon' ), 
			plugins_url( '/img/famfamfam/' . _ecp1_get_option( 'export_icon' ), dirname( __FILE__ ) ),
			__( 'Icon' ), __( 'Change Export Icon' ), __( 'Change picture to use for export icon' ) );

	// Render a script to choose an icon from famfamfam
	$export_icons = glob( ECP1_DIR . '/img/famfamfam/*.png' );
	$export_icons_str = '[';
	foreach( $export_icons as $icon )
		$export_icons_str .= sprintf( '"%s",', basename( $icon ) );
	$export_icons_str = trim( $export_icons_str, ',' ) . ']';
	$export_icons_path = plugins_url( '/img/famfamfam', dirname( __FILE__ ) );
	$close_link_text = __( 'Close' );

	$_ecp1_settings_footer_script .= <<<ENDOFSCRIPT
var _closeLink = '$close_link_text';
var _iconsPath = '$export_icons_path';
var _iconsArray = $export_icons_str;
jQuery(document).ready(function($){
	$('#ecp1changeicon').click(function(){
		var lm = $( '#_ecp1-export-icon' );
		if ( lm.length == 0 ) {
			$( 'body' ).append( $( '<div></div>' )
						.attr( { id:'_ecp1-export-icon' } ).css( { display:'none', 'z-index':99999 } ) );
			lm = $( '#_ecp1-export-icon' );
		}

		var pw = $( window ).width();
		var ph = $( document ).height();
		var ps = $( document ).scrollTop(); ps = ( ps+20 ) + 'px auto 0 auto';
		lm.css( { width:pw, height:ph, position:'absolute', top:0, left:0,
				display:'block', textAlign:'center', background:'rgba(0,0,0,0.7)' } )
			.append( $( '<div></div>' )
				.css( { background:'#ffffff', opacity:1, padding:'1em', width:800, margin:ps } )
				.append( $( '<div></div>' )
					.css( { textAlign:'right' } )
					.append( $( '<a></a>' )
						.css( { cursor:'pointer' } )
						.text( _closeLink )
						.click( function() {
							$( '#_ecp1-export-icon' ).remove();
						} ) ) )
					.append( $( '<div></div>' )
						.attr( { id:'_ecp1-icontainer' } )
						.css( { textAlign:'left', width:800 } ) ) );

		var ic = $( '#_ecp1-icontainer' );
		for ( var i=0; i < _iconsArray.length; i++ )
			ic.append( $( '<span></span>' )
				.css( { display:'inline-block', margin:'2px' } )
				.append( $( '<img>' )
					.attr( { alt:_iconsArray[i].split('.')[0], src:( _iconsPath + '/' + _iconsArray[i] ), id:_iconsArray[i] } )
					.css( { cursor:'pointer' } )
					.click( function() {
						var part = $( this ).attr( 'id' );
						$( '#_export_icon' ).val( part );
						$( '#_export_icon_preview' ).attr( { src: $(this).attr('src') } );
						lm.find( 'div div a' ).first().click();
					} ) ) );
	} );

	$('th.clickexpand').click(function() {
		$(this).parent().find('.expandtarget').toggle();
	});
} );
ENDOFSCRIPT;

	// Should the icon be shown at all?
	printf( '
		<input id="%s[show_export_icon]" name="%s[show_export_icon]" type="checkbox" value="1"%s />
		<label for="%s[show_export_icon]">%s</label><br/>',
			ECP1_GLOBAL_OPTIONS, ECP1_GLOBAL_OPTIONS,
			'1' == _ecp1_get_option( 'show_export_icon' ) ? ' checked="checked"' : '',
			ECP1_GLOBAL_OPTIONS, __( 'Show export icon on calendar posts?' ) );

?>
					</div></td>
				</tr>
				<tr>
					<th><?php _e('Actions and Content' ); ?></th>
					<td><div>
						<input id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[show_time_on_all_day]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[show_time_on_all_day]" type="checkbox" value="1" <?php echo '1' == _ecp1_get_option( 'show_time_on_all_day' ) ? 'checked="checked"' : ''; ?> />
						<label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[show_time_on_all_day]"><?php _e( 'Show time on all day events?' ); ?></label><br/>
						<input id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[show_all_day_message]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[show_all_day_message]" type="checkbox" value="1" <?php echo '1' == _ecp1_get_option( 'show_all_day_message' ) ? 'checked="checked"' : ''; ?> />
						<label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[show_all_day_message]"><?php _e( 'Show (all day) on all day events?' ); ?></label><br/>
						<input id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[popup_on_click]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[popup_on_click]" type="checkbox" value="1" <?php echo '1' == _ecp1_get_option( 'popup_on_click' ) ? 'checked="checked"' : ''; ?> />
						<label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[popup_on_click]"><?php _e( 'Show popup on click?' ); ?></label><br/>
					</div></td>
				</tr>
				<tr>
					<th><?php _e( 'Time Format' ); ?></th>
					<td>
						<label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[week_time_format]"><?php _e( 'Week display:' ); ?></label>
						<input type="text" id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[week_time_format]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[week_time_format]" value="<?php echo _ecp1_get_option( 'week_time_format' ); ?>" /><br/>
						<label for="<?php echo ECP1_GLOBAL_OPTIONS; ?>[month_time_format]"><?php _e( 'Month display:' ); ?></label>
						<input type="text" id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[month_time_format]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[month_time_format]" value="<?php echo _ecp1_get_option( 'month_time_format' ); ?>" /><br/>
						<p><a href="http://arshaw.com/fullcalendar/docs/utilities/formatDate/"><?php _e( 'See FullCalendar documentation for format options.' ); ?></a></p>
					</td>
				</tr>
				<tr>
					<th class="clickexpand"><?php _e( 'Calendar Template<br/>Click to Toggle Help' ); ?></th>
					<td>
						<textarea id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[calendar_template]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[calendar_template]"><?php echo _ecp1_get_option( 'calendar_template' ); ?></textarea>
						<ul>
							<li><?php _e( 'You must use id="ecp1_calendar" on the main container element' ); ?></li>
							<li><?php _e( 'You must use class="fullcal" on the calendar container element' ); ?></li>
						</ul>
						<div class="expandtarget">
							<em><?php _e( 'Placeholders' ); ?></em>
							<ul>
								<li>+FEEDS+ +ENDFEEDS+<br/><?php _e( 'How to layout feeds icon; must have id="ecp1_show_feeds" as clickable' ); ?></li>
								<li>+DESCRIPTION_TEXT+<br/><?php _e( 'Event description text' ); ?></li>
								<li>+TIMEZONE_DISCLAIMER+<br/><?php _e( 'Which timezone the calendar is in' ); ?></li>
								<li>+FEATURE_EVENT_NOTICE+<br/><?php _e( 'The Featured Calendar Note text from above' ); ?></li>
								<li>+FEATURE_TEXT_COLOR+ / +FEATURE_BACKGROUND+<br/><?php _e( 'Replaced with calendar settings; if these are missing your users will not be able to pick their own colors' ); ?></li>
								<li>+CALENDAR_LOADING+<br/><?php _e( 'Where to put the calendar loading message' ); ?></li>
								<li>+FEED_LINK+<br/><?php _e( 'Replaced with the plugin generated feed link' ); ?></li>
								<li>+FEED_ICON+<br/><?php _e( 'URL for the Export Icon above' ); ?></li>
							</ul>
							<em><?php _e( 'An Example' ); ?></em>
							<pre>
<?php echo htmlentities( '<div id="ecp1_calendar">
	+FEEDS+<div class="feeds">
		<a id="ecp1_show_feeds" href="+FEED_LINK+">
			<img src="+FEED_ICON+" alt="iCAL" /></a>
	</div>+ENDFEEDS+
	<p><strong>+DESCRIPTION_TEXT+
	<div class="fullcal">+CALENDAR_LOADING+</div>
	<div>
		<div style="padding:0 5px;">
			<em>+TIMEZONE_DISCLAIMER+</em>
		</div>
		<div style="padding:0 5px;color:FEATURE_TEXT_COLOR;background-color:FEATURE_BACKGROUND">
			<em>+FEATURE_EVENT_NOTICE+</em>
		</div>
	</div>
</div>' ); ?>
							</pre>
						</div><!-- expandtarget -->
					</td>
				</tr>
				<tr>
					<th class="clickexpand"><?php _e( 'Event Template<br/>Click to Toggle Help' ); ?></th>
					<td>
						<textarea id="<?php echo ECP1_GLOBAL_OPTIONS; ?>[event_template]" name="<?php echo ECP1_GLOBAL_OPTIONS; ?>[event_template]"><?php echo _ecp1_get_option( 'event_template' ); ?></textarea>
						<ul>
							<li><?php _e( 'You must use id="ecp1_event" on the main container element' ); ?></li>
						</ul>
						<div class="expandtarget">
							<em><?php _e( 'Placeholders' ); ?></em>
							<ul>
								<li>+TITLE_TIME+, +TITLE_LOCATION+,  +TITLE_SUMMARY+, +TITLE_DETAILS+<br/><?php _e( 'Replaced with titles as appropriate' ); ?></li>
								<li>+FEATURE_IMAGE+<br/><?php _e( 'Replaced with an image element of the post thumbnail if theme supports it' ); ?></li>
								<li>+EVENT_TIME+<br/><?php _e( 'Replaced with a formatted string of the event start and end time' ); ?></li>
								<li>+EVENT_LOCATION+<br/><?php _e( 'Replaced with the event location' ); ?></li>
								<li>+EVENT_SUMMARY+<br/><?php _e( 'Replaced with the event summary' ); ?></li>
								<li>+EVENT_DETAILS+<br/><?php _e( 'Replaced with the event details and an offsite link if available' ); ?></li>
								<li>+MAP_CONTAINER+<br/><?php _e( 'Replaced with a div element that will have the map loaded into it' ); ?></li>
							</ul>
							<em><?php _e( 'An Example' ); ?></em>
							<pre>
<?php echo htmlentities( '<div id="ecp1_event">
	<span id="ecp1_feature">+FEATURE_IMAGE+</span>
	<ul class="ecp1_event-details">
		<li><span class="ecp1_event-title"><strong>+TITLE_TIME+:</strong></span>
				<span class="ecp1_event-text">+EVENT_TIME+</span></li>
		<li><span class="ecp1_event-title"><strong>+TITLE_LOCATION+:</strong></span>
				<span class="ecp1_event-text">
					<span id="ecp1_event_location">+EVENT_LOCATION+</span><br/>
					+MAP_CONTAINER+
				</span></li>
		<li><span class="ecp1_event-title"><strong>+TITLE_SUMMARY+:</strong></span>
				<span class="ecp1_event-text_wide">+EVENT_SUMMARY+</span></li>
		<li><span class="ecp1_event-title"><strong>+TITLE_DETAILS+:</strong></span>
				<span class="ecp1_event-text_wide">+EVENT_DETAILS+</span></li>
	</ul>
</div>' ); ?>
							</pre>
						</div><!-- expandtarget -->
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</form>
	</div>
<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function ecp1_validate_options_page( $input ) {
	// Check the map provider and geocoder are valid
	if ( isset( $input['map_provider'] ) && isset( $input['map_geocoder'] ) ) {
		/* Replaced with mapstraction
		$map_providers = ecp1_map_providers();
		if ( ! array_key_exists( $input['map_provider'], $map_providers ) ) {
			// Not a valid map selected: use default (none)
			$input['map_provider'] = _ecp1_option_get_default( 'map_provider' );
		}
		*/
		if ( ! ECP1Mapstraction::ValidProvider( $input['map_provider'] ) )
			$input['map_provider'] = _ecp1_option_get_default( 'map_provider' );
		if ( ! ECP1Mapstraction::ValidGeocoder( $input['map_geocoder'] ) ) {
			if ( ECP1Mapstraction::ValidGeocoder( $input['map_provider'] ) ) {
				$input['map_geocoder'] = $input['map_provider'];
			} else {
				$input['map_geocoder'] = _ecp1_option_get_default( 'map_geocoder' );
			}
		}
	} else {
		// Use default map provider and geocoder
		$input['map_provider'] = _ecp1_option_get_default( 'map_provider' );
		$input['map_geocoder'] = _ecp1_option_get_default( 'map_geocoder' );
	}
	
	// If the checkbox setting is given then set to true otherwise to false
	$boolean_options = array( 
		'use_maps'=>'use_maps',
		'cdnjs'=>'cdnjs',
		'tz_change'=>'tz_change',
		'feature_tz_local'=>'base_featured_local_to_event',
		'force_cache'=>'enforce_repeat_cache_size',
		'custom_repeats'=>'allow_custom_repeats',
		'export_external'=>'export_include_external',
		'show_export_icon'=>'show_export_icon',
		'show_time_on_all_day' => 'show_time_on_all_day',
		'show_all_day_message' => 'show_all_day_message',
		'popup_on_click' => 'popup_on_click' );
	foreach( $boolean_options as $postkey=>$optkey ) {
		if ( isset( $input[$postkey] ) && '1' == $input[$postkey] ) {
			$input[$optkey] = 1;
		} else {
			$input[$optkey] = 0;
		}

		// if different keys then unset the post
		if ( $postkey != $optkey )
			unset( $input[$postkey] );
	}
	
	// Rebuild the _external_cal_providers CSV list from the checkboxes
	$external_providers = '';
	$cal_providers = ecp1_calendar_providers();
	foreach( $cal_providers as $name=>$details ){
		if ( isset( $input['cal_providers'][$name] ) && '1' == $input['cal_providers'][$name] )
			$external_providers .= $name . ',';
	}
	$input['_external_cal_providers'] = trim( $external_providers, ',' );
	unset( $input['cal_providers'] );

	// Rebuild the _show_featured_on CSV list from the checkboxes
	$feature_calendars = _ecp1_get_option( '_show_on_featured' );
	if ( '' == $feature_calendars )
		$feature_calendars = array();
	else
		$feature_calendars = explode( ',', $feature_calendars );
	$calendars = _ecp1_current_user_calendars();
	foreach( $calendars as $cal ) {
		if ( isset( $input['feature_cals'][$cal->ID] ) && '1' == $input['feature_cals'][$cal->ID] 
				&& ! in_array( $cal->ID, $feature_calendars ) )
			$feature_calendars[] = $cal->ID; // if set and not in array then add

		if ( ! isset( $input['feature_cals'][$cal->ID] ) // look it up and gets index
				&& ( $_c_index = array_search( $cal->ID, $feature_calendars ) ) )
			array_splice( $feature_calendars, $_c_index, 1 ); // remove if now not set
	}
	$feature_calendars = implode( ',', $feature_calendars );
	$input['_show_featured_on'] = $feature_calendars;
	unset( $input['feature_cals'] );

	// If a external calendar provider is enabled then allow otherwise deny
	$input['use_external_cals'] = strlen( $external_providers ) > 0 ? 1 : 0;

	// Note to use when featured events display outside calendar timezone
	$input['base_featured_local_note'] = _ecp1_get_option( 'base_featured_local_note' );
	if ( isset( $input['feature_tz_note'] ) ) {
		$input['base_featured_local_note'] = strip_tags( $input['feature_tz_note'] );
		unset( $input['feature_tz_note'] );
	}

	// Repeat expression settings for disabling expressions
	$disabledexpr = array();
	foreach( EveryCal_RepeatExpression::$TYPES as $key=>$dtl ) {
		if ( isset( $input['disable_expressions'][$key] ) && '1' == $input['disable_expressions'][$key] )
			$disabledexpr[] = $key;
	}
	$input['_disable_builtin_repeats'] = implode( ',', $disabledexpr );
	unset( $input['disable_expressions'] );


	// Validate and verify iCal export start and end offsets and cache life
	$offsetnames = array(
				'max_repeat_cache_block'=>'repeat_cache_size',
				'export_start_offset'=>'ical_start',
				'export_end_offset'=>'ical_end',
				'export_external_cache_life'=>'cache_expire',
				'rss_pubdate_prequel_range'=>'rss_prequel' );
	foreach( $offsetnames as $setting=>$postkey ) {
		$input[$setting] = _ecp1_option_get_default( $setting );
		if ( isset( $input[$postkey] ) ) {  // select box value
			$fixed = $input[$postkey];
			unset( $input[$postkey] );
			if ( $fixed < 0 && isset( $input[$postkey.'_custom'] ) ) { // use custom value
				$fixed = $input[$postkey.'_custom'];
				unset( $input[$postkey.'_custom'] );
			}

			if ( is_numeric( $fixed ) && $fixed >= 0 )
				$input[$setting] = $fixed;
		}
	}

	// Validate the given icon file exists
	// otherwise let it go through as is
	if ( isset( $input['export_icon'] ) && 
		! file_exists( ECP1_DIR . '/img/famfamfam/' . $input['export_icon'] ) )
		unset( $input['export_icon'] );

	// WordPress doesn't allow id attributes on <div> for some reason
	global $allowedposttags;
	$allowedposttags['div']['id'] = array();
	$allowedposttags['span']['id'] = array();

	// Filter out any nastiness in the template strings
	if ( isset( $input['calendar_template'] ) ) {
		if ( ! empty( $input['calendar_template'] ) ) {
			$input['calendar_template'] = wp_kses_post( $input['calendar_template'] );
		} else {
			unset( $input['calendar_template'] ); // set to blank to reset
		}
	}
	if ( isset( $input['event_template'] ) ) {
		if ( ! empty( $input['event_template'] ) ) {
			$input['event_template'] = wp_kses_post( $input['event_template'] );
		} else {
			unset( $input['event_template'] );
		}
	}

	// Make sure the input keys are remapped where using synonyms
	foreach( $input as $key=>$value ) {
		if ( _ecp1_real_option_key( $key ) !== $key ) {
			$input[_ecp1_real_option_key( $key )] = $input[$key];
			unset( $input[$key] );
		}
	}
	
	// Return the sanitized array
	return $input;
}

// Don't close the php interpreter
/*?>*/
