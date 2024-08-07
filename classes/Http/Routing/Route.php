<?php

    namespace local_core_facades\Http\Routing;

    use Exception;
    use local_core_facades\Http\Routing\URL;
    use local_core_facades\Http\Routing\Request;
    use local_core_facades\Facades\Modules\FacadeModule;

    class Route
    {

        public $route;
        public $invoke;
        public $method;
        public $module;

        public $url;
        public $path;
        public array $middlewares = [];

        /**
         * @param string $route
         * given uri route after a parameter 'action'
         * @param string $invoke
         * give full path of a target controller and method name separated with '@'
         * @param string $method
         * method declaration, use 'GET' / 'POST'
         */
        public function __construct(string $route, string $invoke, string $method = 'GET')
        {
            if($method === 'GET' || $method === 'POST'){
                $this->route = $route;
                $params = [];
                if(strpos($invoke, '@')){
                    $e = explode('@', $invoke);
                    $params = [
                        'controller' => $e[0],
                        'method' => $e[1],
                    ];
                } else {
                    $params = [
                        'controller' => $invoke, 
                        'method' => 'index'
                    ];
                }
                $this->invoke = $params;
                $this->method = $method;
                $this->module = (new FacadeModule($invoke));
                $this->url = $this->composeURL();
                $this->path = $this->module->name.'.'.$this->route;

                if(!$this->validate()){
                    throw new Exception(
                        "Missing some Route parameters or their construction is wrong. See documentation for more info."
                    );
                }

            } else {
                throw new Exception(
                    'Unknown method protocol. Try GET|POST.'
                );
            }

        }

        private function composeURL()
        {
            $route = (new URL())->fullhost."/local/".$this->module->name."/public?action=".$this->route;
            return urlencode($route);
        }

        private function validate(): bool
        {
            $required = [
                'route', 'invoke', 'method', 'module', 'url', 'path'
            ];
            foreach($this as $param => $value){
                if(in_array($param, $required)){
                    if(empty($value)){
                        return false;
                    }
                }
            }
            return true;
        }

        public function middleware(string $rules_raw){
            $this->middlewares = explode('|', $rules_raw);
            return $this;
        }

        public static function get(string $route, string $invoke): self
        {
            return new self($route, $invoke, 'GET');
        }

        public static function post(string $route, string $invoke): self
        {
            return new self($route, $invoke, 'POST');
        }

    }