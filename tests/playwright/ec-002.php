<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();
try {
    $page = $browser->newPage();
    echo "EC-002: Verify clicking an event opens its details\n";
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $clicks = 0;
    while ($current->format('Y-m') < '2026-11' && $clicks < 12) {
        $page->locator('#calendar .fc-next-button')->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }
    if ($current->format('Y-m') !== '2026-11') {
        throw new RuntimeException('Could not reach November 2026. Current heading: ' . $heading);
    }

    $event = $page->locator('#calendar a[href="/civicrm/event/info?id=7"]');
    if ($event->count() === 0) {
        throw new RuntimeException('Event ID 7 was not found in the November 2026 calendar.');
    }
    $event->first()->click();
    $page->waitForURL('**/civicrm/event/info?id=7');
    $details = $page->locator('body')->innerText();

    foreach (['campaign', 'November 12th, 2026 12:00 PM', 'November 13th, 2026 9:00 PM'] as $expected) {
        if (stripos($details, $expected) === false) {
            throw new RuntimeException("Event details page is missing expected text: $expected");
        }
    }

    echo "PASS: Event click opened ID 7 details with expected title and date/time.\n";
    echo "EC-002 RESULT: PASS\n";
} catch (Throwable $e) {
    fwrite(STDERR, "EC-002 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
