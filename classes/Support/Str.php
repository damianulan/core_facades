<?php

    namespace local_core_facades\Support;

    use Exception;
    use stdClass;
    use \Ramsey\Uuid\Uuid;
    use \voku\helper\ASCII;

    class Str
    {


        /**
         * Masks a portion of a string with a repeated character.
         *
         * @param  string  $string
         * @param  string  $character
         * @param  int  $index
         * @param  int|null  $length
         * @param  string  $encoding
         * @return string
         */
        public static function mask($string, $character, $index, $length = null, $encoding = 'UTF-8')
        {
            if ($character === '') {
                return $string;
            }

            $segment = mb_substr($string, $index, $length, $encoding);

            if ($segment === '') {
                return $string;
            }

            $strlen = mb_strlen($string, $encoding);
            $startIndex = $index;

            if ($index < 0) {
                $startIndex = $index < -$strlen ? 0 : $strlen + $index;
            }

            $start = mb_substr($string, 0, $startIndex, $encoding);
            $segmentLen = mb_strlen($segment, $encoding);
            $end = mb_substr($string, $startIndex + $segmentLen);

            return $start.str_repeat(mb_substr($character, 0, 1, $encoding), $segmentLen).$end;
        }

        /**
         * Get the string matching the given pattern.
         *
         * @param  string  $pattern
         * @param  string  $subject
         * @return string
         */
        public static function match($pattern, $subject)
        {
            preg_match($pattern, $subject, $matches);

            if (! $matches) {
                return '';
            }

            return $matches[1] ?? $matches[0];
        }


        /**
         * Determine if a given string matches a given pattern.
         *
         * @param  string|iterable<string>  $pattern
         * @param  string  $value
         * @return bool
         */
        public static function isMatch($pattern, $value)
        {
            $value = (string) $value;

            if (! is_iterable($pattern)) {
                $pattern = [$pattern];
            }

            foreach ($pattern as $pattern) {
                $pattern = (string) $pattern;

                if (preg_match($pattern, $value) === 1) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Generate a UUID (version 4).
         *
         * @return \Ramsey\Uuid\UuidInterface
         */
        public static function uuid()
        {
            return Uuid::uuid4();
        }

        /**
         * Transliterate a UTF-8 value to ASCII.
         *
         * @param  string  $value
         * @param  string  $language
         * @return string
         */
        public static function ascii($value, $language = 'en')
        {
            return ASCII::to_ascii((string) $value, $language);
        }

    }

    