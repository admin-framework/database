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
use InvalidArgumentException;

class Import
{
    /**
     * 加载的SQL文件列表
     * @var array
     */
    private array $sqlList = [];

    /**
     * SQL文件后缀
     * @var string
     */
    private string $sqlFileSuffix = '.sql';

    /**
     * 加载SQL文件
     * @param string $file SQL文件路径
     * @return $this
     */
    public function loadSqlFile(string $file): Import
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException("文件不存在：{$file}");
        }

        // 检查文件是否以.sql结尾
        if (substr($file, -strlen($this->sqlFileSuffix)) === $this->sqlFileSuffix) {
            $this->sqlList[] = $file;
        }

        return $this;
    }

    /**
     * 递归SQL文件
     * @param string $dir
     * @param bool $isRecursive 是否递归子目录
     * @return void
     */
    public function loadSqlFileByDir(string $dir, bool $isRecursive = false)
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            // 忽略隐藏文件
            if (substr($file, 0, 1) === '.') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                if ($isRecursive) {
                    $this->loadSqlFileByDir($path, $isRecursive);
                }
            } else {
                $this->loadSqlFile($path);
            }
        }
    }

    /**
     * 获取加载的SQL文件列表
     * @return array
     */
    public function getSqlList(): array
    {
        $arr = $this->sqlList;

        usort($arr, function ($a, $b) {
            // 提取单个文件的数字后缀（核心函数）
            $getSuffixNum = function ($filePath) {
                // 提取文件名（如 af_system_demo-10.sql）
                $fileName = basename($filePath);
                // 去掉.sql后缀（如 af_system_demo-10）
                $name = rtrim($fileName, $this->sqlFileSuffix);
                // 匹配末尾的 "-数字"
                preg_match('/-(\d+)$/', $name, $m);
                // 有数字则转整数，无则为0
                return isset($m[1]) ? (int)$m[1] : 0;
            };
            // 仅比较数字后缀的大小
            return $getSuffixNum($a) <=> $getSuffixNum($b);
        });

        return $arr;
    }

    /**
     * 执行 sql 文件中的 sql 语句
     * @return int 执行的条数
     */
    public function execute(): int
    {
        $count = 0;
        // 获取数据库实例
        $database = Database::getInstance();
        // 执行 sql 文件中的 sql 语句
        $sqlFiles = $this->getSqlList();
        // 遍历每个SQL文件
        foreach ($sqlFiles as $file) {
            // 读取sql文件内容
            $content = file_get_contents($file);
            // 解析SQL内容，分割为独立的SQL语句
            $sqlStatements = $this->parseSqlContent($content);
            // 执行每条SQL语句
            foreach ($sqlStatements as $sql) {
                $sql = trim($sql);
                if (!empty($sql)) {
                    $database->query($sql);
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * 解析SQL文件内容，分割为独立的SQL语句
     * @param string $content SQL文件内容
     * @return array SQL语句数组
     */
    private function parseSqlContent(string $content): array
    {
        // 过滤掉注释和空行
        $content = preg_replace('/^--.*\n|^\s+|\n\s+$/m', '', $content);
        // 按;分割sql
        $content = explode(';' . PHP_EOL, $content);
        // 过滤掉空字符串
        return array_filter($content);
    }
}
