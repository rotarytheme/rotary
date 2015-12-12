<?php
/**
 * Every Calendar Scheduler: Repeating Pattern Expression
 *
 * Modelled of crontab expressions but with a few extra fun parts
 * see the examples below for broad coverage of the syntax that
 * is supported. It is worth noting that unlike cron which matches
 * on DoM/MoY OR DoW this class requires ALL conditions to be met
 * for a repeat instance to be flagged.
 *
 * Also worth noting Sunday is 1 and Saturday is 7.
 *
 * The Minutes and Hours components have been removed.
 *
 * A new component Week since Epoch has been added for weekly schedules,
 * this is the number of weeks since the epoch (start) date of the event.
 *
 * TIMEZONES:
 *  - All manipulations and comparisons are done on whole calendar days
 *  - All timezones are assumed to be set in the DateTime objects
 *  - You MUST ensure timezones are consistent between inputs
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Example Expressions: (using // to allow */ in comments)
//     DoM  MoY  DoW  WsE
//      *    *    1    *	Every Sunday
//      1   */2   *    *	First day every 2nd month
//      1   3/2   *    *	First day every 2nd month when month is March
//     -1    *    *    *	Last day of the month
//      *    *    6   1,-1  First after epoch and last Friday before 1 year repeat
//      *    *    1   1/3	Every 3rd Sunday (1st in group)
//      *    *    4   2/4	Every 4th Wednesday (2nd in group)
//      *    *   1/3   *	The 3rd Sunday of every month
//      *    *   2/-1  *	Last Monday every month
//      *   */3  1/-2  *	2nd last Sunday every 3rd month
//     -2   */6  4,5/5 *	2nd last day of every 6 month where it is the 5th Wed|Thur of the month
// Some more complicated expression examples:
//     DoM    MoY            DoW      WoY
//     *      *              2/1,-1   *         First and last Monday of every month
//     10-20  *              2        *			Mondays between 10th and 20th
//     *      2,3,4,5,12     2/-1     *			Last Monday of Feb|Mar|Apr|May|Dec
//     5-25   3,4,5,9,10,11  6/1,4    *			1st|4th Friday of the month where day is 5th-25th in Autumn/Spring
//     *      */3            1/-1--3  *			Last, 2nd and 3rd Last Sundays of every 3rd month
//     2-8    2,5,9,10/2,5   3,4/1,2  *			1st|2nd Tue|Wed where is 2nd-8th in Feb|May|Sep|Oct and is 2nd or 5th Month cycle since start
//     *      1,2,12         2,3      1,2/5,7	Mon|Tue of 1st|2nd weeks in a 5|7 week rolling cycle since epoch in Summer months

// Define some error types
define( 'PARSE_ERROR_DOM',  -1 );
define( 'PARSE_ERROR_MOY',  -2 );
define( 'PARSE_ERROR_DOW',  -3 );
define( 'PARSE_ERROR_WSE',  -4 );
define( 'PATTERN_MISMATCH', -9 );

// Define some constants for days of the week
define( 'ECP1_SUNDAY',    1); define( 'PHP_SUNDAY',    0);
define( 'ECP1_MONDAY',    2); define( 'PHP_MONDAY',    1);
define( 'ECP1_TUESDAY',   3); define( 'PHP_TUESDAY',   2);
define( 'ECP1_WEDNESDAY', 4); define( 'PHP_WEDNESDAY', 3);
define( 'ECP1_THURSDAY',  5); define( 'PHP_THURSDAY',  4);
define( 'ECP1_FRIDAY',    6); define( 'PHP_FRIDAY',    5);
define( 'ECP1_SATURDAY',  7); define( 'PHP_SATURDAY',  6);

/**
 * RepeatExpression Class
 * This class contains static methods for building instances of an expression
 * for commonly repeated types, and also allows an expression to be passed to
 * the constructor to build more complicated expressions.
 */
class EveryCal_RepeatExpression
{
	
	/**
	 * Known TYPES of expressions and a map to their build functions
	 * The build functions will be given the event start and end dates
	 */
	static $TYPES = array(
		// Monthly repeats (i.e. once a month on the nominated day)
		'MONTHLY' => array(
			'func' => 'BuildMonthly', // function to call
			'desc' => 'Monthly or every X months',
			'params' => array(
				'every' => array( 'required' => false, 'desc' => 'Every X months', 'default' => 1 )
			)
		),

		// Weekly repeats (i.e. once a week on the nominated day)
		'WEEKLY' => array(
			'func' => 'BuildWeekly', // function to call
			'desc' => 'Weekly of every X weeks',
			'params' => array(
				'every' => array( 'required' => false, 'desc' => 'Every X weeks', 'default' => 1 )
			)
		),

		// Last Xday (e.g. sunday) of the month
		'LAST_X_OF_MONTH' => array(
			'func' => 'BuildLastXofMonth', // function to call
			'desc' => 'Last weekday of the month' ,
			'params' => array(
				'day' => array( 'required' => true, 'desc' => 'Day (X) of week',
					'choices' => array( 1=>'Sunday', 2=>'Monday', 3=>'Tuesday', 4=>'Wednesday',
						5=>'Thursday', 6=>'Friday', 7=>'Saturday' )
				)
			)
		),

		// First Xday (e.g. Friday) of the month
		'FIRST_X_OF_MONTH' => array(
			'func' => 'BuildFirstXofMonth',
			'desc' => 'First weekday of the month',
			'params' => array(
				'day' => array( 'required' => true, 'desc' => 'Day (X) of week',
					'choices' => array( 1=>'Sunday', 2=>'Monday', 3=>'Tuesday', 4=>'Wednesday',
						5=>'Thursday', 6=>'Friday', 7=>'Saturday' )
				)
			)
		),
		
		// First Xday (e.g. Friday) of the month
		'SECOND_X_OF_MONTH' => array(
				'func' => 'BuildSecondXofMonth',
				'desc' => 'Second weekday of the month',
				'params' => array(
						'day' => array( 'required' => true, 'desc' => 'Day (X) of week',
								'choices' => array( 1=>'Sunday', 2=>'Monday', 3=>'Tuesday', 4=>'Wednesday',
										5=>'Thursday', 6=>'Friday', 7=>'Saturday' )
						)
				)
		),
		
		// First Xday (e.g. Friday) of the month
		'THIRD_X_OF_MONTH' => array(
				'func' => 'BuildThirdXofMonth',
				'desc' => 'Third weekday of the month',
				'params' => array(
						'day' => array( 'required' => true, 'desc' => 'Day (X) of week',
								'choices' => array( 1=>'Sunday', 2=>'Monday', 3=>'Tuesday', 4=>'Wednesday',
										5=>'Thursday', 6=>'Friday', 7=>'Saturday' )
						)
				)
		),
		
		// First Xday (e.g. Friday) of the month
		'FOURTH_X_OF_MONTH' => array(
				'func' => 'BuildFourthXofMonth',
				'desc' => 'Fourth weekday of the month',
				'params' => array(
						'day' => array( 'required' => true, 'desc' => 'Day (X) of week',
								'choices' => array( 1=>'Sunday', 2=>'Monday', 3=>'Tuesday', 4=>'Wednesday',
										5=>'Thursday', 6=>'Friday', 7=>'Saturday' )
						)
				)
		),

		// Once a year
		'YEARLY' => array(
			'func' => 'BuildYearly', // function to call
			'desc' => 'Yearly',
			'params' => null // no parameters
		)
	);
	
	/**
	 * Private string for storing the internal expression
	 */
	private $internal_expression = null;
	
	/**
	 * Private mapping of the repeat sets and period filter sets.
	 * A null value means ALL so by default this will repeat every day.
	 */
	private $when_sets = array(
		'DoM' => array( 'repeat' => null ),
		'MoY' => array( 'repeat' => null, 'period' => null ),
		'DoW' => array( 'repeat' => null, 'filter' => null ),
		'WsE' => array( 'repeat' => null, 'cycles' => null ),
	);
	
