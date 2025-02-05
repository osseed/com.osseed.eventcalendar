<?php

require_once 'CRM/Core/Form.php';
use CRM_EventCalendar_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_EventCalendar_Form_EventCalendarSettings extends CRM_Core_Form {

  /**
   * Get Action.
   *
   * @var mixed
   */
  public $action;

  /**
   * Get Calendar ID.
   *
   * @var int
   */
  public $calendar_id;

  private $_submittedValues = array();
  private $_settings = array();

  public function buildQuickForm() {
    $this->controller->_destination = CRM_Utils_System::url('civicrm/admin/event-calendar', 'reset=1');
    $this->action = '';
    if(isset($_GET['action'])) {
      $this->action = $_GET['action'];
    }
    $this->calendar_id = $_GET['id'] ?? '';

    if ($this->action == 'delete') {
      $descriptions['delete_warning'] = E::ts('Are you sure you want to delete this calendar?');
      $this->add('hidden', 'action', $this->action);
      $this->add('hidden', 'calendar_id', $this->calendar_id);
      $this->assign('descriptions', $descriptions);
    }
    else {
      CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/jscolor.js');
      CRM_Core_Resources::singleton()->addScriptFile('com.osseed.eventcalendar', 'js/eventcalendar.js');

      $settings = $this->getFormSettings();
      CRM_Utils_System::setTitle(E::ts('Event Calendar Settings'));
      $descriptions = array();

      $this->add('hidden', 'action', $this->action);
      $this->add('hidden', 'calendar_id', $this->calendar_id);
      $this->add('text', 'calendar_title', E::ts('Calendar Title'));
      $this->add('advcheckbox', 'is_default', E::ts('Set as Default Event Calendar'));
      $descriptions['is_default'] = E::ts('Check this box to make this event calendar the default one, Only one calendar can be set as the default at a time.');
      $descriptions['calendar_title'] = E::ts('Event calendar title.');
      $this->add('advcheckbox', 'show_past_events', E::ts('Show Past Events?'));
      $descriptions['show_past_events'] = E::ts('Show past events as well as current/future.');
      $this->add('advcheckbox', 'show_end_date', E::ts('Show End Date?'));
      $descriptions['show_end_date'] = E::ts('Show the event with start and end dates on the calendar.');
      $this->add('advcheckbox', 'show_public_events', E::ts('Show Public Events?'));
      $descriptions['show_public_events'] = E::ts('Show only public events, or all events.');
      $this->add('advcheckbox', 'events_by_month', E::ts('Show Events by Month?'));
      $descriptions['events_by_month'] = E::ts('Show the month parameter on calendar.');
      $this->add('advcheckbox', 'event_timings', E::ts('Show Event Times?'));
      $descriptions['event_timings'] = E::ts('Show the event timings on calendar.');
      $this->add('text', 'events_from_month', E::ts('Events from Month'));
      $descriptions['events_from_month'] = E::ts('Show events from how many months from current month.');
      $this->add('advcheckbox', 'event_type_filters', E::ts('Filter Event Types?'));
      $descriptions['event_type_filters'] = E::ts('Show event types filter on calendar.');
      $this->add('advcheckbox', 'week_begins_from_day', E::ts('Week begins on'));
      $descriptions['week_begins_from_day'] = E::ts('Use weekBegin settings from CiviCRM. You can override settings at Administer > Localization > Date Formats.');
      $this->add('advcheckbox', 'recurring_event', E::ts('Show recurring events'));
      $descriptions['recurring_event'] = E::ts('Show only recurring events.');
      $this->add('advcheckbox', 'enrollment_status', E::ts('Show enrollment status'));
      $descriptions['enrollment_status'] = E::ts('Show enrollment status on calendar event.');
      $searchKitEnabled = \Civi\Api4\Extension::get(FALSE)
        ->addWhere('file', '=', 'search_kit')
        ->addWhere('status', '=', 'installed')
        ->execute()
        ->count();
      if ($searchKitEnabled) {
        $this->addEntityRef('saved_search_id', ts('Search Kit saved search'), [
          'entity' => 'SavedSearch',
          'api' => [
            'params' => ['api_entity' => 'Event'],
          ],
          'select' => ['minimumInputLength' => 0],
        ]);
        $descriptions['saved_search_id'] = ts('Optionally filter only to events found in this saved search.');
      }

      $eventTypes = CRM_Event_PseudoConstant::eventType();
      foreach ($eventTypes as $id => $type) {
        $this->addElement('checkbox', "eventtype_{$id}", E::ts($type), NULL,
          array('onclick' => "showhidecolorbox('{$id}')", 'id' => "event_{$id}"));
        $this->addElement('text', "eventcolor_{$id}", E::ts("Color"),
          array(
            'onchange' => "updatecolor('eventcolor_{$id}', this.value);",
            'class' => 'color',
            'id' => "eventcolorid_{$id}",
          ));
      }

      $this->assign('eventTypes', $eventTypes);
      $this->assign('descriptions', $descriptions);
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
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
      if (!$value && $key != 'calendar_title') {
        $submitted[$key] = 0;
      }
    }

    if ($submitted['action'] == 'add') {
      $sql = "INSERT INTO civicrm_event_calendar(calendar_title, show_past_events, show_end_date, show_public_events, events_by_month, event_timings, events_from_month, event_type_filters, week_begins_from_day, recurring_event, enrollment_status, saved_search_id, is_default)
       VALUES ('{$submitted['calendar_title']}', {$submitted['show_past_events']}, {$submitted['show_end_date']}, {$submitted['show_public_events']}, {$submitted['events_by_month']}, {$submitted['event_timings']}, {$submitted['events_from_month']}, {$submitted['event_type_filters']},
          {$submitted['week_begins_from_day']}, {$submitted['recurring_event']}, {$submitted['enrollment_status']}, {$submitted['saved_search_id']}, {$submitted['is_default']});";
      $dao = CRM_Core_DAO::executeQuery($sql);
      $cfId = CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
      // update default event calendar only if new calendar is default one.
      if ($submitted['is_default'] == 1) {
        $this->updateDefault($cfId, $submitted['is_default']);
      }
      foreach ($submitted as $key => $value) {
        if ("eventtype" == substr($key, 0, 9)) {
          if ($value == 1) {
            $id = explode("_", $key)[1];
            $sql = "INSERT INTO civicrm_event_calendar_event_type(event_calendar_id, event_type, event_color)
             VALUES ({$cfId}, {$id}, '{$submitted['eventcolor_' . $id]}');";
            $dao = CRM_Core_DAO::executeQuery($sql);
          }
        }
      }
    }

    if ($submitted['action'] == 'update') {
      $sql = "UPDATE civicrm_event_calendar
       SET calendar_title = '{$submitted['calendar_title']}', show_past_events = {$submitted['show_past_events']}, show_end_date = {$submitted['show_end_date']}, show_public_events = {$submitted['show_public_events']}, events_by_month = {$submitted['events_by_month']}, event_timings = {$submitted['event_timings']}, events_from_month = {$submitted['events_from_month']},
        event_type_filters = {$submitted['event_type_filters']}, week_begins_from_day = {$submitted['week_begins_from_day']}, recurring_event = {$submitted['recurring_event']},  enrollment_status = {$submitted['enrollment_status']}, saved_search_id = {$submitted['saved_search_id']}, is_default = {$submitted['is_default']}
       WHERE `id` = {$submitted['calendar_id']};";
      $dao = CRM_Core_DAO::executeQuery($sql);
      // update default event calendar.
      $this->updateDefault($submitted['calendar_id'], $submitted['is_default']);
      //delete current event type records to update with new ones
      $sql = "DELETE FROM civicrm_event_calendar_event_type WHERE `event_calendar_id` = {$submitted['calendar_id']};";
      $dao = CRM_Core_DAO::executeQuery($sql);
      //insert new event type records
      foreach ($submitted as $key => $value) {
        if ("eventtype" == substr($key, 0, 9)) {
          if ($value == 1) {
            $id = explode("_", $key)[1];
            $sql = "INSERT INTO civicrm_event_calendar_event_type(event_calendar_id, event_type, event_color)
             VALUES ({$submitted['calendar_id']}, {$id}, '{$submitted['eventcolor_' . $id]}');";
             $dao = CRM_Core_DAO::executeQuery($sql);
          }
        }
      }
    }

    if ($submitted['action'] == 'delete') {
      $sql = "DELETE FROM civicrm_event_calendar WHERE `id` = {$submitted['calendar_id']};";
      $dao = CRM_Core_DAO::executeQuery($sql);
    }

    CRM_Core_Session::setStatus(E::ts('The Calendar has been saved.'), E::ts('Saved'), 'success');
    parent::postProcess();
  }

  /**
   * Updates the default calendar by setting the provided calendar ID as default
   * and resetting the `is_default` field for all other calendars to non-default.
   *
   * @param int|null $calendar_id The ID of the calendar to set as default.
   * @param int $is_default The value to set for `is_default` (1 to set as default, 0 to unset).
   */
  public function updateDefault($calendar_id = NULL, $is_default = 0) {
    // Ensure valid input
    if ($calendar_id === NULL || !in_array($is_default, [0, 1])) {
      Civi::log()->error("[com.osseed.eventcalendar] Invalid calendar_id or is_default.");
      return;
    }

    // Prepare the SQL query to update the default calendar
    $sql = "UPDATE civicrm_event_calendar SET is_default = CASE WHEN id = %1 THEN %2 ELSE 0 END";
    try {
      CRM_Core_DAO::executeQuery($sql, [
        1 => [$calendar_id, 'Integer'],
        2 => [$is_default, 'Integer'],
      ]);
    } catch (Exception $e) {
      Civi::log()->error('[com.osseed.eventcalendar] Error updating default calendar: ' . $e->getMessage());
    }
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
      return isset($settings);
    }
  }

  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  public function setDefaultValues() {
    if ($this->calendar_id && ($this->action != 'delete')) {
      $existing = array();
      $sql = "SELECT * FROM civicrm_event_calendar WHERE id = {$this->calendar_id};";
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        $existing[] = $dao->toArray();
      }
      $defaults = array();
      foreach ($existing as $name => $value) {
        $defaults[$name] = $value;
      }
      $sql = "SELECT * FROM civicrm_event_calendar_event_type WHERE event_calendar_id = {$this->calendar_id};";
      $dao = CRM_Core_DAO::executeQuery($sql);
      $existing = array();
      while ($dao->fetch()) {
        $existing[] = $dao->toArray();
      }
      foreach ($existing as $name => $value) {
        $defaults[0]['eventtype_' . $value['event_type']] = 1;
        $defaults[0]['eventcolor_' . $value['event_type']] = $value['event_color'];
      }
      return $defaults[0];
    }
  }
}
