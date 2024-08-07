<?php

    namespace local_core_facades\App;

    class Controller
    {
        public $middleware = [];
        public $cfg;
        
        public function __construct()
        {
            global $CFG;
            $this->cfg = $CFG;
        }

        public function middleware(string $instruction)
        {
            
        }

    }