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
 * 查看当前数据库-所有表
 */
$tables = $database->table->tables();
//json($tables);

/**
 * 取数据表数组第一个元素(关联数组)
 */
$tableName = reset($tables)['Name'];

/**
 * 数据表信息
 */
//json($database->table->info($tableName));

/**
 * 数据表字段信息(简约)
 */
// json($database->table->fields($tableName, false));

/**
 * 数据表字段信息(详细)
 */
//json($database->table->fields($tableName));

/**
 * 检查数据表是否存在
 */
//json([
//    'tableName' => $tableName,
//    'status' => $database->table->has($tableName)
//]);

/**
 * 查看建表语句
 */
//vd($database->table->createTableSql($tableName, true));

/**
 * 清空数据表
 */
//json([
//    'tableName' => $tableName,
//    'status' => $database->table->truncate($tableName)
//]);

/**
 * 删除数据表
 */
//json([
//    'tableName' => $tableName,
//    'status' => $database->table->drop($tableName)
//]);

/**
 * 数据备份类
 */
$backup = $database->backup
    // 设置要备份的表名或数组(不设置则备份所有表)
//    ->setTable($tableName)
    // 设置备份路径
    ->setPath(ROOT_PATH . '/temp', 'dev')
    // 设置每次处理数据条数
    ->setLimit(3)
    // 设置是否使用多条独立的INSERT语句
    ->setIsMultiInsert(true)
    // 设置是否保存SQL到文件
    ->setIsSaveSqlToFile(true);

/**
 * 导出表结构
 */
//vd($backup->exportStructure());

/**
 * 导出数据
 */
//vd($backup->exportData());

/**
 * 导出所有表结构和数据
 */
//vd($backup->exportAll());

/**
 * 数据库导入类
 */
$import = $database->import;

/**
 * 导入SQL文件
 */
//$import->loadSqlFile(ROOT_PATH . '/temp/dev/af_system_admin.sql');

/**
 * 导入文件(通过目录)
 */
$import->loadSqlFileByDir(ROOT_PATH . '/temp', true);

/**
 * 查看导入的SQL文件列表
 */
// vd($import->getSqlList());

/**
 * 执行导入
 */
//vd($import->execute());
