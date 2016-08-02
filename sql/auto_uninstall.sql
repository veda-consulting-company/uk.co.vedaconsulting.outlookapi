-- drop  table
DROP TABLE IF EXISTS outlook_civicrm_audit;

-- drop table
DROP TABLE IF EXISTS `outlook_civicrm_user_defaults`;

-- drop custom group for syncing groups
DELETE FROM civicrm_custom_group WHERE name = 'Outlook_Group_Settings';

-- drop option group for syncing groups
DELETE  FROM civicrm_option_group WHERE name = 'sync_to_outlook';

-- drop table that stores information for syncing groups
DROP TABLE IF EXISTS civicrm_value_outlook_group_settings_11;