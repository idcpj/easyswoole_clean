<?php

    namespace App\Model;

    use EasySwoole\Component\Di;
    use EasySwoole\MysqliPool\Mysql;

    class Base{
        public $tableName;
        public $db;

        public function __construct(){
            if (empty($this->tableName)) {
                throw new \Exception("table error");
            }
            $db =  \EasySwoole\MysqliPool\Mysql::defer('mysql');
            if (empty($db)){
                throw new \Exception("db config is empty");
            }
            if (  ! ($db instanceof \EasySwoole\Mysqli\Mysqli)) {
                throw new \Exception('db error');
            }
            $this->db = $db;
        }

        public function add($data){
            if (empty($data) || !is_array($data)){
                return false;
            }
            return $this->db->insert($this->tableName, $data);
        }
    }