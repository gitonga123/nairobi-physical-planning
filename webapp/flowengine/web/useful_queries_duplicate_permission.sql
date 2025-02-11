INSERT INTO mf_guard_group_permission (group_id, permission_id)
SELECT 80,
    permission_id
FROM mf_guard_group_permission
WHERE group_id = 46;