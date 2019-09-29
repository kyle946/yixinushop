CREATE TABLE `y_coupon_issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` mediumint(15) NOT NULL COMMENT '用户ID',
  `money` decimal(9,2) NOT NULL COMMENT '面额',
  `amount` decimal(9,2) NOT NULL COMMENT '需要满金额',
  `rtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '发放时间',
  `orderSn` varchar(255) DEFAULT NULL COMMENT '订单ID，如果不为空,则表示已经使用过了。',
  `couponId` int(11) NOT NULL COMMENT '优惠券ID',
  `startTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `endTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '过期时间',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态，1为可用 ，2为禁用，3为过期，4为已用完',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='已发放的优惠券';
