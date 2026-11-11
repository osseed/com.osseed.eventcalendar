<?php

require __DIR__ . '/vendor/autoload.php';

use Playwright\Playwright;

$baseUrl = rtrim(
    getenv('BASE_URL') ?: 'https://drupal10-clean.civicrm.ddev.site',
    '/'
);

$webIp = getenv('DDEV_WEB_IP') ?: '172.19.0.2';
$calendarUrl = $baseUrl . '/civicrm/showevents?id=1';

$host = parse_url($baseUrl, PHP_URL_HOST);

$args = [
    '--no-sandbox',
    '--ignore-certificate-errors',
];

if ($host && filter_var($webIp, FILTER_VALIDATE_IP)) {
    $args[] = '--host-resolver-rules=MAP ' . $host . ' ' . $webIp;
}

$browser = Playwright::chromium(['args' => $args]);

try {
    $page = $browser->newPage();

    echo "EC-001: Verify event appears on the correct calendar date\n";

    $response = $page->goto($calendarUrl);

    if (!$response || $response->status() !== 200) {
        $status = $response ? $response->status() : 'no response';
        throw new RuntimeException(
            "Calendar did not return HTTP 200. Status: $status"
        );
    }

    $page->locator('#calendar')->waitFor();
    $heading = $page->locator('#calendar .fc-center h2');
    $heading->waitFor();

    echo 'PASS: Calendar opened (' .
        trim($heading->innerText()) . ").\n";

    // Navigate to November 2026.
    $currentHeading = trim($heading->innerText());
    $currentDate = DateTimeImmutable::createFromFormat(
        '!F Y',
        $currentHeading
    );

    if (!$currentDate) {
        throw new RuntimeException(
            "Unexpected calendar heading: $currentHeading"
        );
    }

    $clicks = 0;

    while ($currentDate->format('Y-m') !== '2026-11' && $clicks < 24) {
        $oldHeading = $currentHeading;

        $page->locator('#calendar .fc-next-button')->click();

        $changed = false;

        for ($i = 0; $i < 40; $i++) {
            usleep(200000);
            $currentHeading = trim($heading->innerText());

            if ($currentHeading !== '' && $currentHeading !== $oldHeading) {
                $changed = true;
                break;
            }
        }

        if (!$changed) {
            throw new RuntimeException(
                'Calendar heading did not change after clicking Next.'
            );
        }

        $currentDate = DateTimeImmutable::createFromFormat(
            '!F Y',
            $currentHeading
        );

        if (!$currentDate) {
            throw new RuntimeException(
                "Could not parse calendar heading: $currentHeading"
            );
        }

        $clicks++;
    }

    if ($currentDate->format('Y-m') !== '2026-11') {
        throw new RuntimeException(
            'Could not navigate to November 2026. Current month: ' .
            $currentHeading
        );
    }

    echo "PASS: Calendar navigated to November 2026.\n";

    // Find event ID 7.
    $event = $page->locator(
        '#calendar a[href="/civicrm/event/info?id=7"]'
    );

    if ($event->count() === 0) {
        throw new RuntimeException(
            'Event ID 7 was not rendered in November 2026.'
        );
    }

    echo "PASS: Event ID 7 is visible in November 2026.\n";

    // Determine the event's starting date from its calendar column.
    $eventDate = $event->first()->evaluate(<<<'JS'
(el) => {
    const eventCell = el.closest('td.fc-event-container');

    if (!eventCell) return null;

    const eventRow = eventCell.parentElement;
    const week = el.closest('.fc-row');

    if (!eventRow || !week) return null;

    const dayCells = Array.from(
        week.querySelectorAll('td.fc-day[data-date]')
    );

    if (dayCells.length === 0) return null;

    let startColumn = 0;

    for (const cell of Array.from(eventRow.children)) {
        if (cell === eventCell) break;

        if (cell.tagName === 'TD') {
            startColumn += Number(cell.getAttribute('colspan') || 1);
        }
    }

    if (startColumn < 0 || startColumn >= dayCells.length) return null;

    return dayCells[startColumn].getAttribute('data-date');
}
JS
    );

    if ($eventDate !== '2026-11-12') {
        throw new RuntimeException(
            'Expected event ID 7 on 2026-11-12; detected date: ' .
            var_export($eventDate, true)
        );
    }

    echo "PASS: Event ID 7 starts on November 12, 2026.\n";
    echo "EC-001 RESULT: PASS\n";

} catch (Throwable $e) {
    fwrite(STDERR, "EC-001 RESULT: FAIL\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);

} finally {
    $browser->close();
}