	/**
	 * Build Function (static)
	 * Constructs an instance of the repeat expression for the given type
	 * by calling one of the private static methods with the parameters,
	 * then calling the constructor with the expression string returned.
	 *
	 * @param $type One of the keys from EveryCal_RepeatExpression::$TYPES
	 * @param $start A DateTime object whose date is the start of repeat cycle
	 * @param $params Keyed array of parameters to pass to the type build function
	 * @return An instance of EveryCal_RepeatExpression or null on failure
	 */
	public static function Build( $type, $start, $params=array() )
	{
		if ( ! array_key_exists( $type, self::$TYPES ) ) 
			return null;
		$func = self::$TYPES[$type]['func'];
		$expr = self::$func( $start, $params );
		if ( null == $expr )
			return null; // failed to build an expression string
		return new EveryCal_RepeatExpression( $expr );
	}
	
	/**
	 * EveryCal_RepeatExpression Constructor
	 * Returns a new instance which will match times using the given expression
	 * string. If the expression string can not be parsed then an Exception will
	 * be thrown (just a generic PHP Exception).
	 *
	 * @param $crontab The string expression to build the instance out of
	 * @return A new instance of EveryCal_RepeatExpression
	 */
	public function __construct( $crontab )
	{
		$presult = $this->ParseParts( $crontab );
		if ( $presult < 0 )
			throw new Exception( sprintf( __( 'Failed (code:%s) to parse the crontab %s' ), $presult, $crontab ) );
		
		// Cache the crontab expression
		$this->internal_expression = $crontab;
	}

	/**
	 * EveryCal_RepeatExpression String Printing
	 * Returns a string representation of the repeat showing the source expression
	 * and the component parts that are built from parsing the expression.
	 *
	 * @return String representation of the repeat expression.
	 */
	public function __toString()
	{
		$str = sprintf( "%s\nDoM: {{DOM}}\nMoY: {{MOY}}\nDoW: {{DOW}}\nWsE: {{WSE}}", $this->internal_expression );

		// Day of Month
		if ( is_array( $this->when_sets['DoM']['repeat'] ) ) {
			$str = str_replace( '{{DOM}}', implode( ',', $this->when_sets['DoM']['repeat'] ), $str );
		} else if ( $this->when_sets['DoM']['repeat'] == null ) {
			$str = str_replace( '{{DOM}}', '*', $str );
		} else {
			$str = str_replace( '{{DOM}}', $this->when_sets['DoM']['repeat'], $str );
		}

		// Day of Week
		$dow = '?';
		if ( is_array( $this->when_sets['DoW']['repeat'] ) )
			$dow = implode( ',', $this->when_sets['DoW']['repeat'] );
		else if ( $this->when_sets['DoW']['repeat'] == null ) $dow = '*';
		else $dow = $this->when_sets['DoW']['repeat'];
		if ( $this->when_sets['DoW']['filter'] !== null )
			$dow .= '/' . implode( ',', $this->when_sets['DoW']['filter'] );
		$str = str_replace( '{{DOW}}', $dow, $str );
		
		// Month of Year
		$moy = '?';
		if ( is_array( $this->when_sets['MoY']['repeat'] ) )
			$moy = implode( ',', $this->when_sets['MoY']['repeat'] );
		else if ( $this->when_sets['MoY']['repeat'] == null ) $moy = '*';
		else $moy = $this->when_sets['MoY']['repeat'];
		if ( $this->when_sets['MoY']['period'] !== null )
			$moy .= '/' . implode( ',', $this->when_sets['MoY']['period'] );
		$str = str_replace( '{{MOY}}', $moy, $str );

		// Weeks since Epoch
		$wse = '?';
		if ( is_array( $this->when_sets['WsE']['repeat'] ) )
			$wse = implode( ',', $this->when_sets['WsE']['repeat'] );
		else if ( $this->when_sets['WsE']['repeat'] == null ) $wse = '*';
		else $wse = $this->when_sets['WsE']['repeat'];
		if ( $this->when_sets['WsE']['cycles'] !== null )
			$wse .= '/' . implode( ',', $this->when_sets['WsE']['cycles'] );
		$str = str_replace( '{{WSE}}', $wse, $str );

		// Finally return the constructed string
		return $str;
	}
	
	/**
	 * ParseParts Function (private)
	 * Parses the given crontab expression into parts that the matching function
	 * uses and stores them into $this->parts. If the expression is successfully
	 * parsed returns true otherwise returns false.
	 * 
	 * @param $crontab The expression to parse
	 * @return True of False indicating if parsing was successful
	 */
	private function ParseParts( $crontab )
	{
		// All valid crontab expressions should have 3 tokens that are
		// whitespace separated this function parses each token in turn.
		// But first check if there are 3 valid tokens.
		$strip_chars = ", \t\n\r\0\x0B";
		$regexp_crontab = '/^([0-9,\-\*]+)\s+' .   // no / allowed
							'([0-9,\/\*]+)\s+' .   // no - allowed
							'([0-9,\/\-\*]+)\s+' . // - and / allowed
							'([0-9,\/\-\*]+)$/';   // - and / allowed
		$crontab = trim( $crontab );
		$matches = array();
		if ( ! preg_match( $regexp_crontab, $crontab, $matches ) )
			return PATTERN_MISMATCH;
		
		// FIRST TOKEN: Day of Month
		if ( ! $this->ParseDayOfMonth( trim( $matches[1], $strip_chars  ) ) )
			return PARSE_ERROR_DOM;
		
		// SECOND TOKEN: Month of Year
		if ( ! $this->ParseMonthOfYear( trim( $matches[2], $strip_chars ) ) )
			return PARSE_ERROR_MOY;
		
		// THIRD TOKEN: Day of Week
		if ( ! $this->ParseDayOfWeek( trim( $matches[3], $strip_chars ) ) )
			return PARSE_ERROR_DOW;

		// FOURTH TOKEN: Weeks since Epoch
		if ( ! $this->ParseWeekSinceEpoch( trim( $matches[4], $strip_chars ) ) )
			return PARSE_ERROR_WSE;
		
		// All good
		return 1;
	}
	
	/**
	 * ParseDayOfMonth Function (private)
	 * Parses the given substring of a crontab expression as the DoM component.
	 *
	 * @param $crontab The crontab DoM expression.
	 * @return True or False if DoM are successfully parsed.
	 */
	private function ParseDayOfMonth( $crontab )
	{
		// This expression will be one of three types:
		if ( preg_match( '/^\*$/', $crontab ) ) { // all DoM
			
			$this->when_sets['DoM']['repeat'] = null;
			
		} else if ( preg_match( '/^\d+\-\d+$/', $crontab ) ) { // range of days in the month
			
			$parts = explode( '-', $crontab ); // will have exactly two
			$this->when_sets['DoM']['repeat'] = array();
			for ( $i=$parts[0]; $i<=$parts[1]; $i++ )
				$this->when_sets['DoM']['repeat'][] = $i;
			
		} else if ( preg_match( '/^([\-]?\d+[, ]?)+$/', $crontab ) ) { // specific days of the month
			
			$this->when_sets['DoM']['repeat'] = explode( ',', $crontab );
			
		} else { return false; } // unknown DoM expression
		
		// Check the values given make sense
		if ( is_array( $this->when_sets['DoM']['repeat'] ) ) {
			foreach ( $this->when_sets['DoM']['repeat'] as $v ) {
				if ( ! is_numeric( $v ) || $v < -31 || $v > 31 )
					return false;
			}
		}
		
		// As good as can be
		return true;
	}
	
