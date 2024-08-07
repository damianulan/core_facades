<?php

    namespace local_core_facades\Workshop\Features;

    use Exception;

    trait Stalker
    {
        protected static $observer = null;

        protected static function bootStalker()
        {

            if(is_null(static::$observer)){
                $base = class_basename(static::class);
                $core = class_corename(static::class);
                $base_observer = $base.'Observer';

                $class = "$core\\Observers\\$base_observer";

                if(class_exists($class)){
                    $rc = new \ReflectionClass($class);
                    if($rc){
                        if($rc->implementsInterface(\local_core_facades\Workshop\Interfaces\Spectator::class)){
                            $observer = new $class();
                            if($observer){
                                static::$observer = $observer;
                            }

                        } else {
                            throw new Exception("This observer [$class] does not implement Spectator interface!");
                        }
                    }
                }
            }

            $observer = static::$observer;

            if($observer){
                static::retrieved(function ($model) use($observer) {
                    $observer->view($model);
                });

                static::created(function ($model) use($observer) {
                    $observer->create($model);
                });

                static::updated(function ($model) use($observer) {
                    $observer->update($model);
                });

                static::deleted(function ($model) use($observer) {
                    $observer->delete($model);
                });
            }

        }

    }