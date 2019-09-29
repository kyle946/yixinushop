CREATE TABLE `y_jingcheng` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT '名称',
  `actionName` varchar(200) NOT NULL COMMENT '关联的操作名称',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `comment` varchar(254) NOT NULL COMMENT '说明',
  `dtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '上次开启名关闭时间 ',
  `sleeps` int(11) NOT NULL DEFAULT '60' COMMENT '休眠秒数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='PHP执行的后台进程';
