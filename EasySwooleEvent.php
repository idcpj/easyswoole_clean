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
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Task\TaskManager;
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
use Swoole\Table;

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

        self::Event();
}

    public static function mainServerCreate(EventRegister $register)
    {

        PoolManager::getInstance()->register(MysqlPool::class);

        //定时器
        //self::timer($register);

        self::Cache();
        self::hotReload();
        self::tcp();
        self::Process();
        self::crontab();
        self::LogHook();
        self::Table();

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

    protected static function Process(){
        $processConfig = new \EasySwoole\Component\Process\Config();
        $processConfig->setProcessName("testProcess");
        $processConfig->setArg([
            'arg'=>time(),
        ]);
        //可开多个进程
        for ($i=0; $i < 3; $i++) {
            //ServerManager::getInstance()->getSwooleServer()->addProcess((new \App\Process\Test($processConfig))->getProcess());
        }

    }

    public  static function Event(){
        \App\Event\Event::getInstance()->set('test', function ($arg,$arg1) {
            echo "test event:{$arg},{$arg1}\n";
        });
        \App\Event\Event::getInstance()->set('test2', function ($arg,$arg1) {
            echo "test event:{$arg},{$arg1}\n";
        });

        //别处调用
        \App\Event\Event::getInstance()->hook('test','123','222');
        //删除 test2 的hook
        \App\Event\Event::getInstance()->delete('test2');
        //清空所有 hook
        //\App\Event\Event::getInstance()->clear();
        \App\Event\Event::getInstance()->hook('test2','abc','def');
    }

    public  static function crontab(){
        // 开始一个定时任务计划
        Crontab::getInstance()->addTask(\App\Crontab\TaskOne::class);
    }

    public static function LogHook(){
        Logger::getInstance()->onLog()->set('myHook',function ($msg,$logLevel,$category){
            //增加日志写入之后的回调函数
            var_dump(sprintf("category : %s , logLevel : %s , msg : %s", $category,$logLevel,$msg));
        });

    }

    public static function Table(){
        TableManager::getInstance()->add(
            "test",
            [
                'Num'=>['type'=>Table::TYPE_INT,'size'=>2],
                'Num2'=>['type'=>Table::TYPE_INT,'size'=>2],//似乎 size 不管等于几, 都是 4,size 带有符号
                'str'=>['type'=>Table::TYPE_STRING,'size'=>8],//字符串长度的等于实际长度,最小设置 8,最大10 个字符
            ],
            1024
        );
        $table = TableManager::getInstance()->get("test");
        $table->set(1, ['Num'=>2,'Num2'=>2147483647,'str'=>'1234567890']);//最大 2147483647
        $table->set(2, ['Num'=>1,'Num2'=>1,'str'=>'1']);//最大 2147483648
        $table->incr('1', 'Num',1);// 默认自增 1
        $table->del(2); // 删除 del
        var_dump($table->exist(2)); // key 1 是否存在  bool
        var_dump("TableManager : ",$table->get(1));
        /**
         * array(2) {
            ["Num"]=>int(3)
            ["Num2"]=>int(2147483647)
            ["str"]=>string(8) "12345678"
          }
         */
    }

}