	/**
	 * ParseMonthOfYear Function (private)
	 * Parses the given substring of a crontab expression as the MoY component.
	 *
	 * @param $crontab The crontab MoY expression.
	 * @return True or False if MoY are successfully parsed.
	 */
	private function ParseMonthOfYear( $crontab )
	{
		// This expression will either have a period set or will not
		// for MoY expressions the period set effectively says only
		// include every X occurance (where X is in period set) of 
		// the items in the repeat set.
		$slashpos = strpos( $crontab, '/' );
		$repeat_string = $slashpos !== false ? substr( $crontab, 0, $slashpos ) : $crontab;
		$period_string = $slashpos !== false ? substr( $crontab, $slashpos+1, strlen( $crontab ) - $slashpos - 1 ) : '';
		
		// Repeat string will be either a *, single number or comma separated
		if ( preg_match( '/^\*$/', $repeat_string ) ) { // every month by period
			
			$this->when_sets['MoY']['repeat'] = null;

		} else if ( preg_match( '/^(\d+[, ]?)+?$/', $repeat_string ) ) { // the listed set of months
			
			$this->when_sets['MoY']['repeat'] = explode( ',', $repeat_string );

		} else { return false; } // couldn't parse month component
		
		// Is there a period string to parse?
		if ( $slashpos !== false ) {
			
			// Period string will be a single or comma separated set
			if ( preg_match( '/^(\d+[, ]?)+$/', $period_string ) ) { // specific intervals of months
				
				$this->when_sets['MoY']['period'] = explode( ',', $period_string );

			} else { return false; } // couldn't parse
			
		} else { // $slashpos === false meaning all occurances of the repeating months
			$this->when_sets['MoY']['period'] = null;
		}
		
		// Validate we have sensible month values
		if ( is_array( $this->when_sets['MoY']['repeat'] ) ) {
			foreach ( $this->when_sets['MoY']['repeat'] as $v )
				if ( ! is_numeric( $v ) || $v < 1 || $v > 12 )
					return false; // invalid month
		}

		// Validate we have sensible period values
		if ( is_array( $this->when_sets['MoY']['period'] ) ) {
			foreach ( $this->when_sets['MoY']['period'] as $v )
				if ( ! is_numeric( $v ) || $v < 1 || $v > 12 )
					return false; // invalid month
		}
		
		// All good
		return true;
	}
	
	/**
	 * ParseDayOfWeek Function (private)
	 * Parses the given substring of a crontab expression as the DoW component.
	 *
	 * @param $crontab The crontab DoW expression.
	 * @return True or False if DoW are successfully parsed.
	 */
	private function ParseDayOfWeek( $crontab )
	{
		// This expression is the effectively the same as MoY except 
		// it can also have negative values for the day and filter
		$slashpos = strpos( $crontab, '/' );
		$day_string = $slashpos !== false ? substr( $crontab, 0, $slashpos ) : $crontab;
		$flr_string = $slashpos !== false ? substr( $crontab, $slashpos+1, strlen( $crontab ) - $slashpos - 1 ) : '';

		// The day string will be either a *, single number or comma separated
		if ( preg_match( '/^\*$/', $day_string ) ) { // any day of the week by filter
			
			$this->when_sets['DoW']['repeat'] = null;

		} else if ( preg_match( '/^(\d+[, ]?)+?$/', $day_string ) ) { // listed day(s)
			
			$this->when_sets['DoW']['repeat'] = explode( ',', $day_string );

		} else { return false; } // couldn't parse day component

		// If there is a slash then parse the filter
		if ( $slashpos !== false ) {

			// Filter string will be single number, comma separated or a range
			$matches = array();
			if ( preg_match( '/^([\-]?\d+)\-([\-]?\d+)$/', $flr_string, $matches ) ) {
				
				$this->when_sets['DoW']['filter'] = array();
				$min = $matches[1] < $matches[2] ? $matches[1] : $matches[2];
				$max = $matches[1] >= $matches[2] ? $matches[1] : $matches[2];
				for ( $i=$min; $i<=$max; $i++ )
					$this->when_sets['DoW']['filter'][] = $i;
			
			} else if ( preg_match( '/^([\-]?\d+[, ]?)+$/', $flr_string ) ) { // specific filters
				
				$this->when_sets['DoW']['filter'] = explode( ',', $flr_string );

			} else { return false; } // couldn't parse the filter

		}

		// Do some sanity checking on the days specified
		if ( is_array( $this->when_sets['DoW']['repeat'] ) ) {
			foreach( $this->when_sets['DoW']['repeat'] as $v )
				if ( ! is_numeric( $v ) || abs( $v ) > 7 || abs( $v ) < 1 )
					return false;
		}

		// And also do sanity checking on the filters
		if ( is_array( $this->when_sets['DoW']['filter'] ) ) {
			foreach( $this->when_sets['DoW']['filter'] as $v )
				if ( ! is_numeric( $v ) || $v == 0 || abs( $v ) > 5 ) // can't be 0 and never more than 5 of a day in a month
					return false;
		}

		// All good
		return true;
	}

	/**
	 * ParseWeekSinceEpoch (private)
	 * Parses the weeks since epoch component of the cron expression.
	 *
	 * @param $crontab The cron expression component for WsE.
	 * @return True of false if the expression was parsed.
	 */
	private function ParseWeekSinceEpoch( $crontab )
	{
		// If there is a slash get it's position and divide the string
		$slashpos = strpos( $crontab, '/' );
		$period_string = $slashpos !== false ? substr( $crontab, 0, $slashpos ) : $crontab;
		$cycles_string = $slashpos !== false ? substr( $crontab, $slashpos+1, strlen( $crontab ) - $slashpos - 1 ) : '';

		// The period will be either a * or single or comma separated numbers
		if ( preg_match( '/^\*$/', $period_string ) ) {
			
			$this->when_sets['WsE']['repeat'] = null;

		} else if ( preg_match( '/^([\-]?\d+[, ]?)+$/', $period_string ) ) {
			
			$this->when_sets['WsE']['repeat'] = explode( ',', $period_string );

		} else { return false; } // couldn't parse the period of weeks

		// If there is a cycle it will be single or comma separated
		if ( $slashpos !== false ) {
			
			if ( preg_match( '/^(\d+[, ]?)+$/', $cycles_string ) ) { // no negatives allowed

				$this->when_sets['WsE']['cycles'] = explode( ',', $cycles_string );
			
			} else { return false; } // couldn't parse the cycles

		}
		
		// Do some error checking on the cycles component
		$minCycles = 0;
		if ( is_array( $this->when_sets['WsE']['cycles'] ) ) {
			foreach( $this->when_sets['WsE']['cycles'] as $v ) {
				if ( $v > $minCycles ) $minCycles = $v;
				if ( ! is_numeric( $v ) || $v < 1 || $v > 53 )
					return false;
			}
		}

		// Do some error checking on the repeat component
		if ( is_array( $this->when_sets['WsE']['repeat'] ) ) {
			foreach( $this->when_sets['WsE']['repeat'] as $v )
				if ( ! is_numeric( $v ) || ( abs( $v ) > $minCycles && $minCycles > 0 ) )
					return false;
		}

		// All good
		return true;
	}
	
