CREATE TABLE `y_goods_additional` (
  `id` mediumint(15) NOT NULL AUTO_INCREMENT,
  `goodsId` mediumint(15) NOT NULL COMMENT '商品主表ID',
  `shopPrice` decimal(9,2) NOT NULL COMMENT '销售价格',
  `marketPrice` decimal(9,2) NOT NULL COMMENT '市场价格',
  `attribute` text NOT NULL COMMENT '属性，PHP数组，用作详情页读取',
  `attributeStr` varchar(255) NOT NULL COMMENT '属性，字符串，用逗号隔开，用作检索',
  `numbers` int(11) NOT NULL COMMENT '库存',
  `tiaoma` varchar(80) NOT NULL COMMENT '商品条码',
  `warning` int(4) NOT NULL DEFAULT '20' COMMENT '库存警告',
  `sn` varchar(255) NOT NULL COMMENT '编号:表goods的sn后加数字',
  `thumb` varchar(255) NOT NULL COMMENT '缩略图',
  `clickCount` mediumint(15) NOT NULL COMMENT '点击率',
  `addStatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1为上架 ，2为下架',
  `comments` mediumint(15) NOT NULL DEFAULT '0' COMMENT '评论次数',
  `praise` mediumint(15) NOT NULL DEFAULT '2' COMMENT '点赞',
  `weight` decimal(9,3) NOT NULL DEFAULT '100.000' COMMENT '重量，用来计算 运费',
  `salesval` mediumint(15) NOT NULL DEFAULT '0' COMMENT '销量',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `attributeStr` (`attributeStr`)
) ENGINE=MyISAM AUTO_INCREMENT=232 DEFAULT CHARSET=utf8;
