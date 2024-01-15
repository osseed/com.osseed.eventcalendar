# com.osseed.eventcalendar

![Screenshot](screenshot/eventcalendar.png)

[Check Event Calendar Settings](screenshot/eventsettings.png)

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.4+
* CiviCRM

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.osseed.eventcalendar@https://github.com/osseed/com.osseed.eventcalendar/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/osseed/com.osseed.eventcalendar.git
cv en eventcalendar
```

## Getting Started

Event Calendar Extension allows you to view all CiviCRM events in a Calendar by month, day, week.
The setting page allows us to select which events should be shown on calendar with the color we want for particular event type
The setting page configuration for event types filters allows us to filter by particular event types on calendar.

## Requires

Extension required <https://www.drupal.org/project/jquery_update> module for installation with Drupal 7 setup.

## Usage

1. Click `Administrator -> CiviEvent -> Event Calendar Settings (civicrm/admin/event-calendar)` menu, Please change the setting if required.
2. Now you can add multiple calendar with specific settings & preview event data as per settings on calendar.
3. Click `Events->Show Events (civicrm/showevents)` menu to view global `Event Calendar` with Events as per default settings.

## Note

1. If you are using `Joomla` CMS then copy the folder `yourextensiondirectory/com.osseed.eventcalendar/joomla/EventCalendar` to `joomlarootdirectory/components/com_civicrm/views/` which will add menu item type for Event Calendar.

2. If you are using Wordpress CMS then copy the folder `yourextensiondirectory/com.osseed.eventcalendar/wordpress/wordpress-event-calendar` to `wordpressdirectory/wp-content/plugins/` and activate the plugin which will add CiviCRM frontend type for Event Calendar.

3. In `Joomla` for frontend calendar display you need to add menu items with menu item type as `Event calendar` & configure event calendar setting id (if you have multiple calendar) under it. If you pass empty `ID` it will display global calendar for frontend.

4. In `Wordpress` you need to add `Shortcode` under page like `[event_calendar component="event-calendar"]` for displaying calendar on front-end page view. Also you can add parameter for `id` specific the calendar `id` if multiple calendar settings are present.
