CREATE TABLE `y_payment` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `sn` varchar(40) NOT NULL,
  `description` varchar(250) NOT NULL,
  `logo` varchar(120) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态，1为启用，2为禁用',
  `partnerId` varchar(20) NOT NULL COMMENT '商户ID',
  `partnerKey` varchar(20) NOT NULL COMMENT '商户密钥',
  `partnerKeyFile` varchar(255) NOT NULL COMMENT '商户密钥文件',
  `group` varchar(12) NOT NULL COMMENT '分组',
  `runmode` tinyint(2) NOT NULL DEFAULT '1' COMMENT '运行模式 ，1为正式环境，2为测试',
  `config` text NOT NULL COMMENT '支付接口的配置，使用PHP数组的形式。',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
