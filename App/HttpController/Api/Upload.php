<?php
    namespace App\HttpController\Api;


    use App\HttpController\Base;
    use App\Lib\ClassArr;

    /**
     *  http://localhost:9501/api/upload/file/
     * Class Upload
     * @package App\HttpController\Api
     */
    class Upload extends Base{
        public function file(){

            $request = $this->request();
            $files = $request->getSwooleRequest()->files;
            $type=array_keys($files)[0];
            if (empty($type)){
                return $this->writeJson(400,"upload file is failed");
            }
            try{
                $classArr = new ClassArr();
                $uploadObj = $classArr->initClass($type, ClassArr::uploadClassStat(),[$request,$type]);
                $filePath = $uploadObj->upload();
            } catch (\Exception $e){
                return $this->writeJson(400,$e->getMessage());
            }
            if (empty($filePath)){
                $this->writeJson(400,"upload file is failed");
            }
            $data =[
                'url'=>$filePath,
            ];
            return  $this->writeJson(200,'ok',$data);
        }

    }

