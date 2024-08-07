<?php

    namespace local_core_facades\Http\Routing\Bag;

    use Exception;
    use local_core_facades\Support\Sanitizer;
    use local_core_facades\Http\Routing\URL;
    use local_core_facades\Http\QueryEngine;

    class ParameterBag 
    {

        public function __construct()
        {
            $params = QueryEngine::normalizeParameters($_REQUEST);

            foreach($params as $key => $param){
                $this->$key = $param;
            }
        }
    }