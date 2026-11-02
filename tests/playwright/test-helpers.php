<?php

require_once __DIR__ . '/vendor/autoload.php';

use Playwright\Playwright;

const EC_BASE_URL_DEFAULT = 'http://drupal10-clean.civicrm.ddev.site';
const EC_CALENDAR_PATH = '/civicrm/showevents?id=1';
const EC_EVENT_ID = 7;

function ecBaseUrl(): string
{
    return rtrim(getenv('BASE_URL') ?: EC_BASE_URL_DEFAULT, '/');
}

function ecBrowser(string $browserType = 'chromium')
{
    $host = parse_url(ecBaseUrl(), PHP_URL_HOST);
    $ip = getenv('DDEV_WEB_IP') ?: '172.19.0.3';

    $args = [
        '--ignore-certificate-errors',
        '--no-sandbox',
    ];

    if ($host && filter_var($ip, FILTER_VALIDATE_IP)) {
        $args[] = '--host-resolver-rules=MAP ' . $host . ' ' . $ip;
    }

    return match ($browserType) {
        'chromium' => Playwright::chromium(['args' => $args]),

        'firefox' => Playwright::firefox([
            'context' => [
                'ignoreHTTPSErrors' => true,
            ],
        ]),

        'webkit' => Playwright::webkit(['args' => $args]),

        default => throw new InvalidArgumentException(
            "Unsupported browser: $browserType"
        ),
    };
}

function ecHeading($page): string
{
    $locator = $page->locator('#calendar .fc-center h2');
    $locator->waitFor();
    return trim($locator->textContent());
}

function ecWaitForHeadingChange(
    $page,
    string $oldHeading,
    int $timeoutSeconds = 8
): string {
    $deadline = microtime(true) + $timeoutSeconds;

    do {
        usleep(200000);
        $newHeading = ecHeading($page);

        if ($newHeading !== '' && $newHeading !== $oldHeading) {
            return $newHeading;
        }
    } while (microtime(true) < $deadline);

    throw new RuntimeException(
        "Calendar heading did not change from '$oldHeading'."
    );
}

function ecParseHeading(string $heading): DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!F Y', $heading);
    $errors = DateTimeImmutable::getLastErrors();

    if (
        !$date ||
        (is_array($errors) &&
            ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
    ) {
        throw new RuntimeException(
            "Unexpected calendar heading '$heading'; expected format like 'October 2026'."
        );
    }

    return $date;
}

function ecGoToMonthNumber(
    $page,
    int $monthNumber,
    int $maxClicks = 12
): string {
    $heading = ecHeading($page);
    $current = ecParseHeading($heading);
    $clicks = 0;

    while (
        (int) $current->format('n') !== $monthNumber &&
        $clicks < $maxClicks
    ) {
        $old = $heading;
        $page->locator('#calendar .fc-next-button')->click();
        $heading = ecWaitForHeadingChange($page, $old);
        $current = ecParseHeading($heading);
        $clicks++;
    }

    if ((int) $current->format('n') !== $monthNumber) {
        throw new RuntimeException(
            "Could not reach month $monthNumber within $maxClicks next-month clicks; current heading: $heading."
        );
    }

    return $heading;
}
