<?php

require __DIR__ . '/test-helpers.php';

$username = getenv('DRUPAL_USERNAME');
$password = getenv('DRUPAL_PASSWORD');

$eventId = 10;
$eventTitle = 'Group Singing';
$testMarker = '[EC-027 Automated Regression Test]';

if (!$username || !$password) {
    fwrite(STDERR, "Set DRUPAL_USERNAME and DRUPAL_PASSWORD before running.\n");
    exit(1);
}

$browser = ecBrowser();
$descriptionChanged = false;
$originalDescription = null;
$testPassed = false;

try {
    $page = $browser->newPage();

    // STEP 1: Login to Drupal
    echo "STEP 1: Logging in to Drupal...\n";

    $page->goto(ecBaseUrl() . '/user/login');
    $page->locator('#edit-name')->fill($username);
    $page->locator('#edit-pass')->fill($password);
    $page->locator('form#user-login-form input[name="op"]')->click();
    $page->waitForLoadState('load');

    echo "PASS: Drupal login completed.\n";

    // STEP 2: Open CiviCRM Manage Events
    echo "\nSTEP 2: Opening CiviCRM Manage Events...\n";

    $page->goto(ecBaseUrl() . '/civicrm/event/manage');
    $page->waitForLoadState('load');

    $manageText = $page->locator('body')->innerText();

    if (strpos($manageText, 'Manage Events') === false) {
        throw new Exception('Manage Events page was not found.');
    }

    echo "PASS: Manage Events page opened.\n";

    // STEP 3: Open the selected event
    echo "\nSTEP 3: Opening Group Singing event...\n";

    $page->goto(
        ecBaseUrl() . '/civicrm/event/info?id=' . $eventId . '&reset=1'
    );
    $page->waitForLoadState('load');

    $viewText = $page->locator('body')->innerText();

    if (strpos($viewText, $eventTitle) === false) {
        throw new Exception('Group Singing event details were not displayed.');
    }

    echo "PASS: Event details opened successfully.\n";

    // STEP 4: Open the Info and Settings edit form
    echo "\nSTEP 4: Opening event edit form...\n";

    $settingsUrl = ecBaseUrl() .
        '/civicrm/event/manage/settings?reset=1&action=update&id=' . $eventId;

    $page->goto($settingsUrl);
    $page->waitForLoadState('load');

    // Switch tabs to trigger CiviCRM AJAX form loading
    $page->locator('#tab_location a')->click();
    $page->waitForLoadState('load');

    $page->locator('#tab_settings a')->click();
    $page->waitForLoadState('load');

    $page->locator('#title')->fill($eventTitle);

    if ($page->locator('#description')->count() !== 1) {
        throw new Exception('Description field was not found.');
    }

    $originalDescription = $page->locator('#description')->evaluate(
        '(element) => element.value'
    );

    echo "PASS: Event edit form opened.\n";
    echo "PASS: Event title and description fields found.\n";

    // STEP 5: Temporarily update the description
    echo "\nSTEP 5: Updating event description...\n";

    $newDescription = $originalDescription;

    if ($newDescription !== '') {
        $newDescription .= "\n\n";
    }

    $newDescription .= $testMarker;

    $page->locator('#description')->fill($newDescription);

    // Mark before saving so restoration is attempted even if save fails
    $descriptionChanged = true;

    $page->locator('#_qf_EventInfo_upload-bottom')->click();
    $page->waitForLoadState('load');

    echo "PASS: Temporary description submitted.\n";

    // STEP 6: Reopen the form and verify persistence
    echo "\nSTEP 6: Verifying saved description...\n";

    $page->goto($settingsUrl);
    $page->waitForLoadState('load');

    $page->locator('#tab_location a')->click();
    $page->waitForLoadState('load');

    $page->locator('#tab_settings a')->click();
    $page->waitForLoadState('load');

    $savedDescription = $page->locator('#description')->evaluate(
        '(element) => element.value'
    );

    if (strpos($savedDescription, $testMarker) === false) {
        throw new Exception('Updated description was not persisted.');
    }

    echo "PASS: Updated description persisted after reopening.\n";

    $testPassed = true;

} catch (Throwable $e) {
    fwrite(STDERR, "\nFAIL: " . $e->getMessage() . "\n");

} finally {

    // STEP 7: Restore original description
    if ($descriptionChanged && $originalDescription !== null) {
        echo "\nSTEP 7: Restoring original description...\n";

        try {
            $page->goto($settingsUrl);
            $page->waitForLoadState('load');

            $page->locator('#tab_location a')->click();
            $page->waitForLoadState('load');

            $page->locator('#tab_settings a')->click();
            $page->waitForLoadState('load');

            $page->locator('#description')->fill($originalDescription);

            $page->locator('#_qf_EventInfo_upload-bottom')->click();
            $page->waitForLoadState('load');

            // Reopen and verify restoration
            $page->goto($settingsUrl);
            $page->waitForLoadState('load');

            $page->locator('#tab_location a')->click();
            $page->waitForLoadState('load');

            $page->locator('#tab_settings a')->click();
            $page->waitForLoadState('load');

            $restoredDescription = $page->locator('#description')->evaluate(
                '(element) => element.value'
            );

            if ($restoredDescription !== $originalDescription) {
                throw new Exception('Original description restoration could not be verified.');
            }

            echo "PASS: Original description restored and verified.\n";

        } catch (Throwable $restoreError) {
            fwrite(
                STDERR,
                "RESTORATION ERROR: " . $restoreError->getMessage() . "\n"
            );
            $testPassed = false;
        }
    }

    $browser->close();
}

if ($testPassed) {
    echo "\n====================================\n";
    echo "EC-027: PASS\n";
    echo "Event management regression verified.\n";
    echo "====================================\n";
    exit(0);
}

echo "\nEC-027: FAIL\n";
exit(1);
