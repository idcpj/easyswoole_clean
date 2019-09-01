<?php

    namespace App\Utility;

    class utils{
        /**
         * 自动引入配置文件
         */
        static function includeConfig(){
            $configPath = EASYSWOOLE_ROOT . "/Config/";
            $configs = [];
            $handler = opendir($configPath);
            while (($filename = readdir($handler)) !== false) {//务必使用!==，防止目录下出现类似文件名“0”等情况
                if ($filename != "." && $filename != "..") {
                    $path = $configPath . $filename;
                    $configs[basename($filename, '.php')] = include_once $path;
                }
            }
            foreach ($configs as $k => $v) {
                \EasySwoole\EasySwoole\Config::getInstance()->setConf(strtoupper($k), $v);
            }
        }
    }

