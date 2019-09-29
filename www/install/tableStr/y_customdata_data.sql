CREATE TABLE `y_customdata_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `dataid` int(11) NOT NULL COMMENT '数据ID ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=latin1 COMMENT='自定义数据表的扩展数据';
