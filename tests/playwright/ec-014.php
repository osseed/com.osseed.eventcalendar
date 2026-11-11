
<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    echo "EC-014: Verify January-to-December year boundary\n";

    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    // Navigate to January 2027.
    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $target = new DateTimeImmutable('2027-01-01');
    $clicks = 0;

    while ($current->format('Y-m') !== '2027-01' && $clicks < 24) {
        $selector = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($selector)->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }

    if ($current->format('Y-m') !== '2027-01') {
        throw new RuntimeException(
            'Could not navigate to January 2027. Current heading: ' . $heading
        );
    }

    echo "January 2027 displayed successfully.\n";

    // Click Previous Month and verify December 2026.
    $page->locator('#calendar .fc-prev-button')->click();

    $newHeading = ecWaitForHeadingChange($page, $heading);
    $newMonth = ecParseHeading($newHeading);

    if ($newMonth->format('Y-m') !== '2026-12') {
        throw new RuntimeException(
            'Expected December 2026, but calendar showed: ' . $newHeading
        );
    }

    echo "PASS: Previous Month navigated from January 2027 to December 2026.\n";
    echo "EC-014 RESULT: PASS\n";

} catch (Throwable $e) {
    fwrite(STDERR, "EC-014 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
