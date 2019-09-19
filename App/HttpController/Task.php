<?php
    namespace App\HttpController;

    use EasySwoole\EasySwoole\Task\TaskManager;

    class Task extends Base{

        /**
         * curl http://127.0.0.1:9501/task
         */
        public function index(){
            // 异步执行
            TaskManager::getInstance()->async(function (){
                sleep(5);
                var_dump('rrrrr');
            });
            $this->writeJson(200,"task");
        }
    }

