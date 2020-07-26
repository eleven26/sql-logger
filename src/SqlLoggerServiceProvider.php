<?php

namespace Eleven26\SqlLogger;

use Illuminate\Support\ServiceProvider;

class SqlLoggerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/sql-logger.php', 'sql-logger'
        );
    }

    public function boot()
    {
        if (config('sql-logger.enable_sql_logger')) {
            MysqlLogService::registerEventListener();
            MongoLogService::registerEventListener();
        }
    }
}