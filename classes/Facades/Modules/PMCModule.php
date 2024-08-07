<?php

    namespace local_core_facades\Facades\Modules;

    use Exception;
    use local_core_facades\Http\Routing\URL;
    class FacadeModule 
    {

        public $name;
        public $moodle_name;
        public $url;
        public $dir;

        public function __construct(string $invoke)
        {
            global $CFG;
            $string = substr($invoke, 0, strpos($invoke, "\\"));
            $fullname = trim(trim($string, "\\"));
            if(strpos($fullname, "local_") !== false){
                $this->moodle_name = $fullname;
            }
            $this->name = substr($fullname, strpos($fullname, '_')+1);
            $this->url = urlencode($CFG->wwwroot.'/local/'.$this->name);
            $this->dir = $CFG->dirroot.'/local/'.$this->name;
        }
    }
