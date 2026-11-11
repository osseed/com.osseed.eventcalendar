<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    $eventTitle = 'QA Event – Long Title & Special Characters: O\'Brien, café, Zürich 2026';

    echo "EC-010: Display long titles and special characters\n";

    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $target = new DateTimeImmutable('2026-12-01');
    $clicks = 0;

    while ($current->format('Y-m') !== $target->format('Y-m') && $clicks < 24) {
        $button = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($button)->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }

    if ($current->format('Y-m') !== '2026-12') {
        throw new RuntimeException(
            'Could not navigate to December 2026. Current heading: ' . $heading
        );
    }

    echo "PASS: Calendar navigated to December 2026.\n";

    $event = $page->locator('#calendar')->getByText(
        $eventTitle,
        ['exact' => true]
    );

    $event->waitFor(['timeout' => 15000]);
    $renderedTitle = $event->textContent();

    if ($renderedTitle !== $eventTitle) {
        throw new RuntimeException(
            "Displayed title did not match.\nExpected: $eventTitle\nActual: $renderedTitle"
        );
    }

    echo "PASS: Long title and special characters rendered correctly.\n";
    echo "EC-010 RESULT: PASS\n";

} catch (Throwable $e) {
    fwrite(STDERR, "EC-010 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
