<?php

    namespace App\Process;
    use EasySwoole\Component\Process\AbstractProcess;

    class Test extends AbstractProcess{

        protected function run($arg){
            //当进程启动后，会执行的回调
            var_dump($arg);
            var_dump("processname : ".$this->getProcessName());
            var_dump("pid :" . $this->getPid());
            $this->addTick();
        }
        public function addTick(): ?int{
            return parent::addTick(1*1000, function (){
                echo "({$this->getPid()}) hello word\n";
            });
        }
    }

