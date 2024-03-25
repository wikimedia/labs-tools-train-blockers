<?php
// SPDX-License-Identifier: AGPL-3.0-only
// SPDX-FileCopyrightText: 2020-2024 Taavi Väänänen <hi@taavi.wtf>

error_reporting(0);
include __DIR__ . '/../utils.php';

$errorMessage = 'No error message was found. I have no idea what is going on.';

try {
    $connection = tbGetSqlConnection();
    $taskId = tbGetCurrentBlockerId( $connection );
    tbRecordHit( $connection );
    $connection->close();

    if ($taskId) {
        header('Location: https://phabricator.wikimedia.org/' . $taskId);
        die();
    }
} catch (Exception $exception) {
    $errorMessage = $exception->getMessage();
    error_log($exception);
}

require __DIR__ . '/error.php';
