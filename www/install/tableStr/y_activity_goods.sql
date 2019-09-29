CREATE TABLE `y_activity_goods` (
  `id` mediumint(15) NOT NULL AUTO_INCREMENT,
  `goodsid` mediumint(15) NOT NULL COMMENT '商品ID',
  `aprice` decimal(9,2) DEFAULT NULL COMMENT '商品促销价格',
  `aid` int(8) DEFAULT NULL COMMENT '活动ID',
  `xiangou` int(11) NOT NULL DEFAULT '0' COMMENT '每人限购，0为不限购',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COMMENT='参与促销活动的商品';
