CREATE TABLE `y_area_provice` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `provice_id` int(11) unsigned NOT NULL COMMENT '省份id、省份编号',
  `provice_name` char(32) NOT NULL COMMENT '省份名称',
  PRIMARY KEY (`id`),
  UNIQUE KEY `provice_id` (`provice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='省份数据库';
