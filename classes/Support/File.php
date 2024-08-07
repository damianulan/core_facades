<?php

    namespace local_core_facades\Support;

    use Exception;
    use stdClass;

    Class File
    {
        public $path;

        public function __construct(string $path)
        {
            if(file_exists($path)){
                $this->path = $path;
            } else {
                throw new Exception("Couldn't create File instance as the given file doesn't exist!");
            }
        }

        public function contents()
        {
            return file_get_contents($this->path);
        }
        
    }
