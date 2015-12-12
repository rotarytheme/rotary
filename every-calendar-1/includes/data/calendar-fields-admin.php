<?php
/**
 * Defines admin hooks for managaging the meta fields for the calendar post type
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Make sure the plugin settings and calendar fields have been loaded
require_once( ECP1_DIR . '/includes/data/ecp1-settings.php' );
require_once( ECP1_DIR . '/includes/data/calendar-fields.php' );

// Add filters and hooks to add columns and display them
add_filter( 'manage_edit-ecp1_calendar_columns', 'ecp1_calendar_edit_columns' );
add_action( 'manage_posts_custom_column', 'ecp1_calendar_custom_columns' );
add_action( 'admin_init', 'ecp1_calendar_meta_fields' );

// Function that adds extra columns to the post type
function ecp1_calendar_edit_columns( $columns ) {

	$columns = array(
		'title' => 'Name', # Default field title
		'ecp1_featured' => 'Featured events', # Y|N if calendar shows featured events
		'ecp1_cal_description' => 'Description', # Will show description<br/>url
		'ecp1_tz' => 'Timezone', # Calendar timezone
	);
	
	return $columns;
}

// Function that adds values to the custom columns
function ecp1_calendar_custom_columns( $column ) {
	global $ecp1_calendar_fields, $post_type;
	
	// Only do this if post type is calendar
	if ( 'ecp1_calendar' != $post_type )
		return;

	// Make sure the calendar meta is loaded
	_ecp1_parse_calendar_custom();
	
	// act based on the column that is being rendered
	switch ( $column ) {

		case 'ecp1_featured':
			if ( _ecp1_calendar_show_featured( _ecp1_calendar_meta_id() ) )
				printf( __( 'Y' ) );
			else
				printf( __( 'N' ) );
			break;
		
		case 'ecp1_cal_description':
			if ( ! _ecp1_calendar_meta_is_default( 'ecp1_description' ) ) 
				printf( '%s<br/>', htmlspecialchars( $ecp1_calendar_fields['ecp1_description'][0] ) );

			if ( _ecp1_calendar_meta_is_default( 'ecp1_external_cals' ) )
				printf( '<strong>%s</strong>', __( 'Local calendar only' ) );
			else
				printf( '<strong>%s</strong>', __( 'Has external calendars' ) );

			break;
		
		case 'ecp1_tz':
			if ( _ecp1_calendar_meta_is_default( 'ecp1_timezone' ) ) {
				printf( '%s', __( 'WordPress Default' ) );
			} else {
				printf( '%s', ecp1_timezone_display( $ecp1_calendar_fields['ecp1_timezone'][0] ) );
			}
			
			break;
		
	}
}

// Function that registers a meta form box on the ecp1_calendar create / edit page
function ecp1_calendar_meta_fields() {
	global $post_type;
	add_meta_box( 'ecp1_calendar_meta', 'Calendar Settings', 'ecp1_calendar_meta_form', 'ecp1_calendar', 'normal', 'high' );
}

// Function that generates a html section for adding inside a meta fields box
function ecp1_calendar_meta_form() {
	global $ecp1_calendar_fields, $post;
	
	// Make sure the meta is loaded
	_ecp1_parse_calendar_custom();
	
	// Sanitize and do security checks
	$ecp1_desc = _ecp1_calendar_meta_is_default( 'ecp1_description' ) ? '' : esc_textarea( $ecp1_calendar_fields['ecp1_description'][0] );
	$ecp1_tz = _ecp1_calendar_meta_is_default( 'ecp1_timezone' ) ? '_' : $ecp1_calendar_fields['ecp1_timezone'][0];
	$ecp1_defview = _ecp1_calendar_meta_is_default( 'ecp1_default_view' ) ? '' : $ecp1_calendar_fields['ecp1_default_view'][0];
	$ecp1_first_day = _ecp1_calendar_meta_is_default( 'ecp1_first_day' ) ? '-1' : $ecp1_calendar_fields['ecp1_first_day'][0];

	// Colors for local events
	$ecp1_local_color = _ecp1_calendar_meta( 'ecp1_local_event_color' );
	$ecp1_local_textcolor = _ecp1_calendar_meta( 'ecp1_local_event_textcolor' );

	// External calendars array
	$ecp1_cals = _ecp1_calendar_meta( 'ecp1_external_cals' );

	// Colors for featured events (only displayed if allowed)
	// rendered as hidden fields if not allowed to preserve
	$ecp1_feature_color = _ecp1_calendar_meta( 'ecp1_feature_event_color' );
	$ecp1_feature_textcolor = _ecp1_calendar_meta( 'ecp1_feature_event_textcolor' );
	
	// Output the meta box with a custom nonce
?>
	<input type="hidden" name="ecp1_calendar_nonce" id="ecp1_calendar_nonce" value="<?php echo wp_create_nonce( 'ecp1_calendar_nonce' ); ?>" />
	<div class="ecp1_meta">
<?php
	if ( '' != $post->post_title ) {
?>
		<div>Valid shortcodes for displaying this calendar:
			<ul style="padding-top:6px;list-style-type:disc;margin-left:1.5em;">
				<li style="margin-bottom:2px;">FullCalendar: [largecalendar name=&quot;<?php the_title(); ?>&quot;]</li>
				<li>Event List: [eventlist name=&quot;<?php the_title(); ?>&quot; starting=&quot;1st Jan 2011 2:00pm&quot; until=&quot;today&quot;] both the starting and until attributes are optional.</li>
			</ul>
			or you can link directly to this calendar post from your menu.
		</div>
<?php
	}
?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="ecp1_description"><?php _e( 'Description' ); ?></label></th>
				<td><textarea id="ecp1_description" name="ecp1_description" class="ecp1_big"><?php echo $ecp1_desc; ?></textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ecp1_local_color"><?php _e( 'Local Event Colours' ); ?></label><br/>#FFFFFF / #3366CC</th>
				<td>
					<div class="color_selector">
						<span><?php _e( 'Text' ); ?>:</span>
						<input type="hidden" id="ecp1_local_text" name="ecp1_local_text" value="<?php echo $ecp1_local_textcolor; ?>" /> 
						<div><div class="_eCS" style="background-color:<?php echo $ecp1_local_textcolor; ?>"></div></div></div>
					<div class="color_selector">
						<span><?php _e( 'Background' ); ?>:</span>
						<input type="hidden" id="ecp1_local_color" name="ecp1_local_color" value="<?php echo $ecp1_local_color; ?>" />
						<div><div class="_eCS" style="background-color:<?php echo $ecp1_local_color; ?>"></div></div></div>
				</td>
			</tr>
<?php
	// Check if this is a feature event calendar if so display color selectors
	// if not then render as hidden fields to preserve the settings
	if ( _ecp1_calendar_show_featured( $post->ID ) ) {
?>
			<tr valign="top">
				<th scope="row"><label for="ecp1_feature_color"><?php _e( 'Feature Event Colours' ); ?></label><br/>#FFFFFF / #CC6633</th>
				<td>
					<div class="color_selector">
						<span><?php _e( 'Text' ); ?>:</span>
						<input type="hidden" id="ecp1_feature_text" name="ecp1_feature_text" value="<?php echo $ecp1_feature_textcolor; ?>" />
						<div><div class="_eCS" style="background-color:<?php echo $ecp1_feature_textcolor; ?>"></div></div></div>
					<div class="color_selector">
						<span><?php _e( 'Background' ); ?>:</span>
						<input type="hidden" id="ecp1_feature_color" name="ecp1_feature_color" value="<?php echo $ecp1_feature_color; ?>" />
						<div><div class="_eCS" style="background-color:<?php echo $ecp1_feature_color; ?>"></div></div></div>
				</td>
			</tr>
<?php
	} else { // not allowed to show featured events
?>
			<input type="hidden" id="ecp1_feature_text" name="ecp1_feature_text" value="<?php echo $ecp1_feature_textcolor; ?>" />
			<input type="hidden" id="ecp1_feature_color" name="ecp1_feature_color" value="<?php echo $ecp1_feature_color; ?>" />
<?php
	}

	// Check if external calendars are enabled
	if ( _ecp1_get_option( 'use_external_cals' ) && is_array( $ecp1_cals ) ) {
?>
			<tr valign="top">
				<th scope="row">
					<label for="ecp1_external_url"><?php _e( 'External Calendars' ); ?></label>
				</th>
				<td>
<?php
		// Each calendar has: array( 'color'=>'#eeffee', 'text'=>'#333333', 'url'=>'url', 'provider'=>'provider array key' ),
		$ecp1_existing_cals = 0;
		$ecp1_cals['new'] = array( 'color'=>$ecp1_calendar_fields['ecp1_local_event_color'][1],
					'text'=>$ecp1_calendar_fields['ecp1_local_event_textcolor'][1],
					'url'=>__( 'Calendar URL (if required)' ), 'provider'=>'none' );
		foreach( $ecp1_cals as $_e_id => $line ) {
?>
					<div id="ecp1_ex_<?php echo $_e_id; ?>" class="ecp1_ex_container">
					<select id="ecp1_external_prov_<?php echo $_e_id; ?>" name="ecp1_external_prov_<?php echo $_e_id; ?>" class="ecp1_select">
						<option value=""></option>
<?php
			$calproviders = ecp1_calendar_providers();
			foreach( $calproviders as $name=>$details )
				printf( '<option value="%s"%s>%s</option>', $name, $name == $line['provider'] ? ' selected="selected"' : '', $details['name'] );
?>
					</select>
					<input name="ecp1_external_url_<?php echo $_e_id; ?>" type="text" class="ecp1_url" value="<?php echo urldecode( $line['url'] ); ?>" /><br/>
					<div class="color_selector">
						<span><?php _e( 'Text' ); ?>:</span>
						<input type="hidden" name="ecp1_external_txtcol_<?php echo $_e_id; ?>" value="<?php echo $line['text']; ?>" />
						<div><div class="_eCS" style="background-color:<?php echo $line['text']; ?>"></div></div>
					</div>
					<div class="color_selector">
						<span><?php _e( 'Background' ); ?>:</span>
						<input type="hidden" name="ecp1_external_col_<?php echo $_e_id; ?>" value="<?php echo $line['color']; ?>" />
						<div><div class="_eCS" style="background-color:<?php echo $line['color']; ?>"></div></div>
					</div>
<?php
			if ( 'new' !== $_e_id ) {
				$ecp1_existing_cals += 1;
				printf( '<a id="ecp1_remove_external_%s" class="ecp1_ex_rm">%s</a>', $_e_id, __( 'remove' ) );
			}
?>
					</div>
<?php
		}
?>
					<input type="hidden" id="ecp1_existing_cals" name="ecp1_existing_cals" value="<?php echo $ecp1_existing_cals; ?>" />
				</td>
			</tr>
<?php
	} // use external calendars
?>
			<tr valign="top">
				<th scope="row"><label for="ecp1_timezone"><?php _e( 'Timezone' ); ?></label></th>
				<td>
<?php
	// Check if local calendars can change event timezones
	$disabled_str = _ecp1_get_option( 'tz_change' ) ? 'class="ecp1_select"' : 'class="ecp1_select" disabled="disabled"';
	echo _ecp1_timezone_select( 'ecp1_timezone', $ecp1_tz, $disabled_str );
	if ( ! _ecp1_get_option( 'tz_change' ) )
		printf( '<em>%s</em>', __( 'Every Calendar +1 settings prevent change: WordPress TZ will be used.' ) );
	if ( '' == get_option( 'timezone_string' ) )
		printf( '<br/><strong>%s</strong>', __( 'If you are using your WordPress Timezone: Please consider setting a city in the WordPress timezone settings, otherwise Every Calendar will not be able to adjust your event times for day light savings.' ) );
?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ecp1_default_view"><?php _e( 'Default View' ); ?></label></th>
				<td>
					<input id="ecp1_default_view-month" type="radio"  name="ecp1_default_view" value="month" <?php checked( 'month', $ecp1_defview ); ?>/><label for="ecp1_default_view-month"><?php _e( 'Month' ); ?></label>
					<input id="ecp1_default_view-week" type="radio" name="ecp1_default_view" value="week" <?php checked( 'week', $ecp1_defview ); ?>/><label for="ecp1_default_view-week"><?php _e( 'Week' ); ?></label><br/>
					<label for="ecp1_first_day"><?php _e( 'First day of the week:' ); ?></label><select id="ecp1_first_day" name="ecp1_first_day"><option value="-1"><?php _e( 'WordPress Default' );?></option>
<?php
	// Loop over the days of the week
	foreach( array(
		0 => __( 'Sunday' ),
		1 => __( 'Monday' ),
		2 => __( 'Tuesday' ),
		3 => __( 'Wednesday' ),
		4 => __( 'Thursday' ),
		5 => __( 'Friday' ),
		6 => __( 'Saturday' ) ) as $id=>$day ) {
		printf( '<option value="%s"%s>%s</option>', $id, $id == $ecp1_first_day ? ' selected="selected"' : '', $day );
		}
?>
					</select>
				</td>
			</tr>
		</table>
	</div>
<?php
}

// Save the data when the meta box is submitted
add_action( 'save_post', 'ecp1_calendar_save' );
function ecp1_calendar_save() {
	global $post, $ecp1_calendar_fields;
	if ( ! isset( $post ) )
		return; // don't update if not a post yet
	if ( 'revision' == $post->post_type )
		return; // don't update on revisions
	if ( 'ecp1_calendar' != $post->post_type )
		return; // don't update non calendars
	
	// If there is no nonce this is a inline update so ignore
	if ( ! isset( $_POST['ecp1_calendar_nonce'] ) )
		return $post->ID;
	// Verify the nonce just incase
	if ( ! wp_verify_nonce( $_POST['ecp1_calendar_nonce'], 'ecp1_calendar_nonce' ) )
		return $post->ID;
	
	// Verify the user can actually edit posts
	if ( ! current_user_can( 'edit_' . ECP1_CALENDAR_CAP, $post->ID ) )
		return $post->ID;
	
	// Escape any nasty in the description
	$ecp1_description = '';
	if ( isset( $_POST['ecp1_description'] ) )
		$ecp1_description = sanitize_text_field( $_POST['ecp1_description'] );
	
	// Verify the timezone is valid if not error out
	$ecp1_timezone = '';
	if ( isset( $_POST['ecp1_timezone'] ) ) {
		if ( '_' == $_POST['ecp1_timezone'] ) {
			$ecp1_timezone = $_POST['ecp1_timezone'];
		} else {
			try {
				$dtz = new DateTimeZone( $_POST['ecp1_timezone'] );
				$ecp1_timezone = $dtz->getName();
			} catch( Exception $tzmiss ) {
				return $post->ID;
			}
		}
	}

	// Verify month|week|day is the value for default view
	$ecp1_default_view = 'none';
	if ( isset( $_POST['ecp1_default_view'] ) &&
			in_array( $_POST['ecp1_default_view'], array( 'month', 'week' ) ) ) {
		$ecp1_default_view = $_POST['ecp1_default_view'];
	}
	
	// Week start day should be 0<=X<=6
	$ecp1_first_day = -1;
	if ( isset( $_POST['ecp1_first_day'] ) && is_numeric( $_POST['ecp1_first_day'] ) &&
			( 0 <= $_POST['ecp1_first_day'] && $_POST['ecp1_first_day'] <= 6 ) ) {
		$ecp1_first_day = intval( $_POST['ecp1_first_day'] );
	}

	// Get the local event color and text color
	$ecp1_local_event_color = $ecp1_calendar_fields['ecp1_local_event_color'][1];
	if ( isset( $_POST['ecp1_local_color'] ) && preg_match( '/#[0-9A-Fa-f]{6}/', $_POST['ecp1_local_color'] ) )
		$ecp1_local_event_color = $_POST['ecp1_local_color'];
	$ecp1_local_event_textcolor = $ecp1_calendar_fields['ecp1_local_event_textcolor'][1];
	if ( isset( $_POST['ecp1_local_text'] ) && preg_match( '/#[0-9A-Fa-f]{6}/', $_POST['ecp1_local_text'] ) )
		$ecp1_local_event_textcolor = $_POST['ecp1_local_text'];

	// Get the feature event color and text color
	$ecp1_feature_event_color = $ecp1_calendar_fields['ecp1_feature_event_color'][1];
	if ( isset( $_POST['ecp1_feature_color'] ) && preg_match( '/#[0-9A-Fa-f]{6}/', $_POST['ecp1_feature_color'] ) )
		$ecp1_feature_event_color = $_POST['ecp1_feature_color'];
	$ecp1_feature_event_textcolor = $ecp1_calendar_fields['ecp1_feature_event_textcolor'][1];
	if ( isset( $_POST['ecp1_feature_text'] ) && preg_match( '/#[0-9A-Fa-f]{6}/', $_POST['ecp1_feature_text'] ) )
		$ecp1_feature_event_textcolor = $_POST['ecp1_feature_text'];

	// Are there any external calendar fields (existing)
	$ecp1_external_cals = array();
	$providers = ecp1_calendar_providers();
	$_existing = isset( $_POST['ecp1_existing_cals'] ) ? $_POST['ecp1_existing_cals'] : -1;
	for ( $i=0; $i < $_existing; $i++ ) {
		if ( isset( $_POST['ecp1_external_prov_' . $i] ) && isset( $_POST['ecp1_external_url_' . $i] )
				&& isset( $_POST['ecp1_external_col_' . $i] ) && isset( $_POST['ecp1_external_txtcol_' . $i] ) ) {
			$prv = $_POST['ecp1_external_prov_' . $i];
			$url = $_POST['ecp1_external_url_' . $i];
			$col = $_POST['ecp1_external_col_' . $i];
			$txt = $_POST['ecp1_external_txtcol_' . $i];

			if ( ! array_key_exists( $prv, $providers ) )
				$prv = null;
			if ( ! preg_match( '/#[0-9A-Fa-f]{6}/', $col ) )
				$col = $ecp1_calendar_fields['ecp1_local_event_color'][1];
			if ( ! preg_match( '/#[0-9A-Fa-f]{6}/', $txt ) )
				$txt = $ecp1_calendar_fields['ecp1_local_event_textcolor'][1];

			if ( null != $prv )
				$ecp1_external_cals[] = array( 'color'=>$col, 'text'=>$txt, 'url'=>''==$url?'':urlencode( trim( $url ) ), 'provider'=>$prv );
				// Note: this re-bases the numeric index key when items removed
				// the index key is not important it's just used for form names
		}
	}

	// Did someone try and add a new external calendar?
	if ( isset( $_POST['ecp1_external_prov_new'] ) && isset( $_POST['ecp1_external_url_new'] )
			&& isset( $_POST['ecp1_external_col_new'] ) && isset( $_POST['ecp1_external_txtcol_new'] ) ) {
		$prv = $_POST['ecp1_external_prov_new'];
		$url = $_POST['ecp1_external_url_new'];
		$col = $_POST['ecp1_external_col_new'];
		$txt = $_POST['ecp1_external_txtcol_new'];

		if ( ! array_key_exists( $prv, $providers ) )
			$prv = null;
		if ( __( 'Calendar URL (if required)' ) == $url )
			$url = '';
		if ( ! preg_match( '/#[0-9A-Fa-f]{6}/', $col ) )
			$col = $ecp1_calendar_fields['ecp1_local_event_color'][1];
		if ( ! preg_match( '/#[0-9A-Fa-f]{6}/', $txt ) )
			$txt = $ecp1_calendar_fields['ecp1_local_event_textcolor'][1];

		if ( null != $prv )
			$ecp1_external_cals[] = array( 'color'=>$col, 'text'=>$txt, 'url'=>''==$url?'':urlencode( trim( $url ) ), 'provider'=>$prv );
	}

	
	// Create an array to save as post meta (automatically serialized)
	$save_fields = array();
	foreach( array_keys( $ecp1_calendar_fields ) as $key ) {
		if ( ! isset( $$key ) || ! isset( $ecp1_calendar_fields[$key][1] ) )
			continue; // don't store values that don't exist
		if ( $$key != $ecp1_calendar_fields[$key][1] ) // i.e. not default
			$save_fields[$key] = $$key;
	}
	
	// Save the post meta information
	update_post_meta( $post->ID, 'ecp1_calendar', $save_fields );
}

// Don't close the php interpreter
/*?>*/
