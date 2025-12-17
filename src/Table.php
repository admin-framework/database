<?php

namespace AdminFramework\Database;

// +----------------------------------------------------------------------
// | AdminFramework [ 编码如风 极速开发 智慧管控 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024~2025 http://www.adminframework.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小码农 <phpxmn@gmail.com>
// +----------------------------------------------------------------------

use RuntimeException;

class Table
{
    /**
     * 数据库连接实例
     * @var Database
     */
    public Database $db;

    /**
     * 构造函数，初始化数据库连接实例
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 获取数据库所有表名或详细信息
     * @param bool $detail 是否显示详细信息
     * @return array|false
     */
    public function tables(bool $detail = true)
    {
        $tables = $this->db->query("show table status;");
        $keys = array_column($tables, 'Name');
        # 是否显示详情
        if (!$detail) {
            $values = array_column($tables, 'Comment');
            $tables = array_combine($keys, $values);
        } else {
            $tables = array_combine($keys, $tables);
        }
        # 返回
        return $tables;
    }

    /**
     * 获取表信息
     * @param string $tableName 表名称
     * @param string $index 要检索的特定信息索引
     * @return array 表信息
     */
    public function info(string $tableName, string $index = ''): array
    {
        // 验证表名参数
        if (empty($tableName)) {
            throw new RuntimeException('表名参数不能为空');
        }

        // 过滤表名，只允许字母、数字、下划线
        $tableName = $this->db->filterName($tableName);

        // 直接查询单个表信息，而不是所有表，以提高性能
        $sql = "SHOW TABLE STATUS LIKE '$tableName'";
        $tableInfo = $this->db->query($sql);
        $tableInfo = reset($tableInfo) ?: [];

        if (!empty($index)) {
            return $tableInfo[$index] ?? $tableInfo;
        }

        return $tableInfo;
    }

    /**
     * 获取表字段信息
     * @param string $tableName 表名称
     * @param bool $detail 是否显示详细信息
     * @param string $databaseName 数据库名称
     * @return array 表字段信息
     */
    public function fields(string $tableName, bool $detail = true, string $databaseName = ''): array
    {
        // 验证表名参数
        if (empty($tableName)) {
            throw new RuntimeException('表名参数不能为空');
        }
        // 验证数据库名参数，为空时使用默认数据库
        $databaseName = $databaseName ?: $this->db->getConfig('database');
        // 使用预处理语句查询表字段信息，防止SQL注入
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :tableName AND TABLE_SCHEMA = :databaseName ORDER BY ORDINAL_POSITION";
        $tableFields = $this->db->query($sql, [
            ':tableName' => $this->db->filterName($tableName),
            ':databaseName' => $this->db->filterName($databaseName)
        ]);
        $columnKey = null;
        if (!$detail) {
            $columnKey = 'COLUMN_COMMENT';
        }
        return array_column($tableFields, $columnKey, 'COLUMN_NAME');
    }

    /**
     * 判断数据表是否存在
     * @param string $table 表名称
     * @return bool 表存在返回true，否则返回false
     */
    public function has(string $table): bool
    {
        // 验证表名参数
        if (empty($table)) {
            throw new RuntimeException('表名参数不能为空');
        }
        // 使用INFORMATION_SCHEMA.TABLES更高效地检查表是否存在
        $databaseName = $this->db->getConfig('database');
        // 使用预处理语句检查表是否存在，防止SQL注入
        $sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = :table AND TABLE_SCHEMA = :databaseName";
        $result = $this->db->query($sql, [
            ':table' => $this->db->filterName($table),
            ':databaseName' => $this->db->filterName($databaseName)
        ]);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
    }

    /**
     * 删除数据表
     * @param string $table 数据表名称
     * @return bool 成功返回true，失败返回false
     */
    public function drop(string $table): bool
    {
        // 验证表名参数
        if (empty($table)) {
            throw new RuntimeException('表名参数不能为空');
        }
        // 过滤表名，只允许字母、数字、下划线
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        // 构造DROP TABLE语句
        $sql = "DROP TABLE IF EXISTS $table";
        // 使用query方法执行SQL语句
        $this->db->query($sql);
        return true;
    }

    /**
     * 清空数据表
     * @param string $table 数据表名称
     * @return bool 成功返回true，失败返回false
     */
    public function truncate(string $table): bool
    {
        // 验证表名参数
        if (empty($table)) {
            throw new RuntimeException('表名参数不能为空');
        }
        // 过滤表名，只允许字母、数字、下划线
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        // 构造TRUNCATE TABLE语句
        $sql = "TRUNCATE TABLE $table";
        // 使用query方法执行SQL语句
        $this->db->query($sql);
        return true;
    }

    /**
     * 获取表的建表语句
     * @param string $table 表名称
     * @param bool $isDropTable 是否包含DROP TABLE IF EXISTS语句
     * @return string 建表语句
     */
    public function createTableSql(string $table, bool $isDropTable = true): string
    {
        // 验证表名参数
        if (empty($table)) {
            throw new RuntimeException('表名参数不能为空');
        }
        // 过滤表名，只允许字母、数字、下划线
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        // 构造SHOW CREATE TABLE语句
        $sql = "SHOW CREATE TABLE $table";
        // 使用query方法执行SQL语句
        $result = $this->db->query($sql);
        // 检查查询结果是否为空
        if (empty($result)) {
            throw new RuntimeException("表 $table 不存在");
        }
        // 获取建表语句
        $sql = $result[0]['Create Table'] ?? '';
        // 可选：Windows 系统下字段名可能是小写 create table，兼容处理
        if (empty($sql)) {
            $sql = $result['create table'];
        }

        if ($isDropTable) {
            $sql = '-- 删除数据表' . PHP_EOL . 'DROP TABLE IF EXISTS ' . $table . ';' . PHP_EOL . '-- 创建数据表' . PHP_EOL . $sql;
        }

        // 把建表语句前面的CREATE TABLE替换为CREATE TABLE IF NOT EXISTS
        return str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $sql);
    }
}
