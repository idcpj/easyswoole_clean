<?php
    namespace App\HttpController;


    class Index extends base{

        public function index(){
            /** @var \EasySwoole\MysqliPool\Connection $db */
            $redis = new \Redis();
            $redis->connect("127.0.0.1");
            print_r($redis->ping());

            $db = \App\Utility\Pool\MysqlPool::defer();
            $rawQuery = $db->getOne("test");

            return $this->writeJson('11','111',$rawQuery);
        }
    }