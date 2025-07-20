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

CREATE OR REPLACE VIEW form_entry_application_coordinates AS
SELECT fe.id,
    fe.application_id as 'Application' ,
    CASE fe.form_id WHEN 25445 THEN "PLANNING APPLICATION" ELSE 'NA' END as Service,
    f25445.element_198 AS latitude,
    f25445.element_199 AS longitude
FROM form_entry fe
    INNER JOIN ap_form_25445 f25445 ON fe.entry_id = f25445.id
    
WHERE fe.form_id = 25445
    AND f25445.element_198 IS NOT NULL
    AND f25445.element_199 IS NOT NULL
UNION
SELECT fe.id,
    fe.application_id as 'Application' ,
    CASE fe.form_id WHEN 25952 THEN "DEVELOPMENT PERMISSION BUILDING PLAN" ELSE 'NA' END as Service,
    f25952.element_108 AS latitude,
    f25952.element_109 AS longitude
FROM form_entry fe
    INNER JOIN ap_form_25952 f25952 ON fe.entry_id = f25952.id
WHERE fe.form_id = 25952
    AND f25952.element_108 IS NOT NULL
    AND f25952.element_109 IS NOT NULL;
    
CREATE TABLE `system_log_path` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `deleted` TINYINT
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
