CREATE TABLE `y_goods_image` (
  `id` mediumint(15) NOT NULL AUTO_INCREMENT,
  `goodsId` mediumint(15) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `imgDesc` varchar(244) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `source` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
