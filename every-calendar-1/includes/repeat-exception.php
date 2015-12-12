<?php
/**
 * Every Calendar Scheduler: Exceptions to Repeat Event Instances
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// We also need the helper functions
require_once( ECP1_DIR . '/functions.php' );

/**
 * ExceptionCoder Interface
 */
interface ExceptionCoder
{
	/**
	 * Renders the HTML string for this exception widget.
	 * @param $id The id for the field.
	 * @param $name The name for the field.
	 * @param $value The value for the field.
	 * @return The HTML for a textare.
	 */
	public function render( $id, $name, $value=null );

	/**
	 * Processes a given array of POSTed data and returns then
	 * computed value for the widget. This can be any value that
	 * is serializable however there are a few restrictions:
	 * a) The output MUST be in the format the render functions
	 *    $value parameter is expected to be in (for admin); and
	 * b) The output MUST be accepted by the update function.
	 *
	 * @param $key The posted values array key for processing.
	 * @param $values The array of POSTED values related to this field.
	 * @return Value this exception will swap into the event. Return
	 *         null if there is no value that should be swapped.
	 */
	public function process( $key, $values );

	/**
	 * Takes the serialized value from the process function and
	 * converts it into a value that can be directly swapped into
	 * the event data array. Will be given the original event fields
	 * array as a reference to update it.
	 *
	 * If the value parameter is null don't update.
	 *
	 * @param $event The event array fields for complex fields
	 * @param $meta_key The event field that is being updated
	 * @param $value The serialized value to be used in the event
	 * @return True or false if the event array was updated
	 */
	public function update( &$event, $meta_key, $value );
}

/**
 * MediumTextArea Exception Widget
 */
class MediumTextArea implements ExceptionCoder
{
	public function render( $id, $name, $value=null )
	{
		return str_replace(
			array( '{{id}}', '{{name}}', '{{value}}' ),
			array( $id, $name, $value == null ? '' : $value ),
			'<textarea id="{{id}}" name="{{name}}" class="ecp1_med">{{value}}</textarea>'
		);
	}

	public function process( $key, $values )
	{
		// Does the key exist in the array: if so use otherwise empty string
		return array_key_exists( $key, $values ) ? $values[$key] : null;
	}

	public function update( &$event, $meta_key, $value )
	{
		if ( $value == null )
			return false; // don't update with null
		$event[$meta_key] = $value; // simply replace the string
		return true;
	}
}

/**
 * DateTime Exception Widget
 *
 * NOTE: Values are stored as the actual date and time the
 * event field should be updated to. For consistency they
 * are set as at UTC. When using the author will expect to
 * see the date/time entered on the screen, not an adjusted
 * time to account for the timezone of the calendar.
 */
class DateTimeField implements ExceptionCoder
{
	public function render( $id, $name, $value=null )
	{
		$date_str = $select_hours = $select_mins = $select_meridiem = $outstr = '';

		// Try and parse the parameter if given
		if ( $value != null ) {
			try {
				$date = $value['date'] != null ? new DateTime( $value['date'], new DateTimeZone( 'UTC' ) ) : null;
				if ( $date != null )
					$date_str = $date->format( 'Y-m-d' );

				$time = $value['time_in_minutes'] != null ? $value['time_in_minutes'] : null;
				if ( $time != null ) {
					$select_mins = $time % 60;
					$select_hours = ( $time - $select_mins ) / 60; // rounding down
					// Compute AM | PM
					if ( $select_hours >= 12 ) {
						$select_hours -= 12;
						$select_meridiem = 'PM';
					} else {
						$select_meridiem = 'AM';
					}
					// Adjust midnight -> 1am back to a 12
					if ( $select_hours == 0 )
						$select_hours = 12;
				}
			} catch( Exception $serror ) {
				$date_str = $select_hours = $select_mins = $select_meridiem = '';
				$outstr = sprintf( '<div class="ecp1_error">%s</div>', __( 'ERROR: Could not parse start date/time please re-enter.' ) );
			}
		}
			
		// Date component
		$outstr .= sprintf( '<input type="text" id="%s_date" name="%s[date]" class="ecp1_datepick" value="%s" />', $id, $name, $date_str );

		// Hours
		$outstr .= sprintf( '<select id="%s_hour" name="%s[hour]"><option value=""></option>', $id, $name );
		for( $i=1; $i<=12; $i++ )
			$outstr .= sprintf( '<option value="%s"%s>%s</option>', $i, $i == $select_hours ? ' selected="selected"' : '', $i );

		// Minutes
		$outstr .= sprintf( '</select><select id="%s_min" name="%s[min]"><option value=""></option>', $id, $name );
		for( $i=0; $i<=59; $i++ ) {
			$display_i = $i < 10 ? '0' . $i : $i;
			$outstr .= sprintf( '<option value="%s"%s>%s</option>', $display_i, $display_i == $select_mins ? ' selected="selected"' : '', $display_i );
		}

		// Meridiem
		$outstr .= sprintf( '</select><select id="%s_ante" name="%s[ante]">', $id, $name );
		foreach( array( 'AM' => __( 'AM' ), 'PM' => __( 'PM' ) ) as $ante=>$title )
			$outstr .= sprintf( '<option value="%s"%s>%s</option>', $ante, $ante == $select_meridiem ? ' selected="selected"' : '', $title );
		$outstr .= '</select>';
		return $outstr;
	}

