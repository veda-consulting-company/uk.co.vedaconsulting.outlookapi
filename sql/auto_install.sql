CREATE TABLE IF NOT EXISTS `outlook_civicrm_audit` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `datetime` DATETIME NOT NULL ,
  `entity` VARCHAR(50) NOT NULL ,
  `action` VARCHAR(50) NOT NULL ,
  `request` LONGTEXT NOT NULL ,
  `response` LONGTEXT NOT NULL ,
  PRIMARY KEY (`id`) );

CREATE TABLE IF NOT EXISTS `outlook_civicrm_user_defaults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_contact_id` int(11) unsigned DEFAULT NULL,
  `dupe_target_contact_id` int(11) unsigned DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_OutlookSourceContact` (`source_contact_id`),
  KEY `fk_OutlookTargetContact` (`dupe_target_contact_id`)
);