<?php
use CRM_EventCalendar_ExtensionUtil as E;

class CRM_EventCalendar_Page_ManageEventCalendars extends CRM_Core_Page_Basic {
  //This could be an ajax page here, but couldn't get the special colorbox javascript working with ajax functionality on
  //public $useLivePageJS = TRUE;

  static $_links = NULL;

  public function getBAOName() {
    return 'CRM_EventCalendar_BAO_EventCalendar';
  }

  public function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
        CRM_Core_Action::UPDATE => array(
          'name' => ts('Edit'),
          'url' => 'civicrm/eventcalendarsettings',
          'qs' => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Event Calendar'),
        ),
        CRM_Core_Action::DELETE => array(
          'name' => ts('Delete'),
          'url' => 'civicrm/eventcalendarsettings',
          'qs' => 'action=delete&id=%%id%%',
          'title' => ts('Delete Event Calendar'),
        ),
        CRM_Core_Action::VIEW => array(
          'name' => ts('Preview'),
          'url' => 'civicrm/showevents',
          'qs' => 'id=%%id%%',
          'title' => ts('Preview Event Calendar'),
        ),
      );
    }
    return self::$_links;
  }

  public function editForm() {
    return 'CRM_EventCalendar_Form_EventCalendarSettings';
  }

  public function editName() {
    return 'Event Calendars';
  }

  public function userContext($mode = NULL) {
    return 'civicrm/admin/event-calendar';
  }

}
