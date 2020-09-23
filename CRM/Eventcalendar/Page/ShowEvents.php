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
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/moment.js',5);
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/fullcalendar.js',10);
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

    //Check recurringEvent is available or not.
    if($settings['recurring_event'] == 1){
      $query = "
        SELECT `title`, `start_date` start, `end_date` end ,`event_type_id` event_type
        FROM `civicrm_event` LEFT JOIN civicrm_recurring_entity ON civicrm_recurring_entity.entity_id = civicrm_event.id
        WHERE civicrm_recurring_entity.entity_table='civicrm_event'
          AND civicrm_event.is_active = 1
          AND civicrm_event.is_template = 0
      ";
    }
    else{
      $query = "
        SELECT `id`, `title`, `start_date` start, `end_date` end ,`event_type_id` event_type
        FROM `civicrm_event`
        WHERE civicrm_event.is_active = 1
          AND civicrm_event.is_template = 0
      ";
    }

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
          $eventData['textColor'] = $this->_getContrastTextColor($eventData['backgroundColor']);
          $eventData['eventType'] = $civieventTypesList[$dao->event_type];
        }
      }
      $events['timeDisplay'] = $settings['event_time'];
      $events['isfilter'] = $settings['event_event_type_filter'];
      $events['events'][] = $eventData;
      $eventTypesFilter[$dao->event_type] = $civieventTypesList[$dao->event_type];

    }

    if(!empty($settings['event_event_type_filter'])) {
      $events['eventTypes'][]  = $eventTypesFilter;
      $this->assign('eventTypes', $eventTypesFilter);
    }
    //Civi::log()->debug('EventCalendar run', array('events' => $events));
    $events['displayEventEnd'] = 'true';

    //Check weekBegin settings from calendar configuration
    $weekBegins = '';
    if($settings['week_begins_from_day'] == 1) {
      //Get existing setting for weekday from civicrm start & set into event calendar.
      $weekBegins = Civi::settings()->get('weekBegins');
    }
    $weekBegins = $weekBegins ? $weekBegins : 0;
    $this->assign('weekBeginDay', $weekBegins);

    //Send Events array to calendar.
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
      'event_time' => Civi::settings()->get('eventcalendar_event_time'),
      'event_event_type_filter' =>  Civi::settings()->get('eventcalendar_event_type_filter'),
      'week_begins_from_day' =>  Civi::Settings()->get('eventcalendar_week_begins_from_day'),
      'recurring_event' => Civi::Settings()->get('eventcalendar_recurring_event')
    );

    $eventTypes = Civi::settings()->get('eventcalendar_event_types');
    $eventTypes = json_decode($eventTypes);
    if (!empty($eventTypes)) {
      foreach ($eventTypes as $eventType) {
        $settings['event_types'][$eventType->id] = $eventType->color;
      }
    }

    /*Civi::log()->debug('_eventCalendar_getSettings', array(
      'eventTypes' => $eventTypes,
      'settings' => $settings,
    ));*/

    return $settings;
  }

  /*
   * Return contrast color on the basis the hex color passed
   *
   * Referred from https://stackoverflow.com/questions/1331591
   */
  function _getContrastTextColor($hexColor){
    // hexColor RGB
    $R1 = hexdec(substr($hexColor, 1, 2));
    $G1 = hexdec(substr($hexColor, 3, 2));
    $B1 = hexdec(substr($hexColor, 5, 2));

    // Black RGB
    $blackColor = "#000000";
    $R2BlackColor = hexdec(substr($blackColor, 1, 2));
    $G2BlackColor = hexdec(substr($blackColor, 3, 2));
    $B2BlackColor = hexdec(substr($blackColor, 5, 2));

     // Calc contrast ratio
     $L1 = 0.2126 * pow($R1 / 255, 2.2) +
           0.7152 * pow($G1 / 255, 2.2) +
           0.0722 * pow($B1 / 255, 2.2);

    $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
          0.7152 * pow($G2BlackColor / 255, 2.2) +
          0.0722 * pow($B2BlackColor / 255, 2.2);

    $contrastRatio = 0;
    if ($L1 > $L2) {
        $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
    } else {
        $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
    }

    // If contrast is more than 5, return black color
    if ($contrastRatio > 5) {
        return '#000000';
    } else {
        // if not, return white color.
        return '#FFFFFF';
    }
  }
}
