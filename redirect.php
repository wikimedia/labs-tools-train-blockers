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

include __DIR__ . '/utils.php';

try {
    $taskId = '';

    $date = tbGetTargetDate();
    $connection = tbGetSqlConnection();
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
    }

    $result->close();
    $statement->close();
    $connection->close();

    if ($taskId) {
        header('Location: https://phabricator.wikimedia.org/' . $taskId);
        die();
    }
} catch (Exception $exception) {
    error_log($exception);
}

?>

<html lang="en">
    <head>
        <title>train-blockers task not found</title>
    </head>
    <body>
        A task for this week could not be found. Try again later or contact <a href="https://en.wikipedia.org/wiki/User talk:Majavah">User:Majavah</a>.
    </body>
</html>
