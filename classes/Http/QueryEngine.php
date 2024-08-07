<?php

    namespace local_core_facades\Http;

    use local_core_facades\Support\FilterType;
    use local_core_facades\Support\Sanitizer;
    class QueryEngine
    {
        
        public static function normalizeQueryString($qs)
        {
            if ('' === ($qs ?? '')) {
                return '';
            }

            $qs = strip_tags($qs);
            parse_str($qs, $qs);
            ksort($qs);

            return http_build_query($qs, '', '&', \PHP_QUERY_RFC3986);
        }

        // TODO - test with inputs
        public static function normalizeParameters($params): ?array
        {
            $unprotected = [
                'description',
                'opis',
                'comment',
            ];

            if(count($params)){
                $output = [];
                ksort($params);
                foreach($params as $key => $param)
                {
                    $k = Sanitizer::cleanInput($key);

                    if(FilterType::isJson($param)){
                        $param = json_decode($param, true);
                    }

                    if(!is_array($param))
                    {
                        $val = null;
                        if(in_array($key, $unprotected)){
                            $val = s($param);
                        } else {
                            $val = Sanitizer::cleanInput($param);
                        }
                        $p = self::assignParameterType($val);
                    } else {
                        $p = self::redirectArrayParam($param);
                    }

                    $output[$k] = $p;
                }
                return $output;
            }
            return null;
        }

        public static function assignParameterType (string $param)
        {
            if(is_numeric($param)){
                if(FilterType::isInteger($param)){
                    return (int) $param;
                }
                if(FilterType::isFloat($param)){
                    return (float) $param;
                }
            }

            if(FilterType::isBoolean($param)){
                return (bool) $param;
            }
            if(FilterType::isURL($param)){
                return http_build_query((object)$param, '', '&', \PHP_QUERY_RFC3986);
            }

            return htmlspecialchars($param, ENT_QUOTES);

        }

        private static function redirectArrayParam(array $param)
        {
            return self::normalizeParameters($param);
        }


    }