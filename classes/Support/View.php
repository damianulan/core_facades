<?php

    namespace local_core_facades\Support;

    use local_core_facades\Http\Routing\Request;
    use Exception;

    class View 
    {

        /**
         * @param string $path
         * searches in your local module path resources/views for views
         * provide by an e.g. ('pages.index').
         * @param array $data 
         * pass variables you want to use in your view.
         */
        public static function load (string $path, array $data = [], string $module = null)
        {
            $request = new Request();
            $core_root = $request->root."/local/core_facades";

            $header_file = $core_root."/resources/views/layout/header.php";
            $footer_file = $core_root."/resources/views/layout/footer.php";

            $pagetitle = null;
            $pageheading = null;
            $breadcrumbs = null;

            if(isset($data['pagetitle'])){
                $pagetitle = $data['pagetitle'];
                unset($data['pagetitle']);
            }
            if(isset($data['pageheading'])){
                $pageheading = $data['pageheading'];
                unset($data['pageheading']);
            }
            if(isset($data['breadcrumbs'])){
                $breadcrumbs = $data['breadcrumbs'];
                unset($data['breadcrumbs']);
            }

            $with_headers = !empty($pagetitle) && !empty($pageheading);

            ob_start();
            if($with_headers){
                require_once($header_file);
            }

            echo self::render($path, $request, $data, 'views', $module);
            if($with_headers){
                require_once($footer_file);
            }
            return ob_get_clean();
        }

        public static function loadComponent (string $path, array $data = [])
        {
            $request = new Request();
            return self::render($path, $request, $data, 'components');
        }

        private static function render (string $path, Request $request, array $data = [], string $type = 'views', string $module = null)
        {
            global $CFG;
            
            $subpath = str_replace('.','/',$path);
            $module_path = urldecode($request->route['module']['dir']);
            if(!is_null($module)){
                $module_path = $CFG->dirroot .'/local/'. $module;
            }
            $resource_file_path = $module_path."/resources/$type/$subpath.php";

            if(file_exists($resource_file_path))
            {
                if(!empty($data)){
                    extract($data);
                }
                ob_start();
                include($resource_file_path);
                return ob_get_clean();
            } else {
                throw new Exception('A path for the view ['.$path.'] was not found!');
            }
        }

        public static function modal (string $path, array $data = [], string $module = null)
        {
            global $CFG;
            
            $request = new Request();
            $subpath = str_replace('.','/',$path);
            $module_path = urldecode($request->route['module']['dir']);
            if(!is_null($module)){
                $module_path = $CFG->dirroot .'/local/'. $module;
            }
            $resource_file_path = $module_path."/resources/views/$subpath.php";
            $template_path = $CFG->dirroot .'/local/core_facades/resources/views/layout/modal.php';

            if(file_exists($resource_file_path) && file_exists($template_path))
            {
                if(!empty($data)){
                    extract($data);
                }
                ob_start();
                include($template_path);
                return ob_get_clean();
            } else {
                throw new Exception('A path for the view ['.$path.'] was not found!');
            }
        }

    }