CREATE TABLE `y_activity` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `starttime` timestamp NULL DEFAULT NULL,
  `endtime` timestamp NULL DEFAULT NULL,
  `nums` int(11) NOT NULL DEFAULT '0' COMMENT '参与促销商品的数量',
  `salesType` tinyint(2) NOT NULL DEFAULT '2' COMMENT '促销方式，1为打折，2为减价，3为自定义',
  `zhekou` tinyint(4) NOT NULL DEFAULT '95' COMMENT '折扣数值',
  `jianjia` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT '减价数值',
  `bg1` varchar(200) DEFAULT NULL COMMENT '活动背景图1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='商品促销活动';
