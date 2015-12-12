=== Every Calendar +1 for WordPress ===
Contributors: andrewbevitt
Donate link: http://andrewbevitt.com/code/everycalplus1/
Tags: calendar, events
Requires at least: 3.5
Tested up to: 3.8
Stable tag: 2.1.1

A WordPress plugin that integrates calendars, repeating events, custom post types, maps, offsite linking and has widget support.

== Description ==

Every Calendar +1 is a pluggable interface for displaying locally entered events and displaying syndicated calendar feeds.

You can use any calendar feed that is supported by the FullCalendar jQuery library (at time of writing this was only Google Calendar). The event colours are customisable for each event source and the plugin supports a pluggable maps interface for event locations (initially the plugin only provides a Google Maps implementation but many more can be added).

See https://docs.google.com/document/pub?id=1QwmBusfl0gfRVkIt_PomAI3reG3B8Ae45-xCt1Q0uFw for annotated documentation.

The plugin creates two custom post types:
1) Events
2) Calendars

A Calendar Post can contain as many event posts as you like and can also syndicate as many external calendars as you like.

Events can be labeled as feature events: feature events will be displayed on any calendar the administratos configure as a Featured Calendar. This is a great way to have local site calendars (for say a regional office) and a global calendar which shows feature events from the local sites. Events can be scheduled to repeat (see below for details).

Roles and Capbilities: If you can edit a calendar and have edit_others_posts for events then you can edit any event in that calendar. Otherwise you can only edit your own as per normal.

This plugin was written because I could not find a plugin that provided great events management, calendar integration and worked reliably.

There is a planned development roadmap:

* Add support for extra calendar providers
* Add more widgets


== Installation ==

This plugin requires PHP5 >= 5.2.0 that's over 2 years old now so upgrade if you haven't yet.

The best way to install this plugin is through your WordPress Admin. Alternatively upload the zip file to your plugins directory.

Once the plugin has been installed/uploaded you need to Activate this plugin in the 'Plugins' Admin panel.

To put a calendar onto one of your pages use the provided shortcode in the 'Calendar' admin panel (created by plugin).


== Frequently Asked Questions ==

= Widget Support =

As of version 2.0.0 Every Calendar +1 has built in support for widgets. Initially there is a simple widget for listing event titles and dates, more will be addded in time. If you would like to contribute a widget please see widgets/title-list.php for an example of what needs to be done.

= Repeating Events =

As of version 1.0 Every Calendar +1 supports repeating events for local post events.

The repeating model works similar to the crontab model but only on days, months and days of week. You can choose from several predefined repeating patterns when creating your event post; or (if the setting is enabled) specify your own repeating expression for the event. Note that writing your own expression could do weird things with your event repeat dates so make sure you check it carefully.

= What format do I use for repeat expressions =

These are the example expressions that were planned for built-in support.

* DoM - Day of the Month: 1 means 1st of month
* MoY - Month of the Year: 1 means January
* DoW - Day of the Week: 1 means Sunday
* WsE - Weeks since Epoch (the event start date)

Simple examples:

	 DoM  MoY  DoW  WsE
	  *    *    1    *    Every Sunday
	  1   */2   *    *    First day every 2nd month
	  1   3/2   *    *    First day every 2nd month when month is March
	 -1    *    *    *    Last day of the month
	  *    *    6   1,-1  First after epoch and last Friday before 1 year repeat
	  *    *    1   1/3   Every 3rd Sunday (1st in group)
	  *    *    4   2/4   Every 4th Wednesday (2nd in group)
	  *    *   1/3   *    The 3rd Sunday of every month
	  *    *   2/-1  *    Last Monday every month
	  *   */3  1/-2  *    2nd last Sunday every 3rd month
	 -2   */6  4,5/5 *    2nd last day of every 6 month where it is the 5th Wed|Thur of the month
	
Some more complicated expression examples:

	 DoM    MoY            DoW      WoY
	 *      *              2/1,-1   *         First and last Monday of every month
	 10-20  *              2        *         Mondays between 10th and 20th
	 *      2,3,4,5,12     2/-1     *         Last Monday of Feb|Mar|Apr|May|Dec
	 5-25   3,4,5,9,10,11  6/1,4    *         1st|4th Friday of the month where day is 5th-25th in Autumn/Spring
	 *      */3            1/-1--3  *         Last, 2nd and 3rd Last Sundays of every 3rd month
	 2-8    2,5,9,10/2,5   3,4/1,2  *         1st/2nd Tue|Wed where is 2nd-8th in Feb|May|Sep|Oct and is 2nd or 5th Month cycle since start
	 *      1,2,12         2,3      1,2/5,7   Mon|Tue of 1st|2nd weeks in a 5|7 week rolling cycle since epoch in Summer month

= Does Every Calendar +1 Support Gravity Forms Custom Post Type Plugin? =

Yes. But the start / end date functionality requires PHP 5.3.0.

Use the Gravity Forms plugin to create the following custom fields for your event post:

* gravity_summary - The event summary
* gravity_description - The event description
* gravity_url - The event external URL (leave blank if using event post page)
* gravity_all_day - Does the event run all day? Y or N.
* gravity_calendar - Every Calendar +1 Calendar Post ID (leave blank if you want to manually assign)
* gravity_location - The address or location description: you will still need to do geocoding from the admin manually.
* gravity_start_date - The DATE the event starts
* gravity_start_date_format - Format string for the event start date see link below
* gravity_start_time - The TIME the event starts
* gravity_start_time_format - Format string for the event start time see link below
* gravity_end_date, gravity_end_date_format, gravity_end_time, gravity_end_time_format

