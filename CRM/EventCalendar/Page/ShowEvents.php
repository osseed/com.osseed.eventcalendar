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

class CRM_EventCalendar_Page_ShowEvents extends CRM_Core_Page {

  public function run() {
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/moment.js', 5);
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/fullcalendar.js', 10);
    CRM_Core_Resources::singleton()->addStyleFile('com.osseed.eventcalendar', 'css/civicrm_events.css');
    CRM_Core_Resources::singleton()->addStyleFile('com.osseed.eventcalendar', 'css/fullcalendar.css');

    $eventTypesFilter = array();
    $civieventTypesList = CRM_Event_PseudoConstant::eventType();

    $config = CRM_Core_Config::singleton();

    //get settings
    $settings = $this->_eventCalendar_getSettings();

    //set title from settings; allow empty value so we don't duplicate titles
    CRM_Utils_System::setTitle(ts($settings['calendar_title']));

    $whereCondition = '';
    if (array_key_exists("event_types", $settings)) {
      $eventTypes = $settings['event_types'];
    }

    if (!empty($eventTypes)) {
      $eventTypesList = implode(',', array_keys($eventTypes));
      $whereCondition .= " AND civicrm_event.event_type_id in ({$eventTypesList})";
    }
    else {
      $whereCondition .= ' AND civicrm_event.event_type_id in (0)';
    }

    //Show/Hide Past Events
    $currentDate = date("Y-m-d h:i:s", time());
    if (empty($settings['event_past'])) {
      $whereCondition .= " AND civicrm_event.start_date > '" . $currentDate . "'";
    }

    // Show events according to number of next months
    if (!empty($settings['event_from_month'])) {
      $monthEvents = $settings['event_from_month'];
      $monthEventsDate = date("Y-m-d h:i:s",
        strtotime(date("Y-m-d h:i:s", strtotime($currentDate)) . "+" . $monthEvents . " month"));
      $whereCondition .= " AND civicrm_event.start_date < '" . $monthEventsDate . "'";
    }

    //Show/Hide Public Events
    if (!empty($settings['event_is_public'])) {
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
    $eventCalendarParams = array('title' => 'title', 'start' => 'start', 'url' => 'url');

    if (!empty($settings['event_end_date'])) {
      $eventCalendarParams['end'] = 'end';
    }

    while ($dao->fetch()) {
      $eventData = array();

      $dao->url = html_entity_decode(CRM_Utils_System::url('civicrm/event/info', 'id=' . $dao->id));
      foreach ($eventCalendarParams as $k) {
        $eventData[$k] = $dao->$k;
        if (!empty($eventTypes)) {
          $eventData['backgroundColor'] = "#{$eventTypes[$dao->event_type]}";
          $eventData['eventType'] = $civieventTypesList[$dao->event_type];
        }
      }
      $events['timeDisplay'] = $settings['event_time'];
      $events['isfilter'] = $settings['event_event_type_filter'];
      $events['events'][] = $eventData;
      $eventTypesFilter[$dao->event_type] = $civieventTypesList[$dao->event_type];

    }

    if (!empty($settings['event_event_type_filter'])) {
      $events['eventTypes'][]  = $eventTypesFilter;
      $this->assign('eventTypes', $eventTypesFilter);
    }

    $events['header']['left'] = 'prev,next today';
    $events['header']['center'] = 'title';
    $events['header']['right'] = 'month,basicWeek,basicDay';
    $events['displayEventEnd'] = 'true';

    //send Events array to calendar.
    $this->assign('civicrm_events', json_encode($events));
    parent::run();
  }

  /**
   * retrieve and reconstruct extension settings
   */
  public function _eventCalendar_getSettings() {
    $settings = array();
    $calendarId = $_GET['id'];

    if ($calendarId) {
      $sql = "SELECT * FROM civicrm_event_calendar WHERE `id` = {$calendarId};";
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        $settings['calendar_title'] = $dao->calendar_title;
        $settings['event_past'] = $dao->show_past_events;
        $settings['event_end_date'] = $dao->show_end_date;
        $settings['event_is_public'] = $dao->show_public_events;
        $settings['event_month'] = $dao->events_by_month;
        $settings['event_from_month'] = $dao->events_from_month;
        $settings['event_time'] = $dao->event_timings;
        $settings['event_event_type_filter'] = $dao->event_type_filters;
      }
      $sql = "SELECT * FROM civicrm_event_calendar_event_type WHERE `event_calendar_id` = {$calendarId};";
      $dao = CRM_Core_DAO::executeQuery($sql);
      $eventTypes = array();
      while ($dao->fetch()) {
        $eventTypes[] = $dao->toArray();
      }
    }

    if (!empty($eventTypes)) {
      foreach ($eventTypes as $eventType) {
        $settings['event_types'][$eventType['event_type']] = $eventType['event_color'];
      }
    }
    return $settings;
  }

}
