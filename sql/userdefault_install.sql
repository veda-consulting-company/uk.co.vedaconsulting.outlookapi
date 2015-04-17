CREATE TABLE IF NOT EXISTS  `outlook_civicrm_user_defaults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_contact_id` int(11) DEFAULT NULL,
  `dupe_target_contact_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `sid` int(11) NULL,
    
 PRIMARY KEY (`id`),
 FOREIGN KEY (`sid`) REFERENCES `outlook_civicrm_setting`(`id`));