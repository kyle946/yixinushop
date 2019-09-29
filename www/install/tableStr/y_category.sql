CREATE TABLE `y_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `catdesc` varchar(200) DEFAULT NULL,
  `parentId` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT '分类的级别',
  `typeId` int(11) NOT NULL COMMENT '关联的类型id',
  `floor` text NOT NULL COMMENT '楼层设置，使用php数组.',
  `banner` text NOT NULL COMMENT '滚动图片 ，使用PHP数组',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;
