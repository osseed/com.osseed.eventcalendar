
<?php

require __DIR__ . '/test-helpers.php';

$browser = ecBrowser('firefox');

try {
    $page = $browser->newPage();

    echo "EC-025: Browser Compatibility Test\n";
    echo "Opening CiviCRM calendar in Firefox...\n";

    // Open calendar page
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);

    // Verify calendar loads
    $page->locator('#calendar')->waitFor();

    // Verify calendar heading
    $heading = ecHeading($page);

    if ($heading === '') {
        throw new Exception('Calendar heading is empty.');
    }

    echo "PASS: Calendar loaded successfully.\n";
    echo "PASS: Calendar heading detected: $heading\n";

    echo "\n====================================\n";
    echo "EC-025: PASS\n";
    echo "Firefox calendar compatibility verified.\n";
    echo "====================================\n";

} catch (Throwable $e) {
    fwrite(STDERR, "\nEC-025: FAIL\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);

} finally {
    $browser->close();
}

