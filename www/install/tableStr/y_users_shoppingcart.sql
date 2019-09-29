CREATE TABLE `y_users_shoppingcart` (
  `id` mediumint(15) NOT NULL AUTO_INCREMENT,
  `userid` mediumint(15) NOT NULL,
  `goodsid` mediumint(15) NOT NULL COMMENT '这个是商品附加表的ID，不是goods表的id',
  `goodsname` varchar(255) NOT NULL,
  `goodssn` varchar(120) NOT NULL,
  `goodsnum` int(11) NOT NULL,
  `goodsprice` decimal(9,2) NOT NULL,
  `goodsattributeStr` varchar(255) NOT NULL,
  `goodsthumb` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COMMENT='用户购物车的商品';
