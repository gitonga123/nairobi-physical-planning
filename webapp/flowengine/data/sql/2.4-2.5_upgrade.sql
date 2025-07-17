ALTER TABLE ap_settings ADD COLUMN default_form_theme_id BIGINT DEFAULT '0' NOT NULL;
ALTER TABLE ap_settings ADD COLUMN first_run TINYINT DEFAULT '1' NOT NULL;
DROP TABLE mf_user_profile;
CREATE TABLE mf_user_profile (id BIGINT AUTO_INCREMENT, user_id BIGINT, form_id BIGINT, entry_id BIGINT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted INT DEFAULT '0', PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE mf_user_profile_share (id BIGINT AUTO_INCREMENT, profile_id BIGINT, user_id BIGINT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted INT DEFAULT '0', PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE mf_user_profile_inspection (id BIGINT AUTO_INCREMENT, profile_id BIGINT, reviewer_id BIGINT, form_id BIGINT, entry_id BIGINT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted INT DEFAULT '0', PRIMARY KEY(id)) ENGINE = INNODB;
ALTER TABLE form_entry ADD COLUMN business_id INT;
ALTER TABLE form_entry ADD COLUMN service_id INT;
ALTER TABLE ap_number_generator ADD COLUMN service_id INT;
ALTER TABLE ap_forms ADD COLUMN form_name_hide TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN form_unique_ip_maxcount BIGINT DEFAULT '0' NOT NULL;
ALTER TABLE ap_forms ADD COLUMN form_unique_ip_period INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_forms ADD COLUMN form_resume_subject VARCHAR(255) DEFAULT 'Submit' NOT NULL; 
ALTER TABLE ap_forms ADD COLUMN form_resume_content VARCHAR(255) DEFAULT 'Submit' NOT NULL;
ALTER TABLE ap_forms ADD COLUMN form_resume_from_name VARCHAR(255) DEFAULT 'Submit' NOT NULL;
ALTER TABLE ap_forms ADD COLUMN form_resume_from_email_address VARCHAR(255) DEFAULT 'Submit' NOT NULL;
ALTER TABLE ap_forms ADD COLUMN form_custom_script_enable INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_forms ADD COLUMN form_custom_script_url VARCHAR(255) DEFAULT 'Submit' NOT NULL;
ALTER TABLE ap_forms ADD COLUMN logic_webhook_enable TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN logic_success_enable TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN esl_bcc_email_address TEXT;
ALTER TABLE ap_forms ADD COLUMN esl_replyto_email_address TEXT;
ALTER TABLE ap_forms ADD COLUMN esl_pdf_enable TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN esl_pdf_content TEXT;
ALTER TABLE ap_forms ADD COLUMN esr_bcc_email_address TEXT;
ALTER TABLE ap_forms ADD COLUMN esr_replyto_email_address VARCHAR(255);
ALTER TABLE ap_forms ADD COLUMN esr_pdf_enable TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN esr_pdf_content TEXT;
ALTER TABLE ap_forms ADD COLUMN payment_enable_setupfee TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_setupfee_amount DECIMAL(62, 2) DEFAULT 0.00 NOT NULL;
ALTER TABLE ap_forms ADD COLUMN payment_stripe_live_secret_key VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_stripe_live_public_key VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_stripe_test_secret_key VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_stripe_test_public_key VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_stripe_enable_test_mode TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_stripe_enable_receipt TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_stripe_receipt_element_id BIGINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_authorizenet_live_apiloginid VARCHAR(100);
ALTER TABLE ap_forms ADD COLUMN payment_authorizenet_live_transkey VARCHAR(100);
ALTER TABLE ap_forms ADD COLUMN payment_authorizenet_test_apiloginid VARCHAR(100);
ALTER TABLE ap_forms ADD COLUMN payment_authorizenet_test_transkey VARCHAR(100);
ALTER TABLE ap_forms ADD COLUMN payment_authorizenet_enable_test_mode TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_authorizenet_save_cc_data TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_braintree_live_merchant_id VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_braintree_live_public_key VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_braintree_live_private_key VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_braintree_live_encryption_key TEXT;
ALTER TABLE ap_forms ADD COLUMN payment_braintree_test_merchant_id VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_braintree_test_public_key VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_braintree_test_private_key VARCHAR(50);
ALTER TABLE ap_forms ADD COLUMN payment_braintree_test_encryption_key TEXT;
ALTER TABLE ap_forms ADD COLUMN payment_braintree_enable_test_mode TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_paypal_rest_live_clientid VARCHAR(100);
ALTER TABLE ap_forms ADD COLUMN payment_paypal_rest_live_secret_key VARCHAR(100);
ALTER TABLE ap_forms ADD COLUMN payment_paypal_rest_test_clientid VARCHAR(100);
ALTER TABLE ap_forms ADD COLUMN payment_paypal_rest_test_secret_key VARCHAR(100);
ALTER TABLE ap_forms ADD COLUMN payment_paypal_rest_enable_test_mode TINYINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_discount_max_usage BIGINT DEFAULT '0';
ALTER TABLE ap_forms ADD COLUMN payment_discount_expiry_date VARCHAR(100);
ALTER TABLE ap_element_options CHANGE `option` option_text TEXT;
ALTER TABLE ap_form_elements ADD COLUMN element_is_readonly INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_is_private INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_enable_placeholder INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_text_default_type VARCHAR(6);
ALTER TABLE ap_form_elements ADD COLUMN element_text_default_length INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_text_default_random_type VARCHAR(8);
ALTER TABLE ap_form_elements ADD COLUMN element_text_default_prefix VARCHAR(50);
ALTER TABLE ap_form_elements ADD COLUMN element_text_default_case VARCHAR(1);
ALTER TABLE ap_form_elements ADD COLUMN element_type VARCHAR(50);
ALTER TABLE ap_form_elements ADD COLUMN element_position INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_default_value TEXT;
ALTER TABLE ap_form_elements ADD COLUMN element_constraint VARCHAR(255);
ALTER TABLE ap_form_elements ADD COLUMN element_total_child INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_css_class VARCHAR(255) DEFAULT '' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_range_min BIGINT UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_range_max BIGINT UNSIGNED DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_range_limit_by CHAR(1) NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_status INT DEFAULT '2' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_choice_columns INT DEFAULT '1' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_choice_has_other INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_choice_other_label TEXT;
ALTER TABLE ap_form_elements ADD COLUMN element_choice_limit_rule VARCHAR(12);
ALTER TABLE ap_form_elements ADD COLUMN element_choice_limit_qty BIGINT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_choice_limit_range_min BIGINT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_choice_limit_range_max BIGINT DEFAULT '0' NOT NULL;
ALTER TABLE ap_form_elements ADD COLUMN element_choice_max_entry BIGINT DEFAULT '0' NOT NULL;
ALTER TABLE menus ADD COLUMN service_type INT NOT NULL DEFAULT 1;
ALTER TABLE menus ADD COLUMN service_form INT NULL DEFAULT 0;
ALTER TABLE menus ADD COLUMN service_fee_field INT NULL DEFAULT 0;
ALTER TABLE menus ADD COLUMN service_number TEXT NOT NULL;
CREATE TABLE service_fees (id INT AUTO_INCREMENT, service_id INT NOT NULL, field_id INT NOT NULL, option_id INT NOT NULL, total_amount FLOAT(18, 2), PRIMARY KEY(id)) ENGINE = INNODB;
ALTER TABLE ap_permissions ADD COLUMN edit_report TINYINT DEFAULT '0' NOT NULL;
ALTER TABLE ap_email_logic ADD COLUMN custom_replyto_email VARCHAR(255) DEFAULT '' NOT NULL;
ALTER TABLE ap_email_logic ADD COLUMN custom_bcc VARCHAR(255) DEFAULT '' NOT NULL;
ALTER TABLE ap_email_logic ADD COLUMN custom_pdf_enable INT DEFAULT '0' NOT NULL;
ALTER TABLE ap_email_logic ADD COLUMN custom_pdf_content TEXT;
ALTER TABLE ap_email_logic ADD COLUMN delay_notification_until_paid INT DEFAULT '0' NOT NULL;

CREATE TABLE ap_webhook_logic_conditions (wlc_id INT AUTO_INCREMENT, form_id INT, target_rule_id INT, element_name VARCHAR(50) NOT NULL, rule_condition VARCHAR(15) NOT NULL, rule_keyword VARCHAR(255) NOT NULL, PRIMARY KEY(wlc_id, form_id, target_rule_id)) ENGINE = INNODB;
CREATE TABLE ap_webhook_options (awo_id INT AUTO_INCREMENT, form_id INT, rule_id INT, rule_all_any VARCHAR(3) NOT NULL, webhook_url TEXT NOT NULL, webhook_method VARCHAR(4) NOT NULL, webhook_format VARCHAR(10) NOT NULL, webhook_raw_data TEXT NOT NULL, enable_http_auth INT, http_username VARCHAR(255) NOT NULL, http_password VARCHAR(255) NOT NULL, enable_custom_http_headers TINYINT, custom_http_headers TEXT NOT NULL, delay_notification_until_paid TINYINT, PRIMARY KEY(awo_id, form_id, rule_id, enable_http_auth, enable_custom_http_headers, delay_notification_until_paid)) ENGINE = INNODB;
CREATE TABLE ap_webhook_parameters (awp_id INT AUTO_INCREMENT, form_id INT, rule_id INT, param_name TEXT NOT NULL, param_value TEXT NOT NULL, PRIMARY KEY(awp_id, form_id, rule_id)) ENGINE = INNODB;

CREATE TABLE ap_success_logic_conditions (slc_id INT AUTO_INCREMENT, form_id INT, target_rule_id INT, element_name VARCHAR(50) NOT NULL, rule_condition VARCHAR(15) NOT NULL, rule_keyword VARCHAR(255) NOT NULL, PRIMARY KEY(slc_id, form_id, target_rule_id)) ENGINE = INNODB;
CREATE TABLE ap_success_logic_options (slo_id INT AUTO_INCREMENT, form_id INT, rule_id INT, rule_all_any VARCHAR(3) NOT NULL, success_type VARCHAR(11) NOT NULL, success_message TEXT NOT NULL, redirect_url TEXT NOT NULL, PRIMARY KEY(slo_id, form_id, rule_id)) ENGINE = INNODB;

ALTER TABLE mf_invoice_detail MODIFY created_at DATETIME;
ALTER TABLE mf_invoice_detail MODIFY updated_at DATETIME;

ALTER TABLE permits ADD COLUMN expiry_type INT DEFAULT 0;
ALTER TABLE permits ADD COLUMN expiry_trigger INT DEFAULT 0;

ALTER TABLE saved_permit ADD COLUMN sent INT DEFAULT '0' NOT NULL;
ALTER TABLE saved_permit ADD COLUMN expiry_trigger INT DEFAULT '0' NOT NULL;

ALTER TABLE ap_form_filters ADD COLUMN incomplete_entries TINYINT DEFAULT '0' NOT NULL;
ALTER TABLE ap_column_preferences ADD COLUMN incomplete_entries TINYINT DEFAULT '0' NOT NULL;

CREATE TABLE penalty (id INT AUTO_INCREMENT, permit_id INT, invoice_id INT, template_id VARCHAR(255) DEFAULT NULL, paid INT, amount VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE penalty_template (id INT AUTO_INCREMENT, description VARCHAR(255) DEFAULT '' NOT NULL, template_id INT, trigger_type INT, trigger_period VARCHAR(255) DEFAULT NULL, penalty_type INT, penalty_amount VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) ENGINE = INNODB;

update ap_settings set first_run = 0;
update sf_guard_user_categories set formid = 0;

ALTER TABLE ap_form_filters ADD COLUMN expiration_type INT;

ALTER TABLE permits ADD COLUMN expiration_type INT;
ALTER TABLE invoicetemplates ADD COLUMN expiration_type INT;

CREATE TABLE more_fees (id INT AUTO_INCREMENT, service_id INT NOT NULL, field_id INT NOT NULL, fee_title TEXT, PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE service_more_fees (id INT AUTO_INCREMENT, fee_id INT NOT NULL, service_id INT NOT NULL, field_id INT NOT NULL, option_id INT NOT NULL, total_amount FLOAT(18, 2), PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE service_other_fees (id INT AUTO_INCREMENT, service_id INT NOT NULL, service_code TEXT, amount FLOAT(18, 2), PRIMARY KEY(id)) ENGINE = INNODB;

alter table service_other_fees add column as_first_submission_fee INT DEFAULT '0';
alter table service_other_fees add column as_renewal_fee INT DEFAULT '0';

CREATE TABLE service_inspections (id INT AUTO_INCREMENT, service_id INT NOT NULL, stage_id INT NOT NULL, department_id INT NOT NULL, PRIMARY KEY(id)) ENGINE = INNODB;
ALTER TABLE mf_user_profile_inspection ADD COLUMN department_id INT;
ALTER TABLE mf_user_profile_inspection ADD COLUMN service_id INT;
ALTER TABLE mf_user_profile_inspection ADD COLUMN stage_id INT;

CREATE TABLE inspections (id INT AUTO_INCREMENT, application_id INT NOT NULL, stage_id INT NOT NULL, department_id INT NOT NULL, reviewer_id INT NOT NULL, task_id INT NOT NULL, PRIMARY KEY(id)) ENGINE = INNODB;

ALTER TABLE form_entry ADD COLUMN form_data LONGTEXT;
ALTER TABLE mf_user_profile ADD COLUMN form_data LONGTEXT;
CREATE TABLE multiplier_fees (id INT AUTO_INCREMENT, service_id INT NOT NULL, field_id INT NOT NULL, multiplier_amount DOUBLE, PRIMARY KEY(id)) ENGINE = INNODB;