	/**
	 * GetRepeatsBetween Function
	 * Returns an array of all the event repeat start dates that this expression
	 * matches which are between the given start and end parameter DateTimes.
	 *
	 * This is one of the most complicated and important parts of the scheduler.
	 * Any improvements are welcome but please make sure you TEST all changes.
	 *
	 * Algorithm:
	 *  1) Construct EveryCal_RE_DateRange for the start and end
	 *  2) If specific days of a month are requested disable any that don't match.
	 *  3) If specific months are requested drop all days not in those months.
	 *  4) If specific days of the week are requsted disable any that don't match.
	 *  5) If a monthly cycle exists then apply it.
	 *  6) If a weekday filter exists then apply it.
	 *  7) If a weeks since epoch cycle is given then apply it.
	 *     Where only repeats are given and no cycle, the cycle is the whole year.
	 *
	 * @param $epoch DateTime object representing the event start date (epoch).
	 * @param $start DateTime object starting the range to look for matches in.
	 * @param $end DateTime objects ending the range to look for matches in.
	 * @return Array of DateTime objects representing the date each valid
	 *         repeat of the event will start. The array can be empty if there
	 *         are no events in the range or will be null if an error occurs.
	 */
	public function GetRepeatsBetween( $epoch, $start, $end )
	{
		// First make sure the input parameters make sense
		if ( ! ( $epoch instanceof DateTime && $start instanceof DateTime && $end instanceof DateTime ) || $start > $end )
			return null;
		
		// Repeats can't start before the epoch so copy if trying to
		if ( $epoch > $start )
			$start = $epoch;

		// 1 - Construct EveryCal_RE_DateRange
		$ranger = new EveryCal_RE_DateRange( $epoch, $start, $end );

		// 2 - Remove non-matching days of the month
		if ( is_array( $this->when_sets['DoM']['repeat'] ) )
			$ranger->FilterDaysNotIn( $this->when_sets['DoM']['repeat'] );
		
		// 3 - Remove days not in matching months
		if ( is_array( $this->when_sets['MoY']['repeat'] ) )
			$ranger->FilterMonthsNotIn( $this->when_sets['MoY']['repeat'] );

		// 4 - Remove non-matching days of the week
		if ( is_array( $this->when_sets['DoW']['repeat'] ) )
			$ranger->FilterWeekdaysNotIn( $this->when_sets['DoW']['repeat'] );

		// 5 - Apply month cycles
		if ( is_array( $this->when_sets['MoY']['period'] ) )
			$ranger->ApplyMonthlyCycle( $this->when_sets['MoY']['period'] );

		// 6 - Apply weekday filter
		if ( is_array( $this->when_sets['DoW']['filter'] ) )
			$ranger->ApplyWeekdayFilter( $this->when_sets['DoW']['filter'] );

		// 7 - Apply weeks since epoch cycles
		if ( is_array( $this->when_sets['WsE']['repeat'] ) )
			$ranger->ApplyWeeklyCycle( $this->when_sets['WsE']['repeat'],
				$this->when_sets['WsE']['cycles'] );

		// DEBUGGING ONLY: MUST BE REMOVED IN PRODUCTION
		//return $ranger;

		// Finally return the computed array of objects
		return $ranger->GetDates();
	}
	
	/**
	 * GetExpression Function
	 * Returns the crontab expression that represents this instance.
	 *
	 * @return The crontab expression string for this instance.
	 */
	public function GetExpression()
	{
		return $this->internal_expression;
	}
	
	/**
	 * BuildMonthly (private)
	 * Builds an expression that represents an event repeating X months by month
	 * on a given day of the month. The most simple examples of this would be
	 *  1) On the 1st of every month; or
	 *  2) On the 10th of every 3rd month.
	 *
	 * The $params array should contain the following keys:
	 *  every => (optional) The frequency of months 1=every, 2=every 2nd, and so on..
	 *
	 * @param $start The start date of the event repeat cycle
	 * @param $params The keyed parameter array described above
	 * @return String representation of the monthly repeating event
	 */
	private static function BuildMonthly( $start, $params=array() )
	{
		// Extract the DoM from the start date
		$dom = $start->format( 'j' );

		// Every Y months on the same X day is: X */Y * *
		$freq = array_key_exists( 'every', $params ) && is_numeric( $params['every'] ) ? $params['every'] : 1;
		if ( 1 == $freq )
			return sprintf( '%s * * *', $dom );
		return sprintf( '%s */%s * *', $dom, $freq );
	}

	/**
	 * BuildWeekly (private)
	 * Builds an expression that represents an event repeating X weeks by week on
	 * the same day every week. The typical example here is a weekly meeting.
	 *
	 * Parameters:
	 *  every - Optional frequency 1=weekly, 2=fortnightly, etc...
	 *
	 * @param $start The start date of the event repeat cycle
	 * @param $params The key parameter array described in parameters above
	 * @return String representation of the weekly repeating event
	 */
	private static function BuildWeekly( $start, $params=array() )
	{
		// Extract the DoW from the start date
		$dow = $start->format( 'w' ) + 1; // 1=sunday, 6=saturday

		// Every Y weeks on the same X day is: * * X 1/Y
		// if weekly Y is not needed: * * X *
		// NOTE: * * X/1 -> only the first X of month
		$freq = array_key_exists( 'every', $params ) && is_numeric( $params['every'] ) ? $params['every'] : 1;
		if ( 1 == $freq )
			return sprintf( '* * %s *', $dow );
		return sprintf( '* * %s 1/%s', $dow, $freq );
	}

	/**
	 * BuildLastXofMonth (private)
	 * Builds an expression that represents an event repeating on the last Xday
	 * of every month (e.g. the last Sunday of every month).
	 *
	 * Parameters:
	 *  day - Required day of the week (1=Sunday, 7=Saturday)
	 *
	 * @params $start The start date of the event repeat cycle
	 * @params $params The key parameter array described in parameters above
	 * @return String representation of the monthly event
	 */
	private static function BuildLastXofMonth( $start, $params=array() )
	{
		// This has nothing to do with the first day the event runs
		// it is purely based on the day parameter
		if ( ! array_key_exists( 'day', $params ) || ! is_numeric( $params['day'] ) || 1 > $params['day'] || 7 < $params['day'] )
			return null; // invalid day required parameter

		// This is expressed as: * * X/-1 *
		// The converse first X of month is: * * X/1 *
		// NOTE: These are different to * * X * which is weekly X
		return sprintf( '* * %s/-1 *', $params['day'] );
	}

	/**
	 * BuildFirstXofMonth (private)
	 * Builds an expression that represents an event repeating on the first Xday
	 * of every month (e.g. the first Friday of every month) - the reverse of above.
	 *
	 * Parameters:
	 *  day - Required day of the week (1=Sunday, 7=Saturday)
	 *
	 * @param $start The start date of the event repeat cycle
	 * @param $params The key parameter array described in parameters above
	 * @return String representation of the monthly event
	 */
	private static function BuildFirstXofMonth( $start, $params=array() )
	{
		// Identical to BuildLastXofMonth
		if ( ! array_key_exists( 'day', $params ) || ! is_numeric( $params['day'] ) || 1 > $params['day'] || 7 < $params['day'] )
			return null;
		return sprintf( '* * %s/1 *', $params['day'] );
	}
	
	/**
	 * BuildSecondXofMonth (private)
	 * Builds an expression that represents an event repeating on the first Xday
	 * of every month (e.g. the first Friday of every month) - the reverse of above.
	 *
	 * Parameters:
	 *  day - Required day of the week (1=Sunday, 7=Saturday)
	 *
	 * @param $start The start date of the event repeat cycle
	 * @param $params The key parameter array described in parameters above
	 * @return String representation of the monthly event
	 */
	private static function BuildSecondXofMonth( $start, $params=array() )
	{
		// Identical to BuildLastXofMonth
		if ( ! array_key_exists( 'day', $params ) || ! is_numeric( $params['day'] ) || 1 > $params['day'] || 7 < $params['day'] )
			return null;
		return sprintf( '* * %s/2 *', $params['day'] );
	}
	/**
	 * BuildSecondXofMonth (private)
	 * Builds an expression that represents an event repeating on the first Xday
	 * of every month (e.g. the first Friday of every month) - the reverse of above.
	 *
	 * Parameters:
	 *  day - Required day of the week (1=Sunday, 7=Saturday)
	 *
	 * @param $start The start date of the event repeat cycle
	 * @param $params The key parameter array described in parameters above
	 * @return String representation of the monthly event
	 */
	private static function BuildThirdXofMonth( $start, $params=array() )
	{
		// Identical to BuildLastXofMonth
		if ( ! array_key_exists( 'day', $params ) || ! is_numeric( $params['day'] ) || 1 > $params['day'] || 7 < $params['day'] )
			return null;
		return sprintf( '* * %s/3 *', $params['day'] );
	}
	
