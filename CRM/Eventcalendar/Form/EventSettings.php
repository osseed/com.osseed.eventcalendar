<?php

require_once 'CRM/Admin/Form.php';

/**
 * This class generates form for Calendar Event Settings.
 * 
 */
class CRM_Eventcalendar_Form_EventSettings extends CRM_Admin_Form {

	protected $_roles = array(); 
	protected $_types = array(); 
		  
	function preProcess () {
		   
		parent::preProcess( );
		$session = CRM_Core_Session::singleton();
		$url = CRM_Utils_System::url('civicrm/eventcalendarsettings');
		$session->pushUserContext( $url );
	}


	function setDefaultValues() {
		
		$defaults = array_merge(parent::setDefaultValues(),(array)CRM_Core_BAO_Setting::getItem('Eventcalendar', 'events_event_types', null, array()));
		
		require_once 'CRM/Event/PseudoConstant.php';
		$event_type = CRM_Event_PseudoConstant::eventType();
				
		if(!empty($event_type)) {
			foreach($event_type as $key => $val) {
				$eventtype = 'eventtype_' . $key;
				if(empty($defaults[$eventtype])) $defaults[$eventtype] = 0;
				$eventcolor = 'eventcolor_' . $key;
				if(empty($defaults[$eventcolor])) $defaults[$eventcolor] = '3366CC';				
			}
		} 

		if (empty($defaults['event_calendar_title'])) $defaults['event_calendar_title'] = 'Event Calendar';
		if (!isset($defaults['show_end_date'])) $defaults['show_end_date'] = 1;
		if (!isset($defaults['show_past_event'])) $defaults['show_past_event'] = 1;
		if (!isset($defaults['event_is_public'])) $defaults['event_is_public'] = 1;
		if (!isset($defaults['events_event_month'])) $defaults['events_event_month'] = 0;
		if (!isset($defaults['show_event_from_month'])) $defaults['show_event_from_month'] = '';
		
		$viewset = false;
		foreach (EventCalendarDefines::$fullcalendarviews as $view => $viewName) {
			if(!isset($defaults['calendar_views_'.$view])) {
				$defaults['calendar_views_'.$view] = 0;
			} else {
				$viewset = true;
			}
		}
		if (!$viewset) $defaults['calendar_views_month'] = $defaults['calendar_views_basicWeek'] = $defaults['calendar_views_basicDay'] = 1;

		// set defaults if they havent been already set
		CRM_Core_BAO_Setting::setItem($defaults, 'Eventcalendar', 'events_event_types');

		//~ echo '<pre>'.print_r($defaults,true).'</pre>';
		return $defaults;
	}


	/**
	* Function to build the form
	*
	* @return None
	* @access public
	*/
	public function buildQuickForm( ){
				
		parent::buildQuickForm();
		
		$config =  CRM_Core_BAO_Setting::getItem('Eventcalendar', 'events_event_types', null, new stdClass);
		$this->add('text', 'show_event_from_month', ts('Show Events from how many months from current month '), array('size' => 50));
		$this->add('text', 'event_calendar_title', ts('Calendar title'), array('size' => 50));
		$this->addElement('checkbox', 'show_end_date', ts('Show End Date'));
		$this->addElement('checkbox', 'event_is_public', ts('Is Public'));
		$this->addElement('checkbox', 'events_event_month', ts('Events By Month'));
		$this->addElement('checkbox', 'show_past_event', ts('Show Past Events'));
		
		require_once 'CRM/Event/PseudoConstant.php';
		
		$event_type = CRM_Event_PseudoConstant::eventType();
		foreach($event_type as $key => $val) {
			$eventname = 'eventtype_' . $key;
			$colortextbox = 'eventcolor_' . $key;
			$this->addElement('checkbox', $eventname, ts($val) , NULL , array('onclick' => "showhidecolorbox('$key')"));
			$this->addElement('text', $colortextbox,'',array('onchange' => "updatecolor('$colortextbox',this.value);", 'class'=>'color'));
		}
		
		$this->assign('event_type', $event_type);
		$this->assign('fullcalendarviews', EventCalendarDefines::$fullcalendarviews);
		foreach (EventCalendarDefines::$fullcalendarviews as $view => $viewName) {
			$this->addElement('checkbox', 'calendar_views_'.$view, ts($viewName));	  
		}
	}

	/**
	* Function to process the form
	*
	* @access public
	* @return None
	*/
	public function postProcess() {    
	
		$allParams = $this->controller->exportValues($this->_name);
		$defaults = $this->setDefaultValues();
		$params = array();

		if (!empty($defaults)) {
			foreach ($defaults as $key => $val) {
				$params[$key] = isset($allParams[$key]) ? $allParams[$key] : 0;
			}
		}
		
		//~ echo '<pre>'.print_r($params,true).'</pre>'; exit;
		
		CRM_Core_BAO_Setting::setItem($params, 'Eventcalendar', 'events_event_types');
		CRM_Core_Session::setStatus(" ", ts('The value has been saved.'), "success" );
	 }
}
