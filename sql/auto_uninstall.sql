DROP TABLE IF EXISTS `civicrm_event_calendar`;

SET FOREIGN_KEY_CHECKS=1;

DELETE FROM civicrm_navigation WHERE name = 'Show Events Calendar';
DELETE FROM civicrm_navigation WHERE name = 'Event Calendar Settings';
