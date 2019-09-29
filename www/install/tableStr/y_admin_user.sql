CREATE TABLE `y_admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `passwd` varchar(240) NOT NULL,
  `roleId` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `lastTime` datetime DEFAULT NULL,
  `currentTime` datetime DEFAULT NULL,
  `comment` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
