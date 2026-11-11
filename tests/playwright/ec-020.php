<?php

require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    echo "EC-020: Event spanning two months\n";
    echo "Event: Boundary test event\n";
    echo "Expected dates: 30 January 2027 to 1 February 2027\n\n";

    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    $months = [
        '2027-01' => 'January 2027',
        '2027-02' => 'February 2027',
    ];

    $results = [];

    foreach ($months as $month => $label) {

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
                "Could not navigate to $label"
            );
        }

        echo "Checking $label...\n";

        $events = $page->locator('#calendar .fc-row')->evaluateAll(
            '(rows) => rows.flatMap(row => {
                const dates = Array.from(
                    row.querySelectorAll(".fc-bg td[data-date]")
                ).map(td => td.getAttribute("data-date"));

                return Array.from(
                    row.querySelectorAll("td.fc-event-container")
                ).map(td => {
                    const column = Array.from(td.parentElement.children)
                        .indexOf(td);

                    return {
                        date: dates[column] || null,
                        duration: Number(
                            td.getAttribute("colspan") || 1
                        ),
                        title: td.innerText.trim()
                    };
                }).filter(event => event.title && event.date);
            })'
        );

        $matches = array_values(array_filter(
            $events,
            fn($event) =>
                trim($event['title']) === 'Boundary test event'
        ));

        $results[$month] = $matches;

        if (count($matches) > 0) {
            echo "Event found in $label.\n";

            foreach ($matches as $event) {
                echo "Displayed starting date: {$event['date']}\n";
                echo "Displayed duration: {$event['duration']} day(s)\n";
            }
        } else {
            echo "Event NOT found in $label.\n";
        }

        echo "\n";
    }

    $januaryFound = count($results['2027-01']) > 0;
    $februaryFound = count($results['2027-02']) > 0;

    if ($januaryFound && $februaryFound) {
        echo "PASS: Boundary test event appears in both month views.\n";
        echo "EC-020 RESULT: PASS\n";
    } else {
        echo "EC-020 RESULT: FAIL\n";

        if (!$januaryFound) {
            echo "Issue: Event missing from January 2027.\n";
        }

        if (!$februaryFound) {
            echo "Issue: Event missing from February 2027.\n";
        }
    }

} catch (Throwable $e) {

    echo "EC-020 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";

} finally {

    $browser->close();
}
