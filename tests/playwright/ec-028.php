<?php

require __DIR__ . '/test-helpers.php';

$username = getenv('DRUPAL_USERNAME');
$password = getenv('DRUPAL_PASSWORD');

$eventId = 10;
$eventTitle = 'Group Singing';

if (!$username || !$password) {
    fwrite(STDERR, "Set DRUPAL_USERNAME and DRUPAL_PASSWORD first.\n");
    exit(1);
}

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    // STEP 1: Login
    echo "STEP 1: Logging in...\n";

    $page->goto(ecBaseUrl() . '/user/login');
    $page->locator('#edit-name')->fill($username);
    $page->locator('#edit-pass')->fill($password);
    $page->locator('form#user-login-form input[name="op"]')->click();
    $page->waitForLoadState('load');

    echo "PASS: Login successful.\n";

    // STEP 2: Open deletion confirmation
    echo "\nSTEP 2: Opening deletion confirmation...\n";

    $deleteUrl = ecBaseUrl() .
        '/civicrm/event/manage?action=delete&id=' . $eventId;

    $page->goto($deleteUrl);
    $page->waitForLoadState('load');

    $confirmationText = $page->locator('body')->innerText();

    if (strpos($confirmationText, $eventTitle) === false) {
        throw new Exception('Expected event was not found on confirmation page.');
    }

    if (strpos($confirmationText, 'cannot be undone') === false) {
        throw new Exception('Deletion warning was not displayed.');
    }

    echo "PASS: Correct event and deletion warning verified.\n";

    // STEP 3: Confirm deletion
    echo "\nSTEP 3: Confirming event deletion...\n";

    $page->locator('#_qf_Delete_next-bottom')->click();
    $page->waitForLoadState('load');

    echo "Deletion confirmation submitted.\n";
    echo "Current URL: " . $page->url() . "\n";

    // STEP 4: Verify event is absent from Manage Events
    echo "\nSTEP 4: Checking Manage Events...\n";

    $page->goto(ecBaseUrl() . '/civicrm/event/manage');
    $page->waitForLoadState('load');

    $manageText = $page->locator('body')->innerText();

    if (strpos($manageText, $eventTitle) !== false) {
        throw new Exception('Deleted event is still visible in Manage Events.');
    }

    echo "PASS: Group Singing is absent from Manage Events.\n";

    // STEP 5: Verify event details are unavailable
    echo "\nSTEP 5: Checking deleted event details...\n";

    $page->goto(
        ecBaseUrl() . '/civicrm/event/info?id=' . $eventId . '&reset=1'
    );
    $page->waitForLoadState('load');

    $detailsText = $page->locator('body')->innerText();

    if (strpos($detailsText, $eventTitle) !== false) {
        throw new Exception('Deleted event details are still accessible.');
    }

    echo "PASS: Deleted event details are no longer available.\n";

    echo "\n====================================\n";
    echo "EC-028: PASS\n";
    echo "Event deletion verified.\n";
    echo "====================================\n";

} catch (Throwable $e) {
    fwrite(STDERR, "\nEC-028: FAIL\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);

} finally {
    $browser->close();
}
