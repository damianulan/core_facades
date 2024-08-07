<?php

    namespace local_core_facades\Facades\Filters\Components;

    use Exception;
    use ReflectionClass;
    use local_core_facades\Support\View;

    abstract class Component 
    {
        public $type;
        public bool $disabled = false;
        public ?string $label = null;

        public bool $having = false;
        protected ?string $alias = null;
        abstract public function field(): string;
        abstract public function label(): string;

        public function name(): ?string
        {
            $rc = new ReflectionClass(static::class);

            if($rc){
                return snake_case($rc->getShortName());
            }
            return null;
        }

        public function setAlias(?string $alias): static
        {
            $this->alias = $alias;
            return $this;
        }

        public function alias(): ?string
        {
            return $this->alias;
        }

        public function render()
        {
            if(is_null($this->label)){
                $this->label = $this->label();
            }
            $view = $this->getView();
            if($view) {
                echo $view;
            }
        }

        private function getView()
        {
            return View::load('facades.filters.components.'.$this->type, [
                'object' => $this,
            ], 'core_facades');
        }
    }