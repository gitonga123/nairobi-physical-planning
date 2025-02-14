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
ALTER TABLE `ap_form_elements` ADD COLUMN  `element_subcounty` int(11) default 0;
ALTER TABLE `mf_invoice` ADD COLUMN `receipt_number` VARCHAR(255) NULL;
ALTER TABLE `application_reference` ADD COLUMN `is_sms_sent` int(1) DEFAULT 0;
