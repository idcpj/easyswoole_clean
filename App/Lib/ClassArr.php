<?php
    namespace App\Lib;

    /**
     * 类的反射机制
     * @package App\Lib
     */
    class ClassArr {

        /**
         * @param       $type
         * @param       $supportedClass
         * @param array $params
         * @param bool  $needInstance
         * @return bool|mixed|object
         * @throws \ReflectionException
         *
         */
        public function initClass($type, $supportedClass,$params=[],$needInstance=true){
            if (!array_key_exists($type, $supportedClass)){
                return false;
            }
            $className = $supportedClass[$type];
            return $needInstance?(new \ReflectionClass($className))->newInstanceArgs($params):$className;
        }
        static public function uploadClassStat(){
            return[
                'image'=>'\App\Lib\Upload\Image',
                'video'=>'\App\Lib\Upload\Video',
            ];
        }
    }

