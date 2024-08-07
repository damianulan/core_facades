<?php

    namespace local_core_facades\Support;

    use Exception;
    use ReflectionClass;

    abstract class Enum
    {
        final public static function values(): array
        {
            $rc = new ReflectionClass(static::class);
            return $rc->getConstants();
        }

        final public static function keys(): array
        {
            return array_keys(self::values());
        }

        abstract public static function langs(): array;

        final public static function getLang(string $key): ?string
        {
            $langs = static::langs();

            if(isset($langs[$key])){
                return $langs[$key];
            }
            return null;
        }
    }