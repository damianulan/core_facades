<?php

    namespace local_core_facades\Facades\Components;

    class Breadcrumbs
    {
        private $nodes = [];

        public static function make(): self
        {
            return new self();
        }

        public function node(string $title, string $url): self
        {
            $node = new \stdClass();
            $node->title = $title;
            $node->link = $url;
            $this->nodes[] = $node;
            return $this;
        }

        public function render()
        {
            $html = '';
            if(empty($this->nodes)){
                echo $html;
            } else {
                $lastKey = count($this->nodes) - 1;
                $html = '
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">';
                foreach($this->nodes as $key => $node){
                    if($key == $lastKey){
                        $html .= '
                        <li class="breadcrumb-item active" aria-current="page"><a href="'.$node->link.'">'.$node->title.'</a></li>
                        ';
                    } else {
                        $html .= '
                        <li class="breadcrumb-item"><a href="'.$node->link.'">'.$node->title.'</a></li>
                        ';
                    }
                }
                $html .= 
                    '</ol>
                </nav>';
    
                echo $html;
            }
            return;
        }
    }