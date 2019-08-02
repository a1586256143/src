<?php

namespace system\IO\Storage;

use system\MyError;

abstract class Storage {
    // 单例
    protected static $ins;
    // 错误信息
    protected $error;
    // 配置信息
    protected $config = [];

    /**
     * 初始化
     * Storage constructor.
     *
     * @param null $storage
     *
     * @throws \system\MyError
     * @return \system\IO\Storage\Drivers\RedisStorage
     */
    public static function getIns($storage = null) {
        if (self::$ins) {
            return self::$ins;
        }
        !$storage && $storage = Config('STORAGE_TYPE');
        $storage   = ucfirst($storage);
        $className = 'system\IO\Storage\Drivers\\' . $storage . 'Storage';
        try {
            /**
             * @var $class \system\IO\Storage\Drivers\RedisStorage
             */
            $class = new $className;
            if (!$class->connect()) {
                throw new MyError($class->getLastError());
            }
            self::$ins = $class;

            return self::$ins;
        } catch (\Exception $e) {
            throw new MyError($e->getMessage());
        }
    }

    /**
     * 获取最后的错误
     * @return mixed
     */
    public function getLastError() {
        return $this->error;
    }
}