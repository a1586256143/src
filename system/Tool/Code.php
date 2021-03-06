<?php
/**
 * 验证码
 * @author Colin <15070091894@163.com>
 */

namespace system\Tool;

use system\MyError;

class Code {
    private $charset  = 'abcdefghkmnprstuvwxyz23456789';    //随机因子
    private $code;                                        //验证码
    private $codelen  = 4;                                //验证码长度
    private $width    = 130;                                //宽度
    private $height   = 50;                                //高度
    private $img;                                        //图形资源句柄
    private $font;                                        //指定的字体
    private $fontsize = 20;                                //指定字体大小
    private $fontcolor;                                    //指定字体颜色

    /**
     * 初始化
     * @author Colin <15070091894@163.com>
     */
    public function __construct() {
        $this->charset  = Config('CODE_CHARSET');
        $this->codelen  = Config('CODE_LENGTH');
        $this->width    = Config('CODE_WIDTH');
        $this->height   = Config('CODE_HEIGHT');
        $this->fontsize = Config('CODE_FONTSIZE');
        $this->font     = Config('CODE_FONTPATH');
    }

    /**
     * 生成随机码
     * @author Colin <15070091894@163.com>
     */
    private function createCode() {
        $_len = strlen($this->charset) - 1;
        for ($i = 0; $i < $this->codelen; $i++) {
            $this->code .= $this->charset[ mt_rand(0, $_len) ];
        }
    }

    /**
     * 生成背景
     * @author Colin <15070091894@163.com>
     */
    private function createBg() {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color     = imagecolorallocate($this->img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
    }

    /**
     * 生成文字
     * @author Colin <15070091894@163.com>
     */
    private function createFont() {
        $_x = $this->width / $this->codelen;
        for ($i = 0; $i < $this->codelen; $i++) {
            $this->fontcolor = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imagettftext($this->img, $this->fontsize, mt_rand(-30, 30), $_x * $i + mt_rand(1, 5), $this->height / 1.4, $this->fontcolor, $this->font, $this->code[ $i ]);
        }
    }

    /**
     * 生成线条、雪花
     * @author Colin <15070091894@163.com>
     */
    private function createLine() {
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline($this->img, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $color);
        }
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
        }
    }

    /**
     * 输出
     * @author Colin <15070091894@163.com>
     */
    private function outPut() {
        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    /**
     * 对外生成
     *
     * @param string $prefix 多个的区分前缀
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     * @throws MyError
     */
    public function doimg($prefix = 'admin') {
        $this->getCode($prefix);
        $this->createBg();
        $this->createLine();
        $this->createFont();
        $this->outPut();

        return '';
    }

    /**
     * 获取code
     *
     * @param string $prefix 多个的区分前缀
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     * @throws MyError
     */
    public function getCode($prefix = 'admin') {
        $this->createCode();
        $verifyCode = strtolower($this->code);
        session($prefix . 'system_verify_code', $verifyCode);

        return $verifyCode;
    }

    /**
     * 校验验证码
     *
     * @param string $code   用户输入的验证码
     * @param string $prefix 多个的区分前缀
     * @throws MyError
     * @return bool
     */
    public function verify($code, $prefix = 'admin') {
        $verify = session($prefix . 'system_verify_code');
        if (!$verify) {
            return false;
        }
        if ($verify != $code) {
            return false;
        }

        return true;
    }
}