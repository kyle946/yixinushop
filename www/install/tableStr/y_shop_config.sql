CREATE TABLE `y_shop_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '名称',
  `mark` varchar(255) NOT NULL COMMENT '标签 变量名',
  `type` varchar(20) NOT NULL DEFAULT 'text' COMMENT '值类型，text 或者 select',
  `seval` varchar(40) DEFAULT NULL COMMENT 'type为select 时的值，用逗号隔开',
  `val` text NOT NULL COMMENT '值',
  `comment` varchar(255) NOT NULL COMMENT '说明',
  `g` int(8) NOT NULL DEFAULT '1',
  `sort` int(5) DEFAULT '500' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
