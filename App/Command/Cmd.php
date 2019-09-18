<?php

    namespace App\Command;
    use EasySwoole\EasySwoole\Command\Utility;

    class Cmd implements \EasySwoole\EasySwoole\Command\CommandInterface{

        public function commandName(): string{
            return "cmd";
        }

        public function exec(array $args): ?string{
            //打印参数,打印测试值
            var_dump($args);
            echo 'test'.PHP_EOL;
            return null;
        }

        public function help(array $args): ?string{
            //输出logo
            $logo = Utility::easySwooleLog();
            return $logo."this is test";
        }
    }