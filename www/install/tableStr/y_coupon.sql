CREATE TABLE `y_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '名称',
  `money` decimal(9,2) NOT NULL COMMENT '金额',
  `amount` decimal(9,2) NOT NULL COMMENT '需要满金额才能用',
  `send` int(11) NOT NULL DEFAULT '0' COMMENT '已发放数量',
  `startTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '开始日期',
  `endTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '结束日期',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态，1为启用，2为禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='优惠券  模板';
