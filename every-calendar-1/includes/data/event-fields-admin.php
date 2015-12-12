<?php
/**
 * Defines admin hooks for managaging the meta fields for the event post type
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Make sure the plugin settings and event fields have been loaded
require_once( ECP1_DIR . '/includes/data/ecp1-settings.php' );
require_once( ECP1_DIR . '/includes/data/event-fields.php' );
require_once( ECP1_DIR . '/includes/mapstraction/controller.php' );

// Load the scheduler
require_once( ECP1_DIR . '/includes/scheduler.php' );

// Add filters and hooks to add columns and display them
add_filter( 'manage_edit-ecp1_event_columns', 'ecp1_event_edit_columns' );
add_action( 'manage_posts_custom_column', 'ecp1_event_custom_columns' );
add_action( 'admin_init', 'ecp1_event_meta_fields' );
add_action( 'admin_print_footer_scripts', '_ecp1_event_render_admin_init_js' );

// Global variable to store footer js code for on init
$_ecp1_event_admin_init_js = '';

// Function hooked to the print footer scripts to print the above init JS
function _ecp1_event_render_admin_init_js() {
	global $_ecp1_event_admin_init_js, $post_type;

	// Only needed for Event Post Type
	if ( 'ecp1_event' != $post_type )
		return;

	if ( '' != $_ecp1_event_admin_init_js ) {
		printf( '%s<!-- Every Calendar +1 Init -->%s<script type="text/javascript">/* <![CDATA[ */%s%s%s/* ]]> */</script>%s', "\n", "\n", "\n", $_ecp1_event_admin_init_js, "\n", "\n" );
	}

}

// Function that adds extra columns to the post type
function ecp1_event_edit_columns( $columns ) {

	$columns = array(
		'title' => 'What', # Default field title
		'ecp1_dates' => 'When', # Will show From:... <br/>To: ...
		'ecp1_location' => 'Where', # Where the event is happening
		'ecp1_summary' => 'In Brief', # Brief details
		'author' => 'Author',
	);
	
	return $columns;
}

// Function that adds values to the custom columns
function ecp1_event_custom_columns( $column ) {
	global $ecp1_event_fields, $post_type;

	// Only do this if we're loading events
	if ( 'ecp1_event' != $post_type )
		return;

	// Make sure the meta event is loaded and get time fomatting ready
	_ecp1_parse_event_custom();
	$datef = get_option( 'date_format' );
	$timef = get_option( 'time_format' );
	$tz = new DateTimeZone( $ecp1_event_fields['_meta']['calendar_tz'] );
	
	// act based on the column that is being rendered
	switch ( $column ) {

		case 'ecp1_dates':
			try {
				$allday = $ecp1_event_fields['ecp1_full_day'][0];
				$start = $ecp1_event_fields['ecp1_start_ts'][0];
				$end = $ecp1_event_fields['ecp1_end_ts'][0];
				$rpt = $ecp1_event_fields['ecp1_repeating'][0];

				if ( '' != $start && is_numeric( $start ) ) {
					// Output the start date
					$start = new DateTime( "@$start" );
					$start->setTimezone( $tz );
					$outstr = sprintf( '<strong>%s:</strong> %s<br/>', __( 'Start' ), 
							$start->format( $datef . ' ' . $timef ) );
				
					// If an end date was supplied use it
					if ( '' != $end && is_numeric( $end ) ) {
						$end = new DateTime( "@$end" );
						$end->setTimezone( $tz );
						$outstr .= sprintf( '<strong>%s:</strong> %s', __( 'End' ), 
								$end->format( $datef . ' ' . $timef ) );
					} else {
						$outstr .= __( 'No end date given.' );
					}
				
					// Add the timezone as a string to the output
					$outstr .= sprintf( '<br/>%s', ecp1_timezone_display( $tz->getName() ) );

					// Note that this event runs all day if it does
					if ( 'Y' == $allday )
						$outstr .= sprintf( '<br/>%s', __( 'Running all day' ) );

					// If this is a repeating event or not
					if ( 'Y' == $rpt )
						$outstr .= sprintf( '<br/>%s', __( 'Event repeats' ) );

				} else {
					$outstr = __( 'No start date given.' );
				}
			} catch( Exception $tserror ) {
				$outstr = __( 'Invalid date stored in database, please correct it.' );
			}
			
			printf( $outstr );
			break;
		
		case 'ecp1_location':
			$outstr = htmlspecialchars( $ecp1_event_fields['ecp1_location'][0] );
			if ( ! _ecp1_event_meta_is_default( 'ecp1_coord_lat' ) || ! _ecp1_event_meta_is_default( 'ecp1_coord_lng' ) ) {
				$outstr .= sprintf('<br/><em>%s:</em> %s', __( 'Lat' ), $ecp1_event_fields['ecp1_coord_lat'][0] );
				$outstr .= sprintf('<br/><em>%s:</em> %s', __( 'Long' ), $ecp1_event_fields['ecp1_coord_lng'][0] );
			}
			
			printf( $outstr );
			break;
		
		case 'ecp1_summary':
			if ( ! _ecp1_event_meta_is_default( 'ecp1_featured' ) && 'Y' == _ecp1_event_meta( 'ecp1_featured' ) )
				printf( '<strong>%s</strong><br/>', __( 'Feature Event' ) );
			printf( '%s', htmlspecialchars( $ecp1_event_fields['ecp1_summary'][0] ) );
			break;
		
	}
}

// Function that registers a meta form box on the ecp1_event create / edit page
function ecp1_event_meta_fields() {
	add_meta_box( 'ecp1_event_meta', 'Event Details', 'ecp1_event_meta_form', 'ecp1_event', 'normal', 'high' );
}

