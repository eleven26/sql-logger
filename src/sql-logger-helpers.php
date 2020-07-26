<?php

use Eleven26\SqlLogger\MongoLogService;
use Eleven26\SqlLogger\MysqlLogService;

if (!function_exists('enable_mysql_log')) {
    /**
     * Log MySQL 查询
     *
     * @param string $logSqlStack 如果查询语句包含 $logSqlStack 则记录其堆栈
     */
    function enable_mysql_log(string $logSqlStack = '')
    {
        MysqlLogService::enableQueryLog($logSqlStack);
    }
}

if (!function_exists('disable_mysql_log')) {
    /**
     * 禁用 MySQL 查询 log
     */
    function disable_mysql_log()
    {
        MysqlLogService::disableQueryLog();
    }
}

if (!function_exists('enable_mongo_log')) {
    /**
     * 启用 mongo 查询语句记录（会在执行 mongo 操作的时候 fire QueryExecuted 事件，可监听该事件来获取操作执行的语句、执行时间）
     *
     * @param string $logStack 如果查询语句包含 $logStack 则记录其堆栈
     */
    function enable_mongo_log(string $logStack = '')
    {
        MongoLogService::enableQueryLog($logStack);
    }
}

if (!function_exists('disable_mongo_log')) {
    /**
     * 禁用 MongoDB 查询语句记录
     *
     * @see enable_mongo_log()
     */
    function disable_mongo_log()
    {
        MongoLogService::disableQueryLog();
    }
}
