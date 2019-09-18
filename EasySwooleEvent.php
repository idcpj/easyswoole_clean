<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;


use App\Lib\Log;
use App\Utility\Invoker;
use App\Utility\InvokerDriver;
use EasySwoole\Component\Di;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\EasySwoole\Config as GConfig;
use EasySwoole\MysqliPool\Mysql;
use EasySwoole\Mysqli\Config;
use App\Utility\Pool\MysqlPool;
use EasySwoole\FastCache\CacheProcessConfig;
use EasySwoole\FastCache\SyncData;
use EasySwoole\Utility\File;

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

        //定时器
        //self::timer($register);

        self::Cache();
        self::hotReload();
        self::tcp();

        Invoker::getInstance(new InvokerDriver())->attachServer(ServerManager::getInstance()->getSwooleServer());

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

    public static function Cache(){
        // 每隔5秒将数据存回文件
        Cache::getInstance()->setTickInterval(5 * 1000);//设置定时频率
        Cache::getInstance()->setOnTick(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
            $data = [
                'data'  => $SyncData->getArray(),
                'queue' => $SyncData->getQueueArray(),
                'ttl'   => $SyncData->getTtlKeys(),
                // queue支持
                'jobIds'     => $SyncData->getJobIds(),
                'readyJob'   => $SyncData->getReadyJob(),
                'reserveJob' => $SyncData->getReserveJob(),
                'delayJob'   => $SyncData->getDelayJob(),
                'buryJob'    => $SyncData->getBuryJob(),
            ];
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            File::createFile($path,serialize($data));
        });

        // 启动时将存回的文件重新写入
        Cache::getInstance()->setOnStart(function (CacheProcessConfig $cacheProcessConfig) {
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            if(is_file($path)){
                $data = unserialize(file_get_contents($path));
                $syncData = new SyncData();
                $syncData->setArray($data['data']);
                $syncData->setQueueArray($data['queue']);
                $syncData->setTtlKeys(($data['ttl']));
                // queue支持
                $syncData->setJobIds($data['jobIds']);
                $syncData->setReadyJob($data['readyJob']);
                $syncData->setReserveJob($data['reserveJob']);
                $syncData->setDelayJob($data['delayJob']);
                $syncData->setBuryJob($data['buryJob']);
                return $syncData;
            }
        });

        // 在守护进程时,php easyswoole stop 时会调用,落地数据
        Cache::getInstance()->setOnShutdown(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
            $data = [
                'data'  => $SyncData->getArray(),
                'queue' => $SyncData->getQueueArray(),
                'ttl'   => $SyncData->getTtlKeys(),
                // queue支持
                'jobIds'     => $SyncData->getJobIds(),
                'readyJob'   => $SyncData->getReadyJob(),
                'reserveJob' => $SyncData->getReserveJob(),
                'delayJob'   => $SyncData->getDelayJob(),
                'buryJob'    => $SyncData->getBuryJob(),
            ];
            $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
            File::createFile($path,serialize($data));
        });

        Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)->attachToServer(ServerManager::getInstance()->getSwooleServer());

    }

    /**
     * @param EventRegister $register
     */
    private static function timer(EventRegister $register): void{
        $register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId){
            //只在第一个进程执行,这样只触发一次
            //dev.php 中 'worker_num' => 80, 确定了进程数
            if ($workerId == 0) {
                \EasySwoole\Component\Timer::getInstance()->loop(10 * 1000, function () use ($workerId){
                    echo $workerId . "\n";
                });
            }
        });
    }

    private static function hotReload(): void{
        $swooleServer = ServerManager::getInstance()->getSwooleServer();
        $swooleServer->addProcess((new \App\Process\HotReload('HotReload', ['disableInotify' => true]))->getProcess());
    }

    public static function tcp(){
        $server = ServerManager::getInstance()->getSwooleServer();

        ################# tcp 服务器1 没有处理粘包 #####################
        $subPort1 = $server->addlistener('0.0.0.0', 9502, SWOOLE_TCP);
        $subPort1->set(
            [
                'open_length_check' => false,//不验证数据包
            ]
        );
        $subPort1->on('connect', function (\swoole_server $server, int $fd, int $reactor_id) {
            echo "tcp服务1  fd:{$fd} 已连接\n";
            $str = '恭喜你连接成功服务器1';
            $server->send($fd, $str);
        });
        $subPort1->on('close', function (\swoole_server $server, int $fd, int $reactor_id) {
            echo "tcp服务1  fd:{$fd} 已关闭\n";
        });
        $subPort1->on('receive', function (\swoole_server $server, int $fd, int $reactor_id, string $data) {
            $server = ServerManager::getInstance()->getSwooleServer();
            $fdinfo = $server->getClientInfo($fd);
            var_dump($fdinfo);
            echo "tcp服务1  fd:{$fd} 发送消息:{$data}\n";
            $server->send($fd, "接受到你的 data {$data}");
        });
    }

}