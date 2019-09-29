CREATE TABLE `y_user_rank` (
  `id` int(6) NOT NULL,
  `name` varchar(120) NOT NULL COMMENT '名称',
  `alias` varchar(200) NOT NULL COMMENT '别名',
  `points` int(11) NOT NULL COMMENT '达到该级别的积分',
  `discount` int(6) NOT NULL COMMENT '该级别的订单折扣',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1为可用，2为禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
