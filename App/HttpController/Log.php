<?php
    namespace App\HttpController;


    use  EasySwoole\EasySwoole\Logger;


    /**
     * Class Index
     * @package App\HttpController
     */
    class Log extends Base{

        //curl    http://127.0.0.1:9501/log
        public function index(){

            Logger::getInstance()->info('log level info');//记录info级别日志并输出到控制台
            Logger::getInstance()->notice('log level notice');//记录notice级别日志并输出到控制台
            Logger::getInstance()->waring('log level waring');//记录waring级别日志并输出到控制台
            Logger::getInstance()->error('log level error');//记录error级别日志并输出到控制台

            $this->writeJson(200,"ok");
        }


    }