CREATE TABLE `y_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL COMMENT '模型名称',
  `mark` varchar(128) NOT NULL COMMENT '标识',
  `issend` tinyint(2) NOT NULL DEFAULT '2' COMMENT '是否支持投稿,2为不支持',
  `isaudit` tinyint(2) NOT NULL DEFAULT '2' COMMENT '稿件是否默认审核,2为未审核',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态,2为不可用',
  `sys` tinyint(2) NOT NULL DEFAULT '2' COMMENT '是否为系统模型,2为否',
  `menuId` int(11) NOT NULL DEFAULT '0' COMMENT '对应的菜单ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mark` (`mark`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='频道模型';
