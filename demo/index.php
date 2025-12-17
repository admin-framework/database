<?php
// +----------------------------------------------------------------------
// | AdminFramework [ 编码如风 极速开发 智慧管控 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024~2025 http://www.adminframework.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小码农 <phpxmn@gmail.com>
// +----------------------------------------------------------------------
// 当前目录
use AdminFramework\Database\Database;

const ROOT_PATH = __DIR__;
require_once '../vendor/autoload.php';
require_once ROOT_PATH . '/function.php';
// 实例化数据库操作类并设置数据库配置
$database = Database::getInstance()->setConfig(require ROOT_PATH . '/config.php');
/**
 * 数据表操作
 */
// 查询所有表
$tables = $database->table->tables();
//json($tables);

// 取数据表数组第一个元素(关联数组)
$tableName = reset($tables)['Name'];

// 数据表信息
//json($database->table->info($tableName));

// 数据表字段信息(简约)
//json($database->table->fields($tableName, false));

// 数据表字段信息(详细)
//json($database->table->fields($tableName));

// 检查数据表是否存在
//json([
//    'tableName' => $tableName,
//    'status' => $database->table->has($tableName)
//]);

// 查看建表语句
vd($database->table->createTableSql($tableName, true));

// 导出所有表结构和数据
