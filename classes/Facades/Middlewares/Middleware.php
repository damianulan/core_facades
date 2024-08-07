<?php

    namespace local_core_facades\Facades\Middlewares;

    use local_core_facades\App\Models\User;
    use Exception;
    class Middleware 
    {
        public static function boot(string $rule): bool
        {
            $tmp = explode('::', $rule);
            $obj = ucfirst($tmp[0]);
            $class = "local_core_facades\\Facades\\Middlewares\\$obj";

            if(method_exists($class, 'boot')){
                $values = explode(',', $tmp[1]);

                foreach($values as $value){
                    if(!$class::boot($value)){
                        return false;
                    }
                }
            } else {
                throw new Exception("Obiekt $class lub metoda ::boot() nie istnieją!");
            }

            return true;
        }
    }