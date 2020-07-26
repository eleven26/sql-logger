<?php

namespace Eleven26\SqlLogger;

use DB;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Carbon;
use Log;

class MysqlLogService
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
     * 启用 MySQL 查询记录
     *
     * @param string $logSqlStack 如果查询语句包含 $logSqlStack 则记录其堆栈
     */
    public static function enableQueryLog(string $logSqlStack = '')
    {
        static::$logging = true;
        static::$logStack = $logSqlStack;
    }

    /**
     * 禁用 MySQL 查询记录
     */
    public static function disableQueryLog()
    {
        static::$logging = false;
        static::$logStack = '';
    }

    /**
     * 添加 sql 监听
     */
    public static function registerEventListener()
    {
        static::$connections = array_flip(config('sql-logger.mysql_connections'));
        if (empty(static::$connections)) return;

        DB::listen(function (QueryExecuted $sql) {
            if (!static::$logging || !isset(static::$connections[$sql->connectionName])) {
                return;
            }

            $s = str_replace('?', '%s', $sql->sql);

            // binding 替换
            $bindings = array_map(function ($binding) {
                if ($binding instanceof Carbon) {
                    return "\"" . $binding->toDateTimeString() . "\"";
                }

                if (is_string($binding)) {
                    return "\"{$binding}\"";
                }

                return $binding;
            }, $sql->bindings);
            $s = sprintf($s, ...$bindings);

            Log::info(sprintf('time: %.2fms %s', $sql->time, $s));

            // 记录 sql 查询产生的堆栈
            if (!empty(static::$logStack)) {
                if (strpos($s, static::$logStack) !== false) {
                    Log::info(PHP_EOL . (new \Exception())->getTraceAsString());
                }
            }
        });
    }
}
