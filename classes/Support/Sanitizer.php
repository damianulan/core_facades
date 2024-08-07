<?php

    namespace local_core_facades\Support;

    class Sanitizer
    {
        const FILTERS = [

        ];

        public function __construct()
        {

        }

        public static function cleanCookie(string $cookie): string 
        {
            return htmlspecialchars(strip_tags($cookie));
        }

        public static function cleanText (string $text)
        {
            $array = [
                "=", ";", "'", "\"", "--", "#", "*",
                "onbeforeonload", "onafterprint", "onabort", "onbeforeprint",
                "onblur", "oncanplay", "oncanplaythrough", "onchange", "onclick",
                "oncontextmenu", "ondblclick", "ondrag", "ondragend", "ondragenter",
                "ondragleave", "ondragover", "ondragstart", "ondrop", "ondurationchange",
                "onemptied", "onended", "onerror", "onfocus", "onformchange", "onforminput",
                "onhaschange", "oninput", "oninvalid", "onkeydown", "onkeypress", "onkeyup",
                "onload", "onloadeddata", "onloadedmetadata", "onloadstart", "onmessage",
                "onmousedown", "onmousemove", "onmouseout", "onmouseover", "onmouseup",
                "onmousewheel", "onoffline", "onoine", "ononline", "onpagehide",
                "onpageshow", "onpause", "onplay", "onplaying", "onpopstate", "onprogress",
                "onratechange", "onreadystatechange", "onredo", "onresize", "onscroll",
                "onseeked", "onseeking", "onselect", "onstalled", "onstorage",
                "onsubmit", "onsuspend", "ontimeupdate", "onundo", "onunload",
                "onvolumechange", "onwaiting"
            ];
            return str_replace($array,'',$text);
        }

        public static function cleanInput (string $attribute)
        {
            return trim(strip_tags(self::cleanText($attribute)));
        }

    }