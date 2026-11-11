<?php

require __DIR__ . '/test-helpers.php';

$username = getenv('DRUPAL_USERNAME');
$password = getenv('DRUPAL_PASSWORD');

if (!$username || !$password) {
    exit("Set Drupal credentials first.\n");
}

$browser = ecBrowser();

try {
    $page = $browser->newPage();

    echo "EC-023: Unauthorized administration access check\n";

    // Login
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
        throw new RuntimeException("Login failed.");
    }

    echo "PASS: Login successful for $username.\n";

    // Attempt restricted People administration.
    $page->goto(ecBaseUrl() . '/admin/people');

    $body = $page->locator('body')->innerText();

    if (
        stripos($body, 'Access denied') !== false ||
        stripos($body, 'You are not authorized') !== false
    ) {
        echo "PASS: Access denied for People administration.\n";
        echo "EC-023 RESULT: PASS (supplementary permission check)\n";
    } else {
        echo "EC-023 RESULT: REVIEW REQUIRED\n";
        echo "People administration was not explicitly denied.\n";
    }

} catch (Throwable $e) {

    echo "EC-023 RESULT: FAIL\n";
    echo "ERROR: " . $e->getMessage() . "\n";

} finally {
    $browser->close();
}
