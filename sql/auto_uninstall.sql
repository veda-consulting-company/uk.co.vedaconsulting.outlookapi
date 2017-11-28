-- drop  table
DROP TABLE IF EXISTS outlook_civicrm_audit;

-- drop table
DROP TABLE IF EXISTS `outlook_civicrm_user_defaults`;

-- drop table
DROP TABLE IF EXISTS `outlook_civicrm_additional_contact_field_mapping`;

-- drop custom group for syncing groups
DELETE FROM civicrm_custom_group WHERE name = 'Outlook_Group_Settings';

-- drop option group for syncing groups
DELETE  FROM civicrm_option_group WHERE name = 'sync_to_outlook';

-- drop table that stores information for syncing groups
DROP TABLE IF EXISTS civicrm_value_outlook_group_settings_11;

-- drop custom group for file extensions(attachments) that needs to be ignored
DELETE FROM civicrm_custom_group WHERE name = 'Outlook_Safe_File_Extensions';

-- drop option group for file extensions(attachments) that needs to be ignored
DELETE  FROM civicrm_option_group WHERE name = 'ignore_file_extensions';

-- drop table that stores information for file extensions(attachments)
DROP TABLE IF EXISTS civicrm_value_outlook_safe_file_extensions_2;