// Function that generates a html section for adding inside a meta fields box
function ecp1_event_meta_form() {
	global $ecp1_event_fields, $_ecp1_event_admin_init_js, $post, $post_ID, $last_user, $last_id;

	// Load a list of calendars this user has access to
	$calendars = _ecp1_current_user_calendars();

	// The user must be able to edit the calendar to put events on it
	// Note the calendar edit level can be a contributor who's calendar
	// post edits would need to be on their own cals + review approved
	if ( 0 == count( $calendars ) ) {
		printf( '<div class="ecp1_error">%s</div>', __( 'No calendars found! Please create a calendar first.' ) );
		return;
	}
	
	// Load the event meta data from the database
	_ecp1_parse_event_custom();
	
	// Sanitize and do security checks
	$ecp1_summary = _ecp1_event_meta_is_default( 'ecp1_summary' ) ? '' : esc_textarea( $ecp1_event_fields['ecp1_summary'][0] );
	$ecp1_url = _ecp1_event_meta_is_default( 'ecp1_url' ) ? '' : urldecode( $ecp1_event_fields['ecp1_url'][0] );
	$ecp1_description = _ecp1_event_meta_is_default( 'ecp1_description' ) ? '' : $ecp1_event_fields['ecp1_description'][0];
	$ecp1_calendar = _ecp1_event_meta_is_default( 'ecp1_calendar' ) ? '-1' : $ecp1_event_fields['ecp1_calendar'][0];
	$ecp1_full_day = _ecp1_event_meta_is_default( 'ecp1_full_day' ) ? 'N' : $ecp1_event_fields['ecp1_full_day'][0];
	$ecp1_featured = _ecp1_event_meta_is_default( 'ecp1_featured' ) ? 'N' : $ecp1_event_fields['ecp1_featured'][0];
	
	$ecp1_location = _ecp1_event_meta_is_default( 'ecp1_location' ) ? '' : esc_textarea( $ecp1_event_fields['ecp1_location'][0] );
	$ecp1_lat = _ecp1_event_meta_is_default( 'ecp1_coord_lat' ) ? null : $ecp1_event_fields['ecp1_coord_lat'][0];
	$ecp1_lng = _ecp1_event_meta_is_default( 'ecp1_coord_lng' ) ? null : $ecp1_event_fields['ecp1_coord_lng'][0];
	$ecp1_zoom = _ecp1_event_meta_is_default( 'ecp1_map_zoom' ) ? 1 : $ecp1_event_fields['ecp1_map_zoom'][0];
	$ecp1_placemarker = _ecp1_event_meta_is_default( 'ecp1_map_placemarker' ) ? '' : urldecode( $ecp1_event_fields['ecp1_map_placemarker'][0] );
	$ecp1_showmarker = _ecp1_event_meta_is_default( 'ecp1_showmarker' ) ? 'Y' : $ecp1_event_fields['ecp1_showmarker'][0];
	$ecp1_showmap = _ecp1_event_meta_is_default( 'ecp1_showmap' ) ? 'Y' : $ecp1_event_fields['ecp1_showmap'][0];

	$ecp1_start_date = _ecp1_event_meta_is_default( 'ecp1_start_ts' ) ? '' : date( 'Y-m-d', $ecp1_event_fields['ecp1_start_ts'][0] );
	$ecp1_start_time = _ecp1_event_meta_is_default( 'ecp1_start_ts' ) ? '' : $ecp1_event_fields['ecp1_start_ts'][0];
	$ecp1_end_date = _ecp1_event_meta_is_default( 'ecp1_end_ts' ) ? '' : date( 'Y-m-d', $ecp1_event_fields['ecp1_end_ts'][0] );
	$ecp1_end_time = _ecp1_event_meta_is_default( 'ecp1_end_ts' ) ? '' : $ecp1_event_fields['ecp1_end_ts'][0];

	// The current calendars timezone
	$tz = new DateTimeZone( $ecp1_event_fields['_meta']['calendar_tz'] );

	// Load the start date/time if possible
	$ecp1_start_date = $ecp1_start_time = '';
	if ( ! _ecp1_event_meta_is_default( 'ecp1_start_ts' ) ) {
		try {
			$d = new DateTime( '@' . $ecp1_event_fields['ecp1_start_ts'][0] );
			$d->setTimezone( $tz );
			$ecp1_start_date = $d->format( 'Y-m-d' );
			$ecp1_start_time = $d; // formatted later
		} catch( Exception $serror ) {
			$ecp1_start_date = $ecp1_start_time = '';
			printf( '<div class="ecp1_error">%s</div>', __( 'ERROR: Could not parse start date/time please re-enter.' ) );
		}
	}

	// Load the end date/time if possible
	$ecp1_end_date = $ecp1_end_time = '';
	if ( ! _ecp1_event_meta_is_default( 'ecp1_end_ts' ) ) {
		try {
			$d = new DateTime( '@' . $ecp1_event_fields['ecp1_end_ts'][0] );
			$d->setTimezone( $tz );
			$ecp1_end_date = $d->format( 'Y-m-d' );
			$ecp1_end_time = $d; // formatted later
		} catch( Exception $eerror ) {
			$ecp1_end_date = $ecp1_end_time = '';
			printf( '<div class="ecp1_error">%s</div>', __( 'ERROR: Could not parse end date/time please re-enter.' ) );
		}
	}

	// Get the list of secondary calendars this event can appear on
	$ecp1_extra_cals = _ecp1_event_meta_is_default( 'ecp1_extra_cals' ) ? array() : $ecp1_event_fields['ecp1_extra_cals'][0];
	$ecp1_overwrite_color = _ecp1_event_meta_is_default( 'ecp1_overwrite_color' ) ? 'N' : $ecp1_event_fields['ecp1_overwrite_color'][0];
	$ecp1_local_textcolor = _ecp1_event_meta_is_default( 'ecp1_local_textcolor' ) ? '' : $ecp1_event_fields['ecp1_local_textcolor'][0]; 
	$ecp1_local_color = _ecp1_event_meta_is_default( 'ecp1_local_color' ) ? '' : $ecp1_event_fields['ecp1_local_color'][0];

	// Repeating event details
	$ecp1_repeating = _ecp1_event_meta_is_default( 'ecp1_repeating' ) ? 'N' : $ecp1_event_fields['ecp1_repeating'][0];
	$ecp1_repeat_pattern = _ecp1_event_meta_is_default( 'ecp1_repeat_pattern' ) ? '' : $ecp1_event_fields['ecp1_repeat_pattern'][0];
	$ecp1_repeat_custom = _ecp1_event_meta_is_default( 'ecp1_repeat_custom_expression' ) ? '' : $ecp1_event_fields['ecp1_repeat_custom_expression'][0];
	$ecp1_repeat_parameters = _ecp1_event_meta_is_default( 'ecp1_repeat_pattern_parameters' ) ? array() : $ecp1_event_fields['ecp1_repeat_pattern_parameters'][0];
	$ecp1_repeat_end_type = _ecp1_event_meta_is_default( 'ecp1_repeat_termination' ) ? '' : $ecp1_event_fields['ecp1_repeat_termination'][0];
	$ecp1_repeat_end_param = _ecp1_event_meta_is_default( 'ecp1_repeat_terminate_at' ) ? '' : $ecp1_event_fields['ecp1_repeat_terminate_at'][0];

	// If the calendar selected is not editable by the user then they're cheating
	if ( ! _ecp1_event_meta_is_default( 'ecp1_calendar' ) && ! current_user_can( 'edit_' . ECP1_CALENDAR_CAP, $ecp1_calendar ) )
		wp_die( __( 'You can not change event details on a calendar you are not allowed to edit.' ) );

	// Does this event have any Gravity Forms meta?
	// If so provide a method to import it - VERY BETA use with caution:
	// please report bugs with this.
	if ( _ecp1_event_gravity_meta_exists() && ! _ecp1_event_ignore_gravity_meta() ) {
?>
	<div class="ecp1_meta" style="margin-bottom:20px;">
		<p><?php _e( 'Gravity Forms Custom Post Type data found for this event:' ); ?></p>
		<ul>
<?php
		foreach( $ecp1_event_fields['_meta']['_gravity_fields'] as $field )
			printf( '<li>%s = %s</li>', $field, htmlentities( get_post_meta( $post_ID, $field, true ) ) );
?>
		</ul>
		<input type="checkbox" id="import_gravity" name="import_gravity" value="1" />
		<label for="import_gravity"><?php _e( 'Tick this box and save event to import these values to the event (no other changes will be saved)' ); ?></label>
		<input type="submit" class="button-primary" value="<?php _e( 'Save' ); ?>" /><br/>
		<input type="checkbox" id="ignore_gravity" name="ignore_gravity" value="1" />
		<label for="ignore_gravity"><?php _e( 'Tick this box to ignore gravity values for this event.' ); ?></label>
		<p style="margin-top:20px;"><?php _e( 'You can edit these values using the custom fields below.' ); ?></p>
<?php
		if ( ECP1_PHP5 < 3 )
			printf( '<p><em>%s</em></p>', __( 'Note: Start/End import will NOT work for you - upgrade to PHP 5.3.0 or later!' ) );
?>
	</div>
<?php
	}
	
	// Output the meta box with a custom nonce
?>
	<input type="hidden" name="ecp1_event_nonce" id="ecp1_event_nonce" value="<?php echo wp_create_nonce( 'ecp1_event_nonce' ); ?>" />
	<div class="ecp1_meta">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="ecp1_calendar"><?php _e( 'Calendar' ); ?></label></th>
				<td>
					<select id="ecp1_calendar" name="ecp1_calendar" class="ecp1_select">
						<!-- <option value=""></option> -->
<?php
	// Iterate over the calendar list and print options
	foreach( $calendars as $cal ) {
		printf( '<option value="%s"%s>%s</option>', $cal->ID, $cal->ID == $ecp1_calendar ? ' selected="selected"' : '', $cal->post_title );
	}
?>
					</select>
					<span class="ecp1_floater_r">
						<input type="checkbox" id="ecp1_featured" name="ecp1_featured" value="1" <?php checked( 'Y', $ecp1_featured ); ?> />
						<label for="ecp1_featured"><strong><?php _e( 'Feature Event?' ); ?></strong></label>
						<br/><?php _e( 'Feature events can appear on other calendars (e.g. a global calendar).' ); ?>
					</span>
				</td>
			</tr>
<?php
	// If there are more than one calendar for this user
	if ( count( $calendars ) > 1 ) {
?>
			<tr valign="top">
				<th scope="row"><label for="ecp1_extra_cals"><?php _e( 'Also show on' ); ?></label></th>
				<td>
<?php
	// Iterate over the calendar list and print checkboxes
	$counter = 0;
	foreach( $calendars as $cal ) {
		if ( 0 == $counter ) 
			printf( '<div style="clear:both;">' );
		printf( '<span class="ecp1_checkbox_block"><input type="checkbox" id="ecp1_extra_cal_%s" name="ecp1_extra_cal[%s]" value="1"%s /> <label for="ecp1_extra_cal_%s">%s</label></span>',
			$cal->ID, $cal->ID, in_array( $cal->ID, $ecp1_extra_cals ) ? ' checked="checked"' : '', $cal->ID, $cal->post_title );
		$counter += 1;
		if ( 2 == $counter )
			printf( '</div>' );
	}
?>
				</td>
			</tr>
<?php
	} // more than one calendar
?>
			<tr valign="top">
				<th scope="row"><label for="ecp1_summary"><?php _e( 'Summary' ); ?></label></th>
				<td><textarea id="ecp1_summary" name="ecp1_summary" class="ecp1_med"><?php echo $ecp1_summary; ?></textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ecp1_url"><?php _e( 'Event Website' ); ?></label></th>
				<td>
					<input id="ecp1_url" name="ecp1_url" type="text" class="ecp1_w100" value="<?php echo $ecp1_url; ?>" />
					<br/><strong><?php _e( 'and / or full description' ); ?></strong><br/>
					<!-- Copied from WordPress wp-admin/edit-form-advanced.php -->
					<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
					<?php the_editor( $ecp1_description, 'ecp1_description' ); ?>
					<table id="post-status-info" cellspacing="0"><tbody><tr>
							<td id="wp-word-count"><?php printf( __( 'Word count: %s' ), '<span class="word-count">0</span>' ); ?></td>
							<td class="autosave-info">
							<span class="autosave-message">&nbsp;</span>
<?php
	if ( 'auto-draft' != $post->post_status ) {
			echo '<span id="last-edit">';
			if ( $last_id = get_post_meta( $post_ID, '_edit_last', true ) ) {
					$last_user = get_userdata( $last_id );
					printf( __( 'Last edited by %1$s on %2$s at %3$s' ), esc_html( $last_user->display_name ), mysql2date( get_option( 'date_format' ), $post->post_modified ), mysql2date( get_option( 'time_format' ), $post->post_modified ) );
			} else {
					printf( __( 'Last edited on %1$s at %2$s' ), mysql2date( get_option( 'date_format' ), $post->post_modified ), mysql2date( get_option( 'time_format' ), $post->post_modified ) );
			}
			echo '</span>';
	}
?>
							</td>
					</tr></tbody></table>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ecp1_start_date"><?php _e( 'Start' ); ?></label></th>
				<td>
					<input id="ecp1_start_date" name="ecp1_start_date" type="text" class="ecp1_datepick" value="<?php echo $ecp1_start_date; ?>" />
					<?php echo _ecp1_time_select_trio( 'ecp1_start_time', $ecp1_start_time ); ?>
					<label for="ecp1_full_day"><?php _e( 'Full day event?' ); ?></label>
						<input id="ecp1_full_day" name="ecp1_full_day" type="checkbox" value="1" <?php checked( 'Y', $ecp1_full_day ); ?>/><br/>
					<em><?php _e( 'Please enter date as YYYY-MM-DD or use the date picker' ); ?></em>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ecp1_end_date"><?php _e( 'Finish' ); ?></label></th>
				<td>
					<input id="ecp1_end_date" name="ecp1_end_date" type="text" class="ecp1_datepick" value="<?php echo $ecp1_end_date; ?>" />
					<?php echo _ecp1_time_select_trio( 'ecp1_end_time', $ecp1_end_time ); ?><br/>
					<em><?php _e( 'Please enter date as YYYY-MM-DD or use the date picker' ); ?></em>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Repeats' ); ?></th>
				<td>
					<ul style="margin:0;">
<?php
	if ( 'Y' == $ecp1_repeating ) {
		$cachecount = EveryCal_Scheduler::CountCache( $post_ID );
		printf( '<li><em>This event currently has %s cached future repeat%s</em></li>', $cachecount, $cachecount == 1 ? '' : 's' );
	}
?>
					<li>
						<input type="checkbox" id="ecp1_repeating" name="ecp1_repeating" value="1" <?php checked( 'Y', $ecp1_repeating ); ?> />
						<label for="ecp1_repeating"><?php _e( 'This event repeats' ); ?></label>
					</li>
					<li>
						<select id="ecp1_repeat_pattern" name="ecp1_repeat_pattern">
<?php
	if ( _ecp1_get_option( 'allow_custom_repeats' ) ) {
		printf( '<option value="-1"%s>%s</option>',
			'-1' == $ecp1_repeat_pattern ? ' selected="selected"' : '',
			__( 'Custom' ));
	}
	
	$repeat_params = array();
	foreach( EveryCal_RepeatExpression::$TYPES as $key=>$dtl ) {
		printf( '<option value="%s"%s>%s</option>', $key, $key == $ecp1_repeat_pattern ? ' selected="selected"' : '', $dtl['desc'] );
		if ( is_array( $dtl['params'] ) )
			$repeat_params[$key] = $dtl['params'];
	}
	printf( '</select>' );

	if ( _ecp1_get_option( 'allow_custom_repeats' ) ) {
		_e( 'or' );
?>
		<input type="text" id="ecp1_repeat_custom" name="ecp1_repeat_custom" value="<?php if ( '-1' == $ecp1_repeat_pattern ) print $ecp1_repeat_custom; ?>" />
		<label for="ecp1_repeat_custom"><?php _e( 'custom expression' ); ?></label> <a href="<?php echo plugins_url( '/docs/customexpressions.html', dirname( dirname( __FILE__ ) ) ); ?>" target="_blank" id="ecp1_cehelp">(help)</a>
<?php
	}
?>
	<div id="ecp1_repeat_pattern_parameters">
<?php
	foreach( $repeat_params as $k=>$rp ) {
		$s = sprintf( '<div id="ecp1_rpp_%s">', $k );
		foreach( $rp as $name=>$data ) {
			$pval = array_key_exists( $name, $ecp1_repeat_parameters ) ? $ecp1_repeat_parameters[$name] : null;
			$s .= __( $data['desc'] ) . ': ';
			if ( array_key_exists( 'choices', $data ) ) { // render choices as select box
				$s .= sprintf( '<select name="ecp1_rpp_%s[%s]">', $k, $name );
				if ( ! $data['required'] ) $s .= '<option value=""></option>';
				foreach( $data['choices'] as $val=>$choice ) {
					$s .= sprintf( '<option value="%s"%s>%s</option>',
						$val, $pval == $val ? ' selected="selected"' : '', __( $choice ) );
				}
				$s .= '</select>';
			} else { // not choices so must be text box
				$s .= sprintf( '<input type="text" name="ecp1_rpp_%s[%s]" value="%s" />', $k, $name, $pval);
			}
			if ( $data['required'] ) {
				$s .= __( ' (required)' );
			} else if ( array_key_exists( 'default', $data ) ) {
				$s .= sprintf( __( ' (default: %s)' ), $data['default'] );
			}
			$s .= '<br/>';
		}
		printf( '%s</div>', $s );
	}

	// If this is repeat until convert the timestamp to a date
	$until_date = null;
	if ( 'UNTIL' == $ecp1_repeat_end_type ) {
		try {
			$until_date = new DateTime( "@$ecp1_repeat_end_param" );
			$until_date->setTimezone( $tz );
		} catch( Exception $udex ) {
			// do nothing
		}
	}
?>
	</div>
					</li>
					<li>
	<input type="radio" id="ecp1_repeat_forever" name="ecp1_repeat_until" value="4EVA" <?php checked( '4EVA', $ecp1_repeat_end_type ); ?> />
	<label for="ecp1_repeat_forever"><?php _e( 'Repeats forever' ); ?></label><br/>
	
	<input type="radio" id="ecp1_repeat_ntimes" name="ecp1_repeat_until" value="XTIMES" <?php checked( 'XTIMES', $ecp1_repeat_end_type ); ?> />
	<label for="ecp1_repeat_ntimes"><?php _e( 'Repeats' ); ?></label>
	<input type="text" size="4" id="ecp1_repeat_ntimes_value" name="ecp1_repeat_ntimes_value" value="<?php if ( $ecp1_repeat_end_type == 'XTIMES' ) print $ecp1_repeat_end_param; ?>" />
	<label for="ecp1_repeat_ntimes_value"><?php _e( 'times' ); ?></label><br/>

	<input type="radio" id="ecp1_repeat_to_date" name="ecp1_repeat_until" value="UNTIL" <?php checked( 'UNTIL', $ecp1_repeat_end_type ); ?> />
	<label for="ecp1_repeat_to_date"><?php _e( 'Repeats until' ); ?></label>
	<input id="ecp1_repeat_to_date_value" name="ecp1_repeat_to_date_value" type="text" class="ecp1_datepick" value="<?php if ( 'UNTIL' == $ecp1_repeat_end_type && $until_date != null ) print $until_date->format( 'Y-m-d' ); ?>" /><br/>
	<em><?php _e( 'Please enter date as YYYY-MM-DD or use the date picker' ); ?></em>
					</li>
					</ul>
					<em><?php _e( 'Note: you can add exceptions and make changes to specific repeats below' ); ?></em>
				</td>
			</tr>
<?php
	// Create some javascript for repeat events
	$_ecp1_event_admin_init_js .= <<<ENDOFSCRIPT
// Setup on focus handlers for the repeat until patterns
jQuery(document).ready(function($) {
	$('#ecp1_repeat_pattern_parameters > div').hide();
	$('#ecp1_rpp_' + $('#ecp1_repeat_pattern').val()).slideDown();
	$('#ecp1_repeat_ntimes').change(function() { if ($(this).is(':checked')) { $('#ecp1_repeat_ntimes_value').focus(); } });
	$('#ecp1_repeat_ntimes_value').focus(function() { if (! $('#ecp1_repeat_ntimes').is(':checked') ) { $('#ecp1_repeat_ntimes').attr('checked', 'checked'); } });
	$('#ecp1_repeat_to_date').change(function() { if ($(this).is(':checked')) { $('#ecp1_repeat_to_date_value').focus(); } });
	$('#ecp1_repeat_to_date_value').focus(function() { if (! $('#ecp1_repeat_to_date').is(':checked') ) { $('#ecp1_repeat_to_date').attr('checked', 'checked'); } });

	$('#ecp1_repeat_pattern').change(function() {
		$('#ecp1_repeat_pattern_parameters > div').hide();
		var j = $('#ecp1_rpp_' + $(this).val());
		if (j.length > 0) { j.slideDown(); }
	});
});
ENDOFSCRIPT;
?>
			<tr valign="top">
				<th scope="row"><label for="ecp1_location"><?php _e( 'Location' ); ?></label></th>
				<td>
					<input id="ecp1_location" name="ecp1_location" type="text" class="ecp1_w75" value="<?php echo $ecp1_location; ?>" />
<?php

	// Are maps enabled and do we have a valid provider?
	if ( ECP1Mapstraction::MapsEnabled() ) {
		$provider = ECP1Mapstraction::GetProviderKey();
		if ( ECP1Mapstraction::ValidProvider( $provider ) ) {
			
			// Get the geocoder and convert null to javascript 'disabled'
			$geocoder = ECP1Mapstraction::GetGeocoderKey();
			if ( is_null( $geocoder ) ) {
				printf( '<button type="button" id="ecp1_event_geocode" disabled="disabled"><span class="strike">%s</span></button>', __( 'Lookup Address' ) );
				$geocoder = 'disabled';
			} else {
				$geocoder = ECP1Mapstraction::ProviderData( $geocoder, 'mxnid' );
				printf( '<button type="button" id="ecp1_event_geocode">%s</button>', __( 'Lookup Address' ) );
			}

			// Build an array of map placemarker icons
			$marker_path = plugins_url( '/img/mapicons', dirname( dirname( __FILE__ ) ) );
			$marker_icons = glob( ECP1_DIR . '/img/mapicons/*.png' );
			$marker_icons_str = '[';
			foreach( $marker_icons as $icon )
				$marker_icons_str .= sprintf( '"%s",', basename( $icon ) );
			$marker_icons_str = trim( $marker_icons_str, ',' ) . ']';
			$feature_icons_str = '["pin.png","information.png","zoom.png","downloadicon.png"]';

			// I18N / L10N strings for use in JavaScript
			$_map_default_str = __( 'Map Default' );
			$_show_event_details = __( 'Back to Edit Event' );
			$_loading_icons_message = __( 'Loading Icons' );

			// What is the Mapstraction ID of this provider?
			$map_provider = ECP1Mapstraction::ProviderData( $provider, 'mxnid' );

			// Mapstraction needs an initialization function to bootstrap the library
$_ecp1_event_admin_init_js .= <<<ENDOFSCRIPT
// Quick helper function
// http://stackoverflow.com/questions/18082/validate-numbers-in-javascript-isnumeric
function isNumber(n) { return !isNaN(parseFloat(n)) && isFinite(n); }

// Global variables for references
var ecp1_mapstraction = null;
var ecp1_geocoder = null;
var ecp1_marker = null;

// String values and arrays
var _mapDefaultIcon = '$_map_default_str';
var _iconsPath = '$marker_path';
var _featureIconsArray = $feature_icons_str;
var _iconsArray = $marker_icons_str;
var _showEventDetails = '$_show_event_details';
var _loadIconStr = '$_loading_icons_message';

// Load the Mapstraction map
jQuery(document).ready(function($) {
	// Sets the form fields for lat/long
	function ecp1SetLatLon(ll) {
		$('#ecp1_lat').val(ll.lat);
		$('#ecp1_lng').val(ll.lon);
	}

	// Create a LatLonPoint from the form or map center
	function ecp1ToFocalPoint() {
		var lat = $('#ecp1_lat').val();
		var lon = $('#ecp1_lng').val();
		if (isNumber(lat) && isNumber(lon))
			return new mxn.LatLonPoint(lat, lon);
		return new mxn.LatLonPoint(ecp1_mapstraction.getCenter());
	}

	// Function that updates the map with a marker at lat/long
	function ecp1DrawMapMarker() {
		// Mapstraction doesn't allow us to move the marker
		var placeAt = null;
		if ( ecp1_marker != null ) {
			placeAt = ecp1_marker.location;
			ecp1_mapstraction.removeMarker(ecp1_marker);
			ecp1_marker = null;
		}

		// Act based on if the marker is to be shown or not
		if ( $('#ecp1_showmarker').is(':checked') ) {
			// If an argument was given it should be the point to place
			if (arguments.length > 0) placeAt = arguments[0];
			if (placeAt == null) placeAt = ecp1ToFocalPoint();
			// We're displaying the marker so create it
			ecp1_marker = new mxn.Marker(placeAt);
			ecp1_marker.setDraggable(true);
			ecp1SetLatLon(placeAt);
			// Icon if set
			var icon = jQuery.trim($('#ecp1_marker').val());
			if (icon != '') {
				ecp1_marker.setIcon(_iconsPath + '/' + icon, [32,36], [16,36]);
			}
			// Add the marker to the map
			ecp1_mapstraction.addMarker( ecp1_marker );
			ecp1_marker.dragend.addHandler(function() { ecp1MarkerMoved(); });
		} else {
			// We're hiding the marker so center point is to be saved
			ecp1SetLatLon( ecp1_mapstraction.getCenter() );
		}
	}

	// Triggered whenever the map is moved / zoomed
	function ecp1MapViewChange(n, s, a) {
		var zm = ecp1_mapstraction.getZoom();
		$('#ecp1_zoom').val(zm);
		if ( ecp1_marker == null ) {
			// Marker is the point provider unless missing
			ecp1SetLatLon( ecp1_mapstraction.getCenter() );
		}
	}

	// Triggered whenever the marker is moved
	function ecp1MarkerMoved() {
		ecp1SetLatLon(ecp1_marker.location);
	}

	// Called when the geocoder completes
	function ecp1GeocodeComplete(location) {
		ecp1_mapstraction.setCenterAndZoom(location.point, 12);
		ecp1DrawMapMarker(location.point);
	}

	// Create and center the map
	var ecp1_mapstraction = new mxn.Mapstraction("ecp1-event-map", "$map_provider");
	var latV = $('#ecp1_lat').val();
	var lngV = $('#ecp1_lng').val();
	var zoomV = $('#ecp1_zoom').val();
	if (isNumber(latV) && isNumber(lngV)) {
		var cp = new mxn.LatLonPoint(latV, lngV);
		if (!isNumber(zoomV)) zoomV = 9;
		else zoomV = parseInt(zoomV);
		ecp1_mapstraction.setCenterAndZoom(cp, zoomV);
		ecp1DrawMapMarker(); // at center point
	} else {
		var tLL = new mxn.LatLonPoint(0, 0);
		ecp1_mapstraction.setCenterAndZoom(tLL, 1);
	}

	// Controls for the map
	ecp1_mapstraction.addControls({ pan: false, zoom: 'small', map_type: true });
	ecp1_mapstraction.setOption('enableScrollWheelZoom', true);

	// Track move and zoom change events
	ecp1_mapstraction.changeZoom.addHandler(ecp1MapViewChange);
	ecp1_mapstraction.endPan.addHandler(ecp1MapViewChange);

	// Create a geocoder if enabled
	if ( "$geocoder" != "disabled" ) {
		ecp1_geocoder = new mxn.Geocoder("$geocoder", ecp1GeocodeComplete);
	}

	// Listen to geocode enter key and lookup button click
	$('#ecp1_event_geocode').click(function() {
		var address = $.trim( $( '#ecp1_location' ).val() );
		if ( '' != address && ecp1_geocoder != null ) {
			// Geocode the address
			ecp1_geocoder.geocode(address);
		}
		// Do not allow this event to submit the form
		return false;
	});
	// and now the keypress event on the textbox
	$('#ecp1_location').keypress(function(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if ( 13 == code && $('#ecp1_event_geocode').length > 0 ) {
			$('#ecp1_event_geocode').click();
			return false; // prevent form submit
		} else {
			return true; // allow character to be typed
		}
	});

	// Events for handling marker details
	$( '#ecp1_showmarker' ).change(function() { ecp1DrawMapMarker(); });
	$( '#ecp1_reset_marker' ).css( { padding:'0 5px', cursor:'pointer' } ).click( function() {
		$( '#ecp1_marker' ).val( '' );
		$( '#ecp1_marker_preview' ).empty().text( _mapDefaultIcon );
		ecp1DrawMapMarker();
	} );

	// Controls for using a custom marker
	$( '#ecp1_change_marker' ).css( { padding:'0 5px', cursor:'pointer' } ).click( function() {
		var lm = $( '#_ecp1-map-icon' );
		if ( lm.length == 0 ) {
			$( 'body' ).append( $( '<div></div>' )
				.attr( { id:'_ecp1-map-icon' } ).css( { display:'none', 'z-index':99999 } ) );
			lm = $( '#_ecp1-map-icon' );
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
						.text( _showEventDetails )
						.click( function() {
							$( '#_ecp1-map-icon' ).remove();
						} ) ) )
				.append( $( '<div></div>' )
					.attr( { id:'_ecp1-icontainer' } )
					.css( { textAlign:'left', width:800 } ) ) );
		
		var ic = $( '#_ecp1-icontainer' );
        var comb = _featureIconsArray.concat( _iconsArray );
		for ( var i=0; i < comb.length; i++ )
			ic.append( $( '<span></span>' )
				.css( { display:'inline-block', margin:'2px' } )
			    .append( $( '<img>' )
					.attr( { alt:comb[i].split('.')[0], src:( _iconsPath + '/' + comb[i] ), id:comb[i] } )
		            .css( { cursor:'pointer' } )
					.click( function() {
						var part = $( this ).attr( 'id' );
						$( '#ecp1_marker' ).val( part );
						$( '#ecp1_marker_preview' ).empty().append( $( this ).clone() );
						ecp1DrawMapMarker()
						lm.find( 'div div a' ).first().click();
		            } ) ) );
	} );

});

ENDOFSCRIPT;
			
			// Write out a new line before adding the controls
			printf( '<br/>' ); // new line now

			// Next render a maps container with two checkboxes and four hidden inputs
			// the checkboxes are: 1) Show Placemarker and 2) Show This Map on Website
			// if (1) is checked it implies (2). The purpose of (2) is to allow a map
			// to be centered without a placemarker. The event will store center coords.
			//
			// The hidden inputs store the values for form submit.

			// Build a preview URL or text for markers
			$marker_preview = $_map_default_str;
			if ( '' != $ecp1_placemarker )
				$marker_preview = sprintf( '<img alt="Marker Image Not Found" title="Marker Image" src="%s"/>', plugins_url( '/img/mapicons/' . $ecp1_placemarker, dirname( dirname( __FILE__ ) ) ) );

?>
	<input type="hidden" id="ecp1_lat" name="ecp1_lat" value="<?php echo is_null( $ecp1_lat ) ? '' : $ecp1_lat; ?>" />
	<input type="hidden" id="ecp1_lng" name="ecp1_lng" value="<?php echo is_null( $ecp1_lng ) ? '' : $ecp1_lng; ?>" />
	<input type="hidden" id="ecp1_zoom" name="ecp1_zoom" value="<?php echo $ecp1_zoom; ?>" />
	<input type="hidden" id="ecp1_marker" name="ecp1_marker" value="<?php echo $ecp1_placemarker; ?>" />
	<div class="mapmeta">
		<ul>
			<li>
				<input type="checkbox" id="ecp1_showmarker" name="ecp1_showmarker" value="1" <?php checked( 'Y', $ecp1_showmarker ); ?>/>
				<label for="ecp1_showmarker"><?php _e( 'Show Placemarker?' ); ?></label>
				<span>
					<strong><?php _e( 'Marker:' ); ?></strong>
					<span id="ecp1_marker_preview"><?php echo $marker_preview; ?></span>
					<a id="ecp1_change_marker"><?php _e( 'Change Marker' ); ?></a>
					<a id="ecp1_reset_marker"><?php _e( 'Use Default' ); ?></a>
				</span>
			</li>
			<li>
				<input type="checkbox" id="ecp1_showmap" name="ecp1_showmap" value="1" <?php checked( 'Y', $ecp1_showmap ); ?>/>
				<label for="ecp1_showmap"><?php _e( 'Show Map on Event Page?' ); ?></label>
				<span><?php _e( 'Remember to save your changes.' ); ?></span>
			</li>
		</ul>
	</div>
	<div id="ecp1-event-map"></div>
<?php
		} // mapinstance not null
	} // use maps
