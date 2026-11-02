<?php

require __DIR__ . '/test-helpers.php';

$username = getenv('DRUPAL_USERNAME');
$password = getenv('DRUPAL_PASSWORD');

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

    // STEP 3: Verify calendar layout
    echo "\nSTEP 3: Checking calendar layout...\n";

    if ($page->locator('#calendar')->count() !== 1) {
        throw new RuntimeException("Calendar container is missing.");
    }

    if ($page->locator('#calendar .fc-toolbar')->count() !== 1) {
        throw new RuntimeException("Calendar toolbar is missing.");
    }

    echo "PASS: Calendar container and toolbar are visible.\n";

    // STEP 4: Verify calendar controls
    echo "\nSTEP 4: Checking calendar controls...\n";

    $controls = [
        'Previous month' => '#calendar .fc-prev-button',
        'Next month' => '#calendar .fc-next-button',
        'Today' => '#calendar .fc-today-button',
    ];

    foreach ($controls as $label => $selector) {
        if ($page->locator($selector)->count() !== 1) {
            throw new RuntimeException("$label button is missing.");
        }

        echo "PASS: $label button is present.\n";
    }

    if ($page->locator('#calendar .fc-button')->count() !== 6) {
        throw new RuntimeException("Expected 6 calendar controls.");
    }

    echo "PASS: All 6 calendar controls are present.\n";

    // STEP 5: Verify calendar date grid
    echo "\nSTEP 5: Checking calendar date grid...\n";

    $dateCells = $page->locator('#calendar .fc-day[data-date]')->count();

    if ($dateCells !== 42) {
        throw new RuntimeException(
            "Expected 42 date cells, found $dateCells."
        );
    }

    echo "PASS: All 42 date cells are displayed.\n";

    $rows = $page->locator('#calendar .fc-row')->count();

    if ($rows !== 7) {
        throw new RuntimeException(
            "Expected 7 calendar rows, found $rows."
        );
    }

    echo "PASS: Calendar rows are displayed correctly.\n";

    // STEP 6: Verify events
    echo "\nSTEP 6: Checking calendar events...\n";

    $eventCount = $page->locator('#calendar .fc-event')->count();

    if ($eventCount < 1) {
        throw new RuntimeException("No calendar events are displayed.");
    }

    echo "PASS: $eventCount calendar events are displayed.\n";

    // STEP 7: Test next month navigation
    echo "\nSTEP 7: Testing next month navigation...\n";

    $oldHeading = ecHeading($page);

    $page->locator('#calendar .fc-next-button')->click();

    $newHeading = ecWaitForHeadingChange($page, $oldHeading);

    if ($newHeading === $oldHeading) {
        throw new RuntimeException("Next month navigation failed.");
    }

    echo "PASS: Next month navigation works ($newHeading).\n";

    // STEP 8: Test previous month navigation
    echo "\nSTEP 8: Testing previous month navigation...\n";

    $page->locator('#calendar .fc-prev-button')->click();

    $previousHeading = ecWaitForHeadingChange($page, $newHeading);

    if ($previousHeading !== $oldHeading) {
        throw new RuntimeException(
            "Previous month navigation did not return to $oldHeading."
        );
    }

    echo "PASS: Previous month navigation works ($previousHeading).\n";

    echo "\n====================================\n";
    echo "EC-030: PASS\n";
    echo "Calendar UI and usability verified.\n";
    echo "====================================\n";

} catch (Throwable $e) {
    fwrite(STDERR, "\nEC-030: FAIL\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);

} finally {
    $browser->close();
}
