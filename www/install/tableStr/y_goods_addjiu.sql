CREATE TABLE `y_goods_addjiu` (
  `goodsid` mediumint(15) unsigned NOT NULL DEFAULT '0',
  `aid` mediumint(15) DEFAULT NULL,
  `sn` varchar(255) DEFAULT NULL,
  `pingzhong` varchar(254) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='酒 属性记录表';
