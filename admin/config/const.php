<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of const
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */


defined('REDIS_ADDR') or define('REDIS_ADDR',  '127.0.0.1');    //redis  保存的时间
defined('REDIS_PORT') or define('REDIS_PORT',  6379);    //redis  保存的时间
defined('REDIS_PRE') or define('REDIS_PRE',  'y_');    //redis  前缀
defined('REDIS_TTL') or define('REDIS_TTL',  86400);    //redis  保存的时间
defined('SQLPRE') or define('SQLPRE',  'y_');    //数据库  前缀
defined('LOGIN_SESSION_KEY') or define('LOGIN_SESSION_KEY',  'adminUserInfo');    //管理员SESSION  key