ALTER TABLE
    `fee`
ADD
    COLUMN `submission_fee` boolean default false;

ALTER TABLE
    `sf_guard_user_categories`
ADD
    COLUMN `member_database_member_one_single_use` int(11) default 0;

ALTER TABLE `invoicetemplates` ADD COLUMN `qr_content` TEXT NULL;

ALTER TABLE sub_menus ADD COLUMN distribute_equally boolean DEFAULT FALSE;