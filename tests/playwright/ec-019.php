<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();

try {
    $page = $browser->newPage();
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    echo "EC-019: Multi-day event verification\n";

    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $target = new DateTimeImmutable('2027-04-01');

    $clicks = 0;

    while ($current->format('Y-m') !== '2027-04' && $clicks < 24) {
        $selector = $current < $target
            ? '#calendar .fc-next-button'
            : '#calendar .fc-prev-button';

        $page->locator($selector)->click();
        $heading = ecWaitForHeadingChange($page, $heading);
        $current = ecParseHeading($heading);
        $clicks++;
    }

    $event = $page->locator('#calendar .fc-event')->filter([
        'hasText' => 'Fall Fundraiser Dinner'
    ]);

    if ($event->count() !== 1) {
        throw new RuntimeException('Event not found exactly once.');
    }

    $details = $event->first()->evaluate(
        '(element) => {
            const cell = element.parentElement;
            const row = cell.parentElement;
            const calendarRow = row.closest(".fc-row");

            const dates = Array.from(
                calendarRow.querySelectorAll(".fc-bg td[data-date]")
            ).map(td => td.getAttribute("data-date"));

            const column = Array.from(row.children).indexOf(cell);
            const duration = Number(cell.getAttribute("colspan") || 1);

            return {
                title: element.innerText.trim(),
                startDate: dates[column] || null,
                duration: duration,
                endDate: dates[column + duration - 1] || null
            };
        }'
    );

    echo "Event: " . $details['title'] . "\n";
    echo "Start date: " . $details['startDate'] . "\n";
    echo "Duration: " . $details['duration'] . " days\n";
    echo "End date: " . $details['endDate'] . "\n";

    if (
        $details['startDate'] === '2027-04-11'
        && $details['endDate'] === '2027-04-13'
        && $details['duration'] === 3
    ) {
        echo "\nEC-019 RESULT: PASS\n";
    } else {
        echo "\nEC-019 RESULT: FAIL\n";
    }

} catch (Throwable $e) {
    echo "\nEC-019 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    $browser->close();
}
