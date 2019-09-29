CREATE TABLE `y_sendsmslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(20) NOT NULL COMMENT '手机号码',
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发送日期',
  `content` varchar(200) NOT NULL COMMENT '发送内容',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COMMENT='短信发送记录';