	/**
	 * BuildFourthXofMonth (private)
	 * Builds an expression that represents an event repeating on the first Xday
	 * of every month (e.g. the first Friday of every month) - the reverse of above.
	 *
	 * Parameters:
	 *  day - Required day of the week (1=Sunday, 7=Saturday)
	 *
	 * @param $start The start date of the event repeat cycle
	 * @param $params The key parameter array described in parameters above
	 * @return String representation of the monthly event
	 */
	private static function BuildFourthXofMonth( $start, $params=array() )
	{
		// Identical to BuildLastXofMonth
		if ( ! array_key_exists( 'day', $params ) || ! is_numeric( $params['day'] ) || 1 > $params['day'] || 7 < $params['day'] )
			return null;
		return sprintf( '* * %s/4 *', $params['day'] );
	}
	

	/**
	 * BuildYearly (private)
	 * Builds an expression that represents an event repeating on the same day
	 * every year (e.g. 26/JAN is Australia Day). Due to long term caching I
	 * have NOT allowed an X factor here to have every 2nd/3rd/etc.. year.
	 *
	 * @param $start The start date of the event repeat cycle
	 * @param $params Will be an empty array or null because none allowed
	 * @return String representation of the yearly event
	 */
	private static function BuildYearly( $start, $params=null )
	{
		// Get the day and month from the start date
		$day = $start->format( 'j' );
		$mon = $start->format( 'n' );

		// Expressed as: X Y *
		// Meaning only on X in month Y no matter what DoW it is
		return sprintf( '%s %s * *', $day, $mon );
	}
	
}



/* ====================================================================
 * The following classes encapsulate days, weeks, months and years that 
 * exist between two points in time. At it's core the days simply have
 * a flag indicating if the day is still active; this flag is disabled
 * by the RepeatExpression class above when building repeat dates.
 * ==================================================================== */

/**
 * A single Day object that can be filtered.
 */
class EveryCal_RE_Day
{
	private $available = true;
	private $date = null;

	/**
	 * Constructor fo the Day object.
	 *
	 * @param $d The date of this day.
	 */
	public function __construct( $d )
	{
		$this->date = clone $d;
		$this->available = true;
	}

	/**
	 * Returns the available status.
	 *
	 * @return True if the day is still available.
	 */
	public function IsEnabled() { return $this->available; }

	/**
	 * Disables the day
	 */
	public function Disable() { $this->available = false; }
	
	/**
	 * Returns the date of this day.
	 * @return The date of this day.
	 */
	public function GetDate() { return $this->date; }
}

/**
 * Week encapsulates a standard week of day objects for filtering.
 * A week may not have all 7 days if it is on the border of a year.
 */
class EveryCal_RE_Week
{
	private $epoch_offset = null;
	private $day_objs = null;
	private $day0 = null;

	/**
	 * Constructor for the Week object.
	 *
	 * @param $start The first day of this week
	 */
	public function __construct( $start, $offset )
	{
		$this->day0 = clone $start;
		$this->day_objs = array();
		$this->epoch_offset = $offset;
	}

	/**
	 * Returns the epoch offset (number of weeks since event epoch)
	 *
	 * @return Offset from event epoch as number of weeks.
	 */
	public function GetEpochOffset() { return $this->epoch_offset; }

	/**
	 * Add a given day at the given index in the week.
	 *
	 * @param $i The index ECP1_SUNDAY - ECP1_SATURDAY to store in
	 * @param $d Reference to the Day object
	 */
	public function AddDay( $i, &$d ) { $this->day_objs[$i] = $d; }

	/**
	 * Returns an array of the month day numbers for days in this week.
	 *
	 * @return Array of day numbers in this week.
	 */
	public function DaysOfMonth()
	{
		$days = array();
		foreach( array_keys( $this->day_objs ) as $day )
			$days[] = $this->day_objs[$day]->GetDate()->format( 'j' );
		return $days;
	}

	/**
	 * Disables any days in the week which are not in the keep parameter.
	 * This function expects $keep to be all positive values.
	 *
	 * @param $keep The array of weekdays to not disable.
	 */
	public function FilterDaysTo( $keep )
	{
		// We DON'T need to worry about pos/neg values here
		foreach( array_keys( $this->day_objs ) as $day ) {
			$realday = $this->day_objs[$day]->GetDate()->format( 'w' ) + 1;
			if ( ! ( in_array( $realday, $keep ) ) )
				$this->day_objs[$day]->Disable();
		}
	}

	/**
	 * Disables all days in the week.
	 */
	public function FilterAll()
	{
		foreach( array_keys( $this->day_objs ) as $k ) {
			$this->day_objs[$k]->Disable();
		}
	}
}

/**
 * Month encapsulates a standard calendar month of days.
 * The month may not have all the days in it's array if the
 * month is on the border of a year.
 */
class EveryCal_RE_Month
{
	private $epoch_offset = null;
	private $day_objs = null;
	private $day0 = null;

	/**
	 * Constructor for the Month object.
	 *
	 * @param $start The first day of this month
	 */
	public function __construct( $start, $offset )
	{
		$this->day0 = clone $start;
		$this->day_objs = array();
		$this->epoch_offset = $offset;
	}

	/**
	 * Returns the numirical representation of this month 1-12.
	 *
	 * @return The numerical representation of this month.
	 */
	public function GetMonth() { return $this->day0->format( 'n' ); }

	/**
	 * Returns the epoch offset for this month.
	 *
	 * @return Epoch offset for the month.
	 */
	public function GetEpochOffset() { return $this->epoch_offset; }

	/**
	 * Add a given day at the given index in the month.
	 *
	 * @param $i The index 1 - days in month to store in
	 * @param $d Reference to the Day object
	 */
	public function AddDay( $i, &$d ) { $this->day_objs[$i] = $d; }

	/**
	 * Converts the month into an array / vector of Week objects.
	 * The weeks align to the boundary of the month either:
	 *  a) The first week always starts on the 1st; or
	 *  b) The last week always ends on the last day.
	 *
	 * Pass the reverse parameter as false for start of month alignment,
	 * which is the default if none given, or true for end of month.
	 *
	 * UTC Timezone: This function uses UTC timezones to get the day of
	 * week for the 1st and last days of the month; it does NOT use the
	 * timestamp within these DateTime objects for anything comparissons.
	 * The Week objects in the returned vector will have their day0 in
	 * UTC timezone too but this is just the first "date" of the week,
	 * it does not affect the 0th Day object in the week.
	 *
	 * @param $reverse Should the weeks aligned to 1st or last day.
	 * @return Array of week objects holding this months days.
	 */
	public function ToWeeksVector( $reverse=false )
	{
		$weeks = array();
		// Note that day0 is the first date of a real day in month not necessarily 1st of month
		// so we may have weeks in the vector that we don't need to copy days into. As noted 
		// above the 1st of the month is the start of the first week this is NOT aligned to
		// weeks in the year.
		$wcounter = 0;
		$utc = new DateTimeZone( 'UTC' ); // see above
		$tplym = $this->day0->format( 'Y-m-' );
		$days_in_month = $this->day0->format( 't' );
		$first_real_day = $this->day0->format( 'j' );
		$first = new DateTime( $tplym . '1', $utc );
		$weekday_counter = ECP1_SUNDAY; // NOTE: Not necessarily a SUNDAY just first of week
		if ( $reverse ) {
			// Move the first day of the first week to force alignment to the
			// end of the month; effectively we just shorten the first week
			$last = new DateTime( $tplym . $days_in_month, $utc );
			$weekday_counter = $first->format( 'w' ) - $last->format( 'w' );
			if ( $weekday_counter < ECP1_SUNDAY )
				$weekday_counter += ECP1_SATURDAY;
		}

		// Loop over all the days and add them to weeks as necessary
		for ( $i=1; $i<=$days_in_month; $i++ ) {
			if ( ! array_key_exists( $wcounter, $weeks ) )
				$weeks[$wcounter] = new EveryCal_RE_Week( new DateTime( $tplym . $i, $utc ), -1 );
			if ( $i >= $first_real_day && array_key_exists( $i, $this->day_objs ) ) // could be short either end
				$weeks[$wcounter]->AddDay( $weekday_counter, $this->day_objs[$i] );
			if ( $weekday_counter == ECP1_SATURDAY ) {
				$weekday_counter = ECP1_SUNDAY;
				$wcounter += 1;
			} else { 
				$weekday_counter += 1;
			}
		}

		// Return the computed vector
		return $weeks;
	}

