# CiviCRM Event Calendar

This is a [CiviCRM](https://civicrm.ord) extension that allows you to view all CiviCRM events in a calendar by month, day, or week. It uses the [FullCalendar](https://fullcalendar.io) javascript library to produce a full-featured calendar. The settings page allows you to specify event types that should be shown on the calendar, and indicate a color for each event type. 

## Installation

1. Move the downloaded extension to your extensions folder.
2. Go to `/civicrm/admin/extensions&reset=1`, find "Event Calendar", and click **install**.

See CiviCRM's User Guide for more info about [installing extensions](https://docs.civicrm.org/user/en/latest/introduction/extensions/#installing-extensions).

## Usage

1. Click **Administrator > CiviEvent > Event Calendar Settings** (`civicrm/eventcalendarsettings`) to configure the calendar settings.
2. Click **Events > Show Events** (`civicrm/showevents`) to view the calendar.

## Note

* If you are using Joomla CMS then copy the folder `yourextensiondirectory/com.osseed.eventcalendar/joomla/EventCalendar` to `joomlarootdirectory/components/com_civicrm/views/` which will add menu item type for Event Calendar.

* If you are using Wordpress CMS then copy the folder `yourextensiondirectory/com.osseed.eventcalendar/wordpress/wordpress-event-calendar` to `wordpressdirectory/wp-content/plugins/` and activate the plugin which will add CiviCRM frontend type for Event Calendar.
