<?php

    namespace local_core_facades\Facades\Relationships;

    use Exception;

    class Relationlibs
    {

        protected $prefix;
        protected $class;
        protected $foreign_class;
        protected $foreign_key;
        protected $local_key;

        public function __construct($class, $foreign_class, string $foreign_key, string $local_key)
        {
            global $CFG;
            if(!class_exists($foreign_class)){
                throw new Exception("Model obcy [$foreign_class] nie został odnaleziony, prawdopodobnie nie istnieje.");
            }
            $fc = new $foreign_class();
            $this->prefix = $CFG->prefix;
            $this->class = $class;
            $this->foreign_class = $foreign_class;
            $this->foreign_key = $foreign_key;
            $this->local_key = $local_key;
        }

        public function get()
        {

        }
    }