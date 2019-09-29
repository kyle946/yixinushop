CREATE TABLE `y_channel-article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL COMMENT '栏目id',
  `cid2` int(11) NOT NULL COMMENT '副栏目id',
  `title` varchar(240) NOT NULL COMMENT '标题',
  `picname` varchar(250) NOT NULL COMMENT '缩略图',
  `source` varchar(255) NOT NULL COMMENT '来源',
  `modifytime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `hits` mediumint(15) unsigned NOT NULL DEFAULT '1' COMMENT '点击率',
  `sort` int(10) NOT NULL DEFAULT '999' COMMENT '权重 ，越小越靠 前',
  `user` int(11) NOT NULL COMMENT '创建者',
  `arcrank` char(10) NOT NULL DEFAULT 'o' COMMENT '开放权限,o为全部开放',
  `isdelete` tinyint(2) NOT NULL DEFAULT '2',
  `comments` mediumint(15) NOT NULL COMMENT '评论次数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COMMENT='客户案例';
