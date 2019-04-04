<?php
/**
 * 主体引导
 * @author Colin <15070091894@163.com>
 */

namespace system;
class MyClass {
    /**
     * 运行方法
     * @author Colin <15070091894@163.com>
     */
    public static function run() {
        try {
            //加载配置文件
            self::loadConfig();
            //注册autoload方法
            spl_autoload_register('system\\MyClass::autoload');
            //收集错误
            MyError::error_traceassstring();
            //创建项目文件夹
            self::Dir();
            //视图初始化
            self::View();
            //初始化路由
            self::initRoute();
        } catch (MyError $m) {
            die($m);
        }
    }

    /**
     * 自动加载
     *
     * @param ClassName 类名
     *
     * @author Colin <15070091894@163.com>
     */
    public static function autoload($ClassName) {
        if (preg_match("/\\\\/", $ClassName)) {
            //是否为命名空间加载
            $ClassName = preg_replace("/\\\\/", "/", $ClassName);
            // 简单处理app命名空间
            list($dirname) = explode('/', $ClassName);
            $ClassName = $dirname != 'system' ? APP_NAME . '/' . $ClassName : NAME_SPACE . $ClassName;
            require_file($ClassName . Config('DEFAULT_CLASS_SUFFIX'));
        }
    }

    /**
     * 加载配置文件
     * @author Colin <15070091894@163.com>
     */
    public static function loadConfig() {
        //DEBUG
        if (!defined('Debug')) define('Debug', true);
        //载入函数库文件
        require_once CommonDIR . '/functions.php';
        //合并config文件内容
        $merge = replace_recursive_params(Core . '/Conf/config.php', Common . '/config.php');
        //加入配置文件
        Config($merge);
        //设置默认工作空间目录结构
        $dirnames = array(
            'ControllerDIR' => APP_DIR . Config('DEFAULT_CONTROLLER_LAYER'),
            'ModelDIR'      => APP_DIR . Config('DEFAULT_MODEL_LAYER'),
            'ViewDIR'       => APP_DIR . Config('TPL_DIR'),
        );
        //根据数组的key 生成常量
        foreach ($dirnames as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
        //解析session
        if (Config('SESSION_START')) {
            session_start();
        }
        //加载全部配置文件
        $app = array(CommonDIR . '/functions.php', Common . '/functions.php');
        require_file($app);
    }

    /**
     * 目录结构方法
     * @author Colin <15070091894@163.com>
     */
    public static function Dir() {
        //缓存文件夹
        $cache = rtrim(ltrim(Config('CACHE_DIR'), './'), './');
        //缓存临时文件
        $cacheTmp = rtrim(ltrim(Config('CACHE_DATA_DIR'), './'), './');
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac OS')) {
            $cache    = '/' . $cache;
            $cacheTmp = '/' . $cacheTmp;
        }
        //更新文件权限
        shell_exec('chmod -R 0755 ' . APP_DIR);
        //批量创建目录
        $dir = array(
            APP_PATH,                //应用路径
            APP_DIR,                //系统app目录
            RunTime,                //运行目录
            ControllerDIR,        //控制器目录
            ModelDIR,                //模型目录
            ViewDIR,                //视图目录
            $cache,                //缓存目录
            $cacheTmp,            //缓存临时文件
            Common,                //全局目录
            Library,                //第三方目录
        );
        outdir($dir);
        //生成默认的配置文件、控制器
        $data = array(
            array(Common . '/config.php', View::createConfig()),    //配置文件
            array(Common . '/template.php', View::createTemplate()), //模板配置文件
            array(Common . '/routes.php', View::createRoute()), //路由配置文件
            array(Common . '/csrf.php', View::createCSRF()), //csrf配置文件
            array(Common . '/functions.php', View::createFunc()), //函数配置文件
            array(
                ControllerDIR . '/Index' . Config('DEFAULT_CLASS_SUFFIX'),
                View::createIndex(Config('DEFAULT_CONTROLLER'))
            )            //控制器
        );
        //批量创建文件
        foreach ($data as $key => $value) {
            if (!file_exists($value[0])) {
                file_put_contents($value[0], $value[1]);
            }
        }
        //设置默认时间格式
        Date::set_timezone();
    }

    /**
     * 初始化路由
     * @author Colin <15070091894@163.com>
     */
    public static function initRoute() {
        //加载配置文件
        $requires = array(Common . '/routes.php', Common . '/csrf.php');
        //批量引入
        require_file($requires);
        //执行路由
        Route\Route::init();
    }

    /**
     * 视图初始化
     * @author Colin <15070091894@163.com>
     */
    public static function View() {
        //加载配置文件
        require_file(Common . '/template.php');
        //初始化视图工厂
        View::init(Config('TPL_MODEL'), Config('TPL_CONFIG'));
    }
}