	/**
	 * Disables days in this month that are not in the list.
	 *
	 * @param $keep Array of days to keep in this month.
	 */
	public function FilterDaysTo( $keep )
	{
		// How many days are there in this month?
		$days = $this->day0->format( 't' );

		// Loop over the days in this month
		foreach( array_keys( $this->day_objs ) as $day ) {
			// If the day or it's negative is not in the array disable it
			$realday = $this->day_objs[$day]->GetDate()->format( 'j' );
			if ( ! ( in_array( $realday, $keep ) || in_array( $realday - $days - 1, $keep ) ) )
				$this->day_objs[$day]->Disable();
		}
	}

	/**
	 * Disable all days in the month.
	 */
	public function FilterAll()
	{
		foreach( array_keys( $this->day_objs ) as $day ) {
			$this->day_objs[$day]->Disable();
		}
	}
}

/**
 * Year encapsulates a standard calendar year from the epoch yearly
 * repeat point forward to the day before that point in the following
 * year. THIS IS NOT ALIGNED AS 1JAN - 31DEC DO NOT TREAT IT AS SUCH.
 */
class EveryCal_RE_Year
{
	private $years_since_epoch = null;
	private $month_objs = null;
	private $week_objs = null;
	private $day_objs = null;
	private $day0 = null;
	private $day365 = null; // not necessarily 365 maybe 366

	/**
	 * Static method that returns the offset month from epoch for a given start date.
	 * The epoch is in month 0 (aka offset 0), the next calendar month is offset 1,
	 * and so on. We exploit some algebra here:
	 *
	 *   offset = 12*(Y[s])-Y[e]) + M[s] - M[e]
	 *
	 * @param $start The date we want the offset calculated for.
	 * @param $epoch The date we are calculating the offset from.
	 * @return Number of months the epoch is offset.
	 */
	public static function MonthEpochOffset( $start, $epoch )
	{
		$ys = $start->format( 'Y' );
		$ms = $start->format( 'n' );
		$ye = $epoch->format( 'Y' );
		$me = $epoch->format( 'n' );
		return 12 * ( $ys - $ye ) + $ms - $me;
	}

	/**
	 * Static method that returns the offset week from epoch for a given start date.
	 * As above the epoch is in week 0 (aka offset 0), the next Sunday starts week
	 * offset 1, and so on. This is a little harder because we effectively want to
	 * count the number of Sundays between the two dates. LxS means last Sunday.
	 *
	 *   offset = roundDown( ( TS[s] - TS[LxS[e]] ) / 86400 ) 
	 *
	 * @param $start The date we want the offset calculated for.
	 * @param $epoch The date we are calculating the offset from.
	 * @return Number of weeks the epoch is offset.
	 */
	public static function WeekEpochOffset( $start, $epoch )
	{
		$tss = $start->format( 'U' ); // php 5.2 compatible
		$nxs = clone $epoch;
		$dow = $nxs->format( 'w' );
		if ( $dow > 0 ) // epoch is not a sunday
			$nxs->modify( '-' . $dow . ' day' );
		$tse = $nxs->format( 'U' );
		return (int) floor( ( $tss - $tse ) / 604800 );
	}

	/**
	 * Create an instance of the Year object.
	 *
	 * @param $count The year index since epoch
	 * @param $start_date The first day of the year
	 */
	public function __construct( $count, $start_date, $epoch_date )
	{
		$this->years_since_epoch = $count;
		$this->day0 = clone $start_date;

		// Calculate the month and week offsets from epoch date
		$mepoch_offset = self::MonthEpochOffset( $start_date, $epoch_date );
		$wepoch_offset = self::WeekEpochOffset( $start_date, $epoch_date );

		// Instantiate the days, weeks and months
		$this->day_objs = array();
		$this->week_objs = array();
		$this->month_objs = array();
		
		// Compute the days for this year, then assign them to weeks
		// and months by reference (see AddDay function in classes).
		// The week boundaries are sunday to saturday 1 to 7.
		// Month boundaries match calendar months.
		$mcounter = $wcounter = $dcounter = 0;
		$tsdate = clone $start_date; $tfdate = clone $start_date; $tfdate->modify( '+1 year' );
		$this->day365 = clone $tfdate; $this->day365->modify( '-1 day' );

		// Construct the first week that contains this start point
		$wsdate = clone $tsdate; $wsoffset = $wsdate->format( 'w' );
		if ( $wsoffset != PHP_SUNDAY )
			$wsdate->modify( '-' . $wsoffset . ' day' );
		$dwcounter = $wsoffset + 1; // convert to ECP1_XXXX
		$tempweek = new EveryCal_RE_Week( $wsdate, $wcounter + $wepoch_offset );

		// Construct the first month that contains this start point
		$msdate = clone $tsdate; $msoffset = $msdate->format( 'j' );
		if ( $msoffset != 1 ) // not 1st of month: 2nd go back 1, 3rd go back 2, etc...
			$msdate->modify( '-' . ( $msoffset - 1 ) . ' day' );
		$dmcounter = $msoffset; // 1-indexed like calendar
		$tempmonth = new EveryCal_RE_Month( $msdate, $mcounter + $mepoch_offset );

		//printf( "DEBUG: TSDATE=%s | TFDATE=%s\n", $tsdate->format( 'Y-m-d' ), $tfdate->format( 'Y-m-d' ) );
		//printf( "DEBUG: WEEK OFFSET=%s | MONTH OFFSET=%s\n", $wepoch_offset, $mepoch_offset );

		// Loop over all the dates in the year and assign to weeks and months
		while ( $tsdate < $tfdate ) {
			$this->day_objs[$dcounter] = new EveryCal_RE_Day( $tsdate );
			$tempweek->AddDay( $dwcounter, $this->day_objs[$dcounter] );
			$tempmonth->AddDay( $dmcounter, $this->day_objs[$dcounter] );
			$dcounter += 1; $dwcounter += 1; $dmcounter += 1;
			$tsdate->modify( '+1 day' );

			// If we're on a Sunday (week has looped) add the temp week and make a new one
			if ( PHP_SUNDAY == $tsdate->format( 'w' ) ) {
				$this->week_objs[$wcounter] = $tempweek;
				$wcounter += 1;
				$dwcounter = ECP1_SUNDAY; // reset to sunday and start new week
				$tempweek = new EveryCal_RE_Week( $tsdate, $wcounter + $wepoch_offset );
			}

			// If this is the first day of the month (have looped) add the temp month and make a new one
			if ( 1 == $tsdate->format( 'j' ) ) {
				$this->month_objs[$mcounter] = $tempmonth;
				$mcounter += 1;
				$dmcounter = 1; // reset to the 1st and start a new month
				$tempmonth = new EveryCal_RE_Month( $tsdate, $mcounter + $mepoch_offset );
			}
		}

		// Unless we just added the last week and month we need to add them now
		if ( PHP_SATURDAY != $tsdate->format( 'w' ) )
			$this->week_objs[$wcounter] = $tempweek;
		if ( 1 != $tsdate->format( 'j' ) )
			$this->month_objs[$mcounter] = $tempmonth;
	}

	/**
	 * Returns an array of Day objects that are still enabled.
	 *
	 * @return Array of Day objects that are enabled.
	 */
	public function GetDates()
	{
		$set = array();
		foreach( array_keys( $this->day_objs ) as $k ) {
			if ( $this->day_objs[$k]->IsEnabled() )
				$set[] = $this->day_objs[$k];
		}
		return $set;
	}

