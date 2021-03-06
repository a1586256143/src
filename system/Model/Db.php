<?php
/**
 * 数据库接口
 * @author Colin <15070091894@163.com>
 */

namespace system\Model;

use system\Model\Drivers\Mysqli;
use system\MyError;

abstract class Db {
    /**
     * @var $db Mysqli
     */
    protected static $db;
    // $_db 供子类存储用
    protected $_db;

    /**
     * 获取数据库类
     * @author Colin <15070091894@163.com>
     * @return Mysqli
     * @throws
     */
    public static function getIns() {
        if (self::$db) {
            return self::$db;
        } else {
            $types    = 'system\Model\Drivers\\' . ucfirst(strtolower(Config('DB_TYPE')));
            self::$db = new $types;
            if (!self::$db) {
                throw new MyError('数据库驱动失败，请检查配置文件');
            }
            self::$db->connect();
            self::CheckDatabase();
            self::$db->query('SET NAMES ' . Config('DB_CODE'));

            return self::$db;
        }
    }

    /**
     * 确认数据库是否存在
     * @author Colin <15070091894@163.com>
     * @throws MyError
     */
    public static function CheckDatabase() {
        $database = Config('DB_TABS');
        $result   = self::$db->select_db($database);
        if (!$result) {
            E('数据库不存在或数据库名不正确！' . $database);
        }
    }

    /**
     * 确认表是否存在
     *
     * @param string $tables  验证表名
     * @param string $db_tabs 验证数据库
     * @param bool   $throw   是否抛出错误
     *
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public function CheckTables($tables = null, $db_tabs = null, $throw = true) {
        if (empty($db_tabs)) {
            $db_tabs = Config('DB_TABS');
        }
        $tables = $this->filterTables($tables);
        $sql    = "select `TABLE_NAME` from `INFORMATION_SCHEMA`.`TABLES` where `TABLE_SCHEMA`='$db_tabs' and `TABLE_NAME`='$tables' limit 1";
        $result = $this->execute($sql);
        if (empty($result)) {
            $throw && E('数据表不存在！' . $tables);
            if (!$throw) throw new MyError('数据表不存在！' . $tables);
        }
    }

    /**
     * 过滤表名中的``符号
     * @param $tables
     * @return string|string[]
     */
    protected function filterTables($tables){
        return str_replace('`' , '' , $tables);
    }

    /**
     * 确认字段是否存在
     *
     * @param string $table 查询表名
     * @param string $field 查询字段
     *
     * @author Colin <15070091894@163.com>
     * @return bool
     */
    public function CheckFields($table, $field) {
        if (!$this->execute("Describe `$table` `$field`")) {
            return true;
        }
        return false;
    }

    /**
     * 执行源生sql语句并返回结果
     *
     * @param string $sql 要执行的sql语句
     *
     * @author Colin <15070091894@163.com>
     * @return array|bool
     */
    public function execute($sql) {
        $query = $this->query($sql);
        if (!$query) {
            return false;
        }
        return $this->fetch_array($query);
    }

    /**
     * 获取结果集
     *
     * @param  mixed $query query执行后结果
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    protected function getResult($query) {
        $data = [];
        while ($rows = $this->fetch_array($query)) {
            $data[] = $rows;
        }

        return $data;
    }

    /**
     * 关闭数据库方法
     * @author Colin <15070091894@163.com>
     */
    public static function CloseDB() {
        self::$db->close();
    }

    /**
     * 开启事务处理
     * @author Colin <15070091894@163.com>
     */
    public function startTransaction() {
        return $this->query('start transaction');
    }

    /**
     * 回滚事务处理
     * @author Colin <15070091894@163.com>
     */
    public function rollback() {
        return $this->query('rollback');
    }

    /**
     * 提交事务处理
     * @author Colin <15070091894@163.com>
     */
    public function commit() {
        return $this->query('commit');
    }

    /**
     * 获取表所有字段
     *
     * @param string $table  表名
     * @param string $dbName 数据库名
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function getFields($table, $dbName = '') {
        $dbName = $dbName ? $dbName : Config('DB_TABS');
        $query  = $this->_db->query("select COLUMN_NAME from information_schema.COLUMNS where table_name = '$table' and table_schema = '$dbName' ");
        $result = $this->getResult($query);
        $result = array_column($result, 'COLUMN_NAME');

        return $result;
    }
}
