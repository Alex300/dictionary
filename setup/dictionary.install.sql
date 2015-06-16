-- ---
-- Create Tabls
-- ---

CREATE TABLE IF NOT EXISTS `cot_dictionary` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) unsigned DEFAULT 0,
  `parent2` int(10) unsigned DEFAULT 0,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `cot_dictionary_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dictionary` int(10) unsigned DEFAULT 0,
  `parent` int(10) unsigned DEFAULT 0,
  `parent2` int(10) unsigned DEFAULT 0,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (dictionary) REFERENCES cot_dictionary(id),
  KEY `parent` (`parent`),
  KEY `parent2` (`parent2`)
-- FOREIGN KEY (parent)  REFERENCES cot_dictionary_values(id),
--  FOREIGN KEY (parent2) REFERENCES cot_dictionary_values(id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
