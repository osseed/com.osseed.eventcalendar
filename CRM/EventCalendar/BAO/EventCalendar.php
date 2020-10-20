<?php
use CRM_EventCalendar_ExtensionUtil as E;

class CRM_EventCalendar_BAO_EventCalendar extends CRM_EventCalendar_DAO_EventCalendar {

  /**
   * Create a new EventCalendar based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_EventCalendar_DAO_EventCalendar|NULL
   *
  public static function create($params) {
    $className = 'CRM_EventCalendar_DAO_EventCalendar';
    $entityName = 'EventCalendar';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