?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="ecp1_event_colours"><?php _e( 'Event colours' ); ?></label></th>
				<td>
					<div>
						<input type="checkbox" id="ecp1_overwrite_color" name="ecp1_overwrite_color" value="1"<?php if ( 'Y' == $ecp1_overwrite_color ) printf( ' checked="checked"' ); ?> />
						<label for="ecp1_overwrite_color"><?php _e( 'Overwrite calendar colors?' ); ?></label></div>
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
	if ( 'Y' == $ecp1_repeating ) {
		$futureExceptions =  EveryCal_Exception::Count( $post_ID );
?>
			<tr>
				<th scope="row"><?php _e( 'Repeat Exceptions' ); ?></th>
				<td>
	<div>
		<p><?php _e ( 'You can create exceptions for the repeated events these allow you to update certain fields of the event for a particular repeat. <em>Note: you must enter the correct start date of the repeat for this to work.</em>' ); ?></p>
		<p><button id="add_new_exception" type="button"><?php _e( 'Add Exception' ); ?></button></p>
		<div id="new_exceptions_container">
			<input type="hidden" value="0" id="ecp1_exrpt_counter" name="ecp1_exrpt_counter" />
		</div>
	</div>
	<div>
		<strong><?php printf( __( 'There are %s future exceptions' ), $futureExceptions ); ?></strong>
		<?php if ( $futureExceptions > 0 ) printf( '<a id="show_hide_ecp1_exceptions" href="#toggle">%s</a>', __( '(show me)' ) ); ?>
		<ul id="ecp1_repeat_exceptions_existing">
<?php
	$exHTML = sprintf( '<ul style="margin:0;"><li><label style="width:75px;display:inline-block;">%s</label>', __( 'Change the: ' ) );
	foreach( EveryCal_Exception::$FIELDS as $key=>$dtl )
		$exHTML .= sprintf( '<span style="padding-right:10px;"><input type="checkbox" value="1" class="ecp1_extog" id="ecp1_extog_{{INDEX}}_%s" name="ecp1_exdata[{{INDEX}}][toggle][%s]" {{CHECKED_%s}}/> <label for="ecp1_extog_{{INDEX}}_%s">%s</label></span>', $key, $key, $key, $key, __( $dtl['label'] ) );
	$exHTML .= sprintf( '{{DELETE_BUTTON}}</li><li><label for="ecp1_exdata{{INDEX}}_repeat" style="width:75px;display:inline-block;">%s</label> <input type="text" value="{{START_DATE}}" class="ecp1_datepick" id="ecp1_exdata{{INDEX}}_repeat" name="ecp1_exdata[{{INDEX}}][repeat]" /> <label for="ecp1_exdata{{INDEX}}_desc">%s</label> <input type="text" value="{{DESCRIPTION}}" class="ecp1_w50" id="ecp1_exdata{{INDEX}}_desc" name="ecp1_exdata[{{INDEX}}][desc]" /></li><li><label style="width:75px;display:inline-block;">&nbsp;</label> <input type="checkbox" value="1" id="ecp1_exdata{{INDEX}}_cancel" name="ecp1_exdata[{{INDEX}}][cancel]" {{CHECKED_cancel}} /> <label for="ecp1_exdata{{INDEX}}_cancel">%s</label></li>{{CHANGES}}</ul>', __( 'Repeat start:' ), __( 'Description' ), __( 'Cancel event starting on this date' ) );
	$listtemplate = '<li>' . $exHTML . '</li>';
	// Construct a template change set for the above - bit repetitive but oh well
	$mychanges = '';
	foreach( EveryCal_Exception::$FIELDS as $key=>$dtl ) {
		$exHTML = str_replace( "{{CHECKED_$key}}", '', $exHTML ); // not checked
		$mychanges .= sprintf( '<li class="ecp1_exrow_%s" id="ecp1_exrow_{{INDEX}}_%s">', $key, $key );
		$mychanges .= sprintf( '<label for="ecp1_exdata{{INDEX}}_%s" style="width:75px;display:inline-block;">%s:</label> ', $key, __( $dtl['label'] ) );
		$mychanges .= EveryCal_Exception::Render( $key, "ecp1_exdata{{INDEX}}_$key", "ecp1_exdata[{{INDEX}}][$key]" );
		$mychanges .= array_key_exists( 'notes', $dtl ) ? '<br/><em>' . __( $dtl['notes'] ) . '</em>' : '';
		$mychanges .= '</li>';
	}
	$exHTML = str_replace(
		array( '{{DESCRIPTION}}', '{{CHANGES}}', '{{START_DATE}}', '{{CHECKED_cancel}}', '{{DELETE_BUTTON}}' ),
		array( __( 'Describe your changes' ), $mychanges, '', '', '' ),
		$exHTML
	);

	if ( $futureExceptions > 0 ) {
		$exceptions = EveryCal_Exception::Find( $post_ID, null, null ); // all future exceptions
		foreach( $exceptions as $exid=>$exception ) {
			// Calculate a string of all the changes in this exception
			$myout = $listtemplate;
			$mybaseid = 'ecp1_exdata' . $exid . '_';
			$mybasename = 'ecp1_exdata[' . $exid . ']';
			$mychanges = '';
			foreach( EveryCal_Exception::$FIELDS as $key=>$dtl ) {
				$mychanges .= sprintf( '<li class="ecp1_exrow_%s" id="ecp1_exrow_%s_%s">', $key, $exid, $key );
				$value = null;
				if ( array_key_exists( $key, $exception['changes'] ) ) {
					$value = $exception['changes'][$key];
					$myout = str_replace( "{{CHECKED_$key}}", 'checked="checked"', $myout );
				} else // if no value then not checked
					$myout = str_replace( "{{CHECKED_$key}}", '', $myout );
				$mychanges .= sprintf( '<label for="%s" style="width:75px;display:inline-block;">%s:</label> ', $mybaseid . $key, __( $dtl['label'] ) );
				$mychanges .= EveryCal_Exception::Render( $key, "$mybaseid$key", $mybasename . "[$key]", $value );
				if ( array_key_exists( 'notes', $dtl ) )
					$mychanges .= '<br/><em>' . __( $dtl['notes'] ). '</em>';
				$mychanges .= '</li>';
			}

			// Replace the placeholders
			$delbutton = sprintf( '<button type="button" id="ecp1_exdel_%s" class="ecp1_exdel">%s</button><input type="hidden" id="ecp1_exdata_%s_delete" name="ecp1_exdata[%s][delete]" value="0" />', $exid, __( 'Delete' ), $exid, $exid );
			printf( str_replace(
				array( '{{INDEX}}', '{{DESCRIPTION}}', '{{CHANGES}}', '{{START_DATE}}', '{{CHECKED_cancel}}', '{{DELETE_BUTTON}}' ),
				array( $exid, $exception['desc'], $mychanges, $exception['start'], $exception['is_cancelled'] ? ' checked="checked"' : '', $delbutton ),
				$myout
			) );
		}

	} else { printf( '<li><em>%s</em></li>', __( 'There are none to show' ) ); }
?>
		</ul>
	</div>
				</td>
			</tr>
<?php
		// Add some javascript for exception management
		$showMe = __( '(show me)' );
		$hideThem = __( '(hide them)' );
		$removeLine = __( 'cancel' );
		$_ecp1_event_admin_init_js .= <<<ENDOFSCRIPT
// Manage exception control buttons / links
jQuery(document).ready(function($) {
	$('#ecp1_repeat_exceptions_existing').hide();
	$('#show_hide_ecp1_exceptions').click(function() {
		var l = $('#ecp1_repeat_exceptions_existing');
		if (l.is(':visible')) { l.hide(); $(this).text('$showMe'); }
		else { l.slideDown(); $(this).text('$hideThem'); }
		return false;
	});

	// remove a row that already exists by hiding and setting delete field
	$('button.ecp1_exdel').click(function() {
		var splits = $(this).attr('id').split('_');
		$('#ecp1_exdata_' + splits[2] + '_delete').val('1');
		$(this).parent().parent().parent().hide();
		return false;
	});

	// hide / show rows in existing fields based on checkboxes
	$('input[type="checkbox"].ecp1_extog').each(function() {
		if ( ! $(this).is(':checked') ) {
			var splits = $(this).attr('id').split('_');
			$('#ecp1_exrow_' + splits[2] + '_' + splits[3]).hide();
		}
	});

	// Create some dynamic actions based on changing checkboxes
	function exComponentsToggle() {
		var splits = $(this).attr('id').split('_');
		if ( ! $(this).is(':checked') ) {
			$('#ecp1_exrow_' + splits[2] + '_' + splits[3]).hide();
		} else {
			$('#ecp1_exrow_' + splits[2] + '_' + splits[3]).slideDown();
		}
	}
	$('input[type="checkbox"].ecp1_extog').change(exComponentsToggle);
	
	// create new exception lines
	var exHTML = '$exHTML';
	$('#add_new_exception').click(function() {
		var count = parseInt( $('#ecp1_exrpt_counter').val() );
		var iHTML = exHTML.replace(/{{INDEX}}/g, 'n' + count);
		var line = $('<div></div>').css( {'display':'block' } )
			.append( $('<div></div>').css( {'float':'left'} ).html(iHTML) )
			.append( $('<div></div>').css( {'float':'left'} )
				.append( $('<button></button>').text('$removeLine').click(function() {
					$(this).parent().parent().remove();
				}) ) )
			.append( $('<hr/>').css( {'clear':'both', 'border':'none'} ) );
		$('#new_exceptions_container').append(line);
		$('#ecp1_exrpt_counter').val(count + 1);
		$('input[type="checkbox"].ecp1_extog').change(exComponentsToggle).each(exComponentsToggle);
		$('#new_exceptions_container .ecp1_datepick').datepicker( {
			dateFormat: 'yy-mm-dd',
			showOn: 'focus',
			numberOfMonths: 3
		} );
		return false;
	});
});
ENDOFSCRIPT;

	}
