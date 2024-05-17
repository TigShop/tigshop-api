<?php
//**---------------------------------------------------------------------+
//**  LYECS 引导文件
//**---------------------------------------------------------------------+
//**   版权所有：江西禹商科技有限公司. 官网：https://www.lyecs.com
//**---------------------------------------------------------------------+
//**   作者：老杨 yq@lyecs.com
//**---------------------------------------------------------------------+
//**   提示：LYECS老杨商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

//可定义模型名称
define('ADMIN_NAME', 'admin');
define('COMPANY_ID', 'lyecs'); //企业ID
define('WAP_NAME', 'mobile'); //wap版目录名
//固定变模型名称
define('STORE_ADMIN_NAME', 'storeAdm');
define('PC_NAME', 'pc');

define('THINK_START_TIME', microtime(true));
define('THINK_START_MEM', memory_get_usage());
//define('DS', DIRECTORY_SEPARATOR);
define('DS', '/');
define('EXT', '.php');
defined('APP_PATH') or define('APP_PATH', app_path());
defined('ROOT_PATH') or define('ROOT_PATH', root_path());
defined('PUBLIC_ROOT_PATH') or define('PUBLIC_ROOT_PATH', ROOT_PATH . 'public' . DS);
defined('CORE_PATH') or define('CORE_PATH', APP_PATH . 'common' . DS);
defined('COMMON_PATH') or define('COMMON_PATH', APP_PATH . 'common' . DS);
defined('PUBLIC_PATH') or define('PUBLIC_PATH', COMMON_PATH . 'public' . DS);
defined('COMMON_CONTROLLER') or define('COMMON_CONTROLLER', COMMON_PATH . 'controller' . DS);
defined('LIB_PATH') or define('LIB_PATH', COMMON_PATH . 'lib' . DS);
defined('CONF_PATH') or define('CONF_PATH', APP_PATH); // 配置文件目录
defined('CONF_EXT') or define('CONF_EXT', EXT); // 配置文件后缀
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH . 'temp' . DS);
defined('DATA_PATH') or define('DATA_PATH', PUBLIC_ROOT_PATH . 'data' . DS);
defined('STATIC_PATH') or define('STATIC_PATH', PUBLIC_ROOT_PATH . 'static' . DS);
defined('STATIC_SYS_PATH') or define('STATIC_SYS_PATH', STATIC_PATH . 'sys' . DS);
defined('IMAGE_PATH') or define('IMAGE_PATH', PUBLIC_ROOT_PATH . 'img' . DS);
defined('STORE_ADMIN_LIB_PATH') or define('STORE_ADMIN_LIB_PATH', APP_PATH . 'storeAdm' . DS . 'lib' . DS);
defined('COOKIE_PATH') or define('COOKIE_PATH', '/');
defined('COOKIE_DOMAIN') or define('COOKIE_DOMAIN', '');
defined('HASH_CODE') or define('HASH_CODE', 'lyecs');

defined('IMAGE_PATH_NAME') or define('IMAGE_PATH_NAME', 'img');

defined('APP_CHARSET') or define('APP_CHARSET', 'utf-8');
defined('APP_LANG') or define('APP_LANG', 'zh_cn');
defined('APPNAME') or define('APPNAME', 'LYECS');
defined('VERSION') or define('VERSION', '5.0');
defined('RELEASE') or define('RELEASE', '20191001');

defined('TPL_DIR') or define('TPL_DIR', 'tpl'); //模板文件名
defined('DATA_DIR') or define('DATA_DIR', 'data'); //旧版本
defined('IMAGE_DIR') or define('IMAGE_DIR', ROOT_PATH . 'images' . DS); //旧版本

defined('IS_DEBUG') or define('IS_DEBUG', false); //旧版本
// 环境常量
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

defined('ADMIN_LIB_PATH') or define('ADMIN_LIB_PATH', APP_PATH . ADMIN_NAME . DS . 'lib' . DS);
defined('APP_MODULE') or define('APP_MODULE', str_replace('/', '', str_replace(base_path(), '', app_path())));
// defined('APP_REWRITE_MODULE') or define('APP_REWRITE_MODULE', $runRes['rewrite_module']);
// defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $runRes['controller']);
defined('THIS_APP_PATH') or define('THIS_APP_PATH', APP_PATH . APP_MODULE . DS);
defined('IS_ADMIN') or define('IS_ADMIN', APP_MODULE == ADMIN_NAME || APP_MODULE == 'storeAdm' ? true : false);
defined('IS_STORE_ADMIN') or define('IS_STORE_ADMIN', APP_MODULE == 'storeAdm' ? true : false);
defined('IS_WAP') or define('IS_WAP', APP_MODULE == 'mobile' ? true : false);
defined('IS_PC') or define('IS_PC', APP_MODULE == 'pc' ? true : false);
defined('IS_MINI_WECHAT') or define('IS_MINI_WECHAT', isset($_REQUEST['isWechat']) || isset($_REQUEST['uniApp']) ? true : false);
defined('IS_APP') or define('IS_APP', isset($_REQUEST['uniApp']) ? true : false); //APP新增
