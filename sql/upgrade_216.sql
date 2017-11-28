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
(5, 'BusinessTelephoneNumber', '2'),
(6, 'Business2TelephoneNumber', '5'),
(7, 'BusinessFaxNumber', '2'),
(8, 'MobileTelephoneNumber', '2'),
(9, 'BusinessAddressStreet', '2'),
(10, 'BusinessAddressPostalCode', '2'),
(11, 'BusinessAddressState', '2'),
(12, 'BusinessAddressCountry', '2'),
(13, 'OtherAddressStreet', '4'),
(14, 'OtherAddressCity', '4'),
(15, 'OtherAddressPostalCode', '4'),
(16, 'OtherAddressState', '4'),
(17, 'OtherAddressCountry', '4');