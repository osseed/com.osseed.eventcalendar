<?php

require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    echo "EC-026: Error handling - Empty month\n\n";

    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    $target = new DateTimeImmutable('2027-06-01');
    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $clicks = 0;

    // Navigate to June 2027.
    while (
        $current->format('Y-m') !== '2027-06' &&
        $clicks < 36
    ) {
        $selector = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($selector)->click();

        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);

        $clicks++;
    }

    if ($current->format('Y-m') !== '2027-06') {
        throw new RuntimeException('Could not navigate to June 2027.');
    }

    echo "Current month: " . ecHeading($page) . "\n";

    $eventCount = $page->locator('#calendar .fc-event')->count();

    if ($eventCount === 0) {
        echo "PASS: No events are displayed in June 2027.\n";
    } else {
        throw new RuntimeException(
            "Expected an empty month, but found $eventCount event(s)."
        );
    }

    // Verify calendar navigation remains functional.
    $heading = ecHeading($page);
    $page->locator('#calendar .fc-next-button')->click();
    $newHeading = ecWaitForHeadingChange($page, $heading);

    if ($newHeading !== $heading) {
        echo "PASS: Calendar navigation works after the empty month.\n";
    } else {
        throw new RuntimeException('Calendar navigation did not work.');
    }

    echo "\nEC-026 RESULT: PASS\n";

} catch (Throwable $e) {

    echo "\nEC-026 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";

} finally {
    $browser->close();
}
