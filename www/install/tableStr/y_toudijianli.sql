CREATE TABLE `y_toudijianli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT '名称',
  `age` tinyint(2) NOT NULL DEFAULT '0' COMMENT '年龄',
  `mobile` varchar(20) NOT NULL COMMENT '手机',
  `xueli` varchar(100) NOT NULL COMMENT '学历',
  `address` varchar(200) NOT NULL COMMENT '联系地址',
  `works` text NOT NULL COMMENT '工作经验',
  `pingjia` text NOT NULL COMMENT '自我评介',
  `addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `zhiweiid` int(11) NOT NULL COMMENT '职位ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='投递的简历';
