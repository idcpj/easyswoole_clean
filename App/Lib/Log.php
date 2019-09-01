<?php

    namespace App\Lib;

    use EasySwoole\EasySwoole\Logger;

    class Log{
        static public function debug($msg){
            if(is_array($msg)){
                $msg=json_encode($msg,JSON_UNESCAPED_UNICODE);
            }
            Logger::getInstance()->info($msg);
        }
        static public function error($msg){
            if(is_array($msg)){
                $msg=json_encode($msg,JSON_UNESCAPED_UNICODE);
            }
            Logger::getInstance()->error($msg);
        }
    }

