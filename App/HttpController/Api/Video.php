<?php

    namespace App\HttpController\Api;


    use App\Lib\Log;
    use EasySwoole\Http\Message\Status;

    class Video extends \App\HttpController\Base{
        public function add(){
            $params = $this->request()->getRequestParam();
            $data=[
                'name'=>$params['name'],
                'url'=>$params['url'],
                'image'=>$params['image'],
                'content'=>$params['content'],
                'cat_id'=>$params['cat_id'],
                'create_time'=>time(),
                'update_time'=>time(),
                'status'=>1,
            ];
            Log::debug($data);
            try{
                $video = new \App\Model\Video();
                $videoId = $video->add($data);
                Log::debug($videoId);
                if ($videoId===false){
                    throw new \Exception($video->db->getLastError());
                }
            } catch (\Exception $e){
                Log::error($e->getFile()." ".$e->getLine());
               return $this->writeJson(Status::CODE_BAD_REQUEST,$e->getMessage());
            }
            return $this->writeJson(Status::CODE_OK,Status::getReasonPhrase(Status::CODE_OK),['id'=>$videoId]);
        }
    }
