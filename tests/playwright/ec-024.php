<?php

require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    echo "EC-024: Regression testing - Existing CiviCRM events\n\n";

    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    $testEvents = [
        '2026-11' => [
            'month' => 'November 2026',
            'events' => ['campaign']
        ],
        '2026-12' => [
            'month' => 'December 2026',
            'events' => ['Artifical Intelligence']
        ],
        '2027-04' => [
            'month' => 'April 2027',
            'events' => ['Fall Fundraiser Dinner']
        ],
        '2027-05' => [
            'month' => 'May 2027',
            'events' => ['Rain-forest Cup Youth Soccer Tournament']
        ]
    ];

    $passed = 0;
    $failed = 0;

    foreach ($testEvents as $month => $details) {

        $target = new DateTimeImmutable($month . '-01');
        $heading = ecHeading($page);
        $current = ecParseHeading($heading);
        $clicks = 0;

        // Navigate to the required month.
        while (
            $current->format('Y-m') !== $month &&
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

        if ($current->format('Y-m') !== $month) {
            throw new RuntimeException(
                "Could not navigate to {$details['month']}."
            );
        }

        echo "Checking {$details['month']}...\n";

        $calendarEvents = $page->locator('#calendar .fc-event')
            ->evaluateAll(
                '(elements) => elements.map(
                    element => element.innerText.trim()
                )'
            );

        foreach ($details['events'] as $expectedEvent) {

            $found = false;

            foreach ($calendarEvents as $actualEvent) {
                if (
                    stripos($actualEvent, $expectedEvent) !== false
                ) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                echo "PASS: {$expectedEvent} is visible.\n";
                $passed++;
            } else {
                echo "FAIL: {$expectedEvent} is missing.\n";
                $failed++;
            }
        }

        echo "\n";
    }

    echo "----------------------------------\n";
    echo "Total checks: " . ($passed + $failed) . "\n";
    echo "Passed: $passed\n";
    echo "Failed: $failed\n";

    if ($failed === 0) {
        echo "EC-024 RESULT: PASS\n";
    } else {
        echo "EC-024 RESULT: FAIL\n";
    }

} catch (Throwable $e) {

    echo "EC-024 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";

} finally {
    $browser->close();
}
