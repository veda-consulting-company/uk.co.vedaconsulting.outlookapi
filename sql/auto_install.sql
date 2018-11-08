-- This table stores the audit logs
CREATE TABLE IF NOT EXISTS `outlook_civicrm_audit` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `datetime` DATETIME NOT NULL ,
  `entity` VARCHAR(50) NOT NULL ,
  `action` VARCHAR(50) NOT NULL ,
  `request` LONGTEXT NOT NULL ,
  `response` LONGTEXT NOT NULL ,
  PRIMARY KEY (`id`) );

-- This table stores the duplicate contact_id for a contact
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

-- This table stores the mapping of Outlook and CiviCRM field(s) for contact(s)
CREATE TABLE IF NOT EXISTS `outlook_civicrm_additional_contact_field_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outlook_field` varchar(255) NOT NULL,
  `civicrm_field` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Default values for additional contact field mapping(s)
INSERT INTO `outlook_civicrm_additional_contact_field_mapping` (`id`, `outlook_field`, `civicrm_field`) VALUES
(1, 'CompanyName', 'current_employer'),
(2, 'JobTitle', 'job_title'),
(3, 'Email2Address', '2'),
(4, 'Email3Address', '5'),
(5, 'BusinessTelephoneNumber', '5'),
(6, 'Business2TelephoneNumber', '5'),
(7, 'BusinessFaxNumber', '5'),
(8, 'MobileTelephoneNumber', '1'),
(9, 'BusinessAddressStreet', '2'),
(10, 'BusinessAddressPostalCode', '2'),
(11, 'BusinessAddressState', '2'),
(12, 'BusinessAddressCountry', '2'),
(13, 'OtherAddressStreet', '4'),
(14, 'OtherAddressCity', '4'),
(15, 'OtherAddressPostalCode', '4'),
(16, 'OtherAddressState', '4'),
(17, 'OtherAddressCountry', '4'),
(18, 'address_2', '2'),
(19, 'address_1', '1'),
(20, 'HomeTelephoneNumber','1'),
(21, 'Home2TelephoneNumber','1'),
(22, 'HomeFaxNumber','1'),
(23, 'address_4','4'),
(24, 'Assistant_type','6');