?>
		</table>
	</div>
<?php
	// DEBUG OUTPUT:
	//printf( '<pre>%s</pre>', print_r( $ecp1_event_fields, true ) );
}

// Returns a string of HH:MM:AM/PM select boxes for time entry
function _ecp1_time_select_trio( $base_key, $tsdt ) {
	$select_hours = $select_mins = $select_meridiem = '';
	if ( '' != $tsdt ) {
		$select_hours = $tsdt->format( 'g' );
		$select_mins = $tsdt->format( 'i' );
		$select_meridiem = $tsdt->format( 'A' );
	}
	
	$outstr = sprintf( '<select id="%s-hour" name="%s-hour"><option value=""></option>', $base_key, $base_key );
	for( $i=1; $i<=12; $i++ )
		$outstr .= sprintf( '<option value="%s"%s>%s</option>', $i, $i == $select_hours ? ' selected="selected"' : '', $i );
	$outstr .= sprintf( '</select><select id="%s-min" name="%s-min"><option value=""></option>', $base_key, $base_key );
	for( $i=0; $i<=59; $i++ ) {
		$display_i = $i < 10 ? '0' . $i : $i;
		$outstr .= sprintf( '<option value="%s"%s>%s</option>', $display_i, $display_i == $select_mins ? ' selected="selected"' : '', $display_i );
	}
	$outstr .= sprintf( '</select><select id="%s-ante" name="%s-ante">', $base_key, $base_key );
	foreach( array( 'AM' => __( 'AM' ), 'PM' => __( 'PM' ) ) as $ante=>$title )
		$outstr .= sprintf( '<option value="%s"%s>%s</option>', $ante, $ante == $select_meridiem ? ' selected="selected"' : '', $title );
	$outstr .= '</select>';
	return $outstr;
}

