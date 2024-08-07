<?php
use local_core_facades\Http\Routing\Request;
use local_core_facades\Http\Session;
use local_core_facades\Support\View;
use local_core_facades\Http\Response;

    /**
     * Zastępuje przecinek kropką - dla obliczeń na wyrażeniach floatowych.
     */
    function replaceComma(string $str)
    {
        return str_replace(',', '.', $str);
    }

    /**
     * Zastępuje kropkę przecinkiem - dla wyświetlania wyrażeń floatowych.
     */
    function replaceDot(string $str)
    {
        return str_replace('.', ',', $str);
    }

    /**
     * Reformatuje każdego stringa do formatu snake_case.
     */
    function snake_case(string $input): string 
    {
        $input = preg_replace('/\s+/', '_', $input); // Replace spaces with underscores
        $input = preg_replace('/[^a-zA-Z0-9_]/', '', $input); // Remove non-alphanumeric characters
        $input = preg_replace('/([a-z])([A-Z])/', '$1_$2', $input); // Convert camelCase to snake_case
        return strtolower($input);
    }

    /**
     * Stripuje wszelkie nawiasy ze stringa.
     */
    function remove_parentheses(string $input): string
    {
        return preg_replace('/\([^)]*\)/', '', $input);    
    }

    /**
     * @param string $property
     * @param array $collection
     * Zwraca tablicę zawierającą tylko wskazane argumenty z tablicy obiektów, lub tablicy zagnieżdżonej.
     */
    function pluck(string $property, $collection): array
    {
        $result = array();
        if(!is_array($collection)){
            $collection = (array)$collection;
        }
        if(is_array($collection) && count($collection)){
            foreach($collection as $instance){
                if(is_array($instance)){
                    if(isset($instance[$property])){
                        $result[] = $instance[$property];
                    }
                } 
                elseif (is_object($instance)) {
                    if(isset($instance->$property)){
                        $result[] = $instance->$property;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Generuje zrzut zawartości zmiennych w formacie JSON.
     */
    function dump()
    {
        foreach(func_get_args() as $var){
            echo '<pre>'.json_encode($var, JSON_PRETTY_PRINT).'</pre>';
        }
    }

    /**
     * Sprawdza czy tablica zawiera inne wartości niż puste stringi
     */
    function multi_array_has_values(?array $arr, bool $strict = false): bool
    {
        if($arr && count($arr)){
            foreach ($arr as $item){
                if(is_array($item)){
                    return multi_array_has_values($item);
                } else {
                    if(($item && !empty($item)) || (!$strict && $item === false) || (!$strict && $item === 0) || (!$strict && $item === '0')){
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Ukryty input z wartością CSRF.
     */
    function csrf_input(): string
    {
        return '<input type="hidden" name="_token" class="form-input" value="'.sesskey().'"/>';
    }

    /**
     * Generuje ikonkę sortowania dla nazw kolumn tabel.
     */
    function sort_icon(string $order): string
    {
        $dir_icon = '';
        if($order=='ASC') {
            $dir_icon = '<i class="bi bi-caret-up-fill iconsort" title="Malejąco" aria-label="Malejąco"></i>';
        } else {
            $dir_icon = '<i class="bi bi-caret-down-fill iconsort" title="Rosnąco" aria-label="Rosnąco"></i>';
        }
        return $dir_icon;
    }

    function hasDateTimeConflict($array1, $array2) : array
    {
        // Iterate through each range in the first array
        $conflicted = array();
        foreach ($array1 as $range1) {
            $start1 = new DateTime($range1['start']);
            $end1 = new DateTime($range1['end']);
    
            // Iterate through each range in the second array
            foreach ($array2 as $key => $range2) {
                $start2 = new DateTime($range2['start']);
                $end2 = new DateTime($range2['end']);
    
                // Check for conflicts
                if (($start1 <= $end2 && $start1 >= $start2) || ($end1 >= $start2 && $end1 <= $end2) 
                   || ($start1 <= $start2 && $end1 >= $end2) || ($start2 <= $start1 && $end2 >= $end1)) {
                    $conflicted[$key] = $range2;
                }
            }
        }
    
        return $conflicted;
    }

    /**
     * Returns a global instance of current Router object.
     */
    function router()
    {
        $router = $GLOBALS['router'];
        if($router){
            return $router;
        }
        return null;
    }

    /**
     * Returns a set of routes for whole Facade applications.
     */
    function routes()
    {
        $router = $GLOBALS['router'];
        if(!empty($router->routes)){
            return $router->routes;
        } else {
            throw new Exception('No Core Facades routes available!');
        }
        return null;
    }

    function isValidTimestamp($timestamp)
    {
        return ((int) $timestamp == $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX)
        && (!strtotime($timestamp))
        && (strlen((string) $timestamp) === 10);
    }

    /**
     * Returns a object instance of a currently logged user or the one pointed by id.
     */
    function user(int $userid = null): ?\local_core_facades\App\Models\User
    {
        global $USER, $SESSION;
        $uid = $USER->id;
        $user = null;
        if($userid && $userid > 1){
            $uid = $userid;
            $user = \local_core_facades\App\Models\User::get($uid);
        } else {
            if(!isset($SESSION->core_user)){
                $SESSION->core_user = \local_core_facades\App\Models\User::get($uid);
            }
            $user = $SESSION->core_user;
        }

        if(is_null($user)){
            throw new Exception("User [$uid] not found!");
        }
        return $user;
    }

    function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
                    mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
                    mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
                    mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get names of the function in your current scope to retrieve associative array with the names of the parameters
     * @param string $funcName
     */
    function get_func_arg_names(string $funcName) {
        $f = new ReflectionFunction($funcName);
        $result = array();
        foreach ($f->getParameters() as $param) {
            $result[] = $param->name;   
        }
        return $result;
    }

    function blank($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }

    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param  object|string  $class
     * @return array
     */
    function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }

    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param  string  $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }

    function class_corename($class)
    {
        return explode("\\",(string)$class)[0];
    }

    /**
     * Get current function name
     */
    function current_func()
    {
        if(isset(debug_backtrace()[1]['function'])){
            return debug_backtrace()[1]['function'];
        }
        return null;
    }

    /**
     * Get parent function which redirected you to the present
     */
    function parent_caller_func()
    {
        if(isset(debug_backtrace()[2]['function'])){
            return debug_backtrace()[2]['function'];
        }
        return null;
    }

    /**
     * Get parent object of origin
     */
    function parent_caller_object()
    {
        if(isset(debug_backtrace()[2]['object'])){
            return debug_backtrace()[2]['object'];
        }
        return null;
    }

    /**
     * Get parent class name of origin
     */
    function parent_caller_class()
    {
        if(isset(debug_backtrace()[2]['class'])){
            return debug_backtrace()[2]['class'];
        }
        return null;
    }

    function request(): Request
    {
        return new Request();
    }

    function response(int $code = 200, $content = null): Response
    {
        return new Response($code, $content);
    }

    /**
     * returns old instance of Facade request. This is being overwritten by the next request.
     */
    function request_old(): Request
    {
        $old = Session::get('old');
        return $old['request'];
    }

    function old(string $param = null)
    {
        $old = Session::get('old');
        if($old && $param){
            if(isset($old->parameters[$param])){
                return $old->parameters[$param];
            }
        }
        return null;
    }

    /**
     * @param string $name
     * checks if current route is the same as one given in the parameter. Use this function in a View.
     */
    function routeIs(string $name): bool
    {
        $request = Request::instance();
        if($request->route['name'] === $name){
            return true;
        }
        return false;
    }

    /**
     * returns decoded URL based on the Facade Route name.
     */
    function route(string $name, array $params = [])
    {
        $router = $GLOBALS['router'];
        if(isset($router->routes[$name])){
            $url = urldecode($router->routes[$name]['url']);
            if(!empty($params)){
                $url = url_add_params($url, $params);
            }
            return $url;
        }
        throw new Exception("Following route: [".$name."] doesn't exist!");
    }

    function url_add_params(string $url, array $params = [])
    {
        $url_parts = parse_url($url);

        if (isset($url_parts['query'])) {
            parse_str($url_parts['query'], $paramsOrig);
        } else {
            $paramsOrig = array();
        }
        
        foreach($params as $key => $param){
            $paramsOrig[$key] = $param;
        }

        $url_parts['query'] = http_build_query($paramsOrig);
        return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . '?' . $url_parts['query'];
    }

    function clean_input($value)
    {
        return \local_core_facades\Support\Sanitizer::cleanInput($value);
    }

    /**
     * @param string $path
     * searches in your local module path resources/views for views
     * provide by an e.g. ('pages.index').
     * @param string $page_title is a page title (browser card).
     * @param string $page_heading is a heading of the page.
     * @param array $data 
     * pass variables you want to use in your view.
     */
    function view(string $path, array $data = [])
    {
        return View::load($path, $data);
    }

    function component(string $path, array $data = [])
    {
        return View::loadComponent($path, $data);
    }

    function abort(int $code)
    {
        return response()->abort($code);
    }

    /**
     * Wskazuje langi z akutalnej wtyczki lokalnej.
     */
    function lang(string $key, $options = null)
    {
        $module = request()->core_module;
        return get_string($key, 'local_'.$module, $options);
    }

    function core_lang(string $key, $options = null)
    {
        return get_string($key, 'local_core_facades', $options);
    }

    function development()
    {
        global $CFG;
        return (bool) $CFG->debugdeveloper;
    }