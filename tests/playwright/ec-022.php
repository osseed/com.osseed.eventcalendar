<?php

require __DIR__ . '/test-helpers.php';

$username = getenv('DRUPAL_USERNAME');
$password = getenv('DRUPAL_PASSWORD');

if (!$username || !$password) {
    fwrite(STDERR, "Set DRUPAL_USERNAME and DRUPAL_PASSWORD first.\n");
    exit(2);
}

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    echo "EC-022: Authorized user permission verification\n";

    // Step 1: Login to Drupal.
    $page->goto(ecBaseUrl() . '/user/login');

    $page->locator('#edit-name')->fill($username);
    $page->locator('#edit-pass')->fill($password);

    $page->locator('form#user-login-form input[name="op"]')
        ->click(['noWaitAfter' => true]);

    $deadline = microtime(true) + 15;

    while (microtime(true) < $deadline) {
        if ($page->locator('form#user-login-form')->count() === 0) {
            break;
        }
        usleep(250000);
    }

    if ($page->locator('form#user-login-form')->count() > 0) {
        throw new RuntimeException("Drupal login failed.");
    }

    echo "PASS: Authorized user logged in successfully.\n";

    // Step 2: Open Event Calendar settings.
    $settingsUrl = ecBaseUrl()
        . '/civicrm/eventcalendarsettings?action=update&id=1&reset=1';

    $page->goto($settingsUrl);

    // Step 3: Check access-denied messages.
    $body = $page->locator('body')->innerText();

    if (
        stripos($body, 'Access denied') !== false ||
        stripos($body, 'You are not authorized') !== false
    ) {
        throw new RuntimeException(
            "Authorized user received an access-denied response."
        );
    }

    // Step 4: Verify settings form.
    $settingsForm = $page->locator('#EventCalendarSettings');

    if ($settingsForm->count() === 0) {
        throw new RuntimeException(
            "Event Calendar settings form was not found."
        );
    }

    echo "PASS: Event Calendar settings page is accessible.\n";
    echo "PASS: Settings form is available to the authorized user.\n";
    echo "EC-022 RESULT: PASS\n";

} catch (Throwable $e) {

    echo "EC-022 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";

} finally {
    $browser->close();
}
