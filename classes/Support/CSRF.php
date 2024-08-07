<?php

namespace local_core_facades\Support;

use Exception;

class CSRF
{
    public static function GetCSRF(): ?string
    {
        $csrf = null;

        $csrf_token = optional_param('csrf', NULL, PARAM_TEXTCLEAN);
        $sesskey = optional_param('sesskey', NULL, PARAM_TEXTCLEAN);

        $headers = getallheaders();

        if ($csrf_token && $csrf_token !== '') {
            $csrf = $csrf_token;
        }

        if (isset($headers['X-CSRF-TOKEN'])) {
            $csrf = $headers['X-CSRF-TOKEN'];
        }

        if (isset($headers['X-Csrf-Token'])) {
            $csrf = $headers['X-Csrf-Token'];
        }

        if ($csrf == null) {
            $csrf = $sesskey;
        }

        return $csrf;
    }

    public static function validate($originKey, $requestKey): bool
    {
        if($requestKey === $originKey){
            return true;
        } else {
            throw new Exception("Invalid form! Incorrect Facade session key.");
        }

        return false;
    }

    public static function csrf_input() 
    {
        return '<input type="hidden" name="_token" value="'.sesskey().'">';
    }
}