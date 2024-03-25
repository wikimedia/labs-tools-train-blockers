<?php
// SPDX-License-Identifier: AGPL-3.0-only
// SPDX-FileCopyrightText: 2020-2024 Taavi Väänänen <hi@taavi.wtf>

$settings = require __DIR__ . '/settings.php';

const TB_TABLE_NAME = 'train_blockers';

function tbGetTargetDate() {
    return date('Y-m-d', strtotime('tuesday this week'));
}

/**
 * @return mysqli
 */
function tbGetSqlConnection() {
    global $settings;

    $connection = new mysqli($settings['db_hostname'], $settings['db_username'], $settings['db_password'], $settings['db_database']);

    if ($connection->connect_error) {
        throw new RuntimeException("Connection failed: " . $connection->connect_error);
    }

    return $connection;
}

/**
 * @param $connection mysqli
 * @return string
 * @throws Exception
 */
function tbGetCurrentBlockerId( $connection ) {
    $date = tbGetTargetDate();
    $statement = $connection->prepare('select date, version, task_id, updated_at from ' . TB_TABLE_NAME . ' where date = ? limit 1;');
    $statement->bind_param('s', $date);
    $statement->execute();

    if ($statement->error) {
        throw new RuntimeException("Failed to retrieve data: $statement->error");
    }

    $result = $statement->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $taskId = $row['task_id'];

        if (!$taskId) {
            throw new Exception('No tasks found for this week.');
        }
    } else {
        throw new Exception('No row for current week found from database.');
    }

    $result->close();
    $statement->close();
    $connection->close();

    return $taskId;
}

/**
 * @param $connection mysqli
 */
function tbRecordHit( $connection ) {
    $connection->query( 'insert into train_blockers_hit_counter (date, hits) values (current_date(), 1) on duplicate key update hits = hits + 1' );
}
