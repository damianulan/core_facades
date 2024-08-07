<?php

    namespace local_core_facades\Workshop\Features;

    use Exception;
    use local_core_facades\Facades\Policy;
    use local_core_facades\App\Model;

    trait Doctrine
    {
        protected static $policy = null;

        protected static function bootDoctrine()
        {
            if(is_null(static::$policy)){
                $base = class_basename(static::class);
                $core = class_corename(static::class);
                $base_observer = $base.'Policy';

                $class = "$core\\Policies\\$base_observer";

                if(class_exists($class)){
                    static::$policy = $class;
                }
            }
        }

        public function getPolicy(): Policy
        {
            if(static::$policy && class_exists(static::$policy) && $this instanceof Model){
                $policy_class = static::$policy;
                return new $policy_class();
            } else {
                throw new Exception("Polityka uprawnień dla modelu ". static::class ." nie istnieje.");
            }
        }

        public function policy(string $type, int $user_id = null): bool
        {
            if(static::$policy && class_exists(static::$policy) && $this instanceof Model){
                $policy_class = static::$policy;
                $user = user($user_id);
                if($user){
                    if(method_exists($policy_class, $type)){
                        $policy = new $policy_class();
                        $policy->_instance = $this;
                        $policy->id = $this->id;
                        $policy->user = $user;
                        return $policy->$type();
                    } else {
                        throw new Exception("Reguła '$type' nie istnieje dla polityki uprawnień modelu ". static::class .".");
                    }
                } else {
                    throw new Exception("Polityka uprawnień dla modelu ". static::class ." nie mogła zostać połączona z istniejącym użytkownikiem.");
                }
            } else {
                throw new Exception("Polityka uprawnień dla modelu ". static::class ." nie istnieje.");
            }
        }

    }