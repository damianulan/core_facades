<?php

    namespace local_core_facades\Facades\Filters;

    use local_core_facades\Facades\Filters\FilterEngine;
    use local_core_facades\Support\View;

    class FilterBuilder implements \Countable
    {
        protected string $uuid;
        private int $columns = 2;
        private bool $hidden = false;
        private ?string $alias = null;
        private $onFilter = null;

        protected array $objects = [];
        protected array $params = [];
        protected ?string $where = null;
        protected ?string $having = null;

        public function __construct(?string $uuid = null)
        {
            if(empty($uuid)){
                $uuid = 'filter_'.uuid();
            } else {
                $uuid = 'filter_'.$uuid;
            }
            $this->uuid = $uuid;
        }

        public function render()
        {
            $view = $this->getView();
            if($view){
                echo $view;
            }
        }

        public function scripts()
        {
            echo View::load('facades.filters.scripts', [
                'onFilter' => $this->onFilter,
                'uuid' => $this->uuid,
            ], 'core_facades');
        }

        private function getView()
        {
            $col = $this->columns;

            $filters = array();
            $i = 1;
            foreach($this->objects as $name => $filter){
                if($i > $col){
                    $i = 1;
                }
                $filter->setAlias($this->alias);

                $filters[$i][$name] = $filter;
                $i++;
            }

            return View::load('facades.filters.'.$col.'columns', [
                'uuid' => $this->uuid,
                'filters' => $filters,
                'builder' => $this,
            ], 'core_facades');
        }

        public function count(): int
        {
            return count($this->objects);
        }

        public static function boot(array $collection, string $uuid = null): self
        {
            $instance = new self($uuid);
            $filters = FilterEngine::get();

            foreach($collection as $key => $options)
            {

                if(isset($filters[$key])){
                    $filter = $filters[$key];

                    foreach($options as $option => $value){
                        if(property_exists($filter, $option)){
                            $filter->$option = $value;
                        }
                    }
                    
                    $instance->objects[$key] = $filter;
                }
            }

            return $instance;
        }

        public function loadConditions(): self
        {
            global $SESSION, $DB;

            $this->params = [];
            $this->where = null;
            $this->having = null;

            if(isset($SESSION->filters[$this->uuid])){
                $instance = $SESSION->filters[$this->uuid];
                if(count($instance)){
                    $filtered = $instance->toArray();

                    $i = 1;
                    foreach($filtered as $key => $value){
                        if(isset($this->objects[$key])){
                            $filter = $this->objects[$key];
                            $param_key = "filter_param_$i";
                            $condition = $filter->having ? 'having':'where';

                            switch($filter->type){
                                case FilterType::SELECT:
                                    if(is_array($value)){
                                        [$insql, $inparams] = $DB->get_in_or_equal($value, SQL_PARAMS_NAMED, 'filter_param_');
                                        $this->params = array_merge($inparams, $this->params);
                                        $this->$condition .= PHP_EOL." AND ".$filter->field()." $insql ";
                                    } else {
                                        $this->params[$param_key] = $value;
                                        $this->$condition .= PHP_EOL." AND ".$filter->field()." = $param_key ";
                                    }
                                    break;
                            }

                            $i++;
                        }
                    }
                }
            }

            return $this;
        }


        // GETTERS AND SETTERS
        
        public function columns(int $columns): self
        {
            $this->columns = $columns;
            return $this;
        }

        public function getColumns(): int
        {
            return $this->columns;
        }

        public function isVisible(): bool
        {
            return !$this->hidden;
        }

        public function isHidden(): bool
        {
            return $this->hidden;
        }

        public function hide(): self
        {
            $this->hidden = true;
            return $this;
        }

        public function show(): self
        {
            $this->hidden = false;
            return $this;
        }

        public function alias(string $alias): self
        {
            $this->alias = $alias.'_';
            return $this;
        }

        public function getAlias(): ?string
        {
            return $this->alias;
        }

        public function onFilter(string $js_func): self
        {
            $this->onFilter = $js_func;
            return $this;
        }

        public function getParams(): array
        {
            return $this->params;
        }

        public function getWhere(): ?string
        {
            return $this->where;
        }

        public function getHaving(): ?string
        {
            return $this->having;
        }
    }