<?php
    namespace App\HttpController;


    use App\MyInvoker;
    use App\Utility\Invoker;
    use App\Utility\InvokerDriver;
    use EasySwoole\Component\Di;
    use EasySwoole\FastCache\Cache;
    use EasySwoole\Http\Message\Status;
    use EasySwoole\Validate\Validate;

    /**
     * Class Index
     * @package App\HttpController
     */
    class Es extends Base{

        //curl    http://127.0.0.1:9501/es/get
        public function get(){

            return $this->writeJson(200,"asd");
            $client = \Elasticsearch\ClientBuilder::create()->build();


        }


    }