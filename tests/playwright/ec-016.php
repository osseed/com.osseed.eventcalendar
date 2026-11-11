<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();
$failed = false;

try {
    $page = $browser->newPage();
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    echo "EC-016: Multiple event types in calendar\n";

    $months = [
        '2026-11' => [
            'label' => 'November 2026',
            'expected' => ['campaign'],
        ],
        '2026-12' => [
            'label' => 'December 2026',
            'expected' => [
                'Artifical Intelligence',
                'QA Event – Updated After Refresh 2026',
            ],
        ],
    ];

    foreach ($months as $month => $config) {
        $heading = ecHeading($page);
        $current = ecParseHeading($heading);
        $target = new DateTimeImmutable($month . '-01');
        $clicks = 0;

        while ($current->format('Y-m') !== $month && $clicks < 24) {
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
                "Could not navigate to {$config['label']}."
            );
        }

        // Map each event to its actual calendar date.
        $events = $page->locator('#calendar .fc-row')->evaluateAll(
            '(rows) => rows.flatMap(row => {
                const dates = Array.from(
                    row.querySelectorAll(".fc-bg td[data-date]")
                ).map(td => td.getAttribute("data-date"));

                return Array.from(
                    row.querySelectorAll("td.fc-event-container")
                ).map(td => ({
                    date: dates[Array.from(td.parentElement.children)
                        .indexOf(td)],
                    title: td.innerText.trim()
                })).filter(event => event.title && event.date);
            })'
        );

        $actual = [];

        foreach ($events as $event) {
            if (substr($event['date'], 0, 7) === $month) {
                $actual[] = $event['title'];
                echo "{$event['date']} - {$event['title']}\n";
            }
        }

        echo "\n{$config['label']}: ";
        echo count($actual) . " events\n";

        foreach ($config['expected'] as $expectedTitle) {
            if (!in_array($expectedTitle, $actual, true)) {
                $failed = true;
                echo "FAIL: Missing event: $expectedTitle\n";
            } else {
                echo "PASS: Found $expectedTitle\n";
            }
        }
    }

    echo $failed
        ? "\nEC-016 RESULT: FAIL\n"
        : "\nEC-016 RESULT: PASS\n";

} catch (Throwable $e) {
    echo "\nEC-016 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    $browser->close();
}
