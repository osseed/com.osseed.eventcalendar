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
  // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
  $config = CRM_Core_Config::singleton();
  if(NULL != Civi::settings()->get('civicrm_event_calendar_title') ) {
    CRM_Utils_System::setTitle(ts(Civi::settings()->get('civicrm_event_calendar_title')));
  } else {
    CRM_Utils_System::setTitle(ts('Event Calendar'));
  } 
  $whereCondition = '';
  $eventTypes = array(); 
  $colorevents = array(); 
    if(NULL != Civi::settings()->get('civicrm_events_event_types') ) {
      $eventTypes = Civi::settings()->get('civicrm_events_event_types');
      $eventTypes = array_flip($eventTypes);
    } else {
       require_once 'CRM/Event/PseudoConstant.php';
       $all_events = CRM_Event_PseudoConstant::eventType();
       $eventTypes = array_flip($all_events); 
      }
    $colorevents = array_flip($eventTypes);
    if(!empty($eventTypes)) {
     $whereCondition .= ' AND civicrm_event.event_type_id in (' . implode(",", $eventTypes) . ')';
    }

    //Show/Hide Past Events.
    $pastEvents = '';
    $currentDate =  date("Y-m-d h:i:s", time());
    if(NULL != Civi::settings()->get('civicrm_events_event_past')) {
      $pastEvents = Civi::settings()->get('civicrm_events_event_past');
    }
    if(empty($pastEvents)) {
      $whereCondition .= " AND civicrm_event.start_date > '" .$currentDate . "'";
    }
    
    // Show events according to number of next months.
    $monthEvents = '';
    if(NULL != Civi::settings()->get('civicrm_events_event_months')) {
      $monthEvents = Civi::settings()->get('civicrm_events_event_months');
    }
    if(!empty($monthEvents)) {
      $monthEventsDate = date("Y-m-d h:i:s",strtotime(date("Y-m-d h:i:s", strtotime($currentDate)) . "+".$monthEvents." month"));
      $whereCondition .= " AND civicrm_event.start_date < '" .$monthEventsDate . "'";
    }
    
    //Sho/Hide Public Events.
    $ispublicEvents = '';
    if(NULL != Civi::settings()->get('civicrm_events_event_is_public')) {
      $ispublicEvents = Civi::settings()->get('civicrm_events_event_is_public');
    }
    if(!empty($ispublicEvents)) {
      $whereCondition .= " AND civicrm_event.is_public = " .$ispublicEvents. "";
    }

    $query = "SELECT `id`, `title`, `start_date` as start, `end_date`  as end ,`event_type_id` as event_type FROM `civicrm_event` WHERE civicrm_event.is_active = 1 AND civicrm_event.is_template = 0";
  
    $query .= $whereCondition; 
    $events['events'] = array();
    $event_links_active = 0;
    $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
    $eventCalendarParams = array ('title' => 'title', 'start' => 'start', 'url' => 'url');
    if(NULL != Civi::settings()->get('civicrm_events_event_end_date')) {
      $eventCalendarParams['end'] = 'end';
    }
    while ( $dao->fetch( ) ) {
      if ( $dao->title ) { 
        if( isset($startDate) ) {
          $startDate = date("Y,n,j", strtotime( $dao->start_date ) );
        }
        if( isset($endDate) ) {
          $endDate = date("Y,n,j", strtotime( $dao->end_date ) );
        }
          $dao->url =   CRM_Utils_System::url( 'civicrm/event/info', 'id=' . $dao->id );
      }
      $eventData = array();
      foreach ($eventCalendarParams as  $k) {
        $eventData[$k] = $dao->$k; 
        if (NULL != Civi::settings()->get($colorevents[$dao->event_type])) { 
          $eventData['backgroundColor'] = '#'.Civi::settings()->get($colorevents[$dao->event_type]).'';
        }
       }
      $eventData['url'] = html_entity_decode($eventData['url']);
      //build mousehover data.
      $urlParams = '&action=update&id=' . $dao->id;
      if (CRM_Core_Permission::check('edit all events')){
        $event_links_active = 1;
        $event_links['manage_event'] =  CRM_Utils_System::href('Manage Event', 'civicrm/event/manage/settings', 'action=upadate&id=' . $dao->id);
        $event_links['event_location'] =  CRM_Utils_System::href('Change Event Location', 'civicrm/event/manage/location', $urlParams);
        $event_links['event_fee'] =  CRM_Utils_System::href('Change Fee', 'civicrm/event/manage/fee', $urlParams);
        $event_links['event_registration'] = CRM_Utils_System::href('Online Registration' ,'civicrm/event/manage/registration', $urlParams);
        $event_links['event_reminders'] =  CRM_Utils_System::href('Schedule Reminders', 'civicrm/event/manage/reminder','reset=1&action=browse&setTab=1&id=' .$dao->id);
        $event_links['event_reoccuring_schedule'] = CRM_Utils_System::href('Schedule Reoccurring Event', 'civicrm/event/manage/repeat', $urlParams);
        $event_links['event_register_participant'] = CRM_Utils_System::href('Register Participant', 'civicrm/participant/add', 'reset=1&action=add&context=standalone&eid=' .$dao->id);
        $event_links['event_tell_friend'] = CRM_Utils_System::href('Manage Tell a Friend', 'civicrm/event/manage/friend', $urlParams);
        $event_links['event_campaigns'] =  CRM_Utils_System::href( 'Manage Personal Campaigns', 'civicrm/event/manage/pcp',$urlParams );
        $events['event_details_links'][$dao->id] = '
          <div class="event-info-details fc-event" style="background: #ffffff">
            <div>'.$event_links['manage_event'].'</div>
            <div>'.$event_links['event_location'].'</div>
            <div>'.$event_links['event_fee'].'</div>
            <div>'.$event_links['event_registration'].'</div>
            <div>'.$event_links['event_reminders'].'</div>
            <div>'.$event_links['event_reoccuring_schedule'].'</div>
            <div>'.$event_links['event_register_participant'].'</div>
            <div>'.$event_links['event_tell_friend'].'</div>
            <div>'.$event_links['event_campaigns'].'</div>
        </div>';

      }
       $events['events'][] = $eventData;
    }
    $events['header']['left'] = 'prev,next today';
    $events['header']['center'] = 'title';
    $events['header']['right'] = 'month,basicWeek,basicDay';
    //send Events array to calendar.
    $this->assign('civicrm_events', json_encode($events));
    $this->assign('event_links_active', $event_links_active);
    parent::run();
  }
}
