<?php

    namespace local_core_facades\Http;

    use Exception;
    use local_core_facades\Http\FacadeSession;
    use local_core_facades\Http\Routing\Request;

    class Session
    {

        public static function reproduce($new_instance = false)
        {
            global $SESSION;
            if(!isset($SESSION->core_session) || $new_instance){
                $SESSION->core_session = new FacadeSession(); 
            }
            return $SESSION->core_session;
        }

        public static function get(string $key)
        {
            $session = self::getAll();
            return $session->$key;
        }

        public static function getAll(): FacadeSession
        {
            return self::reproduce();
        }

        public static function getRoutes()
        {
            global $SESSION;

            if(isset($SESSION->routes)){
                return $SESSION->routes;
            }
            return null;
        }

        public static function put(string $key, $contents)
        {
            $session = self::getAll();
            return $session->put($key, $contents);
        }

        /**
         * Adds to a possibly existing key more values.
         */
        public static function add(string $key, $contents)
        {
            $session = self::getAll();
            if(!isset($session->$key)){
                return $session->put($key, $contents);
            }

            return $session->add($key, $contents);
        }

        public static function flash(string $key, $contents)
        {
            $session = self::getAll();
            return $session->put($key, $contents, 1);
        }

        public static function reflash(string $key, $contents)
        {
            $session = self::getAll();
            return $session->put($key, $contents, 1);
        }

        /**
         * Search for key directly in Facade Session 
         */
        public static function has(string $key): bool
        {
            $session = self::getAll();
            if(isset($session->$key)){
                return true;
            }
            return false;
        }

        public static function destroy(string $key): bool
        {
            $session = self::getAll();
            unset($session->$key);
            if(isset($session->$key)){
                return false;
            }
            return true;
        }

    }