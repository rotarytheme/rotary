<?php
/**
 * this file will handle all calendar related processes
 * by: N-Media
 * Author: Najeeb, Rameez
 * **/
 
add_action( 'save_post_rotary_speakers', 'save_calendar_fields', 99);
add_action( 'save_post_rotary_projects', 'save_calendar_fields', 99);
function save_calendar_fields( $post_id ){
    global $post, $ecp1_event_fields, $CalendarColor;
    
 //   echo $post->post_type; die;
 
    
    if ( ! isset( $post ) )
    	//$post =  get_post( $post_id );
		return; // don't update if not a post
	if ( 'revision' == $post->post_type )
		return; // don't update on revisions
	if ( 'rotary_projects' != $post->post_type && 'rotary_speakers' != $post->post_type)
     	return; // don't update non-events
	
    $calendar_tz = new DateTimeZone( ecp1_get_calendar_timezone() ); // UTC if error
    //rotary_pa($_POST); exit;
    
    //=============== the start date ==============
    
    
    if( 'rotary_projects' == $post->post_type ){
        $start_date = get_post_meta( $post_id, 'rotary_project_date', true);
        $project_type = get_field(  'field_project_type' );
        if ( get_field( 'long_term_project', $post_id ) ) :
	        $full_day =  'Y';
	        $the_start_time = '12:00 am';
	        $the_end_time   = '11:59 pm';
	        $end_date = get_post_meta($post_id, 'rotary_project_end_date', true);
	        $end_date = $end_date ? $end_date : get_post_meta( $post_id, 'rotary_project_date', true);
        else:
	        $full_day =  'N';
	        $the_start_time = get_post_meta($post_id, 'rotary_project_start_time', true);
	        $the_end_time   = get_post_meta($post_id, 'rotary_project_end_time', true);
	        $end_date = get_post_meta( $post_id, 'rotary_project_date', true);
        endif; 

        $content = apply_filters( 'the_content', $post->post_content );
       
        $location = get_field( 'rotary_project_location', $post_id );
         
        $color = $CalendarColor[$project_type];
        $textcolor = "#FFFFFF";
        
        
    } elseif ( 'rotary_speakers' == $post->post_type){	
        $start_date = get_field( 'speaker_date', $post_id );
        $full_day = 'N';
    	$the_start_time = get_theme_mod( 'rotary_doors_open', '7:00 am' );
        $the_end_time   = get_theme_mod( 'rotary_program_ends', '8:30 am' );
        $end_date =  get_field( 'speaker_date', $post_id );
       
         
        if( get_field( 'rotary_different_location', $post_id )) { // there is an override for this meeting
        	$location = get_field( 'rotary_program_location', $post_id );
        	$color = $CalendarColor[ OFFSITE ];
        } else {
       		$location =  get_option( 'club_location' ); // use the location of the club
       		$color = $CalendarColor[ SPEAKERPROGRAM ];
        }

        $content = trim(get_field( 'speaker_program_notes', $post_id ));
        if ( empty( $content) ) $content = get_field('speaker_program_content', $post_id);
    
        $textcolor = "#FFFFFF";

    }
    
  //  echo 'Calendar TimeZone is' . $calendar_tz; 
 //   echo 'got here';
    $start_date_ts = rotary_create_timestamp_in_calendar_tz( $start_date, $the_start_time, $calendar_tz );
    $end_date_ts = rotary_create_timestamp_in_calendar_tz( $end_date, $the_end_time, $calendar_tz );
    
    if( !$start_date_ts || !$end_date_ts) return $post_id; //something has has gone wrong
    
    if( !empty( $location ) ) {
    	$latitude = $location['lat'];
    	$longitude = $location['lng'];
    	$address = $location['address'];
    }
	
	$calendar_id = rotary_get_first_calendar();
	
    $event_grouped_fields = array(
                                'ecp1_summary'      => strip_tags( rotary_truncate_text( $content, 250, '', false, true )),
    							'ecp1_url'			=> get_permalink( $post->ID ),
                                'ecp1_description'  => '',
                                'ecp1_full_day'     => $full_day,
                                'ecp1_location'     => $address,
                                'ecp1_coord_lat'    => $latitude,
                                'ecp1_coord_lng'    => $longitude,
                                'ecp1_map_zoom'     => 12,
                                'ecp1_showmarker'   => 'Y',
                                'ecp1_showmap'      => 'Y',
								'ecp1_overwrite_color' => 'Y',
						    	'ecp1_local_textcolor' => $textcolor,
						    	'ecp1_local_color' => $color,
                          //      'ecp1_repeat_pattern' => 'MONTHLY',
                                /*'ecp1_repeat_pattern_parameters' => array
                                                                (
                                                                'every' => 2
                                                                ),
                                
                                'ecp1_repeat_termination' => 'UNTIL',*/
                        //        'ecp1_repeat_terminate_at' => 1455408000,
                        //        'ecp1_repeat_last_changed' => 1452005784,
                                );

    /*get_post_meta($post_id, 'rotary_project_start_time', true)
    get_post_meta($post_id, 'rotary_project_end_time', true),*/
    
    //var_dump($event_grouped_fields); exit;
    
    $stand_alone_fields = array(
                                'ecp1_event_start'      => $start_date_ts,
                                'ecp1_event_end'        => $end_date_ts,
                                'ecp1_event_calendar'   => $calendar_id,
                                'ecp1_event_is_featured'=> 'N',
                                'ecp1_event_repeats'    => 'N',
    		
    		);
                                
    // Save the post meta information
    delete_post_meta( $post->ID, 'ecp1_event' );
	$r = add_post_meta( $post->ID, 'ecp1_event', $event_grouped_fields );
	//var_dump($post->ID, $stand_alone_fields); exit;
	foreach( $stand_alone_fields as $key=>$value ) {
		delete_post_meta( $post->ID, $key );  
		add_post_meta( $post->ID, $key, $value );  
	}  
	
    //rotary_pa($event_grouped_fields); exit;

 }
 
 function rotary_create_timestamp_in_calendar_tz( $date_str, $time_str, $calendar_tz ) {

 	if( !$date_str || !$time_str) return;
 	//create a date in the time zone of the calendar
 	$date_ts = strtotime( $date_str );
 	$date_format = date("Y-m-d", $date_ts);
 	$datetime_tz = date_create( $date_format, $calendar_tz );
 	if ( FALSE === $ds ) // used procedural so don't have to catch exception
 		return false;
 	//set the time in the right time zone by parsing hours and minutes from the string input
 	$time = new DateTime( $date_format . ' ' . $time_str);
 	$datetime_tz->setTime( $time->format('H'), $time->format('i') , 0 ); 
 	
 	if ( ECP1_PHP5 < 3 ) // support 5.2.0
 		$date_ts = $datetime_tz->format( 'U' );
 	else
 		$date_ts = $datetime_tz->getTimestamp(); // UTC (i.e. without offset)
 return $date_ts;
}
 