	/**
	 * Returns a string representation of enabled days in the year
	 *
	 * @return String representation of enabled days in the year
	 */
	public function __toString()
	{
		$cyear = $cmonth = 'NONE';
		$s = sprintf( "\nYEAR: %s", $this->years_since_epoch );
		foreach( $this->GetDates() as $d ) {
			if ( $cyear != $d->GetDate()->format( 'Y' ) ) {
				$cyear = $d->GetDate()->format( 'Y' );
				$cmonth = $d->GetDate()->format( 'M' );
				$s .= sprintf( "\n%s:", $cyear );
				$s .= sprintf( "\n  %s: ", $cmonth );
			}
			if ( $cmonth != $d->GetDate()->format( 'M' ) ) {
				$cmonth = $d->GetDate()->format( 'M' );
				$s .= sprintf( "\n  %s: ", $cmonth );
			}
			$s .= sprintf( "%s, ", $d->GetDate()->format( 'j' ) );
		}
		return $s;
	}

	/**
	 * FilterBefore
	 * Marks all days in this year as disabled if they are before the given date.
	 *
	 * @param $d The date to use when marking.
	 */
	public function FilterBefore( $d )
	{
		if ( $this->day0 >= $d || count( $this->day_objs ) == 0 )
			return; // no days to mark
		
		// Counter of days starts at zero for the year and dates are in order
		$counter = 0;
		$max = count( $this->day_objs ) - 1;
		$dtrack = $this->day_objs[$counter]->GetDate();
		while ( $dtrack < $d ) {
			$this->day_objs[$counter]->Disable();
			$counter += 1;
			if ( $counter > $max )
				break;
			$dtrack = $this->day_objs[$counter]->GetDate();
		}
	}

	/**
	 * FilterAfter
	 * Marks all days in this year as disabled if they are after the given date.
	 *
	 * @param $d The date to use when marking.
	 */
	public function FilterAfter( $d )
	{
		if ( $this->day365 <= $d || count( $this->day_objs ) == 0 )
			return; // no days to mark

		// Counting backwards is easy enough as dates are in order
		$counter = count( $this->day_objs ) - 1;
		$dtrack = $this->day_objs[$counter]->GetDate();
		while ( $dtrack > $d ) {
			$this->day_objs[$counter]->Disable();
			$counter -= 1;
			if ( $counter < 0 )
				break;
			$dtrack = $this->day_objs[$counter]->GetDate();
		}
	}

	/**
	 * Filters all the months in the year to only include days given.
	 *
	 * @param $keep The day numbers to keep.
	 */
	public function FilterMonthDaysTo( $keep )
	{
		foreach( array_keys( $this->month_objs ) as $k ) {
			$this->month_objs[$k]->FilterDaysTo( $keep );
		}
	}

	/**
	 * Filters weekdays to the given set of days.
	 *
	 * @param $keep The weekday numbers ECP1_XXX to keep.
	 */
	public function FilterWeekdaysTo( $keep )
	{
		foreach( array_keys( $this->week_objs ) as $k ) {
			$this->week_objs[$k]->FilterDaysTo( $keep );
		}
	}

	/**
	 * Filter all months and only keep days in months in the given array.
	 *
	 * @param $keep The month index (1-12) to keep days in.
	 */
	public function FilterMonthsTo( $keep )
	{
		foreach( array_keys( $this->month_objs ) as $k ) {
			$mnum = $this->month_objs[$k]->GetMonth();
			if ( ! ( in_array( $mnum, $keep ) ) )
				$this->month_objs[$k]->FilterAll();
		}
	}

	/**
	 * Exclude all days in months that are not in any of the given cycles.
	 *
	 * @param $cycles The array of cycles from epoch to keep.
	 */
	public function ApplyMonthlyCycles( $cycles )
	{
		foreach( array_keys( $this->month_objs ) as $k ) {
			$safe = false;
			$moffset = $this->month_objs[$k]->GetEpochOffset();
			foreach( $cycles as $cycle ) {
				if ( $moffset % $cycle == 0 )
					$safe = true;
			}
			// If not marked as safe then remove all from month
			if ( ! $safe )
				$this->month_objs[$k]->FilterAll();
		}
	}

	/**
	 * Filters a month in a weekly schedule. Disables all in any week not in filters.
	 *
	 * @param $filters The set of weeks to keep days in.
	 */
	public function ApplyWeekdayFilters( $filters )
	{
		foreach( array_keys( $this->month_objs ) as $k ) {
			$mweeks = $this->month_objs[$k]->ToWeeksVector( false );
			$rweeks = $this->month_objs[$k]->ToWeeksVector( true ); // reversed
			$max = count( $mweeks ); // number of weeks in month same in both
			$keep_days = array();
			for ( $i=0; $i<$max; $i++ ) {
				// Keep days in this week
				if ( in_array( $i+1, $filters ) ) {
					foreach( $mweeks[$i]->DaysOfMonth() as $day )
						$keep_days[] = $day;
				}
				// Keep days in the reverse week
				if ( in_array( $i-$max, $filters ) ) {
					foreach( $rweeks[$i]->DaysOfMonth() as $day )
						$keep_days[] = $day;
				}
			}
			// Apply the day filter
			$this->month_objs[$k]->FilterDaysTo( $keep_days );
		}
	}

	/**
	 * Filters the weeks in this year by the repeat and cycle parameters.
	 *
	 * Example:
	 *   repeats = 1,2,3
	 *   cycles  = null
	 * This would repeat in the 1st, 2nd and 3rd weeks after epoch.
	 *
	 * Alternatively if
	 *   repeats = 4, 8
	 *   cycles  = 10
	 * This would repeat in the 4th and 8th weeks of a 10 week cycle.
	 *
	 * @param $repeat Array of weeks to repeat the events in.
	 * @param $cycles Array of cycle week lengths from offset (null for year).
	 */
	public function ApplyWeeklyCycles( $repeat, $cycles )
	{
		// If there is no cycle set given then just loop over weeks
		// and filter out all days if the week number doesn't match
		if ( $cycles == null || count( $cycles ) == 0 ) {

			// This is an array that store the index of days to keep
			$keepdays_index = array();

			// Calculate the keep days index array
			$numdays = count( $this->day_objs );
			foreach( $repeat as $week ) {
				if ( $week > 0 ) {

					// Multiply out the week to get the range of days to keep
					$start = ( $week - 1 ) * 7;
					$finish = $week * 7;
					if ( $start >= $numdays ) continue;
					if ( $finish > $numdays ) $finish = $numdays;
					for ( $i=$start; $i<$finish; $i++ )
						$keepdays_index[] = $i;

				} else if ( $week < 0 ) {

					// Do a reverse multiplication to get end of year back 7 days
					$finish = $numdays + ( ( $week + 1 ) * 7 ); // week is neg so days - weeks
					$start = $numdays + ( $week * 7 );
					if ( $finish <= 0 ) continue;
					if ( $start < 0 ) $start = 0;
					for ( $i=$start; $i<$finish; $i++ )
						$keepdays_index[] = $i;

				}
			}

			// Loop over the days and disable any not marked
			foreach( array_keys( $this->day_objs ) as $k ) {
				if ( ! in_array( $k, $keepdays_index ) )
					$this->day_objs[$k]->Disable();
			}

		} else if ( is_array( $cycles ) && count( $cycles ) > 0 ) {

			// This is fundamentally different to the above it loops over
			// actual SUN-SAT weeks instead of 7 day blocks. Where the:
			//    weeks epoch offset % cycles == repeats - 1
			// the week is considered to contain available days.
			foreach( array_keys( $this->week_objs ) as $k ) {
				$save = false;
				$offset = $this->week_objs[$k]->GetEpochOffset();
				foreach( $cycles as $cycle ) {
					foreach( $repeat as $spot ) {
						if ( $offset % $cycle == $spot - 1 ) {
							$save = true;
							break; // no more iterations required
						}
					}
					if ( $save ) break; // don't iterate once we know the answer
				}
				if ( ! $save )
					$this->week_objs[$k]->FilterAll();
			}

		}
	}
}

