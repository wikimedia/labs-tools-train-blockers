<?php
/*
 * MIT License
 *
 * Copyright (c) 2020 Taavi Väänänen
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

error_reporting(0);
include __DIR__ . '/../utils.php';

try {
    $date = tbGetTargetDate();
    $connection = tbGetSqlConnection();
    $statement = $connection->prepare('select date, version, task_id, updated_at from ' . TB_TABLE_NAME . ' where date >= ? order by date asc limit 6;');
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
