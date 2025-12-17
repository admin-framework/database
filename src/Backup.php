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

/**
 * 数据库备份类
 * Class Backup
 * @package AdminFramework\Database
 */
class Backup
{
    /**
     * 备份保存路径
     * @var string
     */
    public string $savePath = '';

    /**
     * 是否添加删除表结构SQL
     * @var bool
     */
    public bool $isAddDropSql = true;

    /**
     * 是否保存SQL语句至文件
     * @var bool
     */
    public bool $isSaveSqlToFile = true;

    /**
     * 要备份的表名或数组,未指定时备份所有表
     * @var array|string|null
     */
    public $table = null;

    /**
     * 每次备份数据的行数限制
     * @var int
     */
    public int $limit = 5000;

    /**
     * 是否使用多条独立的INSERT语句
     * @var bool
     */
    public bool $isMultiInsert = false;

    /**
     * 设置备份保存路径
     * @param string $path 备份保存路径
     * @return $this
     */
    public function setPath(string $path, string $rule = 'time'): self
    {
        switch ($rule) {
            case 'time':
                $path .= '/' . date('YmdHis');
                break;
            case 'md5':
                $path .= '/' . md5($path);
                break;
            case 'sha1':
                $path .= '/' . sha1($path);
                break;
            default:
                $path .= '/' . $rule;
                break;
        }

        $this->savePath = $path;

        return $this;
    }

    /**
     * 设置要备份的表名或数组
     * @param array|string $table 表名或数组
     * @return self
     */
    public function setTable($table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 设置是否添加删除表结构SQL
     * @param bool $isAddDropSql
     * @return $this
     */
    public function setIsAddDropSql(bool $isAddDropSql): self
    {
        $this->isAddDropSql = $isAddDropSql;
        return $this;
    }

    /**
     * 设置每次备份数据的行数限制
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit = 5000): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * 设置是否保存SQL语句至文件
     * @param bool $isSaveSqlToFile
     * @return $this
     */
    public function setIsSaveSqlToFile(bool $isSaveSqlToFile): self
    {
        $this->isSaveSqlToFile = $isSaveSqlToFile;
        return $this;
    }

    /**
     * 设置是否使用多条独立的INSERT语句
     * @param bool $isMultiInsert
     * @return $this
     */
    public function setIsMultiInsert(bool $isMultiInsert): self
    {
        $this->isMultiInsert = $isMultiInsert;
        return $this;
    }

    /**
     * 获取要备份的表名或数组
     * @return array
     */
    public function getTables(): array
    {
        $table = $this->table;

        if (empty($table)) {
            $table = array_keys(Database::getInstance()->table->tables(false));
        }

        if (is_string($table)) {
            $table = [$table];
        }

        return $table;
    }

    /**
     * 保存SQL语句至文件
     * @param string $fileName 文件名
     * @param string $content SQL语句内容
     * @param bool $append 是否追加内容
     * @return void
     */
    private function saveSqlToFile(string $fileName, string $content, bool $append = false): void
    {
        // 备份保存路径
        $path = $this->savePath;
        // 确保目录存在
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        // 文件名
        $filePath = $path . '/' . $fileName;
        // 追加标志
        $flags = $append ? FILE_APPEND : 0;
        // 写入文件
        file_put_contents($filePath, $content, $flags) !== false;
    }

    /**
     * 处理行值，转义特殊字符
     * @param array $row 行数据
     * @return string 转义后的值字符串
     */
    private function escapeRowValues(array $row): string
    {
        $values = [];
        $pdo = Database::getInstance()->pdo();

        foreach ($row as $value) {
            $values[] = is_null($value) ? 'NULL' : $pdo->quote($value);
        }

        return implode(', ', $values);
    }

    /**
     * 导出表结构
     * @return array
     */
    public function exportStructure(): array
    {
        $list = [];
        foreach ($this->getTables() as $table) {
            // sql语句
            $sql = Database::getInstance()->table->createTableSql($table, $this->isAddDropSql);
            // 确保SQL语句以分号结尾并添加换行
            if (substr(trim($sql), -1) !== ';') {
                $sql .= ';';
            }
            $sql .= PHP_EOL;
            // 导出表结构
            $list[$table] = $sql;
            // 保存至文件
            if ($this->isSaveSqlToFile) {
                $this->saveSqlToFile($table . '.sql', $sql);
            }
        }
        return $list;
    }

    /**
     * 导出数据
     * @return int 导出数据行数
     */
    public function exportData(): int
    {
        $count = 0;
        $tables = $this->getTables();

        foreach ($tables as $table) {
            $offset = 0;
            $pageNum = 1;

            // 分页查询表数据
            while (true) {
                // 查询数据
                $sql = "SELECT * FROM `{$table}` LIMIT :limit OFFSET :offset";
                $data = Database::getInstance()->query($sql, [
                    ':limit' => $this->limit,
                    ':offset' => $offset
                ]);

                // 如果没有数据了，退出循环
                if (empty($data)) {
                    break;
                }

                $pageSql = '';
                $firstRow = reset($data);
                $columns = implode('`, `', array_keys($firstRow));

                // 是否使用多条独立的INSERT语句
                if ($this->isMultiInsert) {
                    // 多条独立的INSERT语句
                    foreach ($data as $row) {
                        $pageSql .= "INSERT INTO `{$table}` (`{$columns}`) VALUES ({$this->escapeRowValues($row)});\n";
                        $count++;
                    }
                } else {
                    // 单条INSERT语句，包含多个VALUES
                    $valuesList = [];

                    foreach ($data as $row) {
                        $valuesList[] = "({$this->escapeRowValues($row)})";
                        $count++;
                    }

                    $allValuesStr = implode(',', $valuesList);
                    $pageSql = "INSERT INTO `{$table}` (`{$columns}`) VALUES {$allValuesStr};";
                }

                // 保存当前页SQL到文件（使用表名-页码.sql格式）
                if ($this->isSaveSqlToFile) {
                    $fileName = "{$table}-{$pageNum}.sql";
                    $this->saveSqlToFile($fileName, $pageSql);
                }

                // 增加偏移量和页码
                $offset += $this->limit;
                $pageNum++;
            }
        }

        return $count;
    }

    /**
     * 导出表结构和数据
     * @return array 返回导出结果，包含表结构和数据行数
     */
    public function exportAll(): array
    {
        // 导出表结构
        $structure = $this->exportStructure();

        // 导出数据
        $dataCount = $this->exportData();

        return [
            'structure' => array_keys($structure),
            'data_count' => $dataCount
        ];
    }
}
