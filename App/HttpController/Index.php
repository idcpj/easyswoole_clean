<?php
    namespace App\HttpController;


    use EasySwoole\Component\Di;
    use EasySwoole\FastCache\Cache;

    /**
     * curl http://127.0.0.1:9501/index.php
     * Class Index
     * @package App\HttpController
     */
    class Index extends Base{

        public function index(){
            echo"asd1";
            //Cache::getInstance()->set("asd", "aa");
            var_dump(Cache::getInstance()->get("asd"));
            return $this->writeJson(200,"asd3");
        }
    }