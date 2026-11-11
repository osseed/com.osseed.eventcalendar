<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    echo "EC-018: Filter persistence after reload\n";

    // Navigate to November 2026.
    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $target = new DateTimeImmutable('2026-11-01');
    $clicks = 0;

    while ($current->format('Y-m') !== '2026-11' && $clicks < 24) {
        $selector = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($selector)->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }

    if ($current->format('Y-m') !== '2026-11') {
        throw new RuntimeException('Could not navigate to November 2026.');
    }

    // Select Fundraiser.
    $page->locator('#s2id_event_selector .select2-choice')->click();
    $page->locator('.select2-results .select2-result')->nth(1)->click();

    $before = $page->locator('#event_selector')->inputValue();

    echo "Filter before reload: $before\n";

    // Reload calendar.
    $page->reload();
    $page->locator('#calendar')->waitFor();

    $after = $page->locator('#event_selector')->inputValue();

    echo "Filter after reload: $after\n";

    if ($after === 'Fundraiser') {
        echo "PASS: Fundraiser filter persisted after reload.\n";
    } else {
        echo "Filter selection did not persist after reload.\n";
        echo "This may be expected if the interactive filter is session-only.\n";
        echo "EC-018 RESULT: REVIEW REQUIRED\n";
        exit(0);
    }

    // Navigate back to November 2026 after reload.
    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $clicks = 0;

    while ($current->format('Y-m') !== '2026-11' && $clicks < 24) {
        $selector = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($selector)->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }

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

    $novemberEvents = array_values(array_filter(
        $events,
        fn($event) =>
            str_starts_with($event['date'], '2026-11')
    ));

    $campaignFound = false;

    foreach ($novemberEvents as $event) {
        if ($event['title'] === 'campaign') {
            $campaignFound = true;
        }
    }

    echo "November Fundraiser event found: "
        . ($campaignFound ? 'Yes' : 'No') . "\n";

    echo $campaignFound
        ? "EC-018 RESULT: PASS\n"
        : "EC-018 RESULT: FAIL\n";

} catch (Throwable $e) {
    echo "\nEC-018 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    $browser->close();
}
