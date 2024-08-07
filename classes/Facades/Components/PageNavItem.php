<?php

    namespace local_core_facades\Facades\Components;

    class PageNavItem
    {
        public $title;
        public $route;
        public $active = false;
        public $visible = true;

        public function __construct(string $title, string $routename)
        {
            $current = request()->route;

            if($current['path'] === $routename){
                $this->active = true;
            }

            $this->route = route($routename);
            $this->title = $title;
        }

    }