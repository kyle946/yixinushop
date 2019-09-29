CREATE TABLE `y_rollimages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mark` varchar(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='滚动图片';
