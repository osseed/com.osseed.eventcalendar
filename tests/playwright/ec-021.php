<?php

require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    echo "EC-021: Midnight start-time handling\n";
    echo "Event: midnight (ID: 19)\n\n";

    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    // Navigate to March 2027.
    $heading = ecHeading($page);
    $target = new DateTimeImmutable('2027-03-01');
    $current = ecParseHeading($heading);
    $clicks = 0;

    while ($current->format('Y-m') !== '2027-03' && $clicks < 24) {

        $selector = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($selector)->click();

        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);

        $clicks++;
    }

    if ($current->format('Y-m') !== '2027-03') {
        throw new RuntimeException('Could not navigate to March 2027.');
    }

    echo "Opened March 2027 calendar.\n";

    // Find the event in the calendar.
    $event = $page->locator('#calendar .fc-event')->filter([
        'hasText' => 'midnight'
    ]);

    $count = $event->count();

    if ($count === 0) {
        echo "FAIL: Midnight event not found on calendar.\n";
        echo "EC-021 RESULT: FAIL\n";
    } else {

        $eventText = trim($event->first()->textContent());

        echo "Event found on calendar.\n";
        echo "Displayed event text: $eventText\n";

        // Check the calendar event's date position.
        $eventDate = $event->first()->evaluate(
            '(element) => {
                const cell = element.closest("td.fc-event-container");
                if (!cell) return null;

                const row = cell.closest(".fc-row");
                if (!row) return null;

                const dates = Array.from(
                    row.querySelectorAll(".fc-bg td[data-date]")
                ).map(td => td.getAttribute("data-date"));

                const column = Array.from(cell.parentElement.children)
                    .indexOf(cell);

                return dates[column] || null;
            }'
        );

        echo "Calendar date: " . ($eventDate ?? 'Not detected') . "\n";

        $dateCorrect = $eventDate === '2027-03-24';

        if ($dateCorrect) {
            echo "PASS: Event appears on the correct date.\n";
            echo "EC-021 RESULT: PASS\n";
        } else {
            echo "FAIL: Event date is incorrect or could not be verified.\n";
            echo "EC-021 RESULT: FAIL\n";
        }
    }

} catch (Throwable $e) {

    echo "EC-021 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";

} finally {
    $browser->close();
}