Once you have set ANY of these custom fields the Event admin screen will prompt you to import the values.

You can set as many or as few as you like only the fields with values will be imported.

Once again **start / end date importing requires PHP 5.3.0**.

Note: Importing the values sets the plugin meta value: ecp1_ignore_gravity = Y. This means you will not be prompted again. If you want to be prompted to import again use the custom-fields at the bottom of the Event post edit screen to remove or set this meta value to N.

See http://au.php.net/manual/en/datetime.createfromformat.php for valid format strings.

= How do I allow contributors/authors to add events to a (someone elses) calendar? =

Use a capability manager to assign user as a contributor role to the post and set the calendar contributor role to allow editing of published posts.

In Role Scoper:
1. Go to the calendar and assign the group or user to Contributors for this post
2. Go to Role Scoper -> Options -> RS Role Definitions
3. Assign Calendar Contributor the "Edit Published..." capability.

= Why this plugin? =

I wanted a WordPress calendar that did everything and I couldn't find one that did so I wrote my own.

= What external calendars are supported? =

For the initial release only Google Calendar will be supported. This is more because of limitations in Full Calendar than anything else. The external calendar interface is pluggable so you can extend as you wish.


== Screenshots ==

1. Demo calendar with event details popup and map shown.
2. Event details page in the TwentyTwelve theme.


== Changelog ==

= 2.1.1 =
* Fix issue with CIVICRM_SETTINGS_PATH redefinition leading to include failure
* WP 3.8 compatibility checks

= 2.1.0 =
* Updated to FullCalendar 1.6.4 - includes new visuals
* Added option to use CDNJS for FullCalendar
* Added support for CiviCRM events 
* Event List Shortcode now uses the settings feed icon instead of fixed date icon

= 2.0.0 =
* Updated to FullCalendar 1.5.4 - supporting new WP3.5 jQuery
* Added Mapstraction library for more map providers
* Added support for OpenLayers (OSM) mapping using Mapstraction
* Added widget support and a simple upcoming event list widget
* Added some screenshots

= 1.0.7 =
* Fixed feature events wrapping end of years

= 1.0.6 =
* Added better support for non year/month/day permalinks
* Fixed server not at UTC date() bug when editing event

= 1.0.5 =
* Fixed cache buffer between repeat cache points (was 1 day now 10s)
* Better error checking for cache range date points

= 1.0.4 =
* Better cache coverage when updating the repeat until date
* Exclude cache entries that cause errors

= 1.0.3 =
* Typos when using repeat until paradigm
* Repeat until date now shown as date not timestamp in event edit

= 1.0.2 =
* Fixed PHP 5.2 double colon static class method call from class as variable

= 1.0.1 =
* Fixed PHP 5.2 HEREDOC bug in includes/data/sql.php
* Added option to disable max cache size

= 1.0 =
* Repeating events! This was the most requested feature...
* Updated FullCalendar javascript (1.5.3)
* Remove old reference to qtip (qtip was never used)
* Added RSS feed for a calendar it's in the same place as iCal / Webcal
* Can now have both a FullCalendar and Event List on same page (Joseph Carrington)
* Numerous bug fixes and value sanity checking

= 0.3.3 =
* Added support for event specific colors (NOTE: Feature Event colors still take precendence)
* Added support for users to specify other calendars the event can appear on (extra to featured)

= 0.3.2 =
* Fixed bug when event time was 12 midday would be converted to 12 midnight
* Enforced no pagination on the events-json feed to make sure all events are loaded

= 0.3.1 =
* Fixed show_time_on_all_day parameter so it works when events span multiple days

= 0.3.0 =
* Added many more configuration options: calendar and event posts are now controlled by templates
* Fixed bug so calendar renders as week on load (if chosen)
* Removed PEAR HTTP libaries in preference for WP HTTP_API
* Can now choose Icon for Calendar Export links
* Upgraded to FullCalendar 1.5.2

= 0.2.0 =
* Added [eventlist name="X" start="X" until="X"] shortcode (starting/until take human datetime strings and are optional)
* The [eventlist] shortcode gives a blog like looking list of events for the calendar
* Added **initial beta** support for Gravity Forms Custom Post Type plugin for Events (only for Events).

= 0.1.5 =
* Include comments on the events post
* Clear the loop actions on the iCAL and JSON feeds (fix http://wordpress.org/support/topic/plugin-every-calendar-1-for-wordpress-events-dont-show-up-on-calendar)

= 0.1.4 =
* Added MySQL support for GMT timezone conversion - fixes permalink bug where MySQL timezone is not GMT

= 0.1.3 =
* Client side CSS for popup links

= 0.1.2 =
* Added better permalinks for the event post type: /event/%year%/%month%/%day%/event-name
* First tagged stable release on the WordPress Plugin Directory
* Added screenshots to the repository

= 0.1.1 =
* Fixed bugs where PHP 5.3 API changed from PHP 5.2 now compatible with PHP 5.2
* Tidy up the readme file

= 0.1 =
* First major release with documented functionaility

= 0.1-beta =
* Functional support for event and calendar types but no maps or external feeds

= 0.1-alpha =
* Initial plugin creation


== Upgrade Notice ==

All versions of Every Calendar +1 are backward compatible at present.

= 2.0.0 = 
This version introduces the Mapstraction library - while the changes are backward compatible the database is modified for this version and will no longer work with older versions of the plugin.

The OpenLayers library (map) requires WordPress 3.5 or newer but the Google Maps library will continue to work with older versions of WordPress.

= 0.1-alpha0 =
This is the first alpha development release there is no reason to upgrade yet.