	public function process( $key, $values )
	{
		// This is more complex that the others: date | hour | min | ante
		// and is stored with both a date and time component so if one
		// isn't given update just modifies the given one.
		$vals = array_key_exists( $key, $values ) ? $values[$key] : null;
		$out = null;
		if ( is_array( $vals ) ) {
			$out = array( 'date' => null, 'time_in_minutes' => null );
			// Date should be Y-m-d format which doesn't need to be changed
			$dval = array_key_exists( 'date', $vals ) ? $vals['date'] : null;
			if ( $dval == '' ) $dval = null; // empty string means none
			$out['date'] = $dval;

			// Time needs to be compounded to minutes since midnight
			$ante = array_key_exists( 'ante', $vals ) && $vals['ante'] == 'PM' ? 1 : 0; // PM | AM
			$mins = array_key_exists( 'min', $vals ) && $vals['min'] != '' ? $vals['min'] : null;
			$hrs = array_key_exists( 'hour', $vals ) && $vals['hour'] != '' ? $vals['hour'] : null;
			if ( $hrs == 12 || ( $hrs === null && $mins !== null ) ) $hrs = 0; // midnight -> 1am
			if ( $hrs !== null && $mins === null ) $mins = 0; // set mins
			if ( $hrs !== null && $mins !== null ) { // set value in the array
				if ( $ante == 1 ) $hrs += 12; // adjust for PM
				$out['time_in_minutes'] = $hrs*60 + $mins;
			}
		}

		return $out; // the serializable result
	}

	public function update( &$event, $meta_key, $value )
	{
		if ( $value == null || !is_array( $value ) )
			return false; // don't update null
		
		// There should be two keys in the array: date | time_in_seconds
		// if both are given set the event to that date and time, if only
		// the date just change date, if only time just change time.
		$date = $value['date'] != null ? new DateTime( $value['date'], new DateTimeZone( 'UTC' ) ) : null;
		$time = $value['time_in_minutes']; // null if not needed
		$caltz = new DateTimeZone( 'UTC' );
		$etime = $otime = $event[$meta_key];
		try {
			$caltz = new DateTimeZone( _ecp1_get_calendar_timezone( $event['_meta']['calendar_tz'] ) );
		} catch( Exception $e ) { } // use the UTC zone

		// Two step process here:
		// 1) Compute the updated timestamp; and then
		// 2) Adjust for the calendar timezone
		// Because the timestamp should be at UTC not local time
		// but the exception handler stores at local time not UTC.
		try {
			$its = new DateTime( "@$etime" ); // requires PHP 5.2.0
			$its->setTimezone( $caltz );

			// Update the date component
			if ( $date != null ) {
				$y = $date->format( 'Y' ); // four digit year
				$m = $date->format( 'n' ); // no leading zero
				$d = $date->format( 'j' ); // no leading zero
				$its->setDate( $y, $m, $d );
			}

			// Update the time component
			if ( $time != null ) {
				$m = $time % 60;
				$h = ( $time - $m ) / 60; // round down
				$its->setTime( $h, $m );
			}

			// Modify the time to the new unix timestamp
			if ( ECP1_PHP5 < 3 ) { // support 5.2
				$otime = $its->format( 'U' );
			} else {
				$otime = $its->getTimestamp();
			}
		} catch( Exception $datex ) {
			return false; // don't process unknown dates
		}

		// Adjust event to computed UTC seconds since epoch and return
		$event[$meta_key] = $otime;
		return true;
	}
}

