<?php

    namespace local_core_facades\Http;

    use local_core_facades\Support\View;
    class Response
    {

        private $statusCode;
        private $code;
        private $headers = [];
        private $content;

        public static array $statusTexts = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',            // RFC2518
            103 => 'Early Hints',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',          // RFC4918
            208 => 'Already Reported',      // RFC5842
            226 => 'IM Used',               // RFC3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',    // RFC7238
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Content Too Large',                                           // RFC-ietf-httpbis-semantics
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',                                               // RFC2324
            421 => 'Misdirected Request',                                         // RFC7540
            422 => 'Unprocessable Content',                                       // RFC-ietf-httpbis-semantics
            423 => 'Locked',                                                      // RFC4918
            424 => 'Failed Dependency',                                           // RFC4918
            425 => 'Too Early',                                                   // RFC-ietf-httpbis-replay-04
            426 => 'Upgrade Required',                                            // RFC2817
            428 => 'Precondition Required',                                       // RFC6585
            429 => 'Too Many Requests',                                           // RFC6585
            431 => 'Request Header Fields Too Large',                             // RFC6585
            451 => 'Unavailable For Legal Reasons',                               // RFC7725
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',                                     // RFC2295
            507 => 'Insufficient Storage',                                        // RFC4918
            508 => 'Loop Detected',                                               // RFC5842
            510 => 'Not Extended',                                                // RFC2774
            511 => 'Network Authentication Required',                             // RFC6585
        ];

        public function __construct(int $code = 200, $content = null)
        {
            $this->code = $code;
            $this->content = $content;
        }

        public function setHeader(string $name, string $value)
        {
            $this->headers[$name][] = $value;
            return $this;
        }

        public function redirect(string $url)
        {
            $this->setHeader('Location', $url);
            return $this->execute();
        }

        public function json(array $data = [])
        {
            $this->setHeader('Content-Type', 'application/json');
            $this->content = json_encode($data);
            return $this->execute();
        }

        public function view(string $path, array $data = [])
        {
            $this->setHeader('Content-Type', 'text/html');
            $this->content = View::load($path, $data);
            return $this->execute();
        }

        public function modal(string $path, array $data = [])
        {
            $this->setHeader('Content-Type', 'application/json');
            $view = View::modal($path, $data);
            $status = 'error';
            $message = 'error';
            if($view){
                $status = 'ok';
                $message = 'ok';
            }
            $this->content = json_encode([
                'status' => $status,
                'message' => $message,
                'view' => $view
            ]);
            return $this->execute();
        }

        public function route(string $name)
        {
            $url = route($name);
            return $this->redirect($url);
        }

        public function with(string $messagetype, string $messagetext)
        {
            return $this;
        }

        public function abort(int $code)
        {
            $this->code = $code;
            return $this->execute();
        }

        private function setStatusCode() 
        {
            $code = $this->code;
            $text = self::$statusTexts[$code];
            $this->statusCode = "HTTP/1.1 $code $text";
        }

        private function execute()
        {
            $this->setStatusCode();
            http_response_code($this->code);
            header($this->statusCode);

            if(empty($this->headers)){
                $this->setHeader('Content-Type', 'text/html');
            }

            foreach ($this->headers as $type => $headers)
            {
                foreach($headers as $value){
                    $text = "$type: $value;";
                    if($type === 'Content-Type'){
                        $text .= " charset=utf8";
                    }
                    header($text);
                }
            }

            echo $this->content;
            exit;
        }
    }