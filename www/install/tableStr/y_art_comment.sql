CREATE TABLE `y_art_comment` (
  `id` mediumint(15) NOT NULL AUTO_INCREMENT,
  `userid` mediumint(15) NOT NULL COMMENT '用户id',
  `content` text NOT NULL COMMENT '内容',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '评价时间',
  `artId` int(11) NOT NULL COMMENT '文章ID',
  `pid` int(11) DEFAULT NULL COMMENT '楼层id',
  `cid` int(11) NOT NULL COMMENT '栏目id',
  `status` tinyint(2) NOT NULL DEFAULT '2' COMMENT '状态：1为显示，2为待审核，3为关闭',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COMMENT='用户评论表，文章模块 。';
