<?php

    namespace local_core_facades\Facades\Datatables;

    use Exception;
    use local_core_facades\Support\View;
    use local_core_facades\Http\Routing\Request;

    class DtHeader
    {
        private $id;
        private $classes = ['header'];
        private $text;
        private $width = 'auto';
        private $sortable = false;
        private $sortingKey = null;

        public static function add(string $text)
        {
            $instance = new self();
            $instance->text = $text;

            return $instance;
        }

        public function getText(): string
        {
            return $this->text;
        }

        public function setWidth(string $width): self
        {
            $this->width = $width;
            return $this;
        }

        public function getWidth()
        {
            return $this->width;
        }

        public function getClasses(): string 
        {
            return implode(' ', $this->classes);
        }

        public function addClass(string $class): self
        {
            $this->classes[] = $class;
            return $this;
        }

        public function sortable(string $sorting_key): self
        {
            $this->sortable = true;
            $this->sortingKey = $sorting_key;
            return $this;
        }

        public function isSortable(): bool
        {
            return $this->sortable;
        }
    }