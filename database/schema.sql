-- SPDX-License-Identifier: AGPL-3.0-only
-- SPDX-FileCopyrightText: 2020-2022 Taavi Väänänen <hi@taavi.wtf>

create table train_blockers (
    `date` date primary key not null,
    `version` varchar(16),
    `task_id` varchar(16),
    `status` varchar(16),
    `updated_at` datetime default current_timestamp on update current_timestamp
);

create table train_blockers_hit_counter (
    `date` date primary key not null,
    `hits` int not null
);
