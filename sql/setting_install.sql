CREATE TABLE IF NOT EXISTS `outlook_civicrm_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(64) NOT NULL,
  `setting_value` boolean DEFAULT TRUE,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`) );


 INSERT INTO `outlook_civicrm_setting` (`id`, `setting_name`, `setting_value`, `date_created`) VALUES
(1, 'always_file', 1,'2015-03-24 00:00:00'),
(2, 'remember_duplicate_contacts', 1,'2015-03-24 00:00:00'),
(3, 'prompt_for_case', 1,'2015-03-24 00:00:00');