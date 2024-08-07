<?php

    namespace local_core_facades\Facades\Filters;

    use Exception;
    use local_core_facades\Http\Routing\Request;

    class FilterEngine 
    {
        public static function get(): array
        {
            global $CFG;

            $dir = $CFG->core_cache_dir;
            $filters = array();

            $cache_file = $dir.'/filters.json';
            if(file_exists($cache_file)){
                if(isset($CFG->filters)){
                    $filters = $CFG->filters;
                }
            } else {
                $locals = array_filter(glob(__DIR__.'/../../../../*'), 'is_dir');
                $collection = array();

                foreach($locals as $l){
                    $module = basename($l);
                    $files = glob($l.'/classes/Filters/*.php');

                    foreach($files as $file){
                        if(file_exists($file)){
                            $filename = basename($file, '.php');
                            $class = "\\local_$module\\Filters\\$filename";
                            if(class_exists($class)){
                                $collection[snake_case($filename)] = $class;
                            }

                        }
                    }

                }

                if(!empty($collection)){
                    $json = json_encode($collection, JSON_PRETTY_PRINT);
                    file_put_contents($cache_file, $json);
                }
            }

            if(empty($filters)){
                $contents = null;

                if(file_exists($cache_file)){
                    $contents = file_get_contents($cache_file);
                    $collection = json_decode($contents, true);
                    if(!empty($collection)){
                        foreach($collection as $name => $class){
                            if(class_exists($class)){
                                $filters[$name] = new $class();
                            }
                        }
                    }

                    $CFG->filters = $filters;
                }
            }

            return $filters;
        }

        public function process(Request $request)
        {
            global $SESSION;
            $status = 'ok';
            $message = 'ok';

            if($request->hasParam('formid')){
                $SESSION->filters[$request->parameters['formid']] = new FilterInstance($request);
            }

            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        }
    }