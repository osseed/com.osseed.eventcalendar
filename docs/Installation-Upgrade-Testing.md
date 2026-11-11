# Event Calendar 4.7 – Installation and Upgrade Testing

## Test Environment

- Platform: WordPress
- PHP: 8.3
- Database: MariaDB 11.8
- DDEV: 1.25.4
- Extension: Event Calendar 4.7

Two separate DDEV environments were created. Installation,
extension replacement, upgrade execution, and database verification
were performed using terminal commands and the CiviCRM interface.

## Scenario 1: Fresh Installation

Environment: wordpress-eventcalendar-fresh

Initial condition:
- New WordPress site.
- Event Calendar extension was not installed.

Test procedure:
1. Created a separate DDEV environment.
2. Installed the latest Event Calendar 4.7 extension.
3. Verified that the extension was registered and active.
4. Checked that the calendar database table was created.
5. Verified calendar functionality.

Database verification:
- Extension: com.osseed.eventcalendar
- Schema version: 1005
- Active: 1
- Table: civicrm_event_calendar

Result: PASS

## Scenario 2: Existing Extension Upgrade

Environment: wordpress-eventcalendar-upgrade

Initial condition:
- Existing Event Calendar version: 4.5.

Test procedure:
1. Created a separate DDEV environment.
2. Started with the older Event Calendar 4.5 installation.
3. Replaced the extension files with version 4.7.
4. Verified that the extension upgrade notification appeared.
5. Executed the extension database upgrade.
6. Verified the database changes.
7. Created a test calendar and checked functionality.

Database verification:
- Extension: com.osseed.eventcalendar
- Final version: 4.7
- Schema version: 1005
- Active: 1
- Table: civicrm_event_calendar
- Column: is_default

Result: PASS

## Overall Result

Both installation scenarios passed:

1. Fresh installation of Event Calendar 4.7.
2. Upgrade from Event Calendar 4.5 to 4.7.

Both environments were tested separately to avoid affecting
the original testing site.
