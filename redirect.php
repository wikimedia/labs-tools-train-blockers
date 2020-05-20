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
include __DIR__ . '/utils.php';

$commit = '';
$errorMessage = 'No error message was found. I have no idea what is going on.';

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

        if (!$taskId) {
            $errorMessage = 'Despite our attempts, no task for this week could be found.';
        }
    } else {
        $errorMessage = 'No row for current week found from database.';
    }

    $result->close();
    $statement->close();
    $connection->close();

    if ($taskId) {
        header('Location: https://phabricator.wikimedia.org/' . $taskId);
        die();
    }
} catch (Exception $exception) {
    $errorMessage = $exception->getMessage();
    error_log($exception);
}

?>

<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/4.4.0/css/bootstrap.css">
        <title>train-blockers task not found</title>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="/">train-blockers</a>
        </div>
    </nav>


    <div class="container mt-3">
        <p>
            <?= $errorMessage ?>
        </p>

        <hr/>
        <a href="https://gerrit.wikimedia.org/r/plugins/gitiles/labs/tools/train-blockers">train-blockers</a>,
        a tool by <a href="https://en.wikipedia.org/wiki/User:Majavah" class="text-muted">Majavah</a>.
    </div>

    </body>
</html>
