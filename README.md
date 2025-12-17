# AdminFramework Database

> AdminFramework Database 是一个轻量级的 PHP 数据库操作类库，提供了简洁易用的 API 来管理数据库连接、执行查询、备份和恢复数据库等功能。
<p align="center"> 
    <a href="https://github.com/admin-framework/database/blob/main/LICENSE" target="_blank"> 
        <img src="https://poser.pugx.org/admin-framework/database/license" alt="License"> 
    </a> 
    <a href="https://github.com/admin-framework/database" target="_blank"> 
        <img src="https://poser.pugx.org/admin-framework/database/require/php" alt="PHP Version Require"> 
    </a> 
    <a href="https://github.com/admin-framework/database" target="_blank"> 
        <img src="https://poser.pugx.org/admin-framework/database/v" alt="Latest Stable Version"> 
    </a> 
    <a href="https://packagist.org/packages/admin-framework/database" target="_blank"> 
        <img src="https://poser.pugx.org/admin-framework/database/downloads" alt="Total Downloads"> 
    </a>
</p>
## 目录结构

```
├── demo/
│   ├── config.php      # 数据库配置文件
│   ├── function.php    # 常用函数  
│   ├── index.php       # 示例  
├── src/
│   ├── Database.php    # 数据库核心类（单例模式）
│   ├── Table.php       # 数据表操作类
│   ├── Backup.php      # 数据库备份类
│   └── Import.php      # 数据库导入类
├── tests/
│   ├── DatabaseTest.php    # 数据库核心类测试
│   ├── TableTest.php       # 数据表操作类测试
│   ├── BackupTest.php      # 数据库备份类测试
│   └── ImportTest.php      # 数据库导入类测试
└── README.md           # 说明文档
```

## 核心功能

### 1. 数据库连接管理

- 单例模式设计，确保全局只有一个数据库连接实例
- 支持自定义数据库配置
- 自动重连机制
- PDO 预处理查询，防止 SQL 注入

### 2. 数据表操作

- 获取数据库所有表信息
- 获取单个表的详细信息和字段信息
- 检查表是否存在
- 删除和清空表
- 获取表的建表语句

### 3. 数据库备份

- 导出表结构
- 导出表数据
- 支持批量导出多个表
- 支持设置备份保存路径
- 支持分页导出大数据表

### 4. 数据库导入

- 加载单个 SQL 文件
- 递归加载目录中的 SQL 文件
- 执行 SQL 文件中的语句
- 支持按数字后缀排序执行

## 安装与配置

### 安装

通过 Composer 安装 AdminFramework Database：

```bash
composer require admin-framework/database
```

### 配置

```php
use AdminFramework\Database\Database;

// 获取数据库实例并配置
$db = Database::getInstance([
    'host'     => '127.0.0.1',    // 数据库主机
    'port'     => '3306',          // 数据库端口
    'username' => 'root',          // 数据库用户名
    'password' => '123456',        // 数据库密码
    'database' => 'adminFramework', // 数据库名称
    'charset'  => 'utf8mb4',       // 数据库字符集
    'prefix'   => 'af_',           // 数据库表前缀
]);

// 
$db = Database::getInstance()->setConfig([
    'host'     => '127.0.0.1',    // 数据库主机
    'port'     => '3306',          // 数据库端口
    'username' => 'root',          // 数据库用户名
    'password' => '123456',        // 数据库密码
    'database' => 'adminFramework', // 数据库名称
    'charset'  => 'utf8mb4',       // 数据库字符集
    'prefix'   => 'af_',           // 数据库表前缀
]);
```

## 使用示例

### 1. 执行 SQL 查询

```php
// 查询数据
$users = $db->query("SELECT * FROM users WHERE id > :id", [':id' => 10]);

// 插入数据
$userId = $db->query("INSERT INTO users (name, email) VALUES (:name, :email)", [
    ':name'  => 'John Doe',
    ':email' => 'john@example.com'
]);

// 更新数据
$affectedRows = $db->query("UPDATE users SET name = :name WHERE id = :id", [
    ':name' => 'Jane Doe',
    ':id'   => $userId
]);

// 删除数据
$affectedRows = $db->query("DELETE FROM users WHERE id = :id", [':id' => $userId]);
```

### 2. 数据表操作

```php
// 获取所有表信息
$tables = $db->table->tables();

// 获取表的字段信息
$fields = $db->table->fields('users');

// 检查表是否存在
$exists = $db->table->has('users');

// 获取表的建表语句
$createSql = $db->table->createTableSql('users');

// 删除表
$db->table->drop('users');

// 清空表
$db->table->truncate('users');
```

### 3. 数据库备份

```php
// 创建备份实例
$backup = $db->backup;

// 设置备份保存路径
$backup->setPath('/path/to/backup', 'time'); // 使用时间作为子目录名

// 设置要备份的表
$backup->setTable(['users', 'posts']);

// 设置每次备份数据的行数限制
$backup->setLimit(1000);

// 导出表结构
$structure = $backup->exportStructure();

// 导出表数据
$dataCount = $backup->exportData();

// 导出表结构和数据
$result = $backup->exportAll();
```

