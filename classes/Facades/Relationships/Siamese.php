<?php

    namespace local_core_facades\Facades\Relationships;

    use Exception;
    use local_core_facades\Facades\Relationships\Relationlibs;

    trait Siamese
    {
        protected static $initializedRelationships = [];

        public function hasMany($class, string $foreign_key = null, string $local_key = 'id'): HasMany
        {
            $this->makeFK($foreign_key, static::class);
            $relation = new HasMany(static::class, $class, $foreign_key, $local_key);
            return $relation;
        }

        public function belongsTo($class, string $foreign_key = null, string $local_key = 'id'): BelongsTo
        {
            $this->makeFK($foreign_key, $class);
            $relation = new BelongsTo(static::class, $class, $foreign_key, $local_key);

            return $relation;
        }

        private function makeFK(&$foreign_key, $class){
            if(is_null($foreign_key)){
                $foreign_key = snake_case(class_basename($class)) . '_id';
            }
        }

        final protected static function getJoins(): ?string
        {
            $join = null;
            $relationships = static::getRelationships();
            foreach($relationships as $method => $relation){
                if(method_exists($relation, 'getJoin')){
                    $join .= $relation->getJoin().PHP_EOL;
                }
            }

            return $join;
        }

        final protected static function getRelationships(): array
        {
            $instance = new static();
            if(isset($instance->relationships)){
                foreach($instance->relationships as $method){
                    if(method_exists($instance, $method)){
                        $relation = $instance->$method();

                        if($relation && $relation instanceof Relationlibs){
                            static::$initializedRelationships[$method] = $relation;
                        }
                    }
                }
            }

            return static::$initializedRelationships;
        }

    }