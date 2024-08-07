<?php

    namespace local_core_facades\Http\Routing;

    class URL
    {

        public $protocol;
        public $host;
        public $fullhost;
        public $current;

        public function __construct()
        {
            $this->protocol = $this->setProtocol();
            $this->current = $this->getUrl();
            $this->fullhost = $this->getFullHost();
        }

        public function getUrl()
        {
            return urlencode($this->protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        }

        private function setProtocol()
        {
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        }

        private function getFullHost()
        {
            return $this->protocol . "://$_SERVER[HTTP_HOST]";
        }

        public static function getCurrent()
        {
            return urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        }

    }