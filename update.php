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

function tbGetScheduleFromPhabricator() {
    global $settings;

    $url = $settings['phab_base_url'] . "/api/maniphest.search"
         . "?api.token=" . $settings['phab_api_token']
         . "&constraints[subtypes][0]=release&constraints[projects][0]=train&order=custom.release.date";

    return json_decode(file_get_contents($url))->result->data;
}

function tbGetDataFromPhabricator() {
    $records = tbGetScheduleFromPhabricator();
    $found = [];
    $release_date_field = "custom.release.date";
    $version_field = "custom.release.version";

    foreach ($records as $record) {
        # custom.release.date is always Monday 00:00 UTC.
        # The train-blockers service has always used the following Tuesday, so convert here.
        $date = gmdate('Y-m-d', strtotime('Tuesday this week', $record->fields->$release_date_field));

        $found[$date] = [
            'date' => $date,
            'version' => $record->fields->$version_field,
            'task' => "T" . $record->id,
            'status' => $record->fields->status->value,
        ];
    }

    return $found;
}

function tbUpdate() {
    $data = tbGetDataFromPhabricator();
    $connection = tbGetSqlConnection();

    echo json_encode($data);

    $statement = $connection->prepare('insert into ' . TB_TABLE_NAME . ' (date, version, task_id, status) values (?, ?, ?, ?) on duplicate key update version = ?, task_id = ?, status = ?;');

    foreach (array_values($data) as $entry) {
        $statement->bind_param('sssssss', $entry['date'], $entry['version'], $entry['task'], $entry['status'],
                               $entry['version'], $entry['task'], $entry['status']);
        $statement->execute();

        if ($statement->error) {
            throw new RuntimeException("Failed to insert data: $statement->error");
        }
    }

    $statement->close();
    $connection->close();
}

if (PHP_SAPI === 'cli') {
    tbUpdate();
}
