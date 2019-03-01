<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Eventcalendar_Form_EventCalendarSettings extends CRM_Core_Form {
  //private $_settingFilter = array('group' => 'eventcalendar');
  private $_submittedValues = array();
  private $_settings = array();

  public function buildQuickForm() {
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/jscolor.js');
    CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/eventcalendar.js');

    //This will get used at the bottom of the form for a delete option
    $settings = $this->getFormSettings();
    //@todo get descriptions out of settings and just store them here
    $descriptions = array();

    //Only create a calendar if this is checked off, just so we don't get a bunch of accidental ones with blank values
    $this->add('advcheckbox', 'create_new_calendar', ts('Create A New Calendar'));
    $this->add('advcheckbox', 'edit_existing_calendar', ts('Edit An Existing Calendar'));
    $this->add('text', 'update_id', ts('ID of Calendar to Update'));
    $this->add('text', 'calendar_title', ts('Calendar Title'));
    $this->add('advcheckbox', 'show_past_events', ts('Show Past Events?'));
    $this->add('advcheckbox', 'show_end_date', ts('Show End Date?'));
    $this->add('advcheckbox', 'show_public_events', ts('Show Public Events?'));
    $this->add('advcheckbox', 'events_by_month', ts('Show Events by Month?'));
    $this->add('advcheckbox', 'event_timings', ts('Show Event Times?'));
    $this->add('text', 'events_from_month', ts('Events from Month'));
    $this->add('advcheckbox', 'event_type_filters', ts('Filter Event Types?'));

    $eventTypes = CRM_Event_PseudoConstant::eventType();
    foreach ($eventTypes as $id => $type) {
      $this->addElement('checkbox', "eventtype_{$id}", $type, NULL,
        array('onclick' => "showhidecolorbox('{$id}')", 'id' => "event_{$id}"));
      $this->addElement('text', "eventcolor_{$id}", "Color",
        array(
          'onchange' => "updatecolor('eventcolor_{$id}', this.value);",
          'class' => 'color',
          'id' => "eventcolorid_{$id}",
        ));
    }

    //Add ability to delete current calendars, checkbox is a safety measure so we don't accidentally delete calendars we want to keep
    $this->add('advcheckbox', 'delete_current_calendars', ts('Delete Current Calendar(s)'));
    foreach ($settings as $calendar) {
      $this->add('advcheckbox', 'delete_calendar_' . $calendar['id'], ts('Delete ' . $calendar['calendar_title'] . ' (ID:' . $calendar['id'] . ')'));
    }
    $this->assign('eventTypes', $eventTypes);

    $this->assign('descriptions', $descriptions);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $submitted = $this->exportValues();
    foreach ($submitted as $key => $value) {
      if (!$value) {
        $submitted[$key] = 0;
      }
    }

    if ($submitted['create_new_calendar'] == 1) {
      $sql = "INSERT INTO civicrm_event_calendar(calendar_title, show_past_events, show_end_date, show_public_events, events_by_month, event_timings, events_from_month, event_type_filters)
       VALUES ('{$submitted['calendar_title']}', {$submitted['show_past_events']}, {$submitted['show_end_date']}, {$submitted['show_public_events']}, {$submitted['events_by_month']}, {$submitted['event_timings']}, {$submitted['events_from_month']}, {$submitted['event_type_filters']});";
      $dao = CRM_Core_DAO::executeQuery($sql);
      $cfId = CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
      foreach ($submitted as $key => $value) {
        if ("eventtype" == substr($key, 0, 9)) {
          if ($value == 1) {
            $id = explode("_", $key)[1];
            $sql = "INSERT INTO civicrm_event_calendar_event_type(event_calendar_id, event_type, event_color)
             VALUES ({$cfId}, {$submitted['eventtype_' . $id]}, {$submitted['eventcolor_' . $id]});";
          }
        }
      }
    }

    if ($submitted['edit_existing_calendar'] == 1) {
      $sql = "UPDATE civicrm_event_calendar
       SET (calendar_title = '{$submitted['calendar_title']}', show_past_events = {$submitted['show_past_events']}, show_end_date = {$submitted['show_end_date']}, show_public_events = {$submitted['show_public_events']}, events_by_month = {$submitted['events_by_month']}, event_timings = {$submitted['event_timings']}, events_from_month = {$submitted['events_from_month']}, event_type_filters = {$submitted['event_type_filters']}
       WHERE `id` = {$submitted['update_id']};";
      $dao = CRM_Core_DAO::executeQuery($sql);
      //delete current event type records to update with new ones
      $sql = "DELETE FROM civicrm_event_calendar_event_type WHERE `event_calendar_id` = {$submitted['update_id']};";
      $dao = CRM_Core_DAO::executeQuery($sql);
      //insert new event type records
      foreach ($submitted as $key => $value) {
        if ("eventtype" == substr($key, 0, 9)) {
          if ($value == 1) {
            $id = explode("_", $key)[1];
            $sql = "INSERT INTO civicrm_event_calendar_event_type(event_calendar_id, event_type, event_color)
             VALUES ({$submitted['update_id']}, {$submitted['eventtype_' . $id]}, {$submitted['eventcolor_' . $id]});";
          }
        }
      }
    }

    if ($submitted['delete_current_calendars'] == 1) {
      foreach ($submitted as $key => $value) {
        if ("delete_calendar" == substr($key, 0, 15)) {
          if ($value == 1) {
            $id = explode("_", $key)[2];
            $sql = "DELETE FROM civicrm_event_calendar WHERE `id` = {$id};";
            $dao = CRM_Core_DAO::executeQuery($sql);
          }
        }
      }
    }
    //Without a refresh, our delete items with IDs don't re-generate properly
    //There's probably a better way to do this
    header("Refresh:0");
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
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
  public function getFormSettings() {
    if (empty($this->_settings)) {
      $sql = "SELECT * FROM civicrm_event_calendar;";
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        $settings[] = $dao->toArray();
      }
    }

    return $settings;
  }

}
