<?php

    namespace local_core_facades\Support;

    class FilterType
    {

        public static function isInteger ($input, $options = null)
        {
            if($input === '0' || $input === 0){
                return true;
            }
            return filter_var($input, FILTER_VALIDATE_INT, $options);
        }

        public static function isFloat ($input, $options = null)
        {
            return filter_var($input, FILTER_VALIDATE_FLOAT, $options);
        }

        public static function isBoolean ($input, $options = null)
        {
            return filter_var($input, FILTER_VALIDATE_BOOLEAN, $options);
        }

        public static function isEmail ($input, $options = null)
        {
            return filter_var($input, FILTER_VALIDATE_EMAIL, $options);
        }

        public static function isURL ($input, $options = null)
        {
            return filter_var($input, FILTER_VALIDATE_URL, $options);
        }

        public static function isDomain ($input, $options = null)
        {
            return filter_var($input, FILTER_VALIDATE_DOMAIN, $options);
        }

        public static function isIP ($input, $options = null)
        {
            return filter_var($input, FILTER_VALIDATE_IP, $options);
        }

        public static function isJson ($input)
        {
            if(!is_array($input)){
                json_decode($input);
                return json_last_error() === JSON_ERROR_NONE;
            }
            return false;
        }
    }