<?php

    namespace local_core_facades\Facades\Filters;

    use Exception;
    use local_core_facades\Http\Routing\Request;

    class FilterInstance implements \Countable
    {
        public function __construct(Request $request)
        {
            $available = array_keys(FilterEngine::get());

            if(!empty($available) && isset($request->parameters['formid'])){
                $parameters = array_filter($request->parameters, function ($p) use($available){
                    return in_array($p, $available);
                }, ARRAY_FILTER_USE_KEY);

                foreach($parameters as $key => $param){
                    $this->$key = $param;
                }
            }            
        }

        public function count(): int 
        {
            $parameters = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
            return count($parameters);
        }

        public function toArray(): array
        {
            $output = array();
            $parameters = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
            if(!empty($parameters)){
                foreach($parameters as $param){
                    $name = $param->name;
                    $output[$name] = $this->$name;
                }
            }
            return $output;
        }
    }