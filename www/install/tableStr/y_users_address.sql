CREATE TABLE `y_users_address` (
  `id` mediumint(15) NOT NULL AUTO_INCREMENT,
  `userid` mediumint(15) NOT NULL,
  `proviceSn` varchar(40) NOT NULL COMMENT '省',
  `citySn` varchar(40) NOT NULL COMMENT '市',
  `countySn` varchar(40) NOT NULL COMMENT '区/县',
  `townSn` varchar(40) NOT NULL COMMENT '乡',
  `street` varchar(255) NOT NULL COMMENT '街道',
  `recipients` varchar(255) NOT NULL COMMENT '收货人',
  `mobile` varchar(20) NOT NULL COMMENT '手机号码',
  `phone` varchar(20) NOT NULL COMMENT '电话',
  `zipcode` varchar(20) NOT NULL COMMENT '邮政编码',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态，1为可用',
  `isdefault` tinyint(2) DEFAULT '0' COMMENT '默认地址，1为是',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='用户收货地址';