/**
 * Yes/No Radio Exception Widget
 */
class YesNoRadioChoice implements ExceptionCoder
{
	public function render( $id, $name, $value=null )
	{
		return sprintf( '<input type="radio" id="%s_Y" name="%s" value="Y"%s/> <label for="%s_Y">%s</label> <input type="radio" id="%s_N" name="%s" value="N"%s/> <label for="%s_N">%s</label>',
			$id, $name, $value == 'Y' ? ' checked="checked"' : '', $id, __( 'Yes' ),
			$id, $name, $value == 'N' ? ' checked="checked"' : '', $id, __( 'No' ) );
	}

	public function process( $key, $values )
	{
		$choice = array_key_exists( $key, $values ) ? $values[$key] : null;
		if ( 'Y' == $choice or 'N' == $choice )
			return $choice;
		return null; // null means don't store
	}

	public function update( &$event, $meta_key, $value )
	{
		if ( $value == null )
			return false; // don't update null
		$event[$meta_key] = $value;
		return true;
	}
}

/**
 * Long Textbox Exception Widget
 */
class LongTextBox implements ExceptionCoder
{
	public function render( $id, $name, $value=null )
	{
		return str_replace(
			array( '{{id}}', '{{name}}', '{{value}}' ),
			array( $id, $name, $value == null ? '' : $value ),
			'<input id="{{id}}" name="{{name}}" type="text" class="ecp1_w75" value="{{value}}" />'
		);
	}

	public function process( $key, $values )
	{
		// If the parameter is given return it otherwise null
		return array_key_exists( $key, $values ) ? $values[$key] : null;
	}

	public function update( &$event, $meta_key, $value )
	{
		if ( $value == null )
			return false; // don't update null
		$event[$meta_key] = $value;
		return true;
	}
}


class EveryCal_Exception
{

	/**
	 * Known FIELDS that an exception can over-ride. This is not all of
	 * the event fields because some don't make sense to change.
	 * NOTE: Do not put _ in the key or UI will not work.
	 */
	static $FIELDS = array(
		// Event summary can be changed to highlight differences
		'summary' => array( 'meta_key' => 'ecp1_summary', 'class' => 'MediumTextArea', 'label' => 'Summary' ),
		// Start and end times can be changed
		'start' => array( 'meta_key' => 'ecp1_start_ts', 'class' => 'DateTimeField', 'label' => 'Start' ),
		'finish' => array( 'meta_key' => 'ecp1_end_ts', 'class' => 'DateTimeField', 'label' => 'End' ),
		// Location - note doesn't change map
		'location' => array( 'meta_key' => 'ecp1_location', 'class' => 'LongTextBox', 'label' => 'Location',
			'notes' => '<em>This will <strong>NOT</strong> update the map! Either disable map or add note in summary.</em>' ),
		'showmap' => array( 'meta_key' => 'ecp1_showmap', 'class' => 'YesNoRadioChoice', 'label' => 'Show map' ),
	);

	/**
	 * Private function returns class for calling render/process/update on.
	 *
	 * @param $field_key The field key to find the class.
	 * @return Class object or class name depending on PHP version.
	 */
	private static function GetFieldClass( $field_key )
	{
		$field = array_key_exists( $field_key, EveryCal_Exception::$FIELDS ) ? EveryCal_Exception::$FIELDS[$field_key] : null;
		if ( $field == null )
			return null;
		$className = $field['class'];
		return new $className();
	}

