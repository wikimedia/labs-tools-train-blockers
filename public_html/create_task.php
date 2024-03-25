<?php
// SPDX-License-Identifier: AGPL-3.0-only
// SPDX-FileCopyrightText: 2020-2024 Taavi Väänänen <hi@taavi.wtf>

error_reporting(0);

include __DIR__ . '/../utils.php';
$settings = require __DIR__ . '/../settings.php';

$errorMessage = 'No error message was found. I have no idea what is going on.';

try {
    $connection = tbGetSqlConnection();
    $taskId = tbGetCurrentBlockerId( $connection );
    $connection->close();

    if ($taskId) {
        $query = http_build_query([
            'parent' => $taskId,
            'priority' => 'unbreak',
        ]);

        header('Location: ' . $settings['phab_base_url'] . '/maniphest/task/edit/form/' . $settings['phab_form'] . '?' . $query);
        die();
    }
} catch (Exception $exception) {
    $errorMessage = $exception->getMessage();
    error_log($exception);
}

require __DIR__ . '/error.php';
