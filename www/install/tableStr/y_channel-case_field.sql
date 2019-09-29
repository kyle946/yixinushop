CREATE TABLE `y_channel-case_field` (
  `aid` int(11) unsigned NOT NULL DEFAULT '0',
  `images` text,
  `xiaotu` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客户案例扩展字段';
