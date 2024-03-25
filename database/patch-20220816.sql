-- SPDX-License-Identifier: AGPL-3.0-only
-- SPDX-FileCopyrightText: 2022 Taavi Väänänen <hi@taavi.wtf>

-- Patch 2022-08-16
-- Add 'status' column

alter table train_blockers
    add column `status` varchar(16) after task_id;
