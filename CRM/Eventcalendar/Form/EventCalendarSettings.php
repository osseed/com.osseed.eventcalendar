<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Eventcalendar_Form_EventCalendarSettings extends CRM_Core_Form {
  private $_settingFilter = array('group' => 'eventcalendar');
  private $_submittedValues = array();
  private $_settings = array();

  function buildQuickForm() {
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/jscolor.js');
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/eventcalendar.js');

    $settings = $this->getFormSettings();
    $descriptions = array();
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        $add = 'add' . $setting['quick_form_type'];

        if ($name != 'eventcalendar_event_types') {
          if ($add == 'addElement') {
            $this->$add($setting['html_type'], $name, ts($setting['title']),
              CRM_Utils_Array::value('html_attributes', $setting, array()));
          }
          else {
            $this->$add($name, ts($setting['title']));
          }
          $descriptions[$name] = $setting['description'];
        }
        else {
          //special handling for event types; we construct these dynamically
          //and store as json
          $eventTypes = CRM_Event_PseudoConstant::eventType();
          foreach ($eventTypes as $id => $type) {
            $this->addElement('checkbox', "eventtype_{$id}", $type, NULL,
              array('onclick' => "showhidecolorbox('{$id}')", 'id' => "event_{$id}"));
            $this->addElement('text', "eventcolor_{$id}", "Color",
              array(
                'onchange' => "updatecolor('eventcolor_{$id}', this.value);",
                'class' => 'color',
                'id' => "eventcolorid_{$id}",
                //'value'=> 'EXISTING VALUE?',
              ));
          }

          $this->assign('eventTypes', $eventTypes);
        }
      }
    }
    $this->assign('descriptions', $descriptions);

    $this->addButtons(array(
      array (
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      )
    ));
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function getFormSettings() {
    if (empty($this->_settings)) {
      $settings = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
    }

    //Civi::log()->debug('getFormSettings', array('settings' => $settings));
    return $settings['values'];
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function saveSettings() {
    $settings = $this->getFormSettings();
    //Civi::log()->debug('saveSettings', array('_submitValues' => $this->_submitValues));

    //we extract eventtype_ and eventcolor_ settings and store as json
    $eventTypes = array();
    foreach ($this->_submittedValues as $f => $v) {
      if (strpos($f, 'eventtype_') !== FALSE) {
        $id = str_replace('eventtype_', '', $f);
        $eventTypes[] = array(
          'id' => $id,
          'color' => $this->_submittedValues["eventcolor_{$id}"],
        );
      }
    }
    $this->_submittedValues['eventcalendar_event_types'] = json_encode($eventTypes);

    foreach ($settings as $settingName => $settingDate) {
      if ($settingDate['html_type'] === 'checkbox' &&
        empty($this->_submittedValues[$settingName])
      ) {
        $this->_submittedValues[$settingName] = 0;
      }
    }

    $values = array_intersect_key($this->_submittedValues, $settings);
    //Civi::log()->debug('saveSettings', array('values' => $values));
    civicrm_api3('setting', 'create', $values);
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  function setDefaultValues() {
    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $defaults = array();
    $domainID = CRM_Core_Config::domainID();
    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
      if ($name == 'eventcalendar_event_types') {
        // set event type color
        foreach(json_decode($value, true) as $eventType) {
          $defaults['eventtype_'.$eventType['id']] = 1;
          $defaults['eventcolor_'.$eventType['id']] = $eventType['color'];
        }
      }
    }
    return $defaults;
  }
}
