<?php

    namespace local_core_facades\Support;

    use Exception;
    use stdClass;

    class Arr
    {
        /**
         * Joins two arrays into one sequential array. Avoids keys' conflicts. Alternative to array_merge().
         */
        public static function join(array $array1, array $array2): array
        {
            $output = [];
            foreach($array1 as $arr){
                $output[] = $arr;
            }
            foreach($array2 as $arr){
                $ouptut[] = $arr;
            }
            return $output;
        }

        /**
         * Joins two arrays into one associative array. In case keys' conflicts occur, the second array takes priority. Alternative to array_merge().
         */
        public static function join_assoc(array $array1, array $array2): array
        {
            $output = [];
            foreach($array1 as $key => $arr){
                $output[$key] = $arr;
            }
            foreach($array2 as $key => $arr){
                $ouptut[$key] = $arr;
            }
            return $output;
        }

        /**
         * @param array $arr
         * Determines whether array keys are associative (true) or sequential (false).
         */
        public static function is_assoc(array $arr)
        {
            if (array() === $arr) return false;
            return array_keys($arr) !== range(0, count($arr) - 1);
        }

        public static function sort_int(array $arr, string $field)
        {
            usort($arr, function ($a, $b) use($field){
                $a = (array) $a;
                $b = (array) $b;
                return (int)$a[$field] > (int)$b[$field];
            });
            return $arr;
        }
    
        public static function sort_string(array $arr, string $field)
        {
            usort($arr, function ($a, $b) use($field){
                $a = (array) $a;
                $b = (array) $b;
                return strnatcmp($a[$field], $b[$field]);
            });
            return $arr;
        }
    
        public static function equal(array $arr1, array $arr2): bool
        {
            return (count(array_diff($arr1, $arr2))+count(array_diff($arr2, $arr1))) === 0;
        }

    }

    