<?php
require __DIR__ . '/test-helpers.php';

$username = getenv('DRUPAL_USERNAME');
$password = getenv('DRUPAL_PASSWORD');
if (!$username || !$password) {
    fwrite(STDERR, "Set DRUPAL_USERNAME and DRUPAL_PASSWORD before running EC-005.\n");
    exit(2);
}

$browser = ecBrowser();
$page = null;
$original = null;
$changed = false;
$settingsUrl = ecBaseUrl() . '/civicrm/eventcalendarsettings?action=update&id=1&reset=1';

try {
    $page = $browser->newPage();
    echo "EC-005: Verify a saved calendar setting persists after refresh/reopen\n";
    $page->goto(ecBaseUrl() . '/user/login');
    $page->locator('#edit-name')->fill($username);
    $page->locator('#edit-pass')->fill($password);
    $page->locator('form#user-login-form input[name="op"]')->click();
    $page->waitForLoadState('load');
    if ($page->locator('form#user-login-form')->count() > 0) {
        throw new RuntimeException('Drupal login failed.');
    }

    $page->goto($settingsUrl);
    $checkbox = $page->locator('#EventCalendarSettings #show_past_events');
    $checkbox->waitFor();
    $original = (bool)$checkbox->evaluate('(el) => el.checked');
    $checkbox->setChecked(!$original);
    $page->locator('#EventCalendarSettings button[type="submit"]')->click();
    $page->waitForLoadState('load');
    $changed = true;

    // Reopen the settings page, which creates a fresh server-rendered read of the saved value.
    $page->goto($settingsUrl);
    $persisted = (bool)$page->locator('#EventCalendarSettings #show_past_events')->evaluate('(el) => el.checked');
    if ($persisted !== !$original) {
        throw new RuntimeException('Setting value did not persist after reopening the settings page.');
    }

    echo "PASS: Saved setting persisted after reopening the page.\n";
    echo "EC-005 RESULT: PASS\n";
} catch (Throwable $e) {
    fwrite(STDERR, "EC-005 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    if ($page && $original !== null && $changed) {
        try {
            $page->goto($settingsUrl);
            $checkbox = $page->locator('#EventCalendarSettings #show_past_events');
            $checkbox->setChecked($original);
            $page->locator('#EventCalendarSettings button[type="submit"]')->click();
            $page->waitForLoadState('load');
            $page->goto($settingsUrl);
            $restored = (bool)$page->locator('#EventCalendarSettings #show_past_events')->evaluate('(el) => el.checked');
            if ($restored !== $original) {
                fwrite(STDERR, "WARNING: Original setting restoration could not be verified.\n");
            } else {
                echo "Cleanup: Original setting restored.\n";
            }
        } catch (Throwable $restoreError) {
            fwrite(STDERR, "WARNING: Setting restoration failed: " . $restoreError->getMessage() . "\n");
        }
    }
    $browser->close();
}
