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
use PDO;
use PDOException;
use RuntimeException;

/**
 * 数据库类
 * Class Database
 * @package AdminFramework\Database
 * @property Table $table 数据表操作类实例
 * @property Backup $backup 数据库备份类实例
 * @property Import $import 数据库导入类实例
 */
class Database
{
    /**
     * 保存类的唯一实例
     * @var ?Database
     */
    private static ?Database $instance = null;

    /**
     * Pdo
     * @var PDO|null
     */
    private ?PDO $pdo = null;

    /**
     * 数据库配置
     * @var array|string[]
     */
    private array $config = [
        // 数据库主机
        'host' => '127.0.0.1',
        // 数据库端口
        'port' => '3306',
        // 数据库用户名
        'username' => 'root',
        // 数据库密码
        'password' => '123456',
        // 数据库名称
        'database' => 'adminFramework',
        // 数据库字符集
        'charset' => 'utf8mb4',
        // 数据库表前缀
        'prefix' => 'af_',
    ];

    /**
     * 获取类实例的唯一入口
     * @return static
     */
    public static function getInstance(array $config = []): Database
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * 私有构造函数，防止外部直接实例化
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取 PDO 实例
     * @return PDO
     */
    public function pdo(): ?PDO
    {
        // 测试 pdo 是否还在连接中
        if ($this->pdo && $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
            return $this->pdo;
        }

        // php 使用 pdo 链接数据库
        try {
            // 构建完整的数据库连接字符串
            $dsn = 'mysql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['database'] . ';charset=' . $this->config['charset'];

            // 创建 PDO 实例，包含用户名和密码
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password']);

            // 配置 PDO 错误模式为异常模式，便于错误处理
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 关闭模拟预处理语句，使用真实的预处理语句
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // 设置默认的获取模式为关联数组
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // 禁用自动提交，使用手动事务控制（可选，根据需求调整）
            // $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        } catch (PDOException $e) {
            // 处理连接错误，根据实际需求进行日志记录或其他错误处理
            throw new RuntimeException('Failed to connect to database: ' . $e->getMessage());
        }

        return $this->pdo;
    }

    /**
     * 动态属性获取器
     * @param string $name 属性名
     * @return mixed
     */
    public function __get(string $name)
    {
        $namespace = 'AdminFramework\\Database\\' . ucwords($name);
        if (!class_exists($namespace)) {
            // 抛出异常
            throw new RuntimeException("Class {$namespace} does not exist.");
        }
        return new $namespace();
    }

    /**
     * 设置配置
     * @param string|array $index 配置索引或数组
     * @param mixed $value 配置值
     * @return self
     */
    public function setConfig($index, $value = null): self
    {
        if (is_array($index)) {
            $this->config = array_merge($this->config, $index);
        } else {
            $this->config[$index] = $value;
        }

        return $this;
    }

    /**
     * 获取配置
     * @param string $index 配置索引
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getConfig(string $index = '', $default = false)
    {
        if (!empty($index)) {
            return $this->config[$index] ?? $default;
        }
        return $this->config;
    }

    /**
     * 使用指定数据库
     * @param string $databaseName
     * @return $this
     */
    public function useDatabase(string $databaseName): self
    {
        // 过滤数据库名，只允许字母、数字、下划线
        $databaseName = preg_replace('/[^a-zA-Z0-9_]/', '', $databaseName);
        $this->pdo()->query('USE ' . $databaseName);
        return $this;
    }

    /**
     * 执行查询语句
     * @param string $sql SQL查询语句
     * @param array $params 查询参数数组，用于预处理语句的参数绑定
     * @param bool $returnStmt 是否返回PDOStatement对象而非执行结果
     * @return mixed 执行结果（结果集数组或受影响的行数）或PDOStatement对象（当$returnStmt为true时）
     */
    public function query(string $sql, array $params = [], bool $returnStmt = false)
    {
        try {
            // 获取PDO实例
            $pdo = $this->pdo();

            // 准备预处理语句
            $stmt = $pdo->prepare($sql);

            // 执行预处理语句，传入参数
            $stmt->execute($params);

            // 如果要求返回语句对象，则直接返回
            if ($returnStmt) {
                return $stmt;
            }

            // 检查SQL语句类型，确定返回结果类型
            $sqlType = explode(' ', trim($sql))[0] ?? '';

            switch (strtoupper($sqlType)) {
                case 'SELECT':
                case 'SHOW':
                    // SELECT语句返回结果集数组
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case 'INSERT':
                    // INSERT语句返回最后插入的ID
                    $result = $pdo->lastInsertId();
                    break;
                case 'UPDATE':
                case 'DELETE':
                    // UPDATE和DELETE语句返回受影响的行数
                    $result = $stmt->rowCount();
                    break;
                case 'REPLAC':
                    // REPLACE语句返回受影响的行数
                    $result = $stmt->rowCount();
                    break;
                case 'TRUNC':
                    // TRUNCATE语句返回受影响的行数
                    $result = $stmt->rowCount();
                    break;
                default:
                    // 其他语句返回受影响的行数
                    $result = $stmt->rowCount();
                    break;
            }
        } catch (PDOException $e) {
            // 处理查询错误，根据实际需求进行日志记录或其他错误处理
            throw new RuntimeException('Database query failed: ' . $e->getMessage() . ' SQL: ' . $sql, 0, $e);
        }

        return $result;
    }

    /**
     * 名称过滤
     * @param string $name 原始名称
     * @return string 过滤后的名称
     */
    public function filterName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
    }
}
