<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    $updatedTitle = 'QA Event – Updated After Refresh 2026';

    echo "EC-012: Verify disabled event is not displayed\n";

    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    // Navigate to December 2026.
    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $target = new DateTimeImmutable('2026-12-01');
    $clicks = 0;

    while ($current->format('Y-m') !== '2026-12' && $clicks < 24) {
        $selector = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($selector)->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }

    if ($current->format('Y-m') !== '2026-12') {
        throw new RuntimeException(
            'Could not navigate to December 2026. Current heading: ' . $heading
        );
    }

    // Reload to retrieve the latest event data.
    $page->reload();
    $page->locator('#calendar')->waitFor();

    // Navigate to December again after reload.
    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $clicks = 0;

    while ($current->format('Y-m') !== '2026-12' && $clicks < 24) {
        $selector = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($selector)->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }

    if ($current->format('Y-m') !== '2026-12') {
        throw new RuntimeException(
            'Could not return to December 2026 after reload. Current heading: '
            . $heading
        );
    }

    // Check the rendered calendar for the disabled event.
    $event = $page->locator('#calendar')->getByText(
        $updatedTitle,
        ['exact' => true]
    );

    if ($event->count() > 0) {
        throw new RuntimeException(
            'Disabled event is still displayed on the calendar.'
        );
    }

    echo "PASS: Disabled event is absent after calendar reload.\n";
    echo "EC-012 RESULT: PASS\n";

} catch (Throwable $e) {
    fwrite(STDERR, "EC-012 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
