<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\EasySwoole\Config as GConfig;
use EasySwoole\MysqliPool\Mysql;
use EasySwoole\Mysqli\Config;
use App\Utility\Pool\MysqlPool;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        \App\Utility\utils::includeConfig();
        $configData = GConfig::getInstance()->getConf('MYSQL');
        $config = new Config($configData);
        /**
        这里注册的名字叫mysql，你可以注册多个，比如mysql2,mysql3
         */
        Mysql::getInstance()->register('mysql',$config);


        //print_r(GConfig::getInstance()->getConf("MYSQL"));
}

    public static function mainServerCreate(EventRegister $register)
    {

        PoolManager::getInstance()->register(MysqlPool::class);

        $register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId) {
            //只在第一个进程执行,这样只触发一次
            //dev.php 中 'worker_num' => 80, 确定了进程数
            if ($workerId == 0) {
                \EasySwoole\Component\Timer::getInstance()->loop(10 * 1000, function () use ($workerId){
                    echo $workerId."\n";
                });
            }
        });



    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}