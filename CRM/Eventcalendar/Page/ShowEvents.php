<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';

class CRM_Eventcalendar_Page_ShowEvents extends CRM_Core_Page {

  function run() {
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/fullcalendar.js');
    CRM_Core_Resources::singleton()->addStyleFile('com.osseed.eventcalendar', 'css/civicrm_events.css');
    CRM_Core_Resources::singleton()->addStyleFile('com.osseed.eventcalendar', 'css/fullcalendar.css');

    $config = CRM_Core_Config::singleton();

    //get settings
    $settings = $this->_eventCalendar_getSettings();

    //set title from settings; allow empty value so we don't duplicate titles
    CRM_Utils_System::setTitle(ts($settings['calendar_title']));

    $whereCondition = '';
    $eventTypes = $settings['event_types'];

    if(!empty($eventTypes)) {
      $eventTypesList = implode(',', array_keys($eventTypes));
      $whereCondition .= " AND civicrm_event.event_type_id in ({$eventTypesList})";
    }
    else {
      $whereCondition .= ' AND civicrm_event.event_type_id in (0)';
    }

    //Show/Hide Past Events
    $currentDate = date("Y-m-d h:i:s", time());
    if (empty($settings['event_past'])) {
      $whereCondition .= " AND civicrm_event.start_date > '" .$currentDate . "'";
    }

    // Show events according to number of next months
    if(!empty($settings['event_from_month'])) {
      $monthEvents = $settings['event_from_month'];
      $monthEventsDate = date("Y-m-d h:i:s",
        strtotime(date("Y-m-d h:i:s", strtotime($currentDate))."+".$monthEvents." month"));
      $whereCondition .= " AND civicrm_event.start_date < '" .$monthEventsDate . "'";
    }

    //Show/Hide Public Events
    if(!empty($settings['event_is_public'])) {
      $whereCondition .= " AND civicrm_event.is_public = 1";
    }

    $query = "
      SELECT `id`, `title`, `start_date` start, `end_date` end ,`event_type_id` event_type
      FROM `civicrm_event`
      WHERE civicrm_event.is_active = 1
        AND civicrm_event.is_template = 0
    ";

    $query .= $whereCondition;
    $events['events'] = array();

    $dao = CRM_Core_DAO::executeQuery($query);
    $eventCalendarParams = array ('title' => 'title', 'start' => 'start', 'url' => 'url');

    if(!empty($settings['event_end_date'])) {
      $eventCalendarParams['end'] = 'end';
    }

    while ($dao->fetch()) {
      $eventData = array();

      $dao->url = html_entity_decode(CRM_Utils_System::url('civicrm/event/info', 'id='.$dao->id));
      foreach ($eventCalendarParams as $k) {
        $eventData[$k] = $dao->$k;

        if(!empty($eventTypes)) {
          $eventData['backgroundColor'] = "#{$eventTypes[$dao->event_type]}";
        }
      }
      $events['events'][] = $eventData;
    }
    //Civi::log()->debug('EventCalendar run', array('events' => $events));

    $events['header']['left'] = 'prev,next today';
    $events['header']['center'] = 'title';
    $events['header']['right'] = 'month,basicWeek,basicDay';

    //send Events array to calendar.
    $this->assign('civicrm_events', json_encode($events));
    parent::run();
  }

  /*
   * retrieve and reconstruct extension settings
   */
  function _eventCalendar_getSettings() {
    $settings = array(
      'calendar_title' => Civi::settings()->get('eventcalendar_calendar_title'),
      'event_past' => Civi::settings()->get('eventcalendar_event_past'),
      'event_end_date' => Civi::settings()->get('eventcalendar_event_end_date'),
      'event_is_public' => Civi::settings()->get('eventcalendar_event_is_public'),
      'event_month' => Civi::settings()->get('eventcalendar_event_month'),
      'event_from_month' => Civi::settings()->get('eventcalendar_event_from_month'),
    );

    $eventTypes = Civi::settings()->get('eventcalendar_event_types');
    $eventTypes = json_decode($eventTypes);
    foreach ($eventTypes as $eventType) {
      $settings['event_types'][$eventType->id] = $eventType->color;
    }

    /*Civi::log()->debug('_eventCalendar_getSettings', array(
      'eventTypes' => $eventTypes,
      'settings' => $settings,
    ));*/

    return $settings;
  }
}
