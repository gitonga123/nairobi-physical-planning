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
element_subcounty

ALTER TABLE `ap_form_elements` ADD COLUMN  `element_subcounty` int(11) default 0;
ALTER TABLE `menus` ADD COLUMN  `app_queuing` varchar(255) NULL;
ALTER TABLE `sub_menus` ADD COLUMN  `app_queuing` varchar(255) NULL;
ALTER TABLE `menus` ADD COLUMN  `category_id` int(11) default 0;
ALTER TABLE `saved_permit` ADD COLUMN  `is_activated` int(11) default 0;
ALTER TABLE `saved_permit` ADD COLUMN  `signed_by` int(11) default 0;
ALTER TABLE `form_entry` ADD COLUMN  `merchant_identifier` varchar(211) NULL;
ALTER TABLE `ap_forms` ADD COLUMN  `payment_onsubmission` varchar(211) NULL;
ALTER TABLE `ap_forms` ADD COLUMN  `payment_merchant_identifier` varchar(211) NULL;
ALTER TABLE `task` ADD COLUMN  `group_id` int(11) default 0;

ALTER TABLE ap_form_elements ADD COLUMN `element_plotratioreason` varchar(255) default 0;
ALTER TABLE ap_form_elements ADD COLUMN `element_file_qr_page_position` varchar(255) default 0;
ALTER TABLE ap_form_elements ADD COLUMN `element_file_qr_all_pages` varchar(211) default 0;
ALTER TABLE ap_form_elements ADD COLUMN `element_file_qr_users` varchar(255) default 0;
ALTER TABLE ap_form_elements ADD COLUMN `element_file_qr_page_position` varchar(255) default 0;
ALTER TABLE ap_form_elements ADD COLUMN `element_file_qr_all_pages` varchar(211) default 0;