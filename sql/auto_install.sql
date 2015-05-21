CREATE TABLE IF NOT EXISTS `outlook_civicrm_audit` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `datetime` DATETIME NOT NULL ,
  `entity` VARCHAR(50) NOT NULL ,
  `action` VARCHAR(50) NOT NULL ,
  `request` LONGTEXT NOT NULL ,
  `response` LONGTEXT NOT NULL ,
  PRIMARY KEY (`id`) );