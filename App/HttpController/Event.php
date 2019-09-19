<?php

    namespace  App\HttpController;

    class Event extends Base{
        /**
         * curl http://127.0.0.1:9501/event
         */
        public function index(){

            \App\Event\Event::getInstance()->hook('test','123','222');
            $this->writeJson(200,'hello word');
        }
    }

