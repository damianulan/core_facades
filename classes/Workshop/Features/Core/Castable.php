<?php

    namespace local_core_facades\Workshop\Features\Core;

    use Exception;

    trait Castable
    {
        protected $casts = [];

        /**
         * @param string $property
         * @param mixed  $value
         * @return void
         */
        protected function assignValue(string $property, $value){
            if(!is_null($value) && ($value == (int) $value || is_bool($value))){
                $value = (int) $value;
            }
            if(empty($value) && $value !== 0){
                $value = null;
            }

            if(isset($this->casts[$property])){
                switch($this->casts[$property]){
                    case "boolean":

                        break;
                    case "datetime":

                        break;
                }
            }

            $this->$property = $value;

            return $this;
        }


    }