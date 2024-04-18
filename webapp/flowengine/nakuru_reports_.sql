CREATE VIEW form_entry_all AS
select f.id,
    f.approved as "Current Stage",
    f.entry_id,
    f.form_id,
    f.user_id,
    f.application_id,
    f.declined,
    f.deleted_status,
    f.previous_submission,
    f.parent_submission,
    f.date_of_submission,
    f.date_of_issue,
    CASE
        WHEN bp.element_41 IS NOT NULL THEN CASE
            bp.element_41
            WHEN 1 THEN "Kuresoi South"
            WHEN 2 THEN "Kuresoi North"
            WHEN 3 THEN "Molo"
            WHEN 10 THEN "Njoro"
            WHEN 9 THEN "Subukia"
            WHEN 8 THEN "Rongai"
            WHEN 7 THEN "Bahati"
            WHEN 6 THEN "Nakuru Town East"
            WHEN 5 THEN "Nakuru Town West"
            WHEN 4 THEN "Gilgil"
            WHEN 11 THEN "Naivasha"
            ELSE "UNDEFINED"
        END
        WHEN pw.element_41 IS NOT NULL THEN CASE
            pw.element_41
            WHEN 1 THEN "Kuresoi South"
            WHEN 2 THEN "Kuresoi North"
            WHEN 3 THEN "Molo"
            WHEN 10 THEN "Njoro"
            WHEN 9 THEN "Subukia"
            WHEN 8 THEN "Rongai"
            WHEN 7 THEN "Bahati"
            WHEN 6 THEN "Nakuru Town East"
            WHEN 5 THEN "Nakuru Town West"
            WHEN 4 THEN "Gilgil"
            WHEN 11 THEN "Naivasha"
            ELSE "UNDEFINED"
        END
        WHEN od.element_8 IS NOT NULL THEN CASE
            od.element_8
            WHEN 1 THEN "Kuresoi South"
            WHEN 2 THEN "Kuresoi North"
            WHEN 3 THEN "Molo"
            WHEN 4 THEN "Njoro"
            WHEN 5 THEN "Subukia"
            WHEN 6 THEN "Rongai"
            WHEN 7 THEN "Bahati"
            WHEN 8 THEN "Nakuru Town East"
            WHEN 9 THEN "Nakuru Town West"
            WHEN 10 THEN "Gilgil"
            WHEN 11 THEN "Naivasha"
            ELSE "UNDEFINED"
        END
        WHEN pl.element_112 IS NOT NULL THEN CASE
            pl.element_112
            WHEN 1 THEN "Kuresoi South"
            WHEN 2 THEN "Kuresoi North"
            WHEN 3 THEN "Molo"
            WHEN 4 THEN "Njoro"
            WHEN 5 THEN "Subukia"
            WHEN 6 THEN "Rongai"
            WHEN 7 THEN "Bahati"
            WHEN 8 THEN "Nakuru Town East"
            WHEN 9 THEN "Nakuru Town West"
            WHEN 10 THEN "Gilgil"
            WHEN 11 THEN "Naivasha"
            ELSE "UNDEFINED"
        END
        WHEN dm.element_117 IS NOT NULL THEN CASE
            dm.element_117
            WHEN 4 THEN "Kuresoi South,"
            WHEN 5 THEN "Kuresoi North,"
            WHEN 6 THEN "Molo,"
            WHEN 7 THEN "Njoro,"
            WHEN 8 THEN "Subukia,"
            WHEN 9 THEN "Rongai,"
            WHEN 10 THEN "Bahati,"
            WHEN 11 THEN "Nakuru Town East,"
            WHEN 12 THEN "Nakuru Town West,"
            WHEN 13 THEN "Gilgil,"
            WHEN 14 THEN "Naivasha"
            ELSE 'UNDEFINED'
        END
        WHEN sa.element_3 IS NOT NULL THEN CASE
            WHEN 1 THEN "Kuresoi South"
            WHEN 2 THEN "Kuresoi North"
            WHEN 3 THEN "Molo"
            WHEN 4 THEN "Njoro"
            WHEN 5 THEN "Subukia"
            WHEN 6 THEN "Rongai"
            WHEN 7 THEN "Bahati"
            WHEN 8 THEN "Nakuru Town East"
            WHEN 9 THEN "Nakuru Town West"
            WHEN 10 THEN "Gilgil"
            WHEN 11 THEN "Naivasha"
            ELSE "UNDEFINED"
        END
    END AS SUB_COUNTY
from ap_forms ap
    LEFT JOIN form_entry f on f.form_id = ap.form_id
    LEFT JOIN ap_form_25952 as bp on f.entry_id = bp.id
    and f.form_id = 25952
    LEFT JOIN ap_form_47349 as pw on f.entry_id = pw.id
    and f.form_id = 47349
    LEFT JOIN ap_form_46092 as od on f.entry_id = od.id
    and f.form_id = 46092
    LEFT JOIN ap_form_25445 as pl on f.entry_id = pl.id
    and f.form_id = 25445
    LEFT JOIN ap_form_38732 as dm on f.entry_id = dm.id
    and f.form_id = 38732
    LEFT JOIN ap_form_39839 as sa on f.entry_id = sa.id
    and f.form_id = 39839
where f.deleted_status = 0
    AND f.parent_submission = 0;