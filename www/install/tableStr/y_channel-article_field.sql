CREATE TABLE `y_channel-article_field` (
  `aid` int(11) unsigned NOT NULL DEFAULT '0',
  `content` longtext,
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章列表扩展字段';
