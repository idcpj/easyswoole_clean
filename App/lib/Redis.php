<?php

    namespace App\lib;

    use EasySwoole\EasySwoole\Config;

    class Redis{
        use \EasySwoole\Component\Singleton;//引入单例
        public $redis;

        private function __construct(){
            if(!extension_loaded("redis")){
                throw new \Exception("redis.io 文件不存在");
            }
            try{
                $conf = Config::getInstance()->getConf('REDIS');
                $this->redis = new \Redis();
                $result = $this->redis->connect($conf ['host'], $conf['port'], $conf['timeout']);
            } catch (\Exception $e){
                throw new \Exception("redis 服务器异常");
            }

            if ($result===false){
                throw new \Exception("redis connect is failed");
            }
        }

        public function get($key){
            if (empty($key)) {
                return '';
            }
            return $this->redis->get($key);
        }

        public function set($key, $value){
            return $this->redis->set($key, $value);
        }
    }