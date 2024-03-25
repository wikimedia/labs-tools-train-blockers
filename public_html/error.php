<?php
// SPDX-License-Identifier: AGPL-3.0-only
// SPDX-FileCopyrightText: 2020-2024 Taavi Väänänen <hi@taavi.wtf>

http_response_code(500);
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
