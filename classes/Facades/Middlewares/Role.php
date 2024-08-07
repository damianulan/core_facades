<?php

    namespace local_core_facades\Facades\Middlewares;

    use local_core_facades\App\Models\User;
    class Role 
    {
        public static function boot(string $value): bool
        {
            $user = user();
            if(!$user->hasRole($value)){
                response()->abort(403);
            }

            return true;
        }
    }