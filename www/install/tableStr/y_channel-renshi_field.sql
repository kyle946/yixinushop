CREATE TABLE `y_channel-renshi_field` (
  `aid` int(11) unsigned NOT NULL DEFAULT '0',
  `xinzi` varchar(254) DEFAULT NULL,
  `didian` varchar(254) DEFAULT NULL,
  `jinyan` varchar(254) DEFAULT NULL,
  `xueli` varchar(254) DEFAULT NULL,
  `youhuo` text,
  `zhize` text,
  `zige` text,
  `read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '未读的简历',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='人事招聘扩展字段';
