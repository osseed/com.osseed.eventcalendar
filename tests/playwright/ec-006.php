<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();
try {
    $page = $browser->newPage();
    echo "EC-006: Navigate to the next month\n";
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();
    $before = ecParseHeading(ecHeading($page));
    $page->locator('#calendar .fc-next-button')->click();
    $afterText = ecWaitForHeadingChange($page, $before->format('F Y'));
    $after = ecParseHeading($afterText);
    $expected = $before->modify('+1 month');
    if ($after->format('Y-m') !== $expected->format('Y-m')) {
        throw new RuntimeException("Expected {$expected->format('F Y')}, got $afterText.");
    }
    echo "PASS: Next-month navigation: {$before->format('F Y')} -> {$after->format('F Y')}.\n";
    echo "EC-006 RESULT: PASS\n";
} catch (Throwable $e) {
    fwrite(STDERR, "EC-006 RESULT: FAIL\n" . $e->getMessage() . "\n");
    exit(1);
} finally {
    $browser->close();
}
