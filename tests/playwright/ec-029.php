<?php

require __DIR__ . '/test-helpers.php';

$username = getenv('DRUPAL_USERNAME');
$password = getenv('DRUPAL_PASSWORD');

$eventTitle = 'EC029 Persistence Test';

if (!$username || !$password) {
    fwrite(STDERR, "Set DRUPAL_USERNAME and DRUPAL_PASSWORD first.\n");
    exit(1);
}

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    // STEP 1: Login
    echo "STEP 1: Logging in to Drupal...\n";

    $page->goto(ecBaseUrl() . '/user/login');
    $page->locator('#edit-name')->fill($username);
    $page->locator('#edit-pass')->fill($password);
    $page->locator('form#user-login-form input[name="op"]')->click();
    $page->waitForLoadState('load');

    echo "PASS: Login successful.\n";

    // STEP 2: Open calendar
    echo "\nSTEP 2: Opening Event Calendar...\n";

    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->waitForLoadState('load');

    // STEP 3: Navigate to August 2027
    echo "\nSTEP 3: Navigating to August 2027...\n";

    $heading = ecGoToMonthNumber($page, 8, 12);

    if ($heading !== 'August 2027') {
        throw new RuntimeException("Expected August 2027, found $heading.");
    }

    echo "PASS: Calendar opened for August 2027.\n";

    // STEP 4: Verify event
    $bodyText = $page->locator('body')->innerText();

    if (strpos($bodyText, $eventTitle) === false) {
        throw new RuntimeException("Event not visible in August 2027.");
    }

    echo "PASS: Event is visible.\n";

    // STEP 5: Refresh and navigate back
    echo "\nSTEP 5: Refreshing calendar...\n";

    $page->reload();
    $page->waitForLoadState('load');

    $heading = ecGoToMonthNumber($page, 8, 12);

    if ($heading !== 'August 2027') {
        throw new RuntimeException("Could not return to August 2027.");
    }

    $bodyText = $page->locator('body')->innerText();

    if (strpos($bodyText, $eventTitle) === false) {
        throw new RuntimeException("Event disappeared after first refresh.");
    }

    echo "PASS: Event remains visible after first refresh.\n";

    // STEP 6: Refresh and verify again
    echo "\nSTEP 6: Refreshing calendar again...\n";

    $page->reload();
    $page->waitForLoadState('load');

    $heading = ecGoToMonthNumber($page, 8, 12);
    $bodyText = $page->locator('body')->innerText();

    if (
        $heading !== 'August 2027' ||
        strpos($bodyText, $eventTitle) === false
    ) {
        throw new RuntimeException("Event did not persist after second refresh.");
    }

    echo "PASS: Event remains visible after second refresh.\n";

    echo "\n====================================\n";
    echo "EC-029: PASS\n";
    echo "Event data persistence verified.\n";
    echo "====================================\n";

} catch (Throwable $e) {
    fwrite(STDERR, "\nEC-029: FAIL\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);

} finally {
    $browser->close();
}
