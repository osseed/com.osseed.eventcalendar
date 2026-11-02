<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    $oldTitle = 'QA Event – Long Title & Special Characters: O\'Brien, café, Zürich 2026';
    $updatedTitle = 'QA Event – Updated After Refresh 2026';

    echo "EC-011: Verify event update appears after refresh\n";

    // Load the calendar and refresh it.
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();
    $page->reload();
    $page->locator('#calendar')->waitFor();

    // Navigate to December 2026 after reload.
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

    echo "PASS: Calendar navigated to December 2026 after refresh.\n";

    // Verify the updated title appears.
    $updatedEvent = $page->locator('#calendar')->getByText(
        $updatedTitle,
        ['exact' => true]
    );
    $updatedEvent->waitFor(['timeout' => 15000]);

    $renderedTitle = $updatedEvent->textContent();

    if ($renderedTitle !== $updatedTitle) {
        throw new RuntimeException(
            "Updated title does not match. Actual: $renderedTitle"
        );
    }

    // Confirm the old title is absent.
    $oldEvent = $page->locator('#calendar')->getByText(
        $oldTitle,
        ['exact' => true]
    );

    if ($oldEvent->count() > 0) {
        throw new RuntimeException(
            'The old event title is still displayed after refresh.'
        );
    }

    echo "PASS: Updated title appeared after refresh; old title is absent.\n";
    echo "EC-011 RESULT: PASS\n";

} catch (Throwable $e) {
    fwrite(STDERR, "EC-011 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
