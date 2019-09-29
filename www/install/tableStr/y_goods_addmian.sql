CREATE TABLE `y_goods_addmian` (
  `goodsid` mediumint(15) unsigned NOT NULL DEFAULT '0',
  `aid` mediumint(15) DEFAULT NULL,
  `sn` varchar(255) DEFAULT NULL,
  `pieliao` varchar(254) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='面食 属性记录表';
