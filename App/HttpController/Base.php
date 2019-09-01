<?php
    namespace App\HttpController;

    use EasySwoole\Http\AbstractInterface\Controller;

    class Base extends Controller{

        protected function writeJson($statusCode = 200, $msg = '', $result = [])
        {
            parent::writeJson($statusCode,[],$msg);
        }

        function index(){
            // TODO: Implement index() method.
        }
    }