<?php

    namespace  App\HttpController;

    class Event extends Base{
        /**
         * curl http://127.0.0.1:9502/event
         */
        public function index(){

            \App\Event\Event::getInstance()->hook('test');
            $this->writeJson(200,'hello word');
        }
    }

