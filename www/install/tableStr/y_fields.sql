CREATE TABLE `y_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT '名称',
  `mark` varchar(200) NOT NULL COMMENT '字段标识',
  `type` varchar(40) NOT NULL COMMENT '字段类型',
  `model` int(11) NOT NULL COMMENT '模型ID',
  `description` varchar(254) NOT NULL COMMENT '描述',
  `val` varchar(200) NOT NULL COMMENT 'select,checkbox选项',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `mark` (`mark`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='模型自定义字段';
