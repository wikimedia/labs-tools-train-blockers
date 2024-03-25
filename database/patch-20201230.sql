-- SPDX-License-Identifier: AGPL-3.0-only
-- SPDX-FileCopyrightText: 2020 Taavi Väänänen <hi@taavi.wtf>

-- Patch 2020-12-30
-- Change train_blockers to have a primary key
-- Create train_blockers_hit_counter

alter table train_blockers
    drop index `date`,
    add primary key (`date`);

create table train_blockers_hit_counter (
    `date` date primary key not null,
    `hits` int not null
);
