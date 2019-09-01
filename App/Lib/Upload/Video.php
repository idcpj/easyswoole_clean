<?php

    namespace App\Lib\Upload;

    class Video extends Base{
        public $key='video';
        public $dirType="video";
        public $maxSize=122;
        public $fileExtTypes=[
            'mp4',
            'x-flv',
        ];

    }