<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    echo "EC-017: Filter produces no matching events\n";

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
        throw new RuntimeException('Could not navigate to December 2026.');
    }

    echo "Calendar month: " . ecHeading($page) . "\n";

    // Select Fundraiser.
    $page->locator('#s2id_event_selector .select2-choice')->click();
    $page->locator('.select2-results .select2-result')->nth(1)->click();

    $selected = $page->locator('#event_selector')->inputValue();

    echo "Selected filter: $selected\n";

    if ($selected !== 'Fundraiser') {
        throw new RuntimeException('Fundraiser filter was not selected.');
    }

    // Read event dates from FullCalendar's weekly rows.
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

    $decemberEvents = array_values(array_filter(
        $events,
        fn($event) => str_starts_with($event['date'], '2026-12')
    ));

    echo "Fundraiser events in December: "
        . count($decemberEvents) . "\n";

    if (count($decemberEvents) === 0) {
        echo "PASS: No unrelated events displayed.\n";
        echo "PASS: Calendar remains accessible.\n";
        echo "\nEC-017 RESULT: PASS\n";
    } else {
        echo "Unexpected events:\n";

        foreach ($decemberEvents as $event) {
            echo $event['date'] . " - " . $event['title'] . "\n";
        }

        echo "\nEC-017 RESULT: FAIL\n";
    }

} catch (Throwable $e) {
    echo "\nEC-017 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    $browser->close();
}
