<?php

    namespace local_core_facades\Facades\Middlewares;

    class Permission 
    {
        public static function boot(string $value): bool
        {
            $user = user();
            if(!$user->hasPermission($value)){
                response()->abort(403);
            }

            return true;
        }
    }