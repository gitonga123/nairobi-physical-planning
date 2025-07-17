ALTER TABLE `fee`
ADD COLUMN `submission_fee` boolean default false;
ALTER TABLE `sf_guard_user_categories`
ADD COLUMN `member_database_member_one_single_use` int(11) default 0;
ALTER TABLE `invoicetemplates`
ADD COLUMN `qr_content` TEXT NULL;
ALTER TABLE sub_menus
ADD COLUMN distribute_equally boolean DEFAULT FALSE;
ALTER TABLE `ap_form_elements`
ADD COLUMN `element_subcounty` int(11) default 0;
ALTER TABLE `mf_invoice`
ADD COLUMN `receipt_number` VARCHAR(255) NULL;
---sub counties ---
CREATE TABLE `subcounties` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `uuid` VARCHAR(36) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uuid_UNIQUE` (`uuid`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
--- wards---
CREATE TABLE `wards` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `uuid` VARCHAR(36) NOT NULL,
    `subcounty_id` INT(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uuid_UNIQUE` (`uuid`),
    KEY `fk_subcounty_idx` (`subcounty_id`),
    CONSTRAINT `fk_ward_subcounty` FOREIGN KEY (`subcounty_id`) REFERENCES `subcounties` (`id`) ON DELETE
    SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
