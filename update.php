<?php
// SPDX-License-Identifier: AGPL-3.0-only
// SPDX-FileCopyrightText: 2020-2024 Taavi Väänänen <hi@taavi.wtf>

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
