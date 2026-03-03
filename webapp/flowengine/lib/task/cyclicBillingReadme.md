# Permitflow v2.5.0 : Cyclic Billing #

The following are the steps taking by the cycling billing task to generate a new bill:

### If no application exists? Billing for the first time ###

## Step 1: Iterating through all services that are marked as cyclic billing ##
SELECT service_form FROM menus WHERE service_type = 2;

## Step 2: Iterating through all business profiles linked to cyclic billing services
SELECT * FROM mf_user_profile WHERE form_id = {menus.service_form} AND deleted = 0;

## Step 3: Generate cyclic bills 

* Check if invoice unpaid invoice already exists. If it already exists then skip
SELECT * FROM mf_invoice a LEFT JOIN FORM ENTRY b ON a.app_id = b.id 
    WHERE a.paid = 1 
        AND b.service_id = {menus.id}
        AND b.form_id = {mf_user_profile.form_id}
        AND b.business_id = {mf_user_profile.id};

* If no invoice and application exists then create application
INSERT INTO form_entry(service_id, user_id, business_id, form_id, entry_id, application_id, approved, date_of_submission) VALUES({menus.id}, {mf_user_profile.user_id}, {mf_user_profile.id}, {mf_user_profile.form_id, mf_user_profile.entry_id}, {random}, 0, {todays_date});

* Create invoice
- Get application data from form table
SELECT * FROM ap_form_{mf_user_profile.form_id} WHERE id = {mf_user_profile.entry_id};

$dropdown_value = $application_data_results[{menus.service_fee_field}];

- Check which option is selected
SELECT * FROM ap_element_options 
    WHERE a.form_id = {mf_user_profile.form_id}
        AND a.element_id = {menus.service_fee_field}
        AND a.option_id = {$dropdown_value}
        AND a.live = 1

$option_id = $option_data['aeo_id'];
$option_description = $option_data['option_text'];

- Based on the selected option, check fee table and get amount
SELECT total_amount FROM service_fees 
    WHERE service_id = {menus.id}
        AND field_id = {menus.service_fee_field}
        AND option_id = {$option_id}

- Get invoice template 
SELECT * FROM invoicetemplates WHERE applicationform = {menus.service_form}

- Create invoice with amount and description

INSERT INTO mf_invoice(app_id, invoice_number, template_id, total_amount created_at, updated_at, paid) VALUES({form_entry.id}, {random}, {invoicetemplates.id}, {service_fees.total_amount}, {date}, {date}, 1);

INSERT INTO mf_invoice_detail(description, amount, invoice_id) VALUES({$option_description}, {service_fees.total_amount}, {mf_invoice.id});


### If a previous application exists? Billing after expiration ###

## Step 1: Iterating through all services that are marked as cyclic billing ##
SELECT service_form FROM menus WHERE service_type = 2;

## Step 2: Iterating through all business profiles linked to cyclic billing services
SELECT * FROM mf_user_profile WHERE form_id = {menus.service_form} AND deleted = 0;

## Step 3: Fetch existing application
SELECT * FROM form_entry WHERE form_id = {mf_user_profile.form_id} AND entry_id = {mf_user_profile.entry_id} AND business_id = {mf_user_profile.id};

## Step 4: Generate cyclic bills 

* Check if invoice unpaid invoice already exists. If it already exists then skip
SELECT * FROM mf_invoice a LEFT JOIN FORM ENTRY b ON a.app_id = b.id 
    WHERE a.paid = 1 
        AND b.service_id = {menus.id}
        AND b.form_id = {form_entry.form_id}
        AND b.business_id = {mf_user_profile.id};

* Create invoice
- Get application data from form table
SELECT * FROM ap_form_{mf_user_profile.form_id} WHERE id = {mf_user_profile.entry_id};

$dropdown_value = $application_data_results[{menus.service_fee_field}];

- Check which option is selected
SELECT * FROM ap_element_options 
    WHERE a.form_id = {mf_user_profile.form_id}
        AND a.element_id = {menus.service_fee_field}
        AND a.option_id = {$dropdown_value}
        AND a.live = 1

$option_id = $option_data['aeo_id'];
$option_description = $option_data['option_text'];

- Based on the selected option, check fee table and get amount
SELECT total_amount FROM service_fees 
    WHERE service_id = {menus.id}
        AND field_id = {menus.service_fee_field}
        AND option_id = {$option_id}

- Get invoice template 
SELECT * FROM invoicetemplates WHERE applicationform = {menus.service_form}

- Create invoice with amount and description

INSERT INTO mf_invoice(app_id, invoice_number, template_id, total_amount created_at, updated_at, paid) VALUES({form_entry.id}, {random}, {invoicetemplates.id}, {service_fees.total_amount}, {date}, {date}, 1);

INSERT INTO mf_invoice_detail(description, amount, invoice_id) VALUES({$option_description}, {service_fees.total_amount}, {mf_invoice.id});
