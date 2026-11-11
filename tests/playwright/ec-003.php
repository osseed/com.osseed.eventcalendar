
<?php

require __DIR__ . '/vendor/autoload.php';

use Playwright\Playwright;

$browser = Playwright::chromium([
    'args' => [
        '--host-resolver-rules=MAP drupal10-clean.civicrm.ddev.site 172.19.0.2',
        '--ignore-certificate-errors',
    ],
]);

$page = $browser->newPage();

try {
    $page->goto(
        'https://drupal10-clean.civicrm.ddev.site/civicrm/event/info?id=7'
    );

    echo "EC-003: Verify event title and details\n";

    $details = $page->locator('body')->innerText();

    if (stripos($details, 'campaign') === false) {
        throw new RuntimeException(
            'FAIL: Event title campaign was not found.'
        );
    }

    echo "PASS: Event title campaign is displayed.\n";

    if (stripos($details, 'November 12th, 2026 12:00 PM') === false) {
        throw new RuntimeException(
            'FAIL: Expected event start date and time were not found.'
        );
    }

    echo "PASS: Event start date and time are displayed.\n";

    if (stripos($details, 'November 13th, 2026 9:00 PM') === false) {
        throw new RuntimeException(
            'FAIL: Expected event end date and time were not found.'
        );
    }

    echo "PASS: Event end date and time are displayed.\n";

} finally {
    $browser->close();
}
