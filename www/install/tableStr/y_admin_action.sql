CREATE TABLE `y_admin_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `colltroller` varchar(120) NOT NULL COMMENT '控制器',
  `action` varchar(120) NOT NULL COMMENT '方法',
  `mtagId` int(11) NOT NULL COMMENT '权限标签ID ，对应y_admin_mtag的id',
  `comment` varchar(200) NOT NULL COMMENT '注释说明',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=276 DEFAULT CHARSET=utf8;