### 4. 数据库导入

```php
// 创建导入实例
$import = $db->import;

// 加载单个 SQL 文件
$import->loadSqlFile('/path/to/backup/users.sql');

// 递归加载目录中的所有 SQL 文件
$import->loadSqlFileByDir('/path/to/backup', true);

// 执行所有加载的 SQL 文件
$count = $import->execute();
```

## API 文档

### 1. Database 类

#### `getInstance(array $config = []): Database`

获取数据库实例的唯一入口。

- **参数**：
    - `$config`：数据库配置数组
- **返回**：Database 实例

#### `pdo(): PDO`

获取 PDO 实例。

- **返回**：PDO 实例

#### `query(string $sql, array $params = [], bool $returnStmt = false)`

执行 SQL 查询。

- **参数**：
    - `$sql`：SQL 查询语句
    - `$params`：查询参数数组
    - `$returnStmt`：是否返回 PDOStatement 对象
- **返回**：查询结果或受影响的行数

#### `setConfig($index, $value = null): self`

设置配置。

- **参数**：
    - `$index`：配置索引或数组
    - `$value`：配置值
- **返回**：Database 实例

#### `getConfig(string $index = '', $default = false)`

获取配置。

- **参数**：
    - `$index`：配置索引
    - `$default`：默认值
- **返回**：配置值

### 2. Table 类

#### `tables(bool $detail = true)`

获取数据库所有表名或详细信息。

- **参数**：
    - `$detail`：是否显示详细信息
- **返回**：表信息数组

#### `info(string $tableName, string $index = ''): array`

获取表信息。

- **参数**：
    - `$tableName`：表名称
    - `$index`：要检索的特定信息索引
- **返回**：表信息数组

#### `fields(string $tableName, bool $detail = true, string $databaseName = ''): array`

获取表字段信息。

- **参数**：
    - `$tableName`：表名称
    - `$detail`：是否显示详细信息
    - `$databaseName`：数据库名称
- **返回**：表字段信息数组

#### `has(string $table): bool`

判断数据表是否存在。

- **参数**：
    - `$table`：表名称
- **返回**：表存在返回 true，否则返回 false

#### `drop(string $table): bool`

删除数据表。

- **参数**：
    - `$table`：表名称
- **返回**：成功返回 true，失败返回 false

#### `truncate(string $table): bool`

清空数据表。

- **参数**：
    - `$table`：表名称
- **返回**：成功返回 true，失败返回 false

#### `createTableSql(string $table, bool $isDropTable = true): string`

获取表的建表语句。

- **参数**：
    - `$table`：表名称
    - `$isDropTable`：是否包含 DROP TABLE IF EXISTS 语句
- **返回**：建表语句

### 3. Backup 类

#### `setPath(string $path, string $rule = 'time'): self`

设置备份保存路径。

- **参数**：
    - `$path`：备份保存路径
    - `$rule`：子目录命名规则（time、md5、sha1 或自定义字符串）
- **返回**：Backup 实例

#### `setTable($table): self`

设置要备份的表名或数组。

- **参数**：
    - `$table`：表名或数组
- **返回**：Backup 实例

#### `setLimit(int $limit = 5000): self`

设置每次备份数据的行数限制。

- **参数**：
    - `$limit`：行数限制
- **返回**：Backup 实例

#### `setIsAddDropSql(bool $isAddDropSql): self`

设置是否添加删除表结构 SQL。

- **参数**：
    - `$isAddDropSql`：是否添加
- **返回**：Backup 实例

#### `setIsSaveSqlToFile(bool $isSaveSqlToFile): self`

设置是否保存 SQL 语句至文件。

- **参数**：
    - `$isSaveSqlToFile`：是否保存
- **返回**：Backup 实例

#### `exportStructure(): array`

导出表结构。

- **返回**：表结构数组

#### `exportData(): int`

导出表数据。

- **返回**：导出数据行数

#### `exportAll(): array`

导出表结构和数据。

- **返回**：导出结果数组

### 4. Import 类

#### `loadSqlFile(string $file): Import`

加载 SQL 文件。

- **参数**：
    - `$file`：SQL 文件路径
- **返回**：Import 实例

#### `loadSqlFileByDir(string $dir, bool $isRecursive = false)`

递归加载目录中的 SQL 文件。

- **参数**：
    - `$dir`：目录路径
    - `$isRecursive`：是否递归子目录

#### `execute(): int`

执行所有加载的 SQL 文件中的语句。

- **返回**：执行的 SQL 语句条数

## 依赖要求

- PHP >= 7.4
- PDO 扩展
- MySQL 数据库

## 许可证

Apache License 2.0

## 作者

小码农 <phpxmn@gmail.com>

## 版本历史

- v1.0.0 (2024-12-17)：初始版本发布，包含数据库连接、表操作、备份和导入功能

## 贡献指南

欢迎提交 Issue 和 Pull Request 来改进这个项目。

## 联系方式

- 项目主页：http://www.adminframework.com
- 作者邮箱：phpxmn@gmail.com
