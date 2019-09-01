<?php

    namespace App\Lib\Upload;

    class Image extends Base{
        public $key='image';
        public $dirType="image";
        public $maxSize=122;
        public $fileExtTypes=[
            'png',
            'jpeg',
            'jeg',
            //todo
        ];

    }