/**
 * DateRange is the class the RepeatExpression processor works with
 * it encapsulates the Years, Months, Weeks and Days and filters them
 * based on a "age" style year from the event epoch.
 */
class EveryCal_RE_DateRange
{
	private $years = null;
	private $from = null;
	private $until = null;
	private $epoch = null;

	public function __construct( $epoch, $start, $finish )
	{
		$this->from = clone $start;
		$this->until = clone $finish;
		$this->epoch = clone $epoch;
		$this->years = array();
		
		// Calculate the number of years passed from epoch to start
		$counter = 0;
		$eyear = $this->epoch->format( 'Y' );
		$syear = $this->from->format( 'Y' );
		$fyear = $this->until->format( 'Y' );

		// Error Checking
		if ( $syear < $eyear )
			// Can't start calculating before the epoch so throw error
			throw new Exception( sprintf( __( "Can't construct EveryCal_RE_DateRange with start before event epoch" ) ) );
		if ( $fyear < $syear )
			// Can't start calculating after the end point so throw error
			throw new Exception( sprintf( __( "Can't construct EveryCal_RE_DateRange with finish before start" ) ) );
		
		// Build the year array
		if ( $eyear == $syear ) { // same year still 0th year of life
			
			// Create a year 0 instance from the epoch date
			$this->years[0] = new EveryCal_RE_Year( 0, $this->epoch, $this->epoch );
			$this->years[0]->FilterBefore( $this->from );

			// Check the finish date, if inside year 0 then dont' worry but if
			// not then we need to build a year 1 and filter out the end
			if ( $eyear == $fyear ) {
				$this->years[0]->FilterAfter( $this->until );
			} else {
				// Check if still within the 1 year boundary
				$tdate = clone $this->epoch;
				$tdate->modify( '+1 year' );
				while ( $tdate < $this->until ) { // not within the boundary
					$counter += 1;
					$this->years[$counter] = new EveryCal_RE_Year( $counter, $tdate, $this->epoch );
					$tdate->modify( '+1 year' );
				}

				// Filter the last year by the end date
				$this->years[$counter]->FilterAfter( $this->until );
			}

		} else { // end same start year as epoch
			
			// Two options here:
			// 1) still within year 0 and need to filter out days before start; or
			// 2) need to find year that contains start point
			$tsdate = clone $this->epoch;
			$ts2date = clone $this->epoch;
			while ( $ts2date < $this->from ) {
				$ts2date->modify( '+1 year' );
				if ( $ts2date < $this->from ) {
					$tsdate = clone $ts2date;
					$counter += 1;
				}
			}

			// Build the first year at the counter and filter to the start point
			$this->years[$counter] = new EveryCal_RE_Year( $counter, $tsdate, $this->epoch );
			$this->years[$counter]->FilterBefore( $this->from );

			// Now effectively we need to find the finish date and build years to it
			$tsyear = $tsdate->format( 'Y' );
			if ( $tsyear == $fyear ) { //  finishes same year so simply filter
				$this->years[$counter]->FilterAfter( $this->until );
			} else {
				// Check if still inside a 1 year boundary and loop if not (as above)
				$tsdate->modify( '+1 year' );
				while ( $tsdate < $this->until ) {
					$counter += 1;
					$this->years[$counter] = new EveryCal_RE_Year( $counter, $tsdate, $this->epoch );
					$tsdate->modify( '+1 year' );
				}

				// Filter the final year by the end date
				$this->years[$counter]->FilterAfter( $this->until );
			}

		}
	}

	/**
	 * Returns an string representation of the DateRange and what is still enabled
	 *
	 * @return String representation of enabled days in the date range.
	 */
	public function __toString()
	{
		$s = sprintf( "DUMP OF DATE RANGE %s -> %s (EPOCH: %s)\n", $this->from->format( 'Y-m-d' ),
			$this->until->format( 'Y-m-d' ), $this->epoch->format( 'Y-m-d' ) );
		foreach( array_keys( $this->years ) as $k ) {
			$s .= sprintf( "%s\n", $this->years[$k] );
		}
		return $s;
	}

	/**
	 * GetDates
	 * Returns an array of the dates still available in the range.
	 *
	 * @return Array of DateTime objects which are available days.
	 */
	public function GetDates()
	{
		$mydates = array();
		foreach( array_keys( $this->years ) as $k ) {
			foreach( $this->years[$k]->GetDates() as $d )
				$mydates[] = $d->GetDate();
		}
		return $mydates;
	}

	/**
	 * FilterDaysNotIn
	 * Marks all days in all years as disabled in the day of the month isn't
	 * in the input array of days. Days can be both positive and negative,
	 * where -1 means last day etc...
	 *
	 * @param $keep Array of day numbers to keep.
	 */
	public function FilterDaysNotIn( $keep )
	{
		foreach( array_keys( $this->years ) as $k ) {
			$this->years[$k]->FilterMonthDaysTo( $keep );
		}
	}

	/**
	 * FilterWeekdaysNotIn
	 * Marks all days which are not the weekdays in the given array as disabled.
	 * Days can be both positive and negative but for this pass we only need the
	 * absolute value see ApplyWeekdayFilters for handling of the negative.
	 *
	 * @param $keep Array of ECP1_XXX day numbers to keep.
	 */
	public function FilterWeekdaysNotIn( $keep )
	{
		$xkeep = array();
		foreach( $keep as $k )
			$xkeep[] = abs( $k ); // convert to positive
		foreach( array_keys( $this->years ) as $k ) {
			$this->years[$k]->FilterWeekdaysTo( $xkeep );
		}
	}

	/**
	 * FilterMonthsNotIn
	 * Marks all days as disabled if they are not in one of the given months.
	 *
	 * @param $keep The months to keep days for.
	 */
	public function FilterMonthsNotIn( $keep )
	{
		foreach( array_keys( $this->years ) as $k ) {
			$this->years[$k]->FilterMonthsTo( $keep );
		}
	}

	/**
	 * ApplyMonthlyCycle
	 * Marks all days in months not in the given cycle iterations as disabled.
	 *
	 * @param $cycles The montly cycles to loop over.
	 */
	public function ApplyMonthlyCycle( $cycles )
	{
		foreach( array_keys( $this->years ) as $k ) {
			$this->years[$k]->ApplyMonthlyCycles( $cycles );
		}
	}

	/**
	 * ApplyWeekdayFilter
	 * Marks all weekdays that don't match the filter conditions as disabled.
	 *
	 * @param $filters The weekday filters to look for.
	 */
	public function ApplyWeekdayFilter( $filters )
	{
		foreach( array_keys( $this->years ) as $k ) {
			$this->years[$k]->ApplyWeekdayFilters( $filters );
		}
	}

	/**
	 * ApplyWeeklyCycle
	 * Marks all days not in the repeated index weeks in the cycles since
	 * epoch as disabled. This is a little obtuse so by way of example if
	 *   repeats = 1,2,3
	 *   cycles  = null
	 * This would repeat in the 1st, 2nd and 3rd weeks after epoch.
	 *
	 * Alternatively if
	 *   repeats = 4, 8
	 *   cycles  = 10
	 * This would repeat in the 4th and 8th weeks of a 10 week cycle.
	 *
	 * @param $repeat Array of the week indexes to repeat the event in.
	 * @param $cycles Array of the cycles since epoch.
	 */
	public function ApplyWeeklyCycle( $repeat, $cycles )
	{
		foreach( array_keys( $this->years ) as $k ) {
			$this->years[$k]->ApplyWeeklyCycles( $repeat, $cycles );
		}
	}
}

// Don't close the php interpreter
/*?>*/
