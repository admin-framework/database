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
use AdminFramework\Database\Database;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    /**
     * 测试数据表是否存在(has)
     * @return void
     */
    public function testHasTable()
    {
        // 实例化数据库操作类
        $database = Database::getInstance();
        // 数据表
        $tables = $database->table->tables();
        // 取数据表数组第一个元素(关联数组)
        $tableName = reset($tables)['Name'];
        // 断言数据表存在
        $this->assertTrue($database->table->has($tableName));
    }
}