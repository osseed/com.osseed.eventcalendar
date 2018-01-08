Introduction
------------
Event Calendar Extension allows you to view all CiviCRM events in a Calendar by month,day,week.The setting page allows us to select which events should be shown on Calendar with the color we want for particular event type. 

Installation
-------------
1. Move the downloaded extension to your extensions folder.
2. Goto civicrm/admin/extensions&reset=1  -- install the extension
 
If you are new to CiviCRM Extension you can get help about extension from

http://wiki.civicrm.org/confluence/display/CRMDOC42/Extensions

Library
---------------
Extension uses the fullcalendar library.
Add the fullcalendar library https://github.com/fullcalendar/fullcalendar/releases/tag/v1.6.7

Usage
---------------
1. Click Administrator->CiviEvent->Event Calendar Settings menu (civicrm/eventCalendarsettings) -- change the setting if required.
2. Click Events->Show Events menu (civicrm/showevents) -- to view Event Calendar with Events as per settings.

Note
-----
1: If you are using Joomla CMS then copy the folder "yourextensiondirectory/com.osseed.eventcalendar/joomla/EventCalendar" to "joomlarootdirectory/components/com_civicrm/views/" which will add menu item type for Event Calendar.

2: If you are using Wordpress CMS then copy the folder "yourextensiondirectory/com.osseed.eventcalendar/wordpress/wordpress-event-calendar" to "wordpressdirectory/wp-content/plugins/" and activate the plugin which will add CiviCRm frontend type for Event Calendar.
