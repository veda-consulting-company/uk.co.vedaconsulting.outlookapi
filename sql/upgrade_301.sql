-- Lets empty the mapping table
TRUNCATE TABLE `outlook_civicrm_additional_contact_field_mapping`;

-- Default values for additional contact field mapping(s)
INSERT INTO `outlook_civicrm_additional_contact_field_mapping` (`id`, `outlook_field`, `civicrm_field`) VALUES
('', 'CompanyName', 'current_employer'),
('', 'JobTitle', 'job_title'),
('', 'Email2Address', '2'),
('', 'Email3Address', '5'),
('', 'HomeTelephoneNumber', '1'),
('', 'Home2TelephoneNumber', '1'),
('', 'HomeFaxNumber', '1'),
('', 'BusinessTelephoneNumber', '2'),
('', 'Business2TelephoneNumber', '2'),
('', 'BusinessFaxNumber', '2'),
('', 'BusinessAddressStreet', '2'),
('', 'BusinessAddressCity', '2'),
('', 'BusinessAddressPostalCode', '2'),
('', 'BusinessAddressState', '2'),
('', 'BusinessAddressCountry', '2'),
('', 'OtherAddressStreet', '4'),
('', 'OtherAddressCity', '4'),
('', 'OtherAddressPostalCode', '4'),
('', 'OtherAddressState', '4'),
('', 'OtherAddressCountry', '4');