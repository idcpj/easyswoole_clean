<?php
    namespace App\HttpController;

    use EasySwoole\Http\AbstractInterface\Controller;

    class Base extends Controller{

        protected function writeJson($statusCode = 200, $msg = null, $result = null)
        {
            if (!$this->response()->isEndResponse()) {
                $data = Array(
                    "code" => $statusCode,
                    "msg" => $msg,
                    "result" => $result,
                );
                $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
                $this->response()->withStatus($statusCode);
                return true;
            } else {
                return false;
            }
        }

        function index(){
            // TODO: Implement index() method.
        }
    }