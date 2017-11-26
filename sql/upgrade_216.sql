-- This table stores the mapping of Outlook and CiviCRM field(s) for contact(s)
CREATE TABLE `outlook_civicrm_additional_contact_field_mapping` (
  `id` int(11) NOT NULL,
  `outlook_field` varchar(255) NOT NULL,
  `civicrm_field` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;