	/**
	 * Calls the render function from an ExceptionCoder class
	 *
	 * @param $field_key The field type to call render on
	 * @param $id The id parameter for the render function
	 * @param $name The name parameter for the render function
	 * @param $value The value parameter for the render function
	 * @return The HTML string built by the render function
	 */
	public static function Render( $field_key, $id, $name, $value=null )
	{
		// Get the class
		$class = EveryCal_Exception::GetFieldClass( $field_key );
		if ( $class == null )
			return sprintf( '<div class="ecp1_error">Invalid FIELD="%s" for exception renderer</div>', $field_key );

		// Call the render function on the class
		return $class->render( $id, $name, $value );
		//return $class::render( $id, $name, $value );
	}

	/**
	 * Calls the process function from an ExceptionCoder class
     *
	 * @param $field_key The field type to call process on
	 * @param $key The key parameter for the process function
	 * @param $values The values parameter for the process function
	 * @return The result of the process function or null
	 */
	public static function Process( $field_key, $key, $values )
	{
		// Get the class
		$class = EveryCal_Exception::GetFieldClass( $field_key );
		if ( $class == null )
			return null; // unknown field

		// Call the process function on the class
		return $class->process( $key, $values );
		//return $class::process( $key, $values );
	}

	/**
	 * Calls the update function from an ExceptionCoder class
	 * 
	 * @param $field_key The field type to call update on
	 * @param $event The event field array reference for update function
	 * @param $value The value parameter for the update function
	 * @return True or false the same as update or null
	 */
	public static function Update( $field_key, &$event, $value )
	{
		// Get the class
		$class = EveryCal_Exception::GetFieldClass( $field_key );
		if ( $class == null )
			return null;

		// Get the meta key this field represents
		$meta_key = EveryCal_Exception::$FIELDS[$field_key]['meta_key'];

		// Call the update function on the class
		return $class->update( $event, $meta_key, $value );
		//return $class::update( $event, $meta_key, $value );
	}

	/**
	 * Deletes an exception from the database but leaves the cached
	 * repeat instance - so the cache is not dirty.
	 *
	 * @param $event_id - The event to delete the exception from
	 * @param $cache_id - The id of the cached repeat exception is attached to
	 * @return True or false depending if could be deleted
	 */
	public static function Delete( $event_id, $cache_id )
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'ecp1_cache';
		$count = $wpdb->update( $table_name,
			array( // remove exception details
				'is_exception' => 0,
				'changes' => null,
			), 
			array( // for the matched cache id
				'cache_id' => $cache_id,
				'post_id' => $event_id,
				'is_exception' => 1
			)
		);

