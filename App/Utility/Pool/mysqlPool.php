<?php
    namespace App\Utility\Pool;

    use EasySwoole\Component\Pool\AbstractPool;
    use EasySwoole\EasySwoole\Config;
    use EasySwoole\Component\Pool\PoolObjectInterface;
    use EasySwoole\Mysqli\Mysqli;


    class MysqlConnection extends Mysqli implements PoolObjectInterface
    {

        function gc()
        {
            $this->resetDbStatus();
            $this->getMysqlClient()->close();
        }

        function objectRestore()
        {
            $this->resetDbStatus();
        }

        function beforeUse(): bool
        {
            return $this->getMysqlClient()->connected;
        }
    }


    class MysqlPool extends AbstractPool
    {

        protected function createObject()
        {
            // TODO: Implement createObject() method.
            $conf = Config::getInstance()->getConf('MYSQL');
            $dbConf = new \EasySwoole\Mysqli\Config($conf);
            return new MysqlConnection($dbConf);
        }
    }
