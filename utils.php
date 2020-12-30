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
