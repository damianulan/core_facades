<?php

    namespace local_core_facades\Facades\Datatables;

    use Exception;
    use local_core_facades\Support\View;
    use local_core_facades\Http\Routing\Request;

    class DtCell
    {
        private $id;
        private $classes = [];
        private $contents;
        private $attributes;
        private $icons;

        public static function add(string $contents)
        {
            $instance = new self();
            $instance->contents = $contents;

            return $instance;
        }

        public static function addIcons(array $contents)
        {
            $instance = new self();
            $instance->contents = '<div class="action-icons">';
            foreach($contents as $icon){
                $instance->contents .= '<a href="javascript:void(0)" class="tippy '.$icon['class'].'" data-tippy="'.$icon['title'].'" data-modelid="'.$icon['id'].'">
                                            <i class="'.$icon['icon'].'"></i>
                                        </a>';
            }
            $instance->contents .= '</div>';
            return $instance;
        }

        public function getContent(): string
        {
            return $this->contents;
        }

        public function setMaxLength(int $characters): self
        {
            if(!empty($this->contents)){
                $in = $this->contents;
                $this->contents = strlen($in) > $characters ? substr($in,0,$characters)."..." : $in;
            }
            return $this;
        }

        public function getClasses()
        {
            return empty($this->classes) ? null:implode(' ', $this->classes);
        }

        public function addClass(string $class): self
        {
            $this->classes[] = $class;
            return $this;
        }

        public function setAttribute(string $attribute, string $value): self
        {
            $this->attributes[$attribute] = $value;
            return $this;
        }

        public function getAttributes():string 
        {
            $str = '';
            if(!empty($this->attributes)){
                foreach($this->attributes as $attribute => $value)
                {
                    $str .= " data-$attribute"."='$value'";
                }
            }
            return $str;
        }
    }