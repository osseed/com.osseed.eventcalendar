<?php
use CRM_EventCalendar_ExtensionUtil as E;

class CRM_EventCalendar_BAO_EventCalendarType extends CRM_EventCalendar_DAO_EventCalendarType {

  /**
   * Create a new EventCalendarType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_EventCalendar_DAO_EventCalendarType|NULL
   *
  public static function create($params) {
    $className = 'CRM_EventCalendar_DAO_EventCalendarType';
    $entityName = 'EventCalendarType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
