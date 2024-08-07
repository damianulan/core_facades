<?php

    namespace local_core_facades\Workshop\Features\Core;

    use Exception;
    use local_core_facades\App\Model;

    trait EventfulModel
    {
        protected static $registeredEvents = [];

        public static function created($callback)
        {
            return static::registerEvent(__FUNCTION__, $callback);
        }

        public static function updated($callback)
        {
            return static::registerEvent(__FUNCTION__, $callback);
        }

        public static function deleted($callback)
        {
            return static::registerEvent(__FUNCTION__, $callback);
        }

        public static function retrieved($callback)
        {
            return static::registerEvent(__FUNCTION__, $callback);
        }

        private static function registerEvent($type, $callback)
        {
            if(is_callable($callback)){
                static::$registeredEvents[$type][] = $callback;
            }
        }

        protected function fireEvent(string $type)
        {
            if(isset(static::$registeredEvents[$type]) && count(static::$registeredEvents[$type])){
                foreach(static::$registeredEvents[$type] as $func) {
                    if(is_callable($func) && $this instanceof Model){
                        $func($this);
                    } 
                }
            }
        }


    }