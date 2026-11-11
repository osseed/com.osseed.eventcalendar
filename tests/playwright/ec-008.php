<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();
try {
    $page = $browser->newPage();
    echo "EC-008: Verify December-to-January year transition\n";
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $clicks = 0;
    while ((int)$current->format('n') !== 12 && $clicks < 12) {
        $page->locator('#calendar .fc-next-button')->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }
    if ((int)$current->format('n') !== 12) {
        throw new RuntimeException('Could not reach December within 12 clicks; current heading: ' . $heading);
    }

    $december = $current;
    $page->locator('#calendar .fc-next-button')->click();
    $januaryText = ecWaitForHeadingChange($page, $heading);
    $january = ecParseHeading($januaryText);
    $expected = $december->modify('+1 month');
    if ($january->format('Y-m') !== $expected->format('Y-m') || $january->format('n') !== '1') {
        throw new RuntimeException("Expected January of the next year ({$expected->format('F Y')}), got $januaryText.");
    }

    echo "PASS: December {$december->format('Y')} -> January {$january->format('Y')}.\n";
    echo "EC-008 RESULT: PASS\n";
} catch (Throwable $e) {
    fwrite(STDERR, "EC-008 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
