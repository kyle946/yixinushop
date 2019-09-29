CREATE TABLE `y_config2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `mark` varchar(200) NOT NULL,
  `val` text NOT NULL,
  `type` varchar(120) NOT NULL,
  `seval` varchar(120) NOT NULL,
  `g` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='其他配置参数';
