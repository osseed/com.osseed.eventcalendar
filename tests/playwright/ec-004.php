<?php
require __DIR__ . '/test-helpers.php';

$username = getenv('DRUPAL_USERNAME');
$password = getenv('DRUPAL_PASSWORD');

if (!$username || !$password) {
    fwrite(STDERR, "Set DRUPAL_USERNAME and DRUPAL_PASSWORD before running EC-004.\n");
    exit(2);
}

$browser = ecBrowser();
$page = null;
$original = null;
$settingsReady = false;
$testError = null;
$restoreError = null;

$settingsUrl = ecBaseUrl()
    . '/civicrm/eventcalendarsettings?action=update&id=1&reset=1';

$checkboxSelector = '#EventCalendarSettings #show_past_events';
$submitSelector = '#EventCalendarSettings button[type="submit"]';

try {
    $page = $browser->newPage();

    echo "EC-004: Verify calendar settings can be changed and saved\n";

    // Log in to Drupal.
    $page->goto(ecBaseUrl() . '/user/login');
    $page->locator('#edit-name')->fill($username);
    $page->locator('#edit-pass')->fill($password);
    $page->locator('form#user-login-form input[name="op"]')
        ->click(['noWaitAfter' => true]);

    // Wait for Drupal to finish the login transition.
    $deadline = microtime(true) + 15;
    while (microtime(true) < $deadline) {
        if ($page->locator('form#user-login-form')->count() === 0) {
            break;
        }
        usleep(250000);
    }

    if ($page->locator('form#user-login-form')->count() > 0) {
        $messages = $page->locator('.messages, .messages--error')
            ->allInnerTexts();

        throw new RuntimeException(
            'Drupal login did not complete. Messages: '
            . (count($messages) ? implode(' | ', $messages) : '(none)')
        );
    }

    echo "PASS: Drupal login successful.\n";

    // Open settings and record the starting value.
    $page->goto($settingsUrl);
    $page->locator($checkboxSelector)->waitFor(['timeout' => 15000]);

    $checkbox = $page->locator($checkboxSelector);
    $original = (bool) $checkbox->evaluate('(el) => el.checked');
    $settingsReady = true;

    echo 'Initial Show past events value: '
        . ($original ? 'checked' : 'unchecked') . "\n";

    // Change the value and save it.
    $checkbox->setChecked(!$original);
    $page->locator($submitSelector)->click(['noWaitAfter' => true]);

    // Reopen settings and verify persistence.
    $page->goto($settingsUrl);
    $page->locator($checkboxSelector)->waitFor(['timeout' => 15000]);

    $saved = (bool) $page->locator($checkboxSelector)
        ->evaluate('(el) => el.checked');

    if ($saved !== !$original) {
        throw new RuntimeException(
            'The changed setting did not persist after reopening settings.'
        );
    }

    echo "PASS: Changed setting persisted after reopening settings.\n";

} catch (Throwable $e) {
    $testError = $e->getMessage();

} finally {
    // Restore the value recorded before this test changed it.
    if ($settingsReady && $page !== null && $original !== null) {
        try {
            $page->goto($settingsUrl);
            $page->locator($checkboxSelector)->waitFor(['timeout' => 15000]);

            $checkbox = $page->locator($checkboxSelector);
            $current = (bool) $checkbox->evaluate('(el) => el.checked');

            if ($current !== $original) {
                $checkbox->setChecked($original);
                $page->locator($submitSelector)
                    ->click(['noWaitAfter' => true]);
            }

            // Verify the saved value after reopening the page.
            $page->goto($settingsUrl);
            $page->locator($checkboxSelector)->waitFor(['timeout' => 15000]);

            $restored = (bool) $page->locator($checkboxSelector)
                ->evaluate('(el) => el.checked');

            if ($restored !== $original) {
                $restoreError =
                    'The original checkbox value was not restored.';
            } else {
                echo 'Cleanup: Original recorded value restored and verified ('
                    . ($original ? 'checked' : 'unchecked') . ").\n";
            }

        } catch (Throwable $e) {
            $restoreError = $e->getMessage();
        }
    }

    try {
        $browser->close();
    } catch (Throwable $e) {
        fwrite(STDERR, "WARNING: Browser cleanup failed: "
            . $e->getMessage() . "\n");
    }
}

if ($testError !== null) {
    fwrite(STDERR, "EC-004 TEST: FAIL\n" . $testError . "\n");
}

if ($restoreError !== null) {
    fwrite(STDERR, "EC-004 CLEANUP: FAIL\n" . $restoreError . "\n");
}

if ($testError !== null || $restoreError !== null) {
    fwrite(STDERR, "EC-004 RESULT: FAIL\n");
    exit(1);
}

echo "EC-004 RESULT: PASS\n";
