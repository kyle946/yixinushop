CREATE TABLE `y_column` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `type` tinyint(2) NOT NULL COMMENT '栏目类型：1为频道模型内容，取model字段；3为封面内容，取content字段；2为外部链接，取url字段',
  `mark` varchar(200) NOT NULL COMMENT '栏目标识id',
  `status` tinyint(2) NOT NULL DEFAULT '1',
  `model` int(11) NOT NULL COMMENT '频道模型ID ',
  `location` varchar(10) NOT NULL DEFAULT 'u' COMMENT '栏目摆放的位置',
  `goodstype` tinyint(8) NOT NULL COMMENT '商品分类ID',
  `goodsactivity` int(11) DEFAULT NULL COMMENT '商品促销活动ID',
  `url` varchar(250) NOT NULL COMMENT '链接',
  `content` text NOT NULL COMMENT '栏目内容',
  `tplContent` varchar(222) NOT NULL COMMENT '栏目内容模板',
  `tplList` varchar(222) NOT NULL COMMENT '栏目列表模板',
  `tplArticle` varchar(222) NOT NULL COMMENT '栏目下的文章模板',
  `description` varchar(250) NOT NULL COMMENT '栏目描述',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父栏目ID',
  `issend` tinyint(2) NOT NULL DEFAULT '2' COMMENT '是否支持投稿,1为支持',
  `seotitle` varchar(200) NOT NULL COMMENT 'seo标题',
  `keyword` varchar(255) NOT NULL COMMENT '关键字',
  `sort` int(11) NOT NULL DEFAULT '200',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COMMENT='栏目';
