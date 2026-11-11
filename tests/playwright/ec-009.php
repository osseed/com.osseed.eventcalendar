<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();
try {
    $page = $browser->newPage();
    echo "EC-009: Verify January-to-December previous-year transition\n";
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $clicks = 0;
    while ((int)$current->format('n') !== 1 && $clicks < 12) {
        $page->locator('#calendar .fc-next-button')->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }
    if ((int)$current->format('n') !== 1) {
        throw new RuntimeException('Could not reach January within 12 clicks; current heading: ' . $heading);
    }

    $january = $current;
    $page->locator('#calendar .fc-prev-button')->click();
    $decemberText = ecWaitForHeadingChange($page, $heading);
    $december = ecParseHeading($decemberText);
    $expected = $january->modify('-1 month');
    if ($december->format('Y-m') !== $expected->format('Y-m') || $december->format('n') !== '12') {
        throw new RuntimeException("Expected December of the previous year ({$expected->format('F Y')}), got $decemberText.");
    }

    echo "PASS: January {$january->format('Y')} -> December {$december->format('Y')}.\n";
    echo "EC-009 RESULT: PASS\n";
} catch (Throwable $e) {
    fwrite(STDERR, "EC-009 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
