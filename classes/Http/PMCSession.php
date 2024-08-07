<?php

    namespace local_core_facades\Http;

    use Exception;
    use stdClass;
    use local_core_facades\Support\Arr;

    class FacadeSession
    {
        public array $options;

        public function __construct()
        {
            $this->loadOptions();
        }

        private function loadOptions()
        {
            $this->options = [];
        }

        private function setKeyExpiration(string $key, int $hops)
        {
            $this->options['key_expiration_rules'][$key] = $hops;
        }

        public function put(string $key, $contents, int $hops = 0)
        {
            if($hops){
                $this->setKeyExpiration($key, $hops);
            }
            $this->$key = $contents;
            if($this->key){
                return $this;
            }
            return null;
        }

        public function add(string $key, $contents)
        {
            if(!is_array($contents)){
                $contents = array($contents);
            }
            if(!is_array($this->$key)){
                $this->$key = array($this->$key);
            }

            return Arr::join_assoc($this->$key, $contents);
        }

        public function save()
        {
            global $SESSION;
            $SESSION->core_session = $this;
            return true;
        }

    }