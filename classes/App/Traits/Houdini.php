<?php

    namespace local_core_facades\App\Traits;

    Trait Houdini
    {
        public function show(): self
        {
            $this->active = 1;
            return $this;
        }

        public function hide(): self
        {
            $this->active = 0;
            return $this;
        }

        public function toggleVisibility(): self
        {
            if($this->active == 1){
                $this->active = 0;
            } else {
                $this->active = 1;
            }

            return $this;
        }

        public static function allActive(): array
        {
            return self::where(['active' => 1]);
        }


    }