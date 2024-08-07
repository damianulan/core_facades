<?php

    namespace local_core_facades\Workshop\Interfaces;

    interface Spectator 
    {
        public function view($model);
        public function create($model);
        public function update($model);
        public function delete($model);
    }
