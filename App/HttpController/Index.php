<?php
    namespace App\HttpController;


    use EasySwoole\Component\Di;

    class Index extends Base{

        public function index(){
            /** @var \EasySwoole\MysqliPool\Connection $db */
            Di::getInstance()->get("redis")->set("a", "b");
            print_r(Di::getInstance()->get("redis")->get("a"));
            return $this->writeJson('11','111','');
        }
    }