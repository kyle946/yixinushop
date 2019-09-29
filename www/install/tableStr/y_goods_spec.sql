CREATE TABLE `y_goods_spec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `specValue` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `comments` varchar(255) DEFAULT NULL COMMENT '说明',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
