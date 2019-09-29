CREATE TABLE `y_goods_comment` (
  `id` mediumint(15) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '评论内容',
  `userid` int(11) NOT NULL COMMENT '用户ID',
  `goodsid` int(11) NOT NULL COMMENT '商品ID (goods表的ID)',
  `level` tinyint(2) NOT NULL DEFAULT '1' COMMENT '级别，1为好评，2为中评，3为差评',
  `spec` varchar(255) NOT NULL COMMENT '购买的商品规格 ',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '评论时间',
  `reply` text COMMENT '回复',
  `image1` varchar(255) DEFAULT NULL COMMENT '附图一张',
  `image2` varchar(255) DEFAULT NULL COMMENT '附图一张',
  `status` tinyint(2) NOT NULL DEFAULT '2' COMMENT '1为正常显示，2为待审核，3为关闭',
  `goodsid2` mediumint(15) NOT NULL COMMENT '商品ID 附加表的ID',
  `buytime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '购买时间 。',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='用户评价，商品模块 。';
