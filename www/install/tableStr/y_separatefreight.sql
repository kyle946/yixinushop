CREATE TABLE `y_separatefreight` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deliverId` int(11) NOT NULL,
  `firstWeight` decimal(9,2) NOT NULL COMMENT 'kg',
  `secondWeight` decimal(9,2) NOT NULL COMMENT 'kg',
  `firstPrice` decimal(9,2) NOT NULL,
  `secondPrice` decimal(9,2) NOT NULL,
  `area` text NOT NULL,
  `areaName` varchar(180) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8;
