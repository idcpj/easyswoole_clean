<?php

namespace App\Lib\Upload;


use App\Lib\Log;
use App\Lib\Utils;
use EasySwoole\Http\Request;

class Base{
    private $request;
    /**
     * 上传文件的 post 参数 key 值
     * @var string
     */
    public  $key='file';
    /**
     * 文件保存的目录
     * @var string
     */
    public $dirType='file';
    /**
     * 文件大小
     * @var int
     */
    public $size=0;
    public $maxSize = 0;
    private $fileName;
    /**
     * 上传文件类型
     * @string
     */
    private $clientMediaType='';
    public $fileExtTypes=[
        //'mp4',
        //'x-flv',
    ];

    public function __construct(Request $request,$type='' ){
        $this->request=$request;
        /**
         * $files
         * Array(
                [file] => Array(
                    [name] => tmp.mp4
                    [type] => *\/*; charset=UTF-8
                    [tmp_name] => /tmp/swoole.upfile.NYXyEw
                    [error] => 0
                    [size] => 568151
                )
             )
         */
        if(empty($type)){
            $files= $this->request->getSwooleRequest()->files;
            $types=array_keys($files);
            $this->key= $types[0];
        }else{
            $this->key=$type;
        }

    }

    public function upload(){
        $uploadedFile = $this->request->getUploadedFile($this->key);
        $size = $uploadedFile->getSize();
        $this->size =$size;
        $this->checkSize();
        $fileName = $uploadedFile->getClientFilename();
        $this->clientMediaType = $uploadedFile->getClientMediaType();
        $this->checkMediaType();
        $filePath = $this->getFile($fileName);
        Log::debug('$filePath='.$filePath);
        if(empty($filePath)) {
            throw new \Exception('filePath is empty');
        }
        $flag = $uploadedFile->moveTo($filePath);
        if(empty($flag)){
            throw new \Exception('flag is failed');
        }
        return $filePath;

    }

    public function checkSize(){
        if (empty($this->size)){
            return false;
        }
    }

    public function checkMediaType(){
        $clientMediaType  = explode("/", $this->clientMediaType);
        $clientMediaType =$clientMediaType[1]??'';
        if (empty($clientMediaType)){
            throw new \Exception("上传{$this->key} 不合法");
        }
        if (!in_array($clientMediaType,$this->fileExtTypes)){
            throw new \Exception("上传{$this->key}的{$clientMediaType}文件不合法");
        }
        return true;
    }

    private function getFile($fileName){
        /**
         * [
            'dirname' => '/www/htdocs/inc',
            'basename' => 'lib.inc.php',
            'extension' => 'php',
            'filename' => 'lib.inc',
           ]
         */
        $pathinfo = pathinfo($fileName);
        //print_r($pathinfo);
        $extension = $pathinfo['extension'];
        $dirname = $this->dirType . '/' . date('Y') . '/' . date('m') . '/';
        $dir = EASYSWOOLE_ROOT . "/webroot/upload/" . $dirname;
        if (!is_dir($dir)){
            mkdir($dir,0777,true);
        }

        $basename = Utils::getFileKey($this->fileName) . '.' . $extension;
        return $this->fileName=$dir . $basename;

    }
}