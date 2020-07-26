# sql-logger

> 一个记录 SQL 语句的工具，也可以记录 `jenssegers/mongodb` 包产生的语句。


## 用途

1. **记录某一个 SQL 语句产生于哪一行代码。**

2. 记录 MySQL 或者 MongoDB 产生的语句及其耗时。


## 安装

1.通过 [composer](https://getcomposer.org/) 安装 ([eleven26/sql-logger](https://packagist.org/packages/eleven26/sql-logger))。

```bash
composer require "eleven26/sql-logger:~1.0.0" --dev
```

2.注册 Service Provider

- `Laravel`: 修改文件 `config/app.php`，`Laravel 5.5+` 不需要
    ```php
    'providers' => [
        //...
        Eleven26\SqlLogger\SqlLoggerServiceProvider::class,
    ],
    ```

- `Lumen`: 修改文件 `bootstrap/app.php`，添加下面这一行
    ```php
    $app->register(Eleven26\SqlLogger\SqlLoggerServiceProvider::class);
    ```

3. 在 `config` 文件夹下面添加一个配置文件，名为 `sql-logger.php`，复制如下内容进去：

```
<?php

return [
    // 是否启用 sql-logger
    'enable_sql_logger' => env('ENABLE_SQL_LOGGER', true),

    // config/database.php connections 里面配置的连接名
    'mysql_connections' => [

    ],

    // config/database.php connections 里面配置的连接名
    'mongodb_connections' => [

    ],
];
```


## 用法

> 下面提到的数据库连接名来自于 `config/database.php` 里面 `connections` 数组的键。


### 监听

1. 首先在配置文件 `config/sql-logger.php` 里面添加连接名称，MySQL 连接名加到 `mysql_connections` 数组中，如：

```
'mysql_connections' => [
    "user", 
],
```

2. 如果需要启用 MongoDB 的监听，在 `mongodb_connections` 数组中添加对应的连接名即可。


### 如何启用监听

1. 使用 `enable_mysql_log` 来监听 MySQL 产生的 SQL 语句，使用 `enable_mongo_log` 来监听 MongoDB 产生的语句。

2. 如果只是想对某几行代码进行调试，可以在那几行代码之后使用 `disable_mysql_log` 或者 `disable_mongo_log` 来禁用后续 SQL 语句的记录。


### 如何定位 SQL 语句产生的地方

有时候我们看到代码产生了一些慢查询、重复查询等，这个时候我们想去定位是哪一行代码产生的话。可以给 `enable_mysql_log` 或 `enable_mongo_log` 传入一个参数，参数内容是 SQL 语句的一部分，最好是可以完全和输出的其他 SQL 区分开的。

> 写 Log 的时候，会判断当前要记录的 SQL 语句是否包含了传递的参数字符串，如果包含，则记录产生该 SQL 的堆栈。这样我们就知道是哪一行代码导致了这个 SQL 语句的出现。


## 实例

1. 模型

```
class User extends Model
{
    protected $connection = 'user';
}
```

2. `config/database.php`

```
return [
    // ...
    "connections" => [
        "user" => [
            // ...具体配置项
        ]
    ],
    // ...
];
```

3. 记录 SQL 语句及其时间：

```
enable_mysql_log();

app(User::class)->first();
```

4. 查看日志文件，可以看到如下内容：

```
[2020-07-26 20:13:59] lumen.INFO: time: 37.42ms select * from `users` where `users`.`deleted_at` is null limit 1
```

5. **查看这个 SQL 语句是哪一行产生的**，只需要给 `enable_mysql_log` 传递 SQL 语句中可以区别开其他 SQL 语句的子字符串进去即可，如：

```
enable_mysql_log('select * from `users`');

app(User::class)->first();
```

再次查看日志文件可以看到如下内容：

```
[2020-07-26 20:16:42] lumen.INFO: time: 30.40ms select * from `users` where `users`.`deleted_at` is null limit 1  
[2020-07-26 20:16:42] lumen.INFO: 
#0 /Users/ruby/code/Foundation/vendor/illuminate/events/Dispatcher.php(350): Eleven26\SqlLogger\MysqlLogService::Eleven26\SqlLogger\{closure}(Object(Illuminate\Database\Events\QueryExecuted))
#1 /Users/ruby/code/Foundation/vendor/illuminate/events/Dispatcher.php(200): Illuminate\Events\Dispatcher->Illuminate\Events\{closure}('Illuminate\\Data...', Array)
#2 /Users/ruby/code/Foundation/vendor/illuminate/database/Connection.php(825): Illuminate\Events\Dispatcher->dispatch('Illuminate\\Data...')
#3 /Users/ruby/code/Foundation/vendor/illuminate/database/Connection.php(682): Illuminate\Database\Connection->event(Object(Illuminate\Database\Events\QueryExecuted))
#4 /Users/ruby/code/Foundation/vendor/illuminate/database/Connection.php(635): Illuminate\Database\Connection->logQuery('select * from `...', Array, 214.4)
#5 /Users/ruby/code/Foundation/vendor/illuminate/database/Connection.php(333): Illuminate\Database\Connection->run('select * from `...', Array, Object(Closure))
#6 /Users/ruby/code/Foundation/vendor/illuminate/database/Query/Builder.php(1719): Illuminate\Database\Connection->select('select * from `...', Array, true)
#7 /Users/ruby/code/Foundation/vendor/illuminate/database/Query/Builder.php(1704): Illuminate\Database\Query\Builder->runSelect()
#8 /Users/ruby/code/Foundation/vendor/illuminate/database/Eloquent/Builder.php(481): Illuminate\Database\Query\Builder->get(Array)
#9 /Users/ruby/code/Foundation/vendor/illuminate/database/Eloquent/Builder.php(465): Illuminate\Database\Eloquent\Builder->getModels(Array)
#10 /Users/ruby/code/Foundation/vendor/illuminate/database/Concerns/BuildsQueries.php(77): Illuminate\Database\Eloquent\Builder->get(Array)
#11 /Users/ruby/code/Foundation/vendor/illuminate/database/Eloquent/Model.php(1477): Illuminate\Database\Eloquent\Builder->first()
#12 /Users/ruby/code/Foundation/test.php(9): Illuminate\Database\Eloquent\Model->__call('first', Array)
#13 {main} 
```

**这里的第二条 log 记录了 SQL 语句产生的堆栈，开发者可以依据这些数据来精确判定是哪一行产生的 SQL。**


## 最佳实践

1. 执行 `composer require` 的时候，加上 `--dev`

2. 生产配置文件 `.env` 中，加上配置：`ENABLE_SQL_LOGGER=false`
