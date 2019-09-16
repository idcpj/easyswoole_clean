<?php

    namespace App\Es;

    use App\Lib\Log;
    use EasySwoole\Component\Singleton;
    use EasySwoole\EasySwoole\Config as GConfig;

    class Base  {
        use Singleton;
        public  $es ;
        public function __construct(){
            if (empty($this->es)){
                $hosts=GConfig::getInstance()->getConf("ES");
                if (empty($hosts)){
                    throw new \Exception("没有找到 elasticsearch 的参数");
                }
                Log::debug($hosts[0]);
                $client = \Elasticsearch\ClientBuilder::create()->build();
                if (empty($client)){
                    throw new \Exception("无法获取 elasticsearch");
                }
                 $this->es=$client;
            }
        }

        public function ping(){
            return $this->es->ping();
        }
    }

