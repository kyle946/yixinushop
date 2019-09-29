CREATE TABLE `y_goods_attr` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `typeId` smallint(6) NOT NULL,
  `name` varchar(80) NOT NULL,
  `attrValue` varchar(160) NOT NULL,
  `mothod` tinyint(1) DEFAULT '1',
  `mark` varchar(120) NOT NULL,
  `mainpar` tinyint(2) NOT NULL DEFAULT '2' COMMENT '是否为主体参数：1为是，2为否',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8;
