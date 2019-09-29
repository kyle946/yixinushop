CREATE TABLE `y_admin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(120) NOT NULL,
  `name` varchar(120) NOT NULL,
  `parentId` int(11) NOT NULL,
  `mtagId` int(11) NOT NULL COMMENT '权限标签id',
  `sort` int(4) DEFAULT '150',
  `g` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;
