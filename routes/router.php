<?php
function core_cache_dir()
{
    global $CFG;
    if (isset($CFG->core_cache_dir)){
        return $CFG->core_cache_dir;
    }
    $datas = $CFG->dataroot . '\\core_facades';
    if(!is_dir($datas)){
        mkdir($datas, 0777, true);
    }
    $CFG->core_cache_dir = $datas;
    return $datas;
}

function core_cache_clean(): bool
{
    $dir = core_cache_dir();
    if (is_dir($dir)) { 
        $objects = scandir($dir);
        foreach ($objects as $object) { 
          if ($object != "." && $object != "..") { 
            if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                core_cache_clean($dir. DIRECTORY_SEPARATOR .$object);
            else
                unlink($dir. DIRECTORY_SEPARATOR .$object); 
          } 
        }
        $result = rmdir($dir);
        if($result){
            return true;
        }
    }
    return false;
}

function load_routes()
{
    global $CFG;
    $routes = [];
    $cache_file = core_cache_dir().'/routes.json';
    if(isset($CFG->routes) && file_exists($cache_file)){
        $routes = $CFG->routes;
    } elseif (file_exists($cache_file)){
        $routes = json_decode(file_get_contents($cache_file), true);
        $CFG->routes = $routes;
    }
    else {
        $locals = array_filter(glob(__DIR__.'/../../*'), 'is_dir');
        foreach($locals as $l){
            $dir = $l.'/routes/web.php';
            if(file_exists($dir)){
                $route = include($dir);
    
                if(is_array($route) && !empty($route)){
                    foreach($route as $r){
                        $routes[$r->path] = $r;
                    }
                }
            }
        }
    
        $json = json_encode($routes, JSON_PRETTY_PRINT);
        file_put_contents($cache_file, $json);
        $routes = json_decode($json, true);
        $CFG->routes = $routes;
    }

    return $routes;
}
