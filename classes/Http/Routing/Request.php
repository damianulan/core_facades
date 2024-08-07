<?php

    namespace local_core_facades\Http\Routing;

    use Exception;
    use local_core_facades\Support\Sanitizer;
    use local_core_facades\Support\Validator;
    use local_core_facades\Http\Routing\URL;
    use local_core_facades\Http\QueryEngine;
    use local_core_facades\Http\Session;
    use local_core_facades\Support\Str;
    use local_core_facades\Support\CSRF;
    use local_core_facades\App\Model;
    class Request 
    {
        protected $sesskey;
        protected $checksum;
        private $headers;
        public $root;
        public $method;
        public $caller;
        public $uri;
        public $full_url;
        public $route;
        public $query;
        public $cookies = [];
        public $core_module;

        public $redirect;
         
        // GET ParameterBag
        public $parameters;

        /**
         * If Request is processed instantabily, theres no need to process the Session option rules.
         */
        public function __construct(bool $instantable = false)
        {
            $this->method = filter_var($_SERVER['REQUEST_METHOD']);
            $this->uri = filter_var($_SERVER['REQUEST_URI']);
            $this->full_url = (new URL())->current;
            $this->sesskey = CSRF::GetCSRF();
            $this->root = filter_var($_SERVER['DOCUMENT_ROOT']);
            $this->parameters = QueryEngine::normalizeParameters($_REQUEST);
            $this->caller = parent_caller_class();
            $this->headers = getallheaders();
            
            // Require action parameter for Routing at all times
            if(!isset($this->parameters['action'])){
                throw new Exception('Missing route parameter [action]. Without it, You cannot use the Request class.');
            }

            $this->validateCsrf();
            $this->setCookies();
            $this->setQuery();
            $this->getCurrentRoute();

            // always last thing to do
            if($instantable){
                $this->processSession();
            }
        }

        public static function instance()
        {
            return new self(true);
        }

        /**
         * eg. $request->validate([
         *      'name' => 'required|max:255'
         *       ...
         * ]);
         * @param array $rules
         */
        public function validate(array $rules): Validator 
        {
            if(count($this->parameters)){
                return Validator::make($this->parameters, $rules);
            }
        
            else {
                throw new Exception("No inputs were found in your request to validate.");
            }
        }

        public function old()
        {
            $session = Session::getAll();
            if(isset($session->old['request'])){
                return $session->old['request'];
            }
            return null;
        }

        private function setCookies()
        {
            foreach ($_COOKIE as $name => $cookie){
                $this->cookies[$name] = Sanitizer::cleanCookie($cookie);
            }  
        }

        private function setQuery()
        {
            $this->query = QueryEngine::normalizeQueryString($_SERVER['QUERY_STRING']);
        }

        private function getCurrentRoute()
        {
            $router = $GLOBALS['router'];
            if(isset($this->parameters['action']) && $this->parameters['action'] != '' && !is_null($this->parameters['action'])){
                if(count($router->routes)){
                    foreach($router->routes as $key => $attribute)
                    {
                        if($attribute['route'] === $this->parameters['action'])
                        {
                            $this->route = $attribute;
                            $this->core_module = $attribute['module']['name'];
                        }
                    }
                }
            }
        }

        private function processSession()
        {
            $this->checksum = md5(serialize(get_object_vars($this)));
            $session = Session::getAll();

            if(isset($session->request)){
                if($this->checksum !== $session->request->checksum){
                    $session->old['request'] = $session->request;
                }
            }
            $session->request = $this;

            if(isset($session->url['current'])){
                $session->url['previous'] = $session->url['current'];
            }
            $session->url['current'] = $this->full_url;

            if(isset($session->old['request']->route) && $session->old['request']->route !== $session->request->route){
                if(isset($session->options['key_expiration_rules']) && count($session->options['key_expiration_rules']))
                {
                    foreach($session->options['key_expiration_rules'] as $key => $hops)
                    {
                        $session->options['key_expiration_rules'][$key] = ( (int)$hops - 1 );
    
                        if($session->options['key_expiration_rules'][$key] < 1){
                            unset($session->options['key_expiration_rules'][$key]);
                        }
                    }
                }
            }
            $session->save();
        }

        private function validateCsrf()
        {
            $headers = getallheaders();
            $token = null;

            if(isset($this->parameters['_token'])){
                $token = $this->parameters['_token'];
            } elseif(isset($headers['X-CSRF-TOKEN'])) {
                $token = $headers['X-CSRF-TOKEN'];
            }
            // Require CSRF when sending POST form
            if(!empty($_POST) && !$token){
                throw new Exception("Facade CSRF token not provided!");
            } else {
                CSRF::validate($this->sesskey, $token);
            }
        }

        public function hasParam(string $param): bool
        {
            return isset($this->parameters[$param]);
        }

        public function getParam(string $param)
        {
            return isset($this->parameters[$param]) ? $this->parameters[$param]:null;
        }

        public function getParameters(): array
        {
            $params = $this->parameters;
            if(isset($params['action'])){
                unset($params['action']);
            }
            if(isset($params['_token'])){
                unset($params['_token']);
            }
            return $params;
        }

        public function getPublic(): string
        {
            global $CFG;
            return $CFG->wwwroot . '/local/'.$this->core_module.'/public/index.php';
        }

        public function fillModel($class)
        {
            $rc = new \ReflectionClass($class);
            if($rc){
                if($rc->isSubclassOf(Model::class)){
                    $params = $this->getParameters();
                    $instance = null;
                    if(isset($params['id']) && !empty($params['id'])){
                        $instance = $class::getDeleted($params['id']);
                    } else {
                        $instance = new $class();
                    }
                    if($instance){
                        return $instance->fillWith($params);
                    }
                } else {
                    throw new Exception("Wskazany element nie jest subklasą dla ".Model::class.".");
                }
            }
            return null;
        }

    }