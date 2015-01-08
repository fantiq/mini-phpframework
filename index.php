<?php
/*定义文件路径*/
define('app_dir', dirname(__FILE__).DIRECTORY_SEPARATOR.'app');
/*定义框架核心文件路径*/
define('core_dir', dirname(__FILE__).DIRECTORY_SEPARATOR.'core');
// 控制器路径
define('controller_dir', app_dir.DIRECTORY_SEPARATOR.'controller');
// 模型文件路径
define('model_dir', app_dir.DIRECTORY_SEPARATOR.'model');
// 模版文件路径
define('view_dir', app_dir.DIRECTORY_SEPARATOR.'view');
// 配置文件路径
define('config_dir', app_dir.DIRECTORY_SEPARATOR.'config');
// 日志文件路径
define('log_dir', app_dir.DIRECTORY_SEPARATOR.'logs');
// 系统核心库
define('lib_dir', app_dir.DIRECTORY_SEPARATOR.'lib');
// 第三方库
define('thrid_party_dir', app_dir.DIRECTORY_SEPARATOR.'library');
// 是否开启调试
// define('is_debug', true);
include 'framework.php';
// 启动框架
framework::run();