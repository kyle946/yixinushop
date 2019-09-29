CREATE TABLE `y_customdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mark` varchar(200) DEFAULT NULL COMMENT '标签',
  `title` varchar(200) CHARACTER SET utf8 NOT NULL COMMENT '标题',
  `notes` varchar(254) CHARACTER SET utf8 NOT NULL COMMENT '备注',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1为可用',
  `tablename` varchar(200) CHARACTER SET utf8 NOT NULL COMMENT '数据表名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COMMENT='自定义数据';
