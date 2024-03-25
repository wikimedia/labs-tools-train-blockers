<?php
// SPDX-License-Identifier: AGPL-3.0-only
// SPDX-FileCopyrightText: 2020-2024 Taavi Väänänen <hi@taavi.wtf>

error_reporting(0);
include __DIR__ . '/../utils.php';

header('Access-Control-Allow-Origin: *');

try {
    $date = tbGetTargetDate();
    $connection = tbGetSqlConnection();
    $statement = $connection->prepare('select date, version, task_id, status, updated_at from ' . TB_TABLE_NAME . ' where date >= ? order by date asc limit 6;');
    $statement->bind_param('s', $date);
    $statement->execute();

    if ($statement->error) {
        throw new RuntimeException("Failed to retrieve data: $statement->error");
    }

    $rows = [];

    $result = $statement->get_result();
    while ($row = $result->fetch_assoc()) {
        $rows[$row['date']] = $row;
    }

    $result->close();
    $statement->close();
    $connection->close();

    $data = [
        'dated' => $rows,
        'current' => null,
    ];

    if (array_key_exists($date, $rows)) {
        $data['current'] = $rows[$date];
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    die();
} catch (Exception $exception) {
    $errorMessage = $exception->getMessage();
    error_log($exception);

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $errorMessage,
    ]);

    die();
}
