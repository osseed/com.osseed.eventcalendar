<?php
require __DIR__ . '/test-helpers.php';

$browser = ecBrowser();
$failed = false;

try {
    $page = $browser->newPage();
    $page->goto(ecBaseUrl() . EC_CALENDAR_PATH);
    $page->locator('#calendar')->waitFor();

    echo "EC-015: Filter calendar by one event type\n";

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

    echo "Calendar month: " . ecHeading($page) . "\n";

    $before = $page->locator('#calendar .fc-event')->count();
    echo "Events before filtering: $before\n";

    // Select Fundraiser through the visible Select2 dropdown.
    $page->locator('#s2id_event_selector .select2-choice')->click();
    $page->locator('.select2-results .select2-result')->nth(1)->click();

    $selected = $page->locator('#event_selector')->inputValue();

    echo "Selected event type: $selected\n";

    if ($selected !== 'Fundraiser') {
        throw new RuntimeException('Fundraiser filter was not selected.');
    }

    // Verify Fundraiser events across all three months.
    $months = [
        '2026-11' => 'November 2026',
        '2027-04' => 'April 2027',
        '2027-05' => 'May 2027',
    ];

    foreach ($months as $month => $label) {
        $currentHeading = ecHeading($page);
        $currentDate = ecParseHeading($currentHeading);
        $targetDate = new DateTimeImmutable($month . '-01');
        $navigationClicks = 0;

        while (
            $currentDate->format('Y-m') !== $month
            && $navigationClicks < 24
        ) {
            $selector = $currentDate < $targetDate
                ? '#calendar .fc-next-button'
                : '#calendar .fc-prev-button';

            $page->locator($selector)->click();
            $currentHeading = ecWaitForHeadingChange(
                $page,
                $currentHeading
            );

            $currentDate = ecParseHeading($currentHeading);
            $navigationClicks++;
        }

        if ($currentDate->format('Y-m') !== $month) {
            throw new RuntimeException("Could not navigate to $label.");
        }

        $currentFilter = $page->locator('#event_selector')->inputValue();

        if ($currentFilter !== 'Fundraiser') {
            throw new RuntimeException(
                "Fundraiser filter was not retained in $label."
            );
        }

        $events = $page->locator('#calendar .fc-event');
        $count = $events->count();

        $titles = $events->evaluateAll(
            '(elements) => elements.map(el => el.innerText.trim())'
        );

        echo "\n$label\n";
        echo "Selected filter: $currentFilter\n";
        echo "Displayed events: $count\n";
        echo "Event titles: " . implode(', ', $titles) . "\n";

        if ($count !== 1) {
            $failed = true;
            echo "FAIL: Expected 1 Fundraiser event in $label.\n";
        } else {
            echo "PASS: One Fundraiser event displayed in $label.\n";
        }
    }

    if ($failed) {
        echo "\nEC-015 RESULT: FAIL\n";
    } else {
        echo "\nEC-015 RESULT: PASS\n";
    }

} catch (Throwable $e) {
    echo "\nEC-015 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    $browser->close();
}
