<?php

    namespace App\Lib;

    use EasySwoole\EasySwoole\Logger;

    class Log{
        static public function debug($msg){
            Logger::getInstance()->info($msg);
        }
        static public function error($msg){
            Logger::getInstance()->error($msg);
        }
    }

