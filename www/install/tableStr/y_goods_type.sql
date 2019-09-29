CREATE TABLE `y_goods_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `virtual` tinyint(1) NOT NULL DEFAULT '2',
  `mark` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
