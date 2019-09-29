CREATE TABLE `y_deliverys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `description` varchar(240) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `expressDoc` text NOT NULL COMMENT '快递单区域内的div html信息',
  `ex_width` int(8) NOT NULL DEFAULT '950' COMMENT '快递单宽度',
  `ex_height` int(8) NOT NULL DEFAULT '450' COMMENT '快递单高度',
  `ex_background` varchar(255) NOT NULL COMMENT '快递单背景图片',
  `sendout` text NOT NULL COMMENT '发货信息(PHP数组)',
  `ex_com` varchar(120) NOT NULL COMMENT '快递公司代码(快递100)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
