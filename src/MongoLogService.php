<?php

namespace Eleven26\SqlLogger;

use DB;
use Illuminate\Database\Events\QueryExecuted;
use Log;

class MongoLogService
{
    /**
     * 是否记录 sql 语句
     *
     * @var bool
     */
    private static $logging = false;

    /**
     * 记录堆栈
     *
     * @var string
     */
    private static $logStack = '';

    /**
     * 需要记录 SQL 的数据库连接名
     *
     * @var array
     */
    private static $connections = [];

    /**
     * 启用 mongo log
     *
     * @param string $logStack 如果查询语句包含 $logStack 则记录其堆栈
     */
    public static function enableQueryLog(string $logStack = '')
    {
        static::$logging = true;
        static::$logStack = $logStack;

        foreach (static::$connections as $connection => $_) {
            DB::connection($connection)->enableQueryLog();;
        }
    }

    /**
     * 禁用 mongo log
     */
    public static function disableQueryLog()
    {
        static::$logging = false;
        static::$logStack = '';

        foreach (static::$connections as $connection => $_) {
            DB::connection($connection)->disableQueryLog();;
        }
    }

    /**
     * 添加 sql 监听
     */
    public static function registerEventListener()
    {
        static::$connections = array_flip(config('sql-logger.mongodb_connections'));
        if (empty(static::$connections)) return;

        DB::listen(function (QueryExecuted $sql) {
            if (!static::$logging || !isset(static::$connections[$sql->connectionName])) {
                return;
            }

            Log::info("time: " . $sql->time . 'ms,  connection: ' . $sql->connectionName . ',  ' . $sql->sql);

            // 记录堆栈
            if (!empty(static::$logStack)) {
                if (strpos($sql->sql, static::$logStack) !== false) {
                    Log::info(PHP_EOL . (new \Exception())->getTraceAsString());
                }
            }
        });
    }
}
