<?php
/**
 * this file will handle all calendar related processes
 * by: N-Media
 * Author: Najeeb, Rameez
 * **/
 
add_action('save_post', 'save_calendar_fields', 99);
function save_calendar_fields( $post_id ){
     
    global $post, $ecp1_event_fields;
    
    if ( ! isset( $post ) )
		return; // don't update if not a post
	if ( 'revision' == $post->post_type )
		return; // don't update on revisions
	if ( 'rotary_projects' != $post->post_type && 'rotary_speakers' != $post->post_type)
	    return; // don't update non-events
    
    $calendar_tz = new DateTimeZone( ecp1_get_calendar_timezone() ); // UTC if error
    //rotary_pa($_POST); exit;
    
    //=============== the start date ==============
    
    if($post->post_type === 'rotary_projects'){
		//test for 
        $start_date = get_post_meta( $post_id, 'rotary_project_date', true);
        if ( get_field( 'long_term_project', $post_id ) ) :
	        $full_day =  'Y';
	        $the_start_time = '0:00 am';
	        $the_end_time   = '11:59 pm';
	        $end_date   = get_post_meta($post_id, 'rotary_project_end_date', true);
        else:
	        $full_day =  'N';
	        $the_start_time = get_post_meta($post_id, 'rotary_project_start_time', true);
	        $the_end_time   = get_post_meta($post_id, 'rotary_project_end_time', true);
	        $end_date   = $start_date;
        endif; 
        
        $location = get_field( 'rotary_project_location', $post_id );
        
    }elseif( 'rotary_speakers' == $post->post_type){
        
        $start_date = get_field( 'speaker_date', $post_id );
        $full_day = 'N';
    	$the_start_time = get_theme_mod( 'rotary_doors_open', '7:00 am' );
        $the_end_time   = get_theme_mod( 'rotary_program_ends', '8:30 am' );
        $end_date = $start_date;
         
        if( get_field( 'rotary_different_location', $post_id )) { // there is an override for this meeting
        	$location = get_field( 'rotary_program_location', $post_id );
        } else {
       		$location =  get_option( 'club_location' ); // use the location of the club
        }
    }
    
    $start_date_ts = rotary_create_timestamp_in_calendar_tz( $start_date, $the_start_time, $calendar_tz );
    $end_date_ts = rotary_create_timestamp_in_calendar_tz( $end_date, $the_end_time, $calendar_tz );
    
    if( !$start_date_ts || !$end_date_ts) return $post_id; //something has has gone wrong

    if( !empty( $location ) ) {
    	$latitude = $location['lat'];
    	$longitude = $location['lng'];
    	$address = $location['address'];
    }
    
    //assume the first calendar is being used for this
	$calendars_array = _ecp1_current_user_calendars();
	foreach( $calendars_array as $calendar ): 
		$calendar_id = $calendar->ID;
		break;
	endforeach; 
	
    $event_grouped_fields = array(
                                'ecp1_summary'      => $post -> post_title,
                                'ecp1_description'  => $post -> post_content,
                                'ecp1_full_day'     => $full_day,
                                'ecp1_location'     => $address,
                                'ecp1_coord_lat'    => $latitude,
                                'ecp1_coord_lng'    => $longitude,
                                'ecp1_map_zoom'     => 12,
                                'ecp1_showmarker'   => 'Y',
                                'ecp1_showmap'      => 'Y',
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
	$r = update_post_meta( $post->ID, 'ecp1_event', $event_grouped_fields );
	//var_dump($stand_alone_fields); exit;
	foreach( $stand_alone_fields as $key=>$value )
		update_post_meta( $post->ID, $key, $value );      

    //rotary_pa($event_grouped_fields); exit;
 }
 
 function rotary_create_timestamp_in_calendar_tz( $date_str, $time_str, $calendar_tz ) {
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
 
 
 function rotary_pa($arr){
     echo '<pre>';
     print_r($arr);
     echo '</pre>';
 }