function rotary_get_first_calendar() {
	//assume the first calendar is being used for this
	$calendars_array = get_posts( array( 'post_type'=>'ecp1_calendar', 'post_status'=>'publish', 'suppress_filters'=>false, 'numberposts'=>-1, 'nopaging'=>true, 'order_by'=>'ID' ) );
	foreach( $calendars_array as $calendar ):
		$calendar_id = $calendar->ID;
		break;
	endforeach;

	return $calendar_id;
}
 
 function rotary_pa($arr){
     echo '<pre>';
     print_r($arr);
     echo '</pre>';
 }
 
 /****************************/

 // Save the data when the meta box is submitted
 add_action( 'customize_save_after', 'rotary_ecp1_event_save' );
 function rotary_ecp1_event_save() {
 	global $ecp1_event_fields;
 	
 	$postarr = array(
 		'post_type' => 'ecp1_event',
 		'post_title' => 'Weekly Speaker Program',
 		'comment_status' => 'closed',
 		'post_content' => 'This is a recurring meeting'
 	);
 	
 	$post_id = wp_insert_post( $postarr );
 	
 	$post = get_post($post_id );
 
 	$ecp1_full_day = 'N';
 	$ecp1_featured = 'N';
 	$ecp1_calendar = rotary_get_first_calendar();
 	
 	// Load the calendar so we can convert times to UTC
 	_ecp1_parse_calendar_custom( $ecp1_calendar );
 	$calendar_tz = new DateTimeZone( ecp1_get_calendar_timezone() ); // UTC if error
 	
 	$weekday = get_theme_mod( 'rotary_meeting_day');
 	$start_date = strtotime( 'next '. $weekday );
 	$the_start_time = get_theme_mod( 'rotary_doors_open', '7:00 am' );
 	$the_end_time   = get_theme_mod( 'rotary_program_ends', '8:30 am' );
 	$end_date = $start_date;
 
 	$ecp1_start_ts = rotary_create_timestamp_in_calendar_tz( $start_date, $the_start_time, $calendar_tz );
 	$ecp1_end_ts = rotary_create_timestamp_in_calendar_tz( $end_date, $the_end_time, $calendar_tz );
 	
	$ecp1_repeating = 'Y';
	$ecp1_repeat_pattern = '';
	$ecp1_repeat_custom_expression = '';
	
	$location =  get_option( 'club_location' );
 
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


 	$ecp1_repeat_termination = '4EVA';
 	
 	$location =  get_option( 'club_location' );
 
 	// The location as human address and lat/long coords
 	$ecp1_location = $location['address'];
 	$ecp1_coord_lat = $location['lat'];
 	$ecp1_coord_lng = $location['lng'];
 
 	// Yes if set No if not for show map/markers
 	$ecp1_showmap = $ecp1_showmarker = 'Y';
	$ecp1_map_zoom = 17;
 
 	// The placemarker image should be a file in ECP1_DIR/img/mapicons
 	$ecp1_map_placemarker = $ecp1_event_fields['ecp1_map_placemarker'][1];
 
 	// Are we overwriting the calendar colors
 	$ecp1_overwrite_color = $ecp1_event_fields['ecp1_overwrite_color'][1];
 	$ecp1_local_textcolor = $ecp1_event_fields['ecp1_local_textcolor'][1];
 	$ecp1_local_color = $ecp1_event_fields['ecp1_local_color'][1];
 	
 //	$ecp1_overwrite_color = 'Y';
 
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
 }
/* USE THIS TO CANCEL ALL THE MEETINGS THAT ARE AT THE SAME TIME AS SPEAKER PROGRAMS
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
 /*
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
 						'is_cancelled' => true,
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
 */
 
/*
 * Only run this once
 */
 add_action( 'init', 'update_all_calendar_post_types' );
 function update_all_calendar_post_types() {
 	global $post;
	if( '2' == get_option( 'update_all_calendar_post_types' )) return;
 	
	 	$args = array(
	 		'post_type' => array( 'rotary_projects', 'rotary_speakers' ),
	 		'post_status' => 'publish',
	 		'posts_per_page'   => -1,
	 	);
	 	$posts = get_posts( $args );
	 	foreach ($posts as $post ) : setup_postdata( $post ) ;
	 		save_calendar_fields( $post->ID );
	 	endforeach;
	 	
 		update_option( 'update_all_calendar_post_types', '2' );
 }

 