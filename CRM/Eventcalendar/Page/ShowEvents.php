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
  if(isset($config->civicrm_event_calendar_title) && !empty($config->civicrm_event_calendar_title)) {
    CRM_Utils_System::setTitle(ts($config->civicrm_event_calendar_title));
  } else {
    CRM_Utils_System::setTitle(ts('Event Calendar'));
  } 
  $whereCondition = '';
  $eventTypes = array(); 
  $colorevents = array(); 
    if(isset($config->civicrm_events_event_types)) {
      $eventTypes = $config->civicrm_events_event_types;
      $eventTypes = array_flip($eventTypes);
    } else {
       require_once 'CRM/Event/PseudoConstant.php';
       $all_events = CRM_Event_PseudoConstant::eventType();
       $eventTypes = array_flip($all_events); 
      }
    $colorevents = array_flip($eventTypes);
    if(!empty($eventTypes)) {
     $whereCondition .= ' AND civicrm_event.event_type_id in (' . implode(",", $eventTypes) . ')';
    } else {
     $whereCondition .= ' AND civicrm_event.event_type_id in (0)';
    }
     
    
    //Show/Hide Past Events.
    $pastEvents = '';  
    $currentDate =  date("Y-m-d h:i:s", time());
    if(isset($config->civicrm_events_event_past)) {
      $pastEvents = $config->civicrm_events_event_past;
    }
    if(empty($pastEvents)) {
      $whereCondition .= " AND civicrm_event.start_date > '" .$currentDate . "'";
    }
    
    // Show events according to number of next months.
    $monthEvents = '';
    if(isset($config->civicrm_events_event_months)) {
      $monthEvents = $config->civicrm_events_event_months;
    }
    if(!empty($monthEvents)) {
      $monthEventsDate = date("Y-m-d h:i:s",strtotime(date("Y-m-d h:i:s", strtotime($currentDate)) . "+".$monthEvents." month"));
      $whereCondition .= " AND civicrm_event.start_date < '" .$monthEventsDate . "'";
    }
    
    //Sho/Hide Public Events.
    $ispublicEvents = '';
    if(isset($config->civicrm_events_event_is_public)) {
      $ispublicEvents = $config->civicrm_events_event_is_public;
    }
    if(!empty($ispublicEvents)) {
      $whereCondition .= " AND civicrm_event.is_public = " .$ispublicEvents. "";
    }

    $query = "SELECT `id`, `title`, `start_date` as start, `end_date`  as end ,`event_type_id` as event_type FROM `civicrm_event` WHERE civicrm_event.is_active = 1 AND civicrm_event.is_template = 0";
  
    $query .= $whereCondition; 
    $events['events'] = array();
   
    $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
    $eventCalendarParams = array ('title' => 'title', 'start' => 'start', 'url' => 'url');
    if(isset($config->civicrm_events_event_end_date) && !empty($config->civicrm_events_event_end_date)) {
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
        if(!empty($colorevents) && isset($config->$colorevents[$dao->event_type])) {     
          $eventData['backgroundColor'] = '#'.$config->$colorevents[$dao->event_type].'';
        }
       }
       $eventData['url'] = html_entity_decode($eventData['url']);
       $events['events'][] = $eventData;
    }
    $events['header']['left'] = 'prev,next today';
    $events['header']['center'] = 'title';
    $events['header']['right'] = 'month,basicWeek,basicDay';
    //send Events array to calendar.
    $this->assign('civicrm_events', json_encode($events));
    parent::run();
  }
}
