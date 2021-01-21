Introduction
------------
Event Calendar Extension allows you to view all CiviCRM events in a Calendar by month,day,week.The setting page allows us to select which events should be shown on Calendar with the color we want for particular event type.The setting page configuration for event types filters allows us to filter by particular event types on calendar.

Installation
-------------
1. Move the downloaded extension to your extensions folder.
2. Goto civicrm/admin/extensions&reset=1  -- install the extension

If you are new to CiviCRM Extension you can get help about extension from

http://wiki.civicrm.org/confluence/display/CRMDOC42/Extensions

Requires
--------
Extension required https://www.drupal.org/project/jquery_update module for installation with Drupal7.

Usage
---------------
1. Click Administrator->CiviEvent->Event Calendar Settings menu (civicrm/admin/event-calendar) -- change the setting if required.
2. Now you can add multiple calendar with specific settings & preview event data as per settings on calendar.
3. Click Events->Show Events menu (civicrm/showevents) -- to view global Event Calendar with Events as per settings.

Note
-----
1: If you are using Joomla CMS then copy the folder "yourextensiondirectory/com.osseed.eventcalendar/joomla/EventCalendar" to "joomlarootdirectory/components/com_civicrm/views/" which will add menu item type for Event Calendar.
2: If you are using Wordpress CMS then copy the folder "yourextensiondirectory/com.osseed.eventcalendar/wordpress/wordpress-event-calendar" to "wordpressdirectory/wp-content/plugins/" and activate the plugin which will add CiviCRM frontend type for Event Calendar.
3: In Joomla for frontend Calendar display you need to add menu items with menu item type as `Event calendar` & configure event calendar setting id (if you have multiple calendar) under it.If you pass empty ID it will display global calendar for frontend.
4: In wordpress you need to add shortcode under page like `[event_calendar component="event-calendar"]` for displaying calendar on front-end page view.Also you can add parameter for id specific calendar if multiple calendar setting are present.