		// return the success or failure
		return is_numeric( $count ) && $count >= 1;
	}

	/**
	 * Count (public static)
	 * Returns the number of exceptions after a reference point which
	 * is either the given date or the current date if not given.
	 *
	 * @param $event_id The event to look at
	 * @param $start_from The date to start count from (DateTime object)
	 * @return Number of exceptions after start date (0 if not repeating)
	 */
	public static function Count( $event_id, $start_from=null )
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "ecp1_cache";
		if ( $start_from == null )
			$start_from = new DateTime( null, new DateTimeZone( 'UTC' ) ); // get date as utc
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE post_id = %s AND is_exception=1 AND start >= '%s'", $event_id, $start_from->format( 'Y-m-d' ) ) );
		if ( ! $count ) $count = 0;
		return $count;
	}
	
	/**
	 * Find (public static)
	 * Returns an array of exceptions for the given event id
	 * whose start date matches the given parameter (DateTime).
	 * If start and end are not given will default to current
	 * system time and all exceptions after it.
	 *
	 * @param $event_id The event to look for exceptions on.
	 * @param $start_from The time the repetition list starts (DateTime object).
	 * @param $until The time repetition list ends (optional Datetime object).
	 * @return An array of exception details keyed by id.
	 */
	public static function Find( $event_id, $start_from=null, $until=null )
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "ecp1_cache";
		if ( $start_from == null )
			$start_from = new DateTime( null, new DateTimeZone( 'UTC' ) ); // get date as utc
		$query = null;
		if ( $until == null ) {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %s AND is_exception=1 AND start >= '%s' ORDER BY start", $event_id, $start_from->format( 'Y-m-d' ) );
		} else {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %s AND is_exception=1 AND start >= '%s' AND start <= '%s' ORDER BY start", $event_id, $start_from->format( 'Y-m-d' ), $until->format( 'Y-m-d' ) );
		}

		$found = array(); // id => desc | event_id | start | is_exception | changes
		$results = $wpdb->get_results( $query ); // object array numerically indexed
		foreach( $results as $change ) {
			// Unserialise and parse the actual changes
			$description = '';
			$eventCancelled = false;
			$changeset = array();
			if ( $change->changes ) {
				$loadedset = unserialize( $change->changes );
				if ( is_array( $loadedset ) ) {
					$description = array_key_exists( 'description', $loadedset ) ? $loadedset['description'] : '';
					$eventCancelled = array_key_exists( 'is_cancelled', $loadedset ) ? $loadedset['is_cancelled'] : false;
					foreach( array_keys( EveryCal_Exception::$FIELDS ) as $key ) {
						if ( array_key_exists( $key, $loadedset ) ) {
							$changeset[$key] = $loadedset[$key];
						}
					}
				}
			}

			// Create an entry for this row
			$found[$change->cache_id] = array(
				'desc' => $description, // out of changes
				'event_id' => $change->post_id,
				'start' => $change->start,
				'is_exception' => $change->is_exception == 1 ? true : false, // always true here
				'is_cancelled' => $eventCancelled,
				'changes' => $changeset,
			);
		}

		return $found; // output the array
	}

	/**
	 * Stores an exception in the database
	 *
	 * @param $event_id - The event to store this exception for
	 * @param $fields - Array that matches results from Find function
	 * @param $cache_id - Database ID for this exception or non-numeric if new
	 * @return True or false based on ability to save.
	 */
	public static function Store( $event_id, $fields, $cache_id )
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'ecp1_cache';
		// Construct fields for the database and collate the changes field
		// do not store NULL values from the fields array into the database.
		$changeset = array();
		$exStart = array_key_exists( 'start', $fields ) ? $fields['start'] : null;
		if ( $exStart == null )
			return false; // can't store without a start date

		$desc = array_key_exists( 'desc', $fields ) && '' != $fields['desc'] ? $fields['desc'] : null;
		if ( $desc != null )
			$changeset['description'] = $desc;

		if ( array_key_exists( 'is_cancelled', $fields ) && $fields['is_cancelled'] )
			$changeset['is_cancelled'] = true;

		foreach( array_keys( EveryCal_Exception::$FIELDS ) as $key ) {
			$v = array_key_exists( $key, $fields['changes'] ) && $fields['changes'][$key] != null ? $fields['changes'][$key] : null;
			if ( $v != null )
				$changeset[$key] = $v;
		}

		// Do any non-exception cached events exist at this start date?
		if ( $cache_id === null || ! is_numeric( $cache_id ) ) {
			$check_existing = $wpdb->get_var( $wpdb->prepare(
				"SELECT cache_id FROM $table_name WHERE post_id = %s AND start = %s AND is_exception = 0 LIMIT 1",
				$event_id, $exStart
			), 0 );
			if ( $check_existing != null )
				$cache_id = $check_existing;
		}
		
		// Serialize the changeset
		$serializedChanges = serialize( $changeset );

		// If the cache id is not numeric create a new record otherwise update
		if ( $cache_id !== null && is_numeric( $cache_id ) ) {
			$count = $wpdb->update(
				$table_name,
				array( 'start' => $exStart, 'changes' => $serializedChanges, 'is_exception' => 1 ),
				array( 'cache_id' => $cache_id, 'post_id' => $event_id ),
				array( '%s', '%s', '%d' ),
				array( '%d', '%d' )
			);
			return $count == 1;
		} else {
			$res = $wpdb->insert(
				$table_name,
				array( 'post_id' => $event_id, 'is_exception' => 1, 'start' => $exStart, 'changes' => $serializedChanges ),
				array( '%d', '%d', '%s', '%s' )
			);
			return $res !== false;
		}
	}

}

// Don't close the php interpreter
/*?>*/
