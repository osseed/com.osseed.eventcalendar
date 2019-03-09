<?php
use CRM_EventCalendar_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_EventCalendar_Upgrader extends CRM_EventCalendar_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  public function upgrade_0001() {
    $this->ctx->log->info('Applying update 0001');
    $sql = "CREATE TABLE IF NOT EXISTS `civicrm_event_calendar` (
         `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique EventCalendar ID',
         `calendar_title` varchar(255)    COMMENT 'Calendar Title',
         `show_past_events` tinyint    COMMENT 'Show Past Events',
         `show_end_date` tinyint    COMMENT 'Show End Date',
         `show_public_events` tinyint    COMMENT 'Show Only Public or All',
         `events_by_month` tinyint    COMMENT 'Use the Month param in the calendar',
         `event_timings` tinyint    COMMENT 'Show the event timing',
         `events_from_month` int unsigned    COMMENT 'How many months to show events',
         `event_type_filters` tinyint    COMMENT 'Whether to show event type filters',
            PRIMARY KEY (`id`)
    );";
    CRM_Core_DAO::executeQuery($sql);

    $sql = "CREATE TABLE IF NOT EXISTS `civicrm_event_calendar_event_type` (
         `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique EventCalendarEventType ID',
         `event_calendar_id` int unsigned    COMMENT 'FK to Event Calendar',
         `event_type` int unsigned    COMMENT 'Event Type id',
         `event_color` varchar(255)    COMMENT 'Hex code for event type display color',
            PRIMARY KEY (`id`),
            CONSTRAINT FK_civicrm_event_calendar_event_type_event_calendar_id FOREIGN KEY (`event_calendar_id`) REFERENCES `civicrm_event_calendar`(`id`) ON DELETE CASCADE
    );";
    CRM_Core_DAO::executeQuery($sql);

    $result = civicrm_api3('Navigation', 'get', [
      'sequential' => 1,
      'url' => "civicrm/eventcalendarsettings",
    ]);
    if ($result['values']) {
      $newmenulink = civicrm_api3('Navigation', 'create', [
        'id' => $result['values'][0]['id'],
        'url' => "/civicrm/admin/event-calendar",
      ]);
    }
  }
  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   *
  public function postInstall() {
    $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
      'return' => array("id"),
      'name' => "customFieldCreatedViaManagedHook",
    ));
    civicrm_api3('Setting', 'create', array(
      'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
    ));
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = E::ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
