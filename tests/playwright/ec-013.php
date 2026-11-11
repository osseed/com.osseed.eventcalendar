
<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    echo "EC-013: Verify December-to-January month boundary\n";

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

    echo "December 2026 displayed successfully.\n";

    // Click Next Month and verify January 2027.
    $page->locator('#calendar .fc-next-button')->click();

    $newHeading = ecWaitForHeadingChange($page, $heading);
    $newMonth = ecParseHeading($newHeading);

    if ($newMonth->format('Y-m') !== '2027-01') {
        throw new RuntimeException(
            'Expected January 2027, but calendar showed: ' . $newHeading
        );
    }

    echo "PASS: Next Month navigated from December 2026 to January 2027.\n";
    echo "EC-013 RESULT: PASS\n";

} catch (Throwable $e) {
    fwrite(STDERR, "EC-013 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
