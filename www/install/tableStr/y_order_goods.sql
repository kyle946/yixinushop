CREATE TABLE `y_order_goods` (
  `id` mediumint(15) NOT NULL AUTO_INCREMENT,
  `orderid` mediumint(15) NOT NULL COMMENT '订单ID',
  `goodsid` mediumint(15) NOT NULL COMMENT '这个是商品附加表的ID，不是goods表的id',
  `goodsname` varchar(255) NOT NULL COMMENT '商品名称',
  `goodssn` varchar(120) NOT NULL COMMENT '商品编号',
  `goodsnum` decimal(9,3) NOT NULL COMMENT '数量',
  `goodsprice` decimal(9,2) NOT NULL COMMENT '价格',
  `goodsattributeStr` varchar(255) NOT NULL COMMENT '规格',
  `goodsthumb` varchar(255) NOT NULL COMMENT '缩略图',
  `payStatus` tinyint(2) NOT NULL DEFAULT '1' COMMENT '支付状态：1为未支付，2为已支付，3为申请退款,4为已退款',
  `delStatus` tinyint(2) NOT NULL DEFAULT '1' COMMENT '配送状态：1为未发货，2为已发货，3为已签收，4为拒收',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 COMMENT='订单商品表';
