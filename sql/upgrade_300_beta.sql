-- This table stores the mapping of Outlook and CiviCRM field(s) for contact(s)
CREATE TABLE IF NOT EXISTS `outlook_civicrm_additional_contact_field_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outlook_field` varchar(255) NOT NULL,
  `civicrm_field` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Default values for additional contact field mapping(s)
TRUNCATE TABLE `outlook_civicrm_additional_contact_field_mapping`;
INSERT INTO `outlook_civicrm_additional_contact_field_mapping` (`id`, `outlook_field`, `civicrm_field`) VALUES
('', 'CompanyName', 'current_employer'),
('', 'JobTitle', 'job_title'),
('', 'Email2Address', '2'),
('', 'Email3Address', '5'),
('', 'BusinessTelephoneNumber', '2'),
('', 'Business2TelephoneNumber', '5'),
('', 'BusinessFaxNumber', '2'),
('', 'MobileTelephoneNumber', '2'),
('', 'BusinessAddressStreet', '2'),
('', 'BusinessAddressPostalCode', '2'),
('', 'BusinessAddressState', '2'),
('', 'BusinessAddressCountry', '2'),
('', 'OtherAddressStreet', '4'),
('', 'OtherAddressCity', '4'),
('', 'OtherAddressPostalCode', '4'),
('', 'OtherAddressState', '4'),
('', 'OtherAddressCountry', '4');