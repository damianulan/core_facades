<?php

    namespace local_core_facades\Http\Routing;

    use Exception;
    use local_core_facades\Http\Routing\Route;
    use local_core_facades\Http\Routing\Request;
    use local_core_facades\Facades\Middlewares\Middleware;
    class Router 
    {

        public $routes;

        public function __construct( $routes)
        {
            if(!empty($routes)){
                foreach($routes as $key => $route)
                {
                    $this->routes[$key] = $route;
                }
            } else {
                throw new Exception ("Couldn't find any routes.");
            }
        }

        public function target(Request $request)
        {
            if(array_key_exists('action', $request->parameters) && $request->parameters['action'] != '' && !is_null($request->parameters['action'])){
                $key = $this->findRouteByParameter($request->parameters['action'], $request);
                if(is_null($key)){
                    http_response_code(404);exit;
                } else {
                    if(!empty($key)){
                        $controller = $this->routes[$key]['invoke']['controller'];
                        $method = $this->routes[$key]['invoke']['method'];
                        $middlewares = $this->routes[$key]['middlewares'];
                        $middlewareResult = true;

                        if(!empty($middlewares)){
                            foreach($middlewares as $middleware){
                                if(!Middleware::boot($middleware)){
                                    $middlewareResult = false;
                                }
                            }
                        }
    
                        if(method_exists($controller, $method) && $middlewareResult){
                            $reflection = new \ReflectionMethod($controller, $method);
                            $arguments = [];
                            foreach($reflection->getParameters() as $param)
                            {
                                $arg = null;
                                // If there's a Request param required, assign current instance
                                if(!is_null($param->getType()) && $param->getType()->getName() === 'local_core_facades\Http\Routing\Request'){
                                    $arguments[$param->name] = $request;
                                } else {
                                    $param_type = $param->getType();

                                    if(!array_key_exists($param->name, $request->parameters)){
                                        if(!$param->allowsNull()) {
                                            if($param->isOptional()) {
                                                $arg = $param->getDefaultValue();
                                            }
                                            else {
                                                $message = "Missing required parameter '".$param->name."'";
                                                if($param_type){
                                                    $message .= " of type '".$param_type->getName()."'";
                                                }
                                                $message .= ".";
                                                throw new Exception($message);
                                            }
                                        }
                                    } else {
                                        $arg = $request->parameters[$param->name];
                                        if(!is_null($param_type)){
                                            if($param_type->getName() !== get_debug_type($arg)){
                                                throw new Exception("Parameter '$param->name' is not matching the type! ($param_type)");
                                            }
                                        }
                                    }

                                    $arguments[$param->name] = $arg;

                                }
                            }
                            return $reflection->invokeArgs((new $controller), $arguments);
                        } else {
                            throw new Exception("There's no such method: [".$controller."@".$method."].");
                        }
    
                    } else {
                        throw new Exception("Route key is empty!");
                    }
                }

            }
        }

        public function getCurrentRoute(): ?string
        {
            $request = new Request();
            if(isset($request->parameters['action']) && $request->parameters['action'] != '' && !is_null($request->parameters['action'])){
                return $this->findRouteByParameter($request->parameters['action'], $request);
            }
        }

        public function findRouteByParameter(string $parameter, Request $request): ?string
        {
            if(count($this->routes)){
                foreach($this->routes as $key => $attribute)
                {
                    if(strpos($key,$request->core_module.'.') !== false){
                        if($attribute['route'] === $parameter)
                        {
                            return $key;
                        }
                    }
                }
            }
            return null;
        }

        public function routeExists(string $name): bool
        {
            if(count($this->routes)){
                foreach($this->routes as $key => $a)
                {
                    if($key === $name){
                        return true;
                    }
                }
            }
            return false;
        }

        public function routeParentExists (string $parent): bool
        {
            if(count($this->routes)){
                foreach($this->routes as $key => $a)
                {
                    if(strpos($key, $parent) >= 0){
                        return true;
                    }
                }
            }
            return false;
        }

    }