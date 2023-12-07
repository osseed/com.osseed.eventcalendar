<?php

require_once 'eventcalendar.civix.php';
use CRM_EventCalendar_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function eventcalendar_civicrm_config(&$config) {
  _eventcalendar_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function eventcalendar_civicrm_install() {
  _eventcalendar_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function eventcalendar_civicrm_uninstall() {
  _eventcalendar_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function eventcalendar_civicrm_enable() {
  _eventcalendar_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function eventcalendar_civicrm_disable() {
  _eventcalendar_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function eventcalendar_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _eventcalendar_civix_civicrm_upgrade($op, $queue);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

*/

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function eventcalendar_civicrm_navigationMenu(&$menu) {
  _eventcalendar_civix_insert_navigation_menu($menu, 'Events', array(
    'label' => ts('Show Events Calendar', array('domain' => 'com.osseed.eventcalendar')),
    'name' => 'Show Events Calendar',
    'url' => 'civicrm/showevents',
    'permission' => 'view event info',
    'operator' => 'AND',
    'separator' => 0,
  ));
  _eventcalendar_civix_navigationMenu($menu);

  _eventcalendar_civix_insert_navigation_menu($menu, 'Administer/CiviEvent', array(
    'label' => ts('Event Calendar Settings', array('domain' => 'com.osseed.eventcalendar')),
    'name' => 'Event Calendar Settings',
    'url' => 'civicrm/admin/event-calendar',
    'permission' => 'administer CiviCRM',
    'operator' => 'AND',
    'separator' => 0,
  ));
  _eventcalendar_civix_navigationMenu($menu);
}

// /**
//  * Implements hook_civicrm_postInstall().
//  *
//  * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
//  */
// function eventcalendar_civicrm_postInstall() {
//   _eventcalendar_civix_civicrm_postInstall();
// }

// /**
//  * Implements hook_civicrm_entityTypes().
//  *
//  * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
//  */
// function eventcalendar_civicrm_entityTypes(&$entityTypes) {
//   _eventcalendar_civix_civicrm_entityTypes($entityTypes);
// }
