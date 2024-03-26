<?php
// phpcs:disable
use CRM_EventCalendar_ExtensionUtil as E;
// phpcs:enable

/**
 * Collection of upgrade steps.
 */
class CRM_EventCalendar_Upgrader extends CRM_Extension_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Upgrade the table as support for multicalendar.
   */
  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001');
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
        `week_begins_from_day` tinyint    COMMENT 'Show week begins from day',
        `recurring_event`  tinyint   COMMENT 'Show recurring events',
        `enrollment_status` tinyint   COMMENT 'Show enrollment status',
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
      $url = CRM_Utils_System::url('civicrm/admin/event-calendar', 'reset=1');
      $newmenulink = civicrm_api3('Navigation', 'create', [
        'id' => $result['values'][0]['id'],
        'url' => $url,
      ]);
    }
    CRM_Core_Session::setStatus(E::ts('You may need to clear caches and reset paths as some menu items have changed'), E::ts('Success'), 'success');
    return TRUE;
  }

  /**
   * Upgrade to calendar for filter by saved_search_id.
   */
  public function upgrade_1002() {
    $this->ctx->log->info('Check to see if saved_search_id column is present on the civicrm_event_calendar table.');
    // Add search_id column to civicrm_event_calendar table if not exist.
    if (!CRM_Core_BAO_SchemaHandler::checkIfFieldExists('civicrm_event_calendar', 'saved_search_id')) {
      $this->ctx->log->info('Applying civicrm_event_calendar update 1002.  Adding saved_search_id to civicrm_event_calendar table.');
      CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_event_calendar ADD COLUMN `saved_search_id` int(11) COMMENT "Filter results by this saved search"');
    }
    else {
      $this->ctx->log->info('Skipped civicrm_event_calendar update 1002.  Column saved_search_id already present on civicrm_event_calendar table.');
    }
    return TRUE;
  }

  /**
   * Add recurring_event and enrollment_status columns to the civicrm_event_calendar table if missing.
   */
  public function upgrade_1003() {
    $this->ctx->log->info('Check to see if recurring_event and enrollment_status columns are present on the civicrm_event_calendar table.');
    if (!CRM_Core_BAO_SchemaHandler::checkIfFieldExists('civicrm_event_calendar', 'recurring_event')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_event_calendar ADD COLUMN recurring_event tinyint DEFAULT NULL COMMENT 'Show recurring events'");
    }
    if (!CRM_Core_BAO_SchemaHandler::checkIfFieldExists('civicrm_event_calendar', 'enrollment_status')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_event_calendar ADD COLUMN enrollment_status tinyint DEFAULT NULL COMMENT 'Show enrollment status'");
    }
    else {
      $this->ctx->log->info('Skipped civicrm_event_calendar update 1003. Columns already present on the civicrm_event_calendar table.');
    }
    return TRUE;
  }

  /**
   * Example: Run an external SQL script when the module is installed.
   *
   * Note that if a file is present sql\auto_install that will run regardless of this hook.
   */
  // public function install(): void {
  //   $this->executeSqlFile('sql/my_install.sql');
  // }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  // public function postInstall(): void {
  //  $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
  //    'return' => array("id"),
  //    'name' => "customFieldCreatedViaManagedHook",
  //  ));
  //  civicrm_api3('Setting', 'create', array(
  //    'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
  //  ));
  // }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
   * Note that if a file is present sql\auto_uninstall that will run regardless of this hook.
   */
  // public function uninstall(): void {
  //   $this->executeSqlFile('sql/my_uninstall.sql');
  // }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  // public function enable(): void {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  // public function disable(): void {
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  // public function upgrade_4200(): bool {
  //   $this->ctx->log->info('Applying update 4200');
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
  //   CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
  //   return TRUE;
  // }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  // public function upgrade_4201(): bool {
  //   $this->ctx->log->info('Applying update 4201');
  //   // this path is relative to the extension base dir
  //   $this->executeSqlFile('sql/upgrade_4201.sql');
  //   return TRUE;
  // }

  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  // public function upgrade_4202(): bool {
  //   $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

  //   $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //   $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //   $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //   return TRUE;
  // }
  // public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  // public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  // public function processPart3($arg5) { sleep(10); return TRUE; }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  // public function upgrade_4203(): bool {
  //   $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

  //   $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //   $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //   for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //     $endId = $startId + self::BATCH_SIZE - 1;
  //     $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //       1 => $startId,
  //       2 => $endId,
  //     ));
  //     $sql = '
  //       UPDATE civicrm_contribution SET foobar = apple(banana()+durian)
  //       WHERE id BETWEEN %1 and %2
  //     ';
  //     $params = array(
  //       1 => array($startId, 'Integer'),
  //       2 => array($endId, 'Integer'),
  //     );
  //     $this->addTask($title, 'executeSql', $sql, $params);
  //   }
  //   return TRUE;
  // }

}
