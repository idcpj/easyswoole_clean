<?php

    namespace App\Utility;

    use EasySwoole\Component\Singleton;
    use EasySwoole\SyncInvoker\SyncInvoker;

    class Invoker extends   SyncInvoker{
        use Singleton;
    }