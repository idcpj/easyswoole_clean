<?php

    namespace App\Crontab;


    use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
    use EasySwoole\EasySwoole\Task\TaskManager;

    class TaskOne extends AbstractCronTask{

        public static function getRule(): string{
            return '*/1 * * * *';
        }

        public static function getTaskName(): string{
            return  'taskOne';

        }

        function run(int $taskId, int $workerIndex){
            TaskManager::getInstance()->async(function (){
                var_dump('r');
            });
        }

        function onException(\Throwable $throwable, int $taskId, int $workerIndex){
            echo $throwable->getMessage();
        }
    }
