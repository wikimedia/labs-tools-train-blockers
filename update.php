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

const TB_TRAIN_SCHEDULE_REGEX = "/{{#invoke:Deployment schedule\|row\n    \|when=([A-Z0-9 -:]+)\n    \|length=[0-9]+\n    \|window=([^\n]+)\n    \|who=[^\n]+\n    \|what=[^\n]+\n{{DeployOneWeekMini\|([0-9a-z.]+-wmf.[0-9]+)->[^\n]+\n[^\n]+\n\* '''Blockers: {{phabricator\|(T[0-9]+)/i";
const TB_WIKITECH_BASE = 'https://wikitech.wikimedia.org';
const TB_WIKITECH_API_URL = '/w/api.php?action=parse&format=json&page=Deployments&prop=wikitext';

function tbGetScheduleFromWikitech() {
    $star = '*';
    return json_decode(file_get_contents(TB_WIKITECH_BASE . TB_WIKITECH_API_URL))
        ->parse->wikitext->$star;
}

function tbGetData($targetDate) {
    $wikitext = tbGetScheduleFromWikitech();

    if (preg_match_all(TB_TRAIN_SCHEDULE_REGEX, $wikitext, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            if (substr($match[1], 0, 10) === $targetDate) {
                return [
                    'date' => $targetDate,
                    'version' => $match[3],
                    'task' => $match[4],
                ];
            }
        }
    }

    return [
        'date' => $targetDate,
        'version' => null,
        'task' => null,
    ];
}

function tbUpdate() {
    $date = tbGetTargetDate();
    $data = tbGetData($date);
    $connection = tbGetSqlConnection();

    var_dump($data);

    $statement = $connection->prepare('insert into ' . TB_TABLE_NAME . ' (date, version, task_id) values (?, ?, ?) on duplicate key update version = ?, task_id = ?;');
    $statement->bind_param('sssss', $data['date'], $data['version'], $data['task'], $data['version'], $data['task']);
    $statement->execute();

    if ($statement->error) {
        throw new RuntimeException("Failed to insert data: $statement->error");
    }

    $statement->close();
    $connection->close();
}

if (PHP_SAPI === 'cli') {
    tbUpdate();
}