<?php
/**
 * 基础类，处理视图数据
 * @author Colin <15070091894@163.com>
 */

namespace system;

class Base {
    //是否get
    protected static $is_get;
    //是否post
    protected static $is_post;
    //存储session
    protected static $session;
    //存储缓存
    protected static $cache;
    //存储get字段
    protected static $get;
    //存储post字段
    protected static $post;
    //视图
    protected static $view;

    /**
     * 初始化函数
     * @author Colin <15070091894@163.com>
     */
    public function __construct() {
        self::init();
    }

    /**
     * 初始化
     * @throws
     */
    protected static function init() {
        self::$view    = View::$view;
        self::$is_get  = GET;
        self::$is_post = POST;
        self::$session = session();
        self::$get     = values('get.');
        self::$post    = values('post.');
        unset(self::$post['_token']);
    }

    /**
     * 重定向
     *
     * @param string url  跳转地址
     * @param string info  跳转时提示的信息
     * @param int time  跳转时间
     *
     * @author Colin <15070091894@163.com>
     *
     */
    public static function redirect($url, $info = '正在跳转.....', $time = 3) {
        if (!empty($info)) {
            echo "<meta http-equiv='refresh' content='$time; url=$url'/>";
            exit($info);
        }
        self::location($url);
    }

    /**
     * header跳转
     *
     * @param string url  跳转地址
     *
     * @author Colin <15070091894@163.com>
     *
     */
    public static function location($url) {
        header("Location:$url");
    }

    /**
     * 返回json数据
     *
     * @param message  输出信息
     * @param string url  跳转地址
     * @param int status  信息状态
     *
     * @author Colin <15070091894@163.com>
     */
    public static function ajaxReturn($message, $url = null, $status = 0) {
        $return['info']   = $message;
        $return['url']    = $url;
        $return['status'] = $status;
        ajaxReturn($return);
        exit;
    }

    /**
     * 显示视图
     *
     * @param string $filename 文件名
     * @param array  $data     参数
     *
     * @return \system\View
     */
    protected static function view($filename = null, $data = []) {
        if ($data) {
            self::assign($data);
        }
        if (is_array($filename)) {
            self::assign($filename);
            $filename = null;
        }
        $filename = _parseFileName($filename);
        try {
            return self::$view->display($filename, ViewDIR);
        } catch (\SmartyException $e) {
            preg_match('/Unable to load template\s+\'file:(.*)\'/', $e->getMessage(), $match);
            if (count($match) > 0) {
                E('模板不存在 ' . ViewDIR . $match[1]);
            } else {
                E($e->getMessage());
            }
        }
    }

    /**
     * 注入变量
     *
     * @param string       $name  变量名
     * @param string|array $value 变量值
     */
    protected static function assign($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                self::$view->assign($key, $value);
            }
        } else {
            self::$view->assign($name, $value);
        }
    }

    /**
     * 提示信息模板
     *
     * @param string $message 输出信息
     * @param string $type    类型
     * @param array  $param   参数
     *
     * @author Colin <15070091894@163.com>
     * @return \system\View
     * @throws
     */
    public static function MessageTemplate($message, $type, $param = []) {
        $tpl = Config('TPL_' . $type . '_PAGE');
        if (!$tpl) {
            E('请设置提示载入的页面');
        }
        $data = [
            'param'   => $param,
            'message' => $message,
        ];
        self::assign($data);

        return self::$view->display($tpl);
    }

    /**
     * 成功后显示的对话框
     *
     * @param string $message 要输出的信息
     * @param int    $time    刷新的时间
     * @param string $url     要跳转的地址。为空则跳转为上一个页面
     *
     * @author Colin <15070091894@163.com>
     * @return string
     */
    protected static function success($message, $url = null, $time = 3) {
        return self::MessageTemplate($message, 'SUCCESS', ['url' => url($url), 'time' => $time, 'status' => 1]);
    }

    /**
     * 错误后显示的对话框
     *
     * @param string $message 要输出的信息
     * @param int    $time    刷新的时间
     * @param string $url     要跳转的地址。为空则跳转为上一个页面
     *
     * @author Colin <15070091894@163.com>
     * @return string
     */
    protected static function error($message, $url = null, $time = 3) {
        return self::MessageTemplate($message, 'ERROR', ['url' => url($url), 'time' => $time, 'status' => 0]);
    }

    /**
     * 读取session
     *
     * @param $name
     *
     * @return mixed
     */
    protected static function readSession($name = null) {
        $data = self::$session[ $name ];
        $json = json_decode($data, true);
        if (!$json) {
            return $data;
        }

        return $json;
    }

    /**
     * 设置session
     *
     * @param $name
     * @param $data
     *
     * @return bool
     * @throws \system\MyError
     */
    protected static function setSession($name = null, $data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        session($name, $data);

        return true;
    }

    /**
     * 删除session
     *
     * @param $name
     * @param $data
     *
     * @return bool|null
     * @throws \system\MyError
     */
    protected static function removeSession($name = '') {
        return session($name, null);
    }
}