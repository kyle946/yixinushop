CREATE TABLE `y_goods_brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `logo` varchar(230) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `link` varchar(254) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
