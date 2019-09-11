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
     * curl http://127.0.0.1:9501/sms?phone=12321312312
     * Class Index
     * @package App\HttpController
     */
    class Index extends Base{

        //curl    http://127.0.0.1:9501/sms?phone=123
        public function sms(){

        }

        /**
         *curl    http://127.0.0.1:9501/testinvoker
         */
        public function testinvoker(){
            $ret = Invoker::getInstance()->client()->test(1,2);
            $this->writeJson(200,"phone {$ret}");
        }


    }