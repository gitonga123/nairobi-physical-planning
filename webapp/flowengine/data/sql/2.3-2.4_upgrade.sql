ALTER TABLE ap_settings ADD COLUMN organisation_help TEXT;
ALTER TABLE ap_settings ADD COLUMN organisation_sidebar TEXT;
ALTER TABLE ap_form_elements ADD COLUMN element_field_error_message TEXT;
ALTER TABLE ap_form_elements ADD COLUMN element_remote_server_field VARCHAR(255);
ALTER TABLE ap_form_elements ADD COLUMN element_remote_post VARCHAR(255);

ALTER TABLE ap_form_elements ADD COLUMN element_remote_post VARCHAR(255);
ALTER TABLE ap_form_elements ADD COLUMN element_remote_post VARCHAR(255);
ALTER TABLE ap_form_elements ADD COLUMN element_remote_post VARCHAR(255);
ALTER TABLE ap_form_elements ADD COLUMN element_remote_post VARCHAR(255);
ALTER TABLE ap_form_elements ADD COLUMN element_remote_post VARCHAR(255);

ALTER TABLE ap_form_elements ADD COLUMN element_price_class VARCHAR(255);
ALTER TABLE ap_form_elements ADD COLUMN element_jsondef TEXT;
ALTER TABLE ap_form_elements ADD COLUMN element_validator_class VARCHAR(255);

CREATE TABLE form_entry_archive (id BIGINT, form_id INT, entry_id INT, user_id INT, circulation_id INT, approved INT DEFAULT '0', application_id VARCHAR(255) DEFAULT '0', declined INT DEFAULT '0', deleted_status INT DEFAULT '0' NOT NULL, saved_permit TEXT, previous_submission BIGINT NOT NULL, parent_submission BIGINT NOT NULL, date_of_submission VARCHAR(250) NOT NULL, date_of_response VARCHAR(250) NOT NULL, date_of_issue VARCHAR(250) NOT NULL, observation TEXT NOT NULL, pdf_path VARCHAR(250) NOT NULL, PRIMARY KEY(id)) ENGINE = INNODB;

ALTER TABLE permits ADD COLUMN page_type INT;
ALTER TABLE permits ADD COLUMN page_orientation INT;
ALTER TABLE permits ADD COLUMN qr_content TEXT;
