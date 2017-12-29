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
		
		if (!CRM_Core_Permission::check('access CiviEvent')) {
			return;
		}
		
		$config = (array)CRM_Core_BAO_Setting::getItem('Eventcalendar', 'events_event_types', null, array());
		//~ echo pre($config);
		
		if(isset($config['event_calendar_title']) && !empty($config['event_calendar_title'])) {
			CRM_Utils_System::setTitle(ts($config['event_calendar_title']));
		} else {
			CRM_Utils_System::setTitle(ts('Event Calendar'));
		} 

		// add assets
		CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/fullcalendar.js', 10);
		CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/civicrm_events.js', 11);
		CRM_Core_Resources::singleton()->addStyleFile('com.osseed.eventcalendar', 'css/fullcalendar.css');
		CRM_Core_Resources::singleton()->addStyleFile('com.osseed.eventcalendar', 'css/civicrm_events.css');

		$whereCondition = '';

		require_once 'CRM/Event/PseudoConstant.php';
		$eventTypes = CRM_Event_PseudoConstant::eventType();
				
		$eventTypesColors = array();
		$eventIds = array();
		 if (!empty($eventTypes)) {
			foreach ($eventTypes as $key => $eventType) {
				$eventname = 'eventtype_' . $key;
				if (empty($config[$eventname])) continue;
				$eventIds[] = $key;
				$colortextbox = 'eventcolor_' . $key;
				$eventTypesColors[$key] = array(
					'id' => $key,
					'color' => empty($config[$colortextbox]) ? '#3366CC' : '#'.$config[$colortextbox],
					'name' => $eventType,
				);
			}
		}
		//~ print_r($eventTypesColors);
		$this->assign('eventTypesColors',$eventTypesColors);

		if(!empty($eventIds)) {
			$whereCondition .= ' AND civicrm_event.event_type_id in ('.implode(",", $eventIds).')';
		} else {
			$whereCondition .= ' AND civicrm_event.event_type_id in (0)';
		}
		 
		$currentDate =  date("Y-m-d h:i:s", time());

		//Show/Hide Past Events.
		if(empty($config['show_past_event'])) $whereCondition .= " AND civicrm_event.start_date > '" .$currentDate . "'";

		// Show events according to number of next months.
		if(!empty($config['events_event_month']) && !empty($config['show_event_from_month'])) {
			$past = (new DateTime())->modify("-".$config['show_event_from_month']." month")->format('Y-m-d H:i:s');
			$future = (new DateTime())->modify("+".$config['show_event_from_month']." month")->format('Y-m-d H:i:s');
			$whereCondition .= " AND civicrm_event.start_date BETWEEN '" .$past . "' AND '" .$future . "' ";
		}

		//Sho/Hide Public Events.
		if(!empty($config['event_is_public'])) $whereCondition .= " AND civicrm_event.is_public = 1";
		
		$query = "
			SELECT `id`, `title`, `start_date` as start, `end_date`  as end ,`event_type_id` as event_type 
			FROM `civicrm_event` 
			WHERE civicrm_event.is_active = 1 AND civicrm_event.is_template = 0
		";

		$query .= $whereCondition; 
		// echo pre($query);
		
		$events['events'] = array();

		//~ echo $query;
		$dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );

		$eventCalendarParams = array ('title' => 'title', 'start' => 'start', 'url' => 'url');
		if(!empty($config['show_end_date'])) $eventCalendarParams['end'] = 'end';
		
		//~ echo pre($eventCalendarParams); exit;

		while ($dao->fetch()) {
			
			//~ echo pre($dao);
			if ( !$dao->title ) continue;
			
			$eventData = array( 'allDay' => true, );
			
			if( !empty($dao->start) ) $dao->start = date("Y-m-d\TH:i:s", strtotime($dao->start) );
			if( !empty($dao->end) ) $dao->end = date("Y-m-d\TH:i:s", strtotime($dao->end) );
			if( isset($dao->start) && isset($dao->end)) $eventData['allDay'] = false;
			$eventData['event_id'] = $dao->id;
			$dao->url =   CRM_Utils_System::url( 'civicrm/event/info', 'id='.$dao->id );
			if(!empty($eventTypesColors[$dao->event_type])) $eventData['backgroundColor'] = $eventTypesColors[$dao->event_type]['color'].'';
			foreach ($eventCalendarParams as  $k) $eventData[$k] = $dao->$k; 
			$eventData['url'] = html_entity_decode($eventData['url']);
			$events['events'][] = $eventData;
		}
		// echo pre($events['events']);

		$events['header']['left'] = 'prev,next today';
		$events['header']['center'] = 'title';
		$views = array_intersect(array_keys(EventCalendarDefines::$fullcalendarviews), empty($_REQUEST['calendar_views']) ? array() : $_REQUEST['calendar_views']);
		if (empty($views)) {
			foreach (EventCalendarDefines::$fullcalendarviews as $view => $viewName) {
				if (empty($config['calendar_views_'.$view])) continue;
				$views[] = $view;
			}
		}
		if (empty($views)) $views = array('month','basicWeek','basicDay');
		$events['header']['right'] = implode(',',$views);

		$events['defaultView'] = reset($views);
		$requestDefaultView = CRM_Utils_Request::retrieve('calendar_defaultView','String');
		if (!empty($requestDefaultView)) {
			$events['defaultView'] = $requestDefaultView;
		} elseif (!empty($config['calendar_defaultView'])) {
			$events['defaultView'] = $config['calendar_defaultView'];
		}
		$events['defaultView'] = !empty($events['defaultView'])&&in_array($events['defaultView'],$views)?$events['defaultView']:reset($views);

		//send Events array to calendar.
		$this->assign('civicrm_events', json_encode($events));
		parent::run();
	}
}
