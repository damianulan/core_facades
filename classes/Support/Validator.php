<?php

    namespace local_core_facades\Support;

    use DateTime;

    /**
     * 
     */
    class Validator
    {
        private $errors = [];
        private $rules = [];
        private $obj;

        public static function make($obj, array $rules): self
        {
            $instance = new self();
            if(is_array($obj)){
                $obj = (object) $obj;
            }
            $instance->rules = $rules;
            $instance->obj = $obj;
            $instance->validate();

            return $instance;
        }

        private function validate()
        {
            foreach($this->rules as $property => $rule) {
                $validators = explode('|',$rule);
                foreach($validators as $validator){
                    if(strpos($validator, 'required') !== false) {
                        if(isset($this->obj->$property)) {
                            $value = '';
                            if(is_array($this->obj->$property)){
                                $value = $this->obj->$property;
                            } else {
                                $value = trim($this->obj->$property);
                            }
                            if(!$value || empty($value)){
                                $this->makeError($property, core_lang('validation_required')); 
                            }
                        } else {
                            $this->makeError($property, core_lang('validation_required'));
                        }
                    } 
                    elseif(strpos($validator, 'radio') !== false) {
                        if(!isset($this->obj->$property) || ($this->obj->$property != 0 && $this->obj->$property != 1) ) {
                            $this->makeError($property, core_lang('required'));
                        }
                    }
                    elseif(strpos($validator, 'date') !== false && !empty($this->obj->$property)){
                        $date_rule = explode(',', $validator);
                        $date_format = 'Y-m-d';
                        if(count($date_rule) > 1) {
                            $date_format = $date_rule[1];
                        }
                        $date = DateTime::createFromFormat($date_format, $this->obj->$property);
                        if($date->format($date_format) != $this->obj->$property){
                            $this->makeError($property, core_lang('validation_incorrect_date_format'));
                        }
                    }
                    elseif(strpos($validator, 'time') !== false && !empty($this->obj->$property)){
                        $time_rule = explode(',', $validator);
                        $time_format = 'H:i:s';
                        if(count($time_rule) > 1) {
                            $time_format = $time_rule[1];
                        }
                        $date = DateTime::createFromFormat($time_format, $this->obj->$property);
                        if($date->format($time_format) != $this->obj->$property){
                            $this->makeError($property, core_lang('validation_incorrect_time_format'));
                        }
                    }
                    elseif(strpos($validator, 'integer') !== false){
                        if((int)$this->obj->$property != $this->obj->$property){
                            $this->makeError($property, core_lang('validation_incorrect_integer_format'));
                        }
                    }
                    elseif(strpos($validator, 'min') !== false){
                        $col = explode(':', $validator);
                        if(count($col) > 1){
                            $min = (int)$col[1];
                            if(is_numeric($this->obj->$property)){
                                if((int) $this->obj->$property < $min){
                                    $this->makeError($property, core_lang('validation_numeric_min', ['min' => $min]));
                                }
                            } else {
                                if(strlen($this->obj->$property) < $min){
                                    $this->makeError($property, core_lang('validation_string_min', ['min' => $min]));
                                }
                            }
                        }
                    }
                    elseif(strpos($validator, 'max') !== false){
                        $col = explode(':', $validator);
                        if(count($col) > 1){
                            $max = (int)$col[1];
                            if(is_numeric($this->obj->$property)){
                                if((int) $this->obj->$property > $max){
                                    $this->makeError($property, core_lang('validation_numeric_max', ['max' => $max]));
                                }
                            } else {
                                if(strlen($this->obj->$property) > $max){
                                    $this->makeError($property, core_lang('validation_string_max', ['max' => $max]));
                                }
                            }
                        }
                    }
                    elseif(strpos($validator, 'array') !== false){
                        if(!is_array($this->obj->$property)){
                            $this->makeError($property, core_lang('validation_array'));
                        }
                    }
                    elseif(strpos($validator, 'dependent_on') !== false) {
                        $d = explode(':', $validator);
                        $s = explode(',', $d[0]);
                        $value = 1;
                        if(isset($s[1])){
                            $value = $s[1];
                        }
                        $dependent_on = $d[1];
                        if($this->obj->$dependent_on != $value) {
                            if(isset($this->errors[$property])){
                                unset($this->errors[$property]);
                            }
                        } 
                    }
                }
            }

            return $this;
        }

        public function customDateComparison(
            ?int $date1, ?int $date2, string $errorProperty, string $date1Property, string $date2Property, string $operator = '<'
            ): self
        {
            if($date1 && $date2){
                switch($operator)
                {
                    case '<':
                        if($date1 >= $date2){
                            $this->makeError($errorProperty, core_lang('validation_date_lesser', [
                                'date1' => $date1Property,
                                'date2' => $date2Property
                            ]));
                        }
                        break;
                    case '<=':
                        if($date1 > $date2){
                            $this->makeError($errorProperty, core_lang('validation_date_lesser_equal', [
                                'date1' => $date1Property,
                                'date2' => $date2Property,
                            ]));
                        }
                        break;
                    case '=':
                        if($date1 !== $date2){
                            $this->makeError($errorProperty, core_lang('validation_date_equal', [
                                'date1' => $date1Property,
                                'date2' => $date2Property,
                            ]));
                        }
                        break;
                    case '>':
                        if($date1 <= $date2){
                            $this->makeError($errorProperty, core_lang('validation_date_greater', [
                                'date1' => $date1Property,
                                'date2' => $date2Property,
                            ]));
                        }
                        break;
                    case '>=':
                        if($date1 < $date2){
                            $this->makeError($errorProperty, core_lang('validation_date_greater_equal', [
                                'date1' => $date1Property,
                                'date2' => $date2Property,
                            ]));
                        }
                        break;
                }
            }

            return $this;
        }

        private function makeError($property, $text)
        {
            if(!isset($this->errors[$property])){
                $this->errors[$property] = $text;
            }
            return;
        }

        public function getErrors(): array
        {
            return $this->errors;
        }

        public function hasErrors(): bool
        {
            return count($this->errors) ? true:false;
        }

        public function valid(): bool
        {
            return count($this->errors) ? false:true;
        }
    }