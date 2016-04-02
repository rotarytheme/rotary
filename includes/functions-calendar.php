<?php
/**
 * this file will handle all calendar related processes
 * by: N-Media
 * Author: Najeeb, Rameez
 * **/
 
add_action('save_post', 'save_calendar_fields', 99);
function save_calendar_fields($post_id){
     
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
        $start_date = get_post_meta($post_id, 'rotary_project_date', true);
        $start_date_ts = strtotime( $start_date );
        $start_date_format = date("Y-m-d", $start_date_ts);
        $ds = date_create( $start_date_format, $calendar_tz );
    		if ( FALSE === $ds ) // used procedural so don't have to catch exception
    			return $post->ID;
    			
    	$the_start_time = get_post_meta($post_id, 'rotary_project_start_time', true);
        $the_end_time   = get_post_meta($post_id, 'rotary_project_end_time', true);
        
        //$start_time = DateTime::createFromFormat('d/m/Y H:i A', $start_date.' '.$the_start_time);
        if($the_start_time != ''){
            $start_time = new DateTime($start_date.' '.$the_start_time);
            $start_time_H = $start_time->format('H');
            $start_time_Mnt = $start_time->format('i');
        	$ds->setTime( $start_time_H, $start_time_Mnt, 1 ); // set to just after midnight if time not given
        }else{
            $ds->setTime( 10, 0, 1 ); // set to just after midnight if time not given
        }
        
        $end_date   = get_post_meta($post_id, 'rotary_project_end_date', true);
    
        if( $end_date == NULL ){  // if end date is define then use it othrewise use start date as end.
            $end_date = $start_date;
        }
        $end_date_ts = strtotime( $end_date );
    }elseif('rotary_speakers' == $post->post_type){
        
        $project_start_date = DateTime::createFromFormat('Ymd', get_field( 'speaker_date' ));
        //var_dump(strtotime(get_field( 'speaker_date' ))); exit;    
        if( !$project_start_date)
            return $post->ID;
            
            
        $start_date_ts = strtotime($project_start_date->format('Y-m-d'));
        $start_date_format = date("Y-m-d", $start_date_ts);
        $ds = date_create( $start_date_format, $calendar_tz );
    		if ( FALSE === $ds ) // used procedural so don't have to catch exception
    			return $post->ID;
    			
    	$the_start_time = "7:00 am";
        $the_end_time   = "8:30 am";
        
        //$start_time = DateTime::createFromFormat('d/m/Y H:i A', $start_date.' '.$the_start_time);
        if($the_start_time != ''){
            $start_time = new DateTime($start_date.' '.$the_start_time);
            $start_time_H = $start_time->format('H');
            $start_time_Mnt = $start_time->format('i');
            //var_dump($start_time_Mnt); exit;
        	$ds->setTime( $start_time_H, $start_time_Mnt, 1 ); // set to just after midnight if time not given
        }
    
        $end_date_ts = $start_date_ts;
    }
		
	if ( ECP1_PHP5 < 3 ) // support 5.2.0
		$start_date_ts = $ds->format( 'U' );
	else
		$start_date_ts = $ds->getTimestamp(); // UTC (i.e. without offset)
			

    //=============== the start date ==============
    
    
    
    $end_date_format = date("Y-m-d", $end_date_ts);

    $ds = date_create( $end_date_format, $calendar_tz );
		if ( FALSE === $ds ) // used procedural so don't have to catch exception
			return $post->ID;
		 $ds->setTime( 0, 0, 1 ); // set to just after midnight if time not given
		
	if ( ECP1_PHP5 < 3 ) // support 5.2.0
		$end_date_ts = $ds->format( 'U' );
	else
		$end_date_ts = $ds->getTimestamp(); // UTC (i.e. without offset)
    
    
    
    //calendar used for this
    $calendar_id = 845;
    
    $event_grouped_fields = array(
                                'ecp1_summary'      => $post -> post_title,
                                'ecp1_description'  => $post -> post_content,
                                'ecp1_full_day'     => "N",
                                'ecp1_location'     => 'Lahore',
                                'ecp1_coord_lat'    => 31.55460609999999,
                                'ecp1_coord_lng'    => 74.35715809999999,
                                'ecp1_map_zoom'     => 12,
                                'ecp1_showmarker'   => 'Y',
                                'ecp1_showmap'      => 'N',
                                'ecp1_repeat_pattern' => 'MONTHLY',
                                /*'ecp1_repeat_pattern_parameters' => array
                                                                (
                                                                'every' => 2
                                                                ),
                                
                                'ecp1_repeat_termination' => 'UNTIL',*/
                                'ecp1_repeat_terminate_at' => 1455408000,
                                'ecp1_repeat_last_changed' => 1452005784,
                                );

    /*get_post_meta($post_id, 'rotary_project_start_time', true)
    get_post_meta($post_id, 'rotary_project_end_time', true),*/
    
    //var_dump($event_grouped_fields); exit;
    
    $stand_alone_fields = array(
                                'ecp1_event_start'      => $start_date_ts,
                                'ecp1_event_end'        => $end_date_ts,
                                'ecp1_event_calendar'   => $calendar_id,
                                'ecp1_event_is_featured'    => 'N',
                                'ecp1_event_repeats'    => 'N',
                                );
                                
    // Save the post meta information
	$r = update_post_meta( $post->ID, 'ecp1_event', $event_grouped_fields );
	//var_dump($stand_alone_fields); exit;
	foreach( $stand_alone_fields as $key=>$value )
		update_post_meta( $post->ID, $key, $value );      

    //rotary_pa($event_grouped_fields); exit;
 }
 
 
 function rotary_pa($arr){
     echo '<pre>';
     print_r($arr);
     echo '</pre>';
 }