// Save the data when the meta box is submitted
add_action( 'save_post', 'ecp1_event_save' );
function ecp1_event_save() {
	global $post, $ecp1_event_fields;
	if ( ! isset( $post ) )
		return; // don't update if not a post
	if ( 'revision' == $post->post_type )
		return; // don't update on revisions
	if ( 'ecp1_event' != $post->post_type )
		return; // don't update non-events
	
	// No nonce means auto-save which we're not supporting
	if ( ! isset( $_POST['ecp1_event_nonce'] ) )
		return $post->ID;
	// Verify the nonce just incase
	if ( ! wp_verify_nonce( $_POST['ecp1_event_nonce'], 'ecp1_event_nonce' ) )
		return $post->ID;
	
	// Verify the user can actually edit posts
	if ( ! current_user_can( 'edit_' . ECP1_EVENT_CAP, $post->ID ) )
		return $post->ID;

	// Check if importing Gravity Form fields if so do that and return
	if ( isset( $_POST['import_gravity'] ) && '1' == $_POST['import_gravity'] ) {
		_ecp1_parse_event_custom();
		_ecp1_event_gravity2ecp1();
		return $post->ID;
	}

	// Check if the user wants to ignore Gravity Form data
	$gravity_ignore = $ecp1_event_fields['gravity_ignore'][1];
	if ( isset( $_POST['ignore_gravity'] ) && '1' == $_POST['ignore_gravity'] )
		$gravity_ignore = 'Y';

	// Escape any nasty in the summary (it's meant to be HTML free)
	$ecp1_summary = $ecp1_event_fields['ecp1_summary'][1];
	if ( isset( $_POST['ecp1_summary'] ) )
		$ecp1_summary = sanitize_text_field( $_POST['ecp1_summary'] );
	
	// URL Encode the external URL
	$ecp1_url = $ecp1_event_fields['ecp1_url'][1];
	if ( isset( $_POST['ecp1_url'] ) )
		$ecp1_url = urlencode( $_POST['ecp1_url'] ) ;
	
	// Escape any nasty in the description
	$ecp1_description = $ecp1_event_fields['ecp1_description'][1];
	if ( isset( $_POST['ecp1_description'] ) )
		$ecp1_description = wp_filter_post_kses( $_POST['ecp1_description'] );
	
	// Is this a full day event?
	$ecp1_full_day = $ecp1_event_fields['ecp1_full_day'][1];
	if ( isset( $_POST['ecp1_full_day'] ) && '1' == $_POST['ecp1_full_day'] ) {
		$ecp1_full_day = 'Y';
	}

	// Is this a featured event?
	$ecp1_featured = 'N';
	if ( isset( $_POST['ecp1_featured'] ) && '1' == $_POST['ecp1_featured'] ) {
		$ecp1_featured = 'Y';
	}

	// Which calendar should this event go on?
	$ecp1_calendar = isset( $_POST['ecp1_calendar'] ) ? $_POST['ecp1_calendar'] : $ecp1_event_fields['ecp1_calendar'][1];
	if ( $ecp1_event_fields['ecp1_calendar'][1] != $ecp1_calendar ) {
		// If the calendar was set then check the user can edit it
		if ( ! current_user_can( 'edit_' . ECP1_CALENDAR_CAP, $ecp1_calendar ) )
			$ecp1_calendar = $ecp1_event_fields['ecp1_calendar'][1];
	}
	
	// Load the calendar so we can convert times to UTC
	_ecp1_parse_calendar_custom( $ecp1_calendar );
	$calendar_tz = new DateTimeZone( ecp1_get_calendar_timezone() ); // UTC if error
	
	// Which extra calendars should the event be displayed on
	$ecp1_extra_cals = $ecp1_event_fields['ecp1_extra_cals'][1];
	if ( isset( $_POST['ecp1_extra_cal'] ) && is_array( $_POST['ecp1_extra_cal'] ) ) {
		foreach( $_POST['ecp1_extra_cal'] as $cid=>$val ) {
			if ( current_user_can( 'edit_' . ECP1_CALENDAR_CAP, $cid ) )
				$ecp1_extra_cals[] = $cid;
		}
	}

	// Decide if a time was given
	$time_given = ! _ecp1_event_no_time_given();
	
	// Convert the Start Date + Time into a single UNIX time
	$ecp1_start_ts = $ecp1_event_fields['ecp1_start_ts'][1];
	if ( isset( $_POST['ecp1_start_date'] ) ) {
		// Dates should be in YYYY-MM-DD format by UI request
		$ds = date_create( $_POST['ecp1_start_date'], $calendar_tz );
		if ( FALSE === $ds ) // used procedural so don't have to catch exception
			return $post->ID;
		$ds->setTime( 0, 0, 1 ); // set to just after midnight if time not given
		
		// Do we have times?
		if ( isset( $_POST['ecp1_start_time-hour'] ) && isset( $_POST['ecp1_start_time-min'] ) && 
				isset( $_POST['ecp1_start_time-ante'] ) && ( '' != $_POST['ecp1_start_time-hour'] || '' != $_POST['ecp1_start_time-min'] ) ) {
			$meridiem = isset( $_POST['ecp1_start_time-ante'] ) ? $_POST['ecp1_start_time-ante'] : 'AM';
			$hours = isset( $_POST['ecp1_start_time-hour'] ) ? $_POST['ecp1_start_time-hour'] : 0;
			$hours = 'AM' == $meridiem ? (12 == $hours ? 0 : $hours) : (12 == $hours ? $hours : 12 + $hours); // convert to 24hr for setting time
			$mins = isset( $_POST['ecp1_start_time-min'] ) ? $_POST['ecp1_start_time-min'] : 0;
			$ds->setTime( $hours, $mins, 0 ); // 0 to undo the 1s above
		}
		
		// Save as a timestamp and reset the post values
		if ( ECP1_PHP5 < 3 ) // support 5.2.0
			$ecp1_start_ts = $ds->format( 'U' );
		else
			$ecp1_start_ts = $ds->getTimestamp(); // UTC (i.e. without offset)
		unset( $_POST['ecp1_start_date'] );
		unset( $_POST['ecp1_start_time-hour'] );
		unset( $_POST['ecp1_start_time-min'] );
		unset( $_POST['ecp1_start_time-ante'] );
	}
	
	// Convert the End Date + Time into a single UNIX time
	$ecp1_end_ts = $ecp1_event_fields['ecp1_end_ts'][1];
	if ( isset( $_POST['ecp1_end_date'] ) ) {
		// Dates should be in YYYY-MM-DD format by UI request
		$ds = date_create( $_POST['ecp1_end_date'], $calendar_tz );
		if ( FALSE === $ds ) // used procedural so don't have to catch exception
			return $post->ID;
		$ds->setTime( 23, 59, 59 ); // set to just before midnight if time not given
		
		// Do we have times?
		if ( isset( $_POST['ecp1_end_time-hour'] ) && isset( $_POST['ecp1_end_time-min'] ) && 
				isset( $_POST['ecp1_end_time-ante'] ) && ( '' != $_POST['ecp1_end_time-hour'] || '' != $_POST['ecp1_end_time-min'] ) ) {
			$meridiem = isset( $_POST['ecp1_end_time-ante'] ) ? $_POST['ecp1_end_time-ante'] : 'AM';
			$hours = isset( $_POST['ecp1_end_time-hour'] ) ? $_POST['ecp1_end_time-hour'] : 0;
			$hours = 'AM' == $meridiem ? (12 == $hours ? 0 : $hours) : (12 == $hours ? $hours : 12 + $hours); // convert to 24hr time
			$mins = isset( $_POST['ecp1_end_time-min'] ) ? $_POST['ecp1_end_time-min'] : 0;
			$ds->setTime( $hours, $mins, 0 ); // 0 to undo the 59s above
		}
		
		// Save as a timestamp and reset the post values
		if ( ECP1_PHP5 < 3 ) // support 5.2.0
			$ecp1_end_ts = $ds->format( 'U' );
		else
			$ecp1_end_ts = $ds->getTimestamp(); // UTC (i.e. without offset)
		unset( $_POST['ecp1_end_date'] );
		unset( $_POST['ecp1_end_time-hour'] );
		unset( $_POST['ecp1_end_time-min'] );
		unset( $_POST['ecp1_end_time-ante'] );
	}
	
	// If no times we're given then assume all day event
	if ( ! $time_given )
		$ecp1_full_day = 'Y';
	
	// Repeating event details
	$ecp1_repeating = 'N';
	if ( isset( $_POST['ecp1_repeating'] ) && '1' == $_POST['ecp1_repeating'] )
		$ecp1_repeating = 'Y';
	
	// The repeat pattern / expression
	$ecp1_repeat_pattern = $ecp1_event_fields['ecp1_repeat_pattern'][1];
	$ecp1_repeat_custom_expression = $ecp1_event_fields['ecp1_repeat_custom_expression'][1];
	if ( isset( $_POST['ecp1_repeat_pattern'] ) )
		$ecp1_repeat_pattern = $_POST['ecp1_repeat_pattern'];
	if ( isset( $_POST['ecp1_repeat_custom'] ) ) {
		$ecp1_repeat_custom_expression = $_POST['ecp1_repeat_custom'];
		unset( $_POST['ecp1_repeat_custom'] );
	}

	// Parameters for the repeated pattern
	$ecp1_repeat_pattern_parameters = $ecp1_event_fields['ecp1_repeat_pattern_parameters'][1];
	if ( array_key_exists( $ecp1_repeat_pattern, EveryCal_RepeatExpression::$TYPES ) ) {
		$posted = isset( $_POST['ecp1_rpp_' . $ecp1_repeat_pattern] ) ? $_POST['ecp1_rpp_' . $ecp1_repeat_pattern] : null;
		if ( is_array( $posted ) ) {
			foreach( EveryCal_RepeatExpression::$TYPES[$ecp1_repeat_pattern]['params'] as $name=>$options ) {
				$myval = isset( $posted[$name] ) ? $posted[$name] : ( !$options['required'] ? $options['default'] : null );
				if ( $myval != null )
					$ecp1_repeat_pattern_parameters[$name] = $myval;
			}
		}
	}
	// Cleanup the input array
	foreach( EveryCal_RepeatExpression::$TYPES as $k=>$rp ) {
		if ( is_array( $rp['params'] ) ) {
			unset( $_POST['ecp1_rpp_' + $k] ); // whole array in one
		}
	}
	
	// When the even repeats until
	$ecp1_repeat_termination = $ecp1_event_fields['ecp1_repeat_termination'][1];
	$ecp1_repeat_terminate_at = $ecp1_event_fields['ecp1_repeat_terminate_at'][1];
	if ( isset( $_POST['ecp1_repeat_until'] ) ) {
		$until = $_POST['ecp1_repeat_until'];
		if ( '4EVA' == $until ) {
			$ecp1_repeat_termination = $until;
		} else if ( 'XTIMES' == $until ) {
			$at = isset( $_POST['ecp1_repeat_ntimes_value'] ) ? $_POST['ecp1_repeat_ntimes_value'] : null;
			if ( is_numeric( $at ) ) {
				$ecp1_repeat_termination = $until;
				$ecp1_repeat_terminate_at = $at;
			}
		} else if ( 'UNTIL' == $until ) {
			$ds = date_create( $_POST['ecp1_repeat_to_date_value'], $calendar_tz );
			if ( FALSE !== $ds ) {
				$ecp1_repeat_termination = $until;
				if ( ECP1_PHP5 < 3 ) // support 5.2.0
					$ecp1_repeat_terminate_at = $ds->format( 'U' );
				else
					$ecp1_repeat_terminate_at = $ds->getTimestamp(); // UTC
			}
		}
	}
	// Cleanup the input array
	unset( $_POST['ecp1_repeat_until'] );
	unset( $_POST['ecp1_repeat_ntimes_value'] );
	unset( $_POST['ecp1_repeat_to_date_value'] );
	
	// The location as human address and lat/long coords
	$ecp1_location = $ecp1_event_fields['ecp1_location'][1];
	if ( isset( $_POST['ecp1_location'] ) )
		$ecp1_location = sanitize_text_field( $_POST['ecp1_location'] );

	// Yes if set No if not for show map/markers
	$ecp1_showmap = $ecp1_showmarker = 'N';
	if ( isset( $_POST['ecp1_showmap'] ) && '1' == $_POST['ecp1_showmap'] )
		$ecp1_showmap = 'Y';
	if ( isset( $_POST['ecp1_showmarker'] ) && '1' == $_POST['ecp1_showmarker'] )
		$ecp1_showmarker = 'Y';

	// Lat/Lng values are default or what is set
	$ecp1_coord_lat = $ecp1_event_fields['ecp1_coord_lat'][1];
	$ecp1_coord_lng = $ecp1_event_fields['ecp1_coord_lng'][1];
	if ( isset( $_POST['ecp1_lat'] ) && is_numeric( $_POST['ecp1_lat'] ) )
		$ecp1_coord_lat = $_POST['ecp1_lat'];
	if ( isset( $_POST['ecp1_lng'] ) && is_numeric( $_POST['ecp1_lng'] ) )
		$ecp1_coord_lng = $_POST['ecp1_lng'];

	// Zoom level will default to 1 if not set
	$ecp1_map_zoom = $ecp1_event_fields['ecp1_map_zoom'][1];
	if ( isset( $_POST['ecp1_zoom'] ) && is_numeric( $_POST['ecp1_zoom'] ) )
		$ecp1_map_zoom = $_POST['ecp1_zoom'];

	// The placemarker image should be a file in ECP1_DIR/img/mapicons
	$ecp1_map_placemarker = $ecp1_event_fields['ecp1_map_placemarker'][1];
	if ( isset( $_POST['ecp1_marker'] ) && file_exists( ECP1_DIR . '/img/mapicons/' . $_POST['ecp1_marker'] ) )
		$ecp1_map_placemarker = $_POST['ecp1_marker'];
	
	// Are we overwriting the calendar colors
	$ecp1_overwrite_color = $ecp1_event_fields['ecp1_overwrite_color'][1];
	$ecp1_local_textcolor = $ecp1_event_fields['ecp1_local_textcolor'][1];
	$ecp1_local_color = $ecp1_event_fields['ecp1_local_color'][1];
	if ( isset( $_POST['ecp1_overwrite_color'] ) && 1 == $_POST['ecp1_overwrite_color'] ) {
		$ecp1_overwrite_color = 'Y';
		if ( isset( $_POST['ecp1_local_text'] ) && preg_match( '/#[0-9A-Fa-f]{6}/', $_POST['ecp1_local_text'] ) )
			$ecp1_local_textcolor = $_POST['ecp1_local_text'];
		if ( isset( $_POST['ecp1_local_color'] ) && preg_match( '/#[0-9A-Fa-f]{6}/', $_POST['ecp1_local_color'] ) )
			$ecp1_local_color = $_POST['ecp1_local_color'];
	}
	
	// Create an array to save as post meta (automatically serialized)
	$save_fields_group = array();
	$save_fields_alone = array();
	$save_fields_multi = array();
	foreach( array_keys( $ecp1_event_fields ) as $key ) {
		if ( ! isset( $$key ) || ! isset( $ecp1_event_fields[$key][1] ) )
			continue; // only process if the variable is set
		if ( $$key != $ecp1_event_fields[$key][1] ) { // only where the value is NOT default
			if ( array_key_exists( $key, $ecp1_event_fields['_meta']['standalone'] ) ) {
				// for fields in _meta['standalone'] store to be saved separately
				// remember _meta['standalone'] = array( $ecp1_event_fields key => postmeta table key )
				// basically rename the fields key to the database key and write value for saving
				$save_fields_alone[$ecp1_event_fields['_meta']['standalone'][$key]] = $$key;
			} else {
				// for all other keys
				$save_fields_group[$key] = $$key;
			}
		}
		// For multiple key values MUST set even if is default
		if ( array_key_exists( $key, $ecp1_event_fields['_meta']['multiple_keys'] ) ) {
			// for all fields that need to be exploded
			// for array values where want one value per row in post meta
			$save_fields_multi[$ecp1_event_fields['_meta']['multiple_keys'][$key]] = $$key;
		} 
	}

	// Before saving track and changes to the event repeat details cache
	// this function call will set extra fields on the meta value arrays
	try {
		EveryCal_Scheduler::EventCacheUpdate( $post->ID, $save_fields_group, $save_fields_alone, $calendar_tz );
	} catch( Exception $cuex ) {
		return $post->ID; // don't save changes cache couldn't update
	}

	// Save the post meta information
	update_post_meta( $post->ID, 'ecp1_event', $save_fields_group );
	foreach( $save_fields_alone as $key=>$value )
		update_post_meta( $post->ID, $key, $value );
	foreach( $save_fields_multi as $key=>$values ) {
		delete_post_meta( $post->ID, $key ); // clear existing meta values
		foreach( $values as $value )
			add_post_meta( $post->ID, $key, $value );
	}

	// Finally process all of the repeat exceptions (if necessary)
	if ( 'Y' == $ecp1_repeating ) {

		/* The exceptions are POSTED using subarrays in POST
		 * ecp1_exdata[nX][ABC] - new exception X field ABC
		 * ecp1_exdata[X][ABC]  - existing exception db key X field ABC
		 * ecp1_exrpt_counter   - JS counter for repeat ID (not a counter)
		 *
		 * The field keys are based on EveryCal_Exception::$FIELDS
		 *
		 * All we do here is call the process function with each of the
		 * constructed base names (ecp1_exdata[X]) and the input array.
		 *
		 * It is possible for saved exceptions to be deleted by setting
		 * the field ecp1_exdata[X][delete] == 'Y' in those cases we use
		 * the delete function.
		 */
		
		$expost = array_key_exists( 'ecp1_exdata', $_POST ) ? $_POST['ecp1_exdata'] : null;
		if ( is_array( $expost ) ) {
			// Deal with each exception in order of the array
			foreach( $expost as $key=>$fields ) {
				// Is this a delete field request
				$to_delete = array_key_exists( 'delete', $fields ) && '1' == $fields['delete'] ? true : false;
				if ( $to_delete ) {
					EveryCal_Exception::Delete( $post->ID, $key );
					continue; // don't resave the fields
				}

				// Get the top level fields for the exception
				$description = array_key_exists( 'desc', $fields ) ? $fields['desc'] : '';
				$start_date = array_key_exists( 'repeat', $fields ) ? $fields['repeat'] : null;
				$is_cancelled = array_key_exists( 'cancel', $fields ) && $fields['cancel'] == 1 ? true : false;
				// Can't be saved without a start date so check
				if ( $start_date == null )
					continue; // skip to next exception

				// Setup the array to write to the database
				$changeset = array(
					'desc' => $description,
					'event_id' => $post->ID,
					'start' => $start_date,
					'is_exception' => true, // always true cause this is an exception
					'is_cancelled' => $is_cancelled,
					'changes' => array()
				);

				// Lookup the toggles for this exception
				$toggles = array_key_exists( 'toggle', $fields ) ? $fields['toggle'] : array();

				// For all the field types process the values into changes
				foreach( array_keys( EveryCal_Exception::$FIELDS ) as $field_type ) {
					if ( array_key_exists( $field_type, $toggles ) && '1' == $toggles[$field_type] )
						$changeset['changes'][$field_type] = EveryCal_Exception::Process( $field_type, $field_type, $fields );
				}

				// Store the new or update exception into the database
				EveryCal_Exception::Store( $post->ID, $changeset, $key );
			}
		}
	}
}

// Returns true if NO time given on start and finish date
function _ecp1_event_no_time_given() {
	// this is ugly but it does the job
	return (
			(
			( ! isset( $_POST['ecp1_start_time-hour'] ) && ! isset( $_POST['ecp1_start_time-min'] ) ) ||	// neither start given
			( '' == $_POST['ecp1_start_time-hour'] && '' == $_POST['ecp1_start_time-min'] ) 		// or both blank
			) && 			// AND
			(
			( ! isset( $_POST['ecp1_end_time-hour'] ) && ! isset( $_POST['ecp1_end_time-min'] ) ) ||		// neither end given
			( '' == $_POST['ecp1_end_time-hour'] && '' == $_POST['ecp1_end_time-min'] )				// or both blank
			)
		);
}

// Don't close the php interpreter